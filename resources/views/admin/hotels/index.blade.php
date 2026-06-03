@extends('layouts.admin')

@section('title', 'Hotels')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Hotels</h4>
            <small class="text-muted">Manage the hotel catalog and room access.</small>
        </div>
        <a href="{{ route('admin.hotels.create') }}" class="btn btn-primary btn-sm">Add Hotel</a>
    </div>

    <form class="row g-2 mb-4" method="GET">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search hotels or cities" value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-primary w-100" type="submit">Search</button>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Hotel</th>
                        <th>City</th>
                        <th>Rooms</th>
                        <th>Reviews</th>
                        <th>Bookings</th>
                        <th>Starting</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hotels as $hotel)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $hotel->name }}</div>
                            <div class="text-muted small">{{ \Illuminate\Support\Str::limit($hotel->address, 50) }}</div>
                        </td>
                        <td>{{ $hotel->city }}</td>
                        <td>{{ $hotel->rooms_count }}</td>
                        <td>{{ $hotel->reviews_count }} <span class="text-muted small">({{ number_format($hotel->reviews_avg_rating ?? 0, 1) }})</span></td>
                        <td>{{ $hotel->bookings_count }}</td>
                        <td>৳{{ number_format($hotel->min_price ?? 0) }}</td>
                        <td>
                            <span class="badge bg-{{ $hotel->is_active ? 'success' : 'secondary' }}">{{ $hotel->is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.hotels.rooms.create', $hotel) }}" class="btn btn-outline-success">Room</a>
                                <a href="{{ route('admin.hotels.edit', $hotel) }}" class="btn btn-outline-primary">Edit</a>
                                <form action="{{ route('admin.hotels.toggle', $hotel) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-outline-warning" type="submit">Toggle</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No hotels found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $hotels->withQueryString()->links() }}</div>
</div>
@endsection
