@extends('layouts.admin')

@section('title', 'User Details')

@section('content')
<div class="container-fluid">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" width="100" height="100" class="rounded-circle mb-3" style="object-fit:cover;">
                    <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-1">{{ $user->email }}</p>
                    <span class="badge bg-{{ $user->role === 'admin' ? 'dark' : 'primary' }}">{{ ucfirst($user->role) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">Bookings</h5>
                    <ul class="list-group list-group-flush">
                        @forelse($user->bookings as $booking)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $booking->booking_reference }}</div>
                                <div class="text-muted small">{{ $booking->room->hotel->name ?? '—' }}</div>
                            </div>
                            <span>{!! $booking->status_badge !!}</span>
                        </li>
                        @empty
                        <li class="list-group-item text-muted">No bookings.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">Reviews</h5>
                    @forelse($user->reviews as $review)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="fw-semibold">{{ $review->hotel->name }}</div>
                        <div class="text-warning small">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
                        <p class="text-muted small mb-0">{{ $review->comment }}</p>
                    </div>
                    @empty
                    <p class="text-muted mb-0">No reviews.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
