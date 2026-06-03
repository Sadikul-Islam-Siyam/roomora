@extends('layouts.admin')

@section('title', 'Rooms')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Rooms</h4>
            <small class="text-muted">Browse and manage hotel room inventory.</small>
        </div>
    </div>

    <form class="row g-2 mb-4" method="GET">
        <div class="col-md-4">
            <select name="hotel_id" class="form-select">
                <option value="">All Hotels</option>
                @foreach($hotels as $id => $name)
                    <option value="{{ $id }}" {{ request('hotel_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" name="type" class="form-control" placeholder="Room type" value="{{ request('type') }}">
        </div>
        <div class="col-md-2"><button class="btn btn-outline-primary w-100" type="submit">Filter</button></div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Room</th>
                        <th>Hotel</th>
                        <th>Price</th>
                        <th>Capacity</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rooms as $room)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $room->type_name }}</div>
                            <div class="text-muted small">{{ $room->room_number ?: 'No room number' }}</div>
                        </td>
                        <td>{{ $room->hotel->name ?? '—' }}</td>
                        <td>৳{{ number_format($room->price) }}</td>
                        <td>{{ $room->capacity }}</td>
                        <td>{{ $room->quantity }}</td>
                        <td><span class="badge bg-{{ $room->is_available ? 'success' : 'secondary' }}">{{ $room->is_available ? 'Available' : 'Hidden' }}</span></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.hotels.rooms.create', $room->hotel) }}" class="btn btn-outline-success">Add</a>
                                <a href="{{ route('admin.rooms.edit', $room) }}" class="btn btn-outline-primary">Edit</a>
                                <form action="{{ route('admin.rooms.destroy', $room) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No rooms found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $rooms->withQueryString()->links() }}</div>
</div>
@endsection
