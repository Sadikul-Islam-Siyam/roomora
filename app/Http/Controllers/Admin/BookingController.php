<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $query = Booking::with(['user', 'room.hotel'])->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }
        if ($from = $request->get('from')) {
            $query->whereDate('check_in', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('check_in', '<=', $to);
        }

        $bookings = $query->paginate(20)->withQueryString();

        $statusCounts = Booking::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')->pluck('count', 'status');

        return view('admin.bookings.index', compact('bookings', 'statusCounts'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'room.hotel']);
        return view('admin.bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $oldStatus = $booking->status;
        $request->validate([
            'status' => ['required', 'in:pending,confirmed,checked_in,checked_out,cancelled'],
        ]);

        $newStatus = $request->status;

        if (!Booking::isValidTransition($oldStatus, $newStatus)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Invalid transition from {$oldStatus} to {$newStatus}."
                ], 422);
            }
            return back()->withErrors(['status' => "Invalid transition from {$oldStatus} to {$newStatus}."]);
        }

        if ($oldStatus !== $newStatus) {
            DB::transaction(function () use ($booking, $oldStatus, $newStatus) {
                $booking->update(['status' => $newStatus]);

                \App\Models\BookingLog::create([
                    'booking_id'  => $booking->id,
                    'changed_by'  => auth()->id(),
                    'from_status' => $oldStatus,
                    'to_status'   => $newStatus,
                ]);
            });
        }

        return back()->with('success', 'Booking status updated to ' . ucfirst($newStatus) . '.');
    }

    public function reports(Request $request)
    {
        $year = $request->get('year', now()->year);

        $monthlyStats = Booking::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total_bookings'),
                DB::raw('SUM(CASE WHEN status != "cancelled" THEN total_price ELSE 0 END) as revenue'),
                DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancellations')
            )
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($row) => [
                'label'          => date('M', mktime(0, 0, 0, $row->month, 1, $year)),
                'month'          => $row->month,
                'total_bookings' => (int) $row->total_bookings,
                'revenue'        => (float) $row->revenue,
                'cancellations'  => (int) $row->cancellations,
            ]);

        $topRevenue = Booking::join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->join('hotels', 'rooms.hotel_id', '=', 'hotels.id')
            ->whereYear('bookings.created_at', $year)
            ->whereNotIn('bookings.status', ['cancelled'])
            ->groupBy('hotels.id', 'hotels.name')
            ->selectRaw('hotels.name, SUM(bookings.total_price) as revenue, COUNT(bookings.id) as bookings')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $totalRevenue    = $monthlyStats->sum('revenue');
        $totalBookings   = $monthlyStats->sum('total_bookings');
        $totalCancelled  = $monthlyStats->sum('cancellations');
        $avgBookingValue = $totalBookings > 0 ? $totalRevenue / $totalBookings : 0;

        $topSearchTerms = DB::table('search_logs')
            ->select('term', DB::raw('COUNT(*) as searches'))
            ->groupBy('term')
            ->orderByDesc('searches')
            ->limit(10)
            ->get();

        $topSearchCities = DB::table('search_logs')
            ->select('city', DB::raw('COUNT(*) as searches'))
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->groupBy('city')
            ->orderByDesc('searches')
            ->limit(10)
            ->get();

        return view('admin.reports', compact(
            'monthlyStats', 'topRevenue', 'year',
            'totalRevenue', 'totalBookings', 'totalCancelled', 'avgBookingValue',
            'topSearchTerms', 'topSearchCities'
        ));
    }

    public function export(Request $request)
    {
        $bookings = Booking::with(['user', 'room.hotel'])
            ->when($request->get('status'), fn($q, $s) => $q->where('status', $s))
            ->when($request->get('from'),   fn($q, $f) => $q->whereDate('check_in', '>=', $f))
            ->when($request->get('to'),     fn($q, $t) => $q->whereDate('check_in', '<=', $t))
            ->latest()
            ->get();

        $filename = 'bookings-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($bookings) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Reference', 'Guest', 'Email', 'Hotel', 'Room', 'Check-in', 'Check-out', 'Nights', 'Guests', 'Total', 'Status', 'NIDs', 'Booked On']);

            foreach ($bookings as $b) {
                $nids = '';
                if (is_array($b->guest_details) && count($b->guest_details)) {
                    $nids = collect($b->guest_details)->pluck('nid')->filter()->values()->all();
                    $nids = implode(';', $nids);
                }

                fputcsv($file, [
                    $b->booking_reference,
                    $b->guest_name,
                    $b->guest_email,
                    $b->room->hotel->name ?? '—',
                    $b->room->room_type,
                    $b->check_in->format('Y-m-d'),
                    $b->check_out->format('Y-m-d'),
                    $b->nights,
                    $b->guests,
                    $b->total_price,
                    $b->status,
                    $nids,
                    $b->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
