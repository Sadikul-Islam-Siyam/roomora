@extends('layouts.app')

@section('title', 'My Bookings')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">My Bookings</h3>
            <p class="text-muted mb-0">Track your trips and invoices in one place.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Reference</th>
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
                        <td>
                            <div class="fw-semibold">{{ $booking->room->hotel->name ?? '—' }}</div>
                            <div class="text-muted small">{{ $booking->room->type_name }}</div>
                        </td>
                        <td class="small">{{ $booking->check_in->format('M d, Y') }} - {{ $booking->check_out->format('M d, Y') }}</td>
                        <td>৳{{ number_format($booking->total_price) }}</td>
                        <td><x-status-badge :status="$booking->status" /></td>
                        <td class="text-end">
                            <a href="{{ route('bookings.show', $booking) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">You have no bookings yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $bookings->withQueryString()->links() }}
    </div>
</div>
@endsection
