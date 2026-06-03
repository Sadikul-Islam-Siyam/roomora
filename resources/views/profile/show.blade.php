@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" width="110" height="110" class="rounded-circle mb-3" style="object-fit:cover;">
                    <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-1">{{ $user->email }}</p>
                    <p class="text-muted small mb-3">{{ $user->phone ?: 'No phone number set' }}</p>
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm">Edit Profile</a>
                </div>
            </div>

            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Booking Stats</h6>
                    <div class="d-flex justify-content-between small mb-2"><span>Total</span><strong>{{ $bookingStats['total'] }}</strong></div>
                    <div class="d-flex justify-content-between small mb-2"><span>Upcoming</span><strong>{{ $bookingStats['upcoming'] }}</strong></div>
                    <div class="d-flex justify-content-between small mb-2"><span>Completed</span><strong>{{ $bookingStats['completed'] }}</strong></div>
                    <div class="d-flex justify-content-between small"><span>Cancelled</span><strong>{{ $bookingStats['cancelled'] }}</strong></div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">Recent Bookings</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Reference</th>
                                    <th>Hotel</th>
                                    <th>Dates</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($user->bookings->take(5) as $booking)
                                <tr>
                                    <td><a href="{{ route('bookings.show', $booking) }}" class="text-decoration-none fw-semibold">{{ $booking->booking_reference }}</a></td>
                                    <td>{{ $booking->room->hotel->name ?? '—' }}</td>
                                    <td class="small">{{ $booking->check_in->format('M d, Y') }} - {{ $booking->check_out->format('M d, Y') }}</td>
                                    <td>{!! $booking->status_badge !!}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No bookings yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">Wishlist</h5>
                    <div class="row g-3">
                        @forelse($user->favoriteHotels->take(4) as $hotel)
                        <div class="col-md-6">
                            <a href="{{ route('hotels.show', $hotel) }}" class="text-decoration-none">
                                <div class="border rounded p-3 h-100">
                                    <div class="fw-semibold text-dark">{{ $hotel->name }}</div>
                                    <div class="small text-muted">{{ $hotel->city }}</div>
                                </div>
                            </a>
                        </div>
                        @empty
                        <div class="col-12 text-center text-muted py-3">No hotels saved yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">Reviews</h5>
                    @forelse($user->reviews->take(5) as $review)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="fw-semibold small">{{ $review->hotel->name }}</div>
                        <div class="text-warning small">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
                        <p class="text-muted small mb-0">{{ $review->comment }}</p>
                    </div>
                    @empty
                    <p class="text-muted mb-0">No reviews yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
