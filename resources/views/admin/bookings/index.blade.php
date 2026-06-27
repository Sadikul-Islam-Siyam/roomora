@extends('layouts.admin')

@section('title', 'Bookings')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Bookings</h4>
            <small class="text-muted">Track reservations and status changes.</small>
        </div>
    </div>

    <form class="row g-2 mb-4" method="GET">
        <div class="col-md-3"><input type="text" name="search" class="form-control" placeholder="Reference or guest" value="{{ request('search') }}"></div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                @foreach(['pending','confirmed','cancelled'] as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><input type="date" name="from" class="form-control" value="{{ request('from') }}"></div>
        <div class="col-md-2"><input type="date" name="to" class="form-control" value="{{ request('to') }}"></div>
        <div class="col-md-2"><button class="btn btn-outline-primary w-100" type="submit">Filter</button></div>
    </form>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap gap-2">
            @foreach($statusCounts as $status => $count)
                <span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $status)) }}: {{ $count }}</span>
            @endforeach
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Reference</th>
                        <th>Guest</th>
                        <th>Hotel</th>
                        <th>Dates</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                    <tr>
                        <td class="fw-semibold">{{ $booking->booking_reference }}</td>
                        <td>{{ $booking->guest_name }}</td>
                        <td>
                            <div class="fw-semibold">{{ $booking->room->hotel->name ?? '—' }}</div>
                            <div class="text-muted small">{{ $booking->room->type_name }}</div>
                        </td>
                        <td class="small">{{ $booking->check_in->format('M d, Y') }} - {{ $booking->check_out->format('M d, Y') }}</td>
                        <td>৳{{ number_format($booking->total_price) }}</td>
                        <td><x-status-badge :status="$booking->status" /></td>
                        <td class="text-end"><a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No bookings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $bookings->withQueryString()->links() }}</div>
</div>
@endsection
