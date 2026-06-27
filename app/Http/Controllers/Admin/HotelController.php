<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HotelController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $query = Hotel::withStats()->withCount(['rooms', 'bookings']);

        if ($search = $request->get('search')) {
            $searchLower = mb_strtolower($search);
            $query->where(function ($q) use ($searchLower) {
                $q->where(\Illuminate\Support\Facades\DB::raw('LOWER(name)'), 'like', "%{$searchLower}%")
                  ->orWhere(\Illuminate\Support\Facades\DB::raw('LOWER(city)'), 'like', "%{$searchLower}%");
            });
        }

        $hotels = $query->latest()->paginate(15)->withQueryString();

        return view('admin.hotels.index', compact('hotels'));
    }

    public function create()
    {
        return view('admin.hotels.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateHotel($request);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('hotels', 'public');
        }

        if ($request->hasFile('gallery')) {
            $images = [];
            foreach ($request->file('gallery') as $file) {
                $images[] = $file->store('hotels/gallery', 'public');
            }
            $validated['images'] = $images;
        }

        $hotel = Hotel::create(array_merge($validated, ['created_by' => Auth::id()]));

        return redirect()->route('admin.hotels.index')
            ->with('success', 'Hotel "' . $hotel->name . '" created successfully.');
    }

    public function show(Hotel $hotel)
    {
        $hotel->loadCount(['rooms', 'bookings']);

        // Calculate basic statistics
        $activeBookingsCount = $hotel->bookings()->whereIn('status', ['confirmed', 'checked_in'])->count();
        $cancelledBookingsCount = $hotel->bookings()->where('status', 'cancelled')->count();
        $cancellationRate = $hotel->bookings_count > 0 ? round(($cancelledBookingsCount / $hotel->bookings_count) * 100, 1) : 0;

        $totalRevenue = (float) $hotel->bookings()->whereNotIn('status', ['cancelled'])->sum('total_price');
        $avgStayNights = round($hotel->bookings()->whereNotIn('status', ['cancelled'])->avg('nights') ?? 0, 1);
        $avgRevenuePerBooking = $hotel->bookings_count - $cancelledBookingsCount > 0 
            ? round($totalRevenue / ($hotel->bookings_count - $cancelledBookingsCount), 2) 
            : 0;

        // Fetch 12-month trend data
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $yearExpr = match ($driver) {
            'sqlite' => 'CAST(strftime(\'%Y\', bookings.created_at) AS INTEGER)',
            'mysql' => 'YEAR(bookings.created_at)',
            default => 'EXTRACT(YEAR FROM bookings.created_at)',
        };
        $monthExpr = match ($driver) {
            'sqlite' => 'CAST(strftime(\'%m\', bookings.created_at) AS INTEGER)',
            'mysql' => 'MONTH(bookings.created_at)',
            default => 'EXTRACT(MONTH FROM bookings.created_at)',
        };

        $monthlyStats = $hotel->bookings()
            ->select(
                \Illuminate\Support\Facades\DB::raw("$yearExpr as year"),
                \Illuminate\Support\Facades\DB::raw("$monthExpr as month"),
                \Illuminate\Support\Facades\DB::raw('SUM(total_price) as revenue'),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as count')
            )
            ->whereNotIn('status', ['cancelled'])
            ->where('bookings.created_at', '>=', now()->subMonths(12))
            ->groupByRaw("$yearExpr, $monthExpr")
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(fn($row) => [
                'label'   => date('M Y', mktime(0, 0, 0, (int) $row->month, 1, (int) $row->year)),
                'revenue' => (float) $row->revenue,
                'count'   => (int) $row->count,
            ]);

        // Load rooms with their bookings count
        $hotel->load([
            'rooms' => function ($q) {
                $q->withCount('bookings');
            },
            'bookings' => function ($q) {
                $q->with(['user', 'room'])->latest('bookings.created_at')->limit(10);
            }
        ]);

        // Calculate occupancy rate (last 30 days) for each room
        foreach ($hotel->rooms as $room) {
            $occupiedNights = $room->bookings()
                ->whereNotIn('status', ['cancelled'])
                ->where('check_out', '>=', now()->subDays(30))
                ->where('check_in', '<=', now())
                ->sum('nights');
            $availableCapacity = $room->quantity * 30;
            $room->occupancy_rate = $availableCapacity > 0 
                ? min(round(($occupiedNights / $availableCapacity) * 100, 1), 100) 
                : 0;
        }

        // Generate 14-day occupancy grid data
        $calendarDates = [];
        for ($i = 0; $i < 14; $i++) {
            $calendarDates[] = now()->addDays($i)->format('Y-m-d');
        }

        $roomCalendar = [];
        foreach ($hotel->rooms as $room) {
            $dateData = [];
            foreach ($calendarDates as $dateString) {
                $bookedCount = $room->bookings()
                    ->whereNotIn('status', ['cancelled'])
                    ->where('check_in', '<=', $dateString)
                    ->where('check_out', '>', $dateString)
                    ->count();
                $dateData[$dateString] = [
                    'booked'    => $bookedCount,
                    'available' => max($room->quantity - $bookedCount, 0),
                    'percent'   => $room->quantity > 0 ? min(round(($bookedCount / $room->quantity) * 100), 100) : 0,
                ];
            }
            $roomCalendar[$room->id] = $dateData;
        }

        return view('admin.hotels.show', compact(
            'hotel', 
            'activeBookingsCount', 
            'cancelledBookingsCount', 
            'cancellationRate', 
            'totalRevenue', 
            'avgStayNights', 
            'avgRevenuePerBooking',
            'monthlyStats',
            'calendarDates',
            'roomCalendar'
        ));
    }

    public function edit(Hotel $hotel)
    {
        return view('admin.hotels.edit', compact('hotel'));
    }

    public function update(Request $request, Hotel $hotel)
    {
        $validated = $this->validateHotel($request, $hotel->id);

        if ($request->hasFile('image')) {
            // Remove old image
            if ($hotel->image) Storage::disk('public')->delete($hotel->image);
            $validated['image'] = $request->file('image')->store('hotels', 'public');
        }

        $hotel->update($validated);

        return redirect()->route('admin.hotels.index')
            ->with('success', 'Hotel updated successfully.');
    }

    public function destroy(Hotel $hotel)
    {
        // Soft delete
        $hotel->delete();
        return back()->with('success', 'Hotel deleted.');
    }

    public function toggle(Hotel $hotel)
    {
        $hotel->update(['is_active' => !$hotel->is_active]);
        $status = $hotel->is_active ? 'activated' : 'deactivated';

        return response()->json(['success' => true, 'is_active' => $hotel->is_active, 'message' => "Hotel {$status}."]);
    }

    private function validateHotel(Request $request, ?int $hotelId = null): array
    {
        return $request->validate([
            'name'           => ['required', 'string', 'max:200'],
            'city'           => ['required', 'string', 'max:100'],
            'address'        => ['required', 'string', 'max:500'],
            'description'    => ['nullable', 'string', 'max:5000'],
            'star_rating'    => ['required', 'numeric', 'min:1', 'max:5'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email', 'max:255'],
            'website'        => ['nullable', 'url', 'max:255'],
            'amenities'      => ['nullable', 'array'],
            'amenities.*'    => ['string', 'max:50'],
            'check_in_time'  => ['nullable', 'date_format:H:i'],
            'check_out_time' => ['nullable', 'date_format:H:i'],
            'is_active'      => ['boolean'],
            'image'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'gallery.*'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }
}
