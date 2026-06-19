@extends('layouts.admin')

@section('title', $hotel->name)

@section('content')
<div class="container-fluid">
    {{-- Header with navigation --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ $hotel->name }}</h4>
            <small class="text-muted"><i class="bi bi-geo-alt-fill me-1"></i>{{ $hotel->address }}, {{ $hotel->city }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.hotels.index') }}" class="btn btn-outline-secondary btn-sm">
                Back to Hotels
            </a>
            <a href="{{ route('admin.hotels.edit', $hotel) }}" class="btn btn-outline-primary btn-sm">
                Edit Hotel
            </a>
            <a href="{{ route('admin.hotels.rooms.create', $hotel) }}" class="btn btn-primary btn-sm">
                Add Room
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Left Column: Hotel details --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <img src="{{ $hotel->image_url }}" alt="{{ $hotel->name }}" class="card-img-top rounded-top" style="max-height: 240px; object-fit: cover;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-{{ $hotel->is_active ? 'success' : 'secondary' }}">{{ $hotel->is_active ? 'Active' : 'Inactive' }}</span>
                        <div class="text-warning">
                            {!! $hotel->star_icons !!}
                        </div>
                    </div>
                    
                    <h5 class="fw-semibold mb-3">Hotel Info</h5>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><i class="bi bi-telephone text-muted me-2"></i>{{ $hotel->phone ?: 'No phone contact' }}</li>
                        <li class="mb-2"><i class="bi bi-envelope text-muted me-2"></i>{{ $hotel->email ?: 'No email contact' }}</li>
                        <li class="mb-2"><i class="bi bi-globe text-muted me-2"></i>
                            @if($hotel->website)
                                <a href="{{ $hotel->website }}" target="_blank" class="text-decoration-none">{{ $hotel->website }}</a>
                            @else
                                No website
                            @endif
                        </li>
                        <li class="mb-2"><i class="bi bi-clock text-muted me-2"></i>Check-in: {{ $hotel->check_in_time ?: '14:00' }} / Out: {{ $hotel->check_out_time ?: '12:00' }}</li>
                    </ul>

                    <h5 class="fw-semibold mb-2">Description</h5>
                    <p class="text-muted small mb-4">{{ $hotel->description ?: 'No description provided.' }}</p>

                    <h5 class="fw-semibold mb-2">Amenities</h5>
                    <div>
                        @forelse($hotel->amenities ?? [] as $amenity)
                            <span class="badge bg-light text-dark border me-1 mb-1">{{ $amenity }}</span>
                        @empty
                            <span class="text-muted small">No amenities specified.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Stats & Rooms list --}}
        <div class="col-lg-8">
            {{-- Stats Widgets --}}
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3 me-3">
                                <i class="bi bi-door-open fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1 small">Total Rooms</h6>
                                <h3 class="fw-bold mb-0">{{ $hotel->rooms_count }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center p-4">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3 me-3">
                                <i class="bi bi-journal-check fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1 small">Total Bookings</h6>
                                <h3 class="fw-bold mb-0">{{ $hotel->bookings_count }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rooms List --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-semibold mb-0">Rooms</h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Room Type</th>
                                <th>Room Number</th>
                                <th>Price</th>
                                <th>Capacity</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Bookings</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($hotel->rooms as $room)
                            <tr>
                                <td><strong>{{ $room->type_name }}</strong></td>
                                <td>{{ $room->room_number ?: '—' }}</td>
                                <td>৳{{ number_format($room->price) }}</td>
                                <td>{{ $room->capacity }} guest(s)</td>
                                <td>{{ $room->quantity }}</td>
                                <td>
                                    <span class="badge bg-{{ $room->is_available ? 'success' : 'secondary' }}">
                                        {{ $room->is_available ? 'Available' : 'Hidden' }}
                                    </span>
                                </td>
                                <td>{{ $room->bookings_count }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.rooms.edit', $room) }}" class="btn btn-outline-primary">Edit</a>
                                        <form action="{{ route('admin.rooms.destroy', $room) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this room?')" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No rooms added yet. <a href="{{ route('admin.hotels.rooms.create', $hotel) }}">Add one now</a>.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent Bookings --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-semibold mb-0">Recent Bookings</h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reference</th>
                                <th>Guest</th>
                                <th>Dates</th>
                                <th>Room</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($hotel->bookings as $booking)
                            <tr>
                                <td><span class="font-monospace fw-semibold">{{ $booking->booking_reference }}</span></td>
                                <td>
                                    <div>{{ $booking->guest_name }}</div>
                                    <small class="text-muted">{{ $booking->guest_email }}</small>
                                </td>
                                <td>
                                    <div>{{ $booking->check_in->format('Y-m-d') }}</div>
                                    <small class="text-muted">to {{ $booking->check_out->format('Y-m-d') }}</small>
                                </td>
                                <td>{{ $booking->room->type_name }}</td>
                                <td>৳{{ number_format($booking->total_price) }}</td>
                                <td>
                                    {!! $booking->status_badge !!}
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-link btn-sm text-decoration-none">Details</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No bookings record found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
