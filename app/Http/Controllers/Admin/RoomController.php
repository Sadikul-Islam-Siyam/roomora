<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request, Hotel $hotel = null)
    {
        $query = Room::with('hotel')->withCount('bookings');

        if ($hotel && $hotel->exists) {
            $query->where('hotel_id', $hotel->id);
            $request->merge(['hotel_id' => $hotel->id]);
        } elseif ($hotelId = $request->get('hotel_id')) {
            $query->where('hotel_id', $hotelId);
        }

        if ($type = $request->get('type')) {
            $query->where('room_type', $type);
        }

        $rooms  = $query->latest()->paginate(20)->withQueryString();
        $hotels = Hotel::active()->pluck('name', 'id');

        return view('admin.rooms.index', compact('rooms', 'hotels'));
    }

    public function create(Hotel $hotel)
    {
        return view('admin.rooms.create', compact('hotel'));
    }

    public function store(Request $request, Hotel $hotel)
    {
        $validated = $this->validateRoom($request);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('rooms', 'public');
        }

        $hotel->rooms()->create($validated);

        return redirect()->route('admin.hotels.show', $hotel)
            ->with('success', 'Room added successfully.');
    }

    public function edit(Room $room)
    {
        return view('admin.rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $this->validateRoom($request);

        if ($request->hasFile('image')) {
            if ($room->image) Storage::disk('public')->delete($room->image);
            $validated['image'] = $request->file('image')->store('rooms', 'public');
        }

        $room->update($validated);

        return redirect()->route('admin.hotels.index')
            ->with('success', 'Room updated.');
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return back()->with('success', 'Room deleted.');
    }

    private function validateRoom(Request $request): array
    {
        return $request->validate([
            'room_type'   => ['required', 'string', 'max:50'],
            'price'       => ['required', 'numeric', 'min:0'],
            'capacity'    => ['required', 'integer', 'min:1', 'max:20'],
            'quantity'    => ['required', 'integer', 'min:1'],
            'facilities'  => ['nullable', 'array'],
            'facilities.*'=> ['string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'size_sqm'    => ['nullable', 'integer', 'min:1'],
            'is_available'=> ['boolean'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }
}
