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
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
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
