@extends('layouts.app')

@section('title', 'Wishlist')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Saved Hotels</h3>
            <p class="text-muted mb-0">Your personal shortlist of hotels.</p>
        </div>
    </div>

    <div class="row g-4">
        @forelse($hotels as $hotel)
        <div class="col-md-6 col-xl-4">
            <div class="card hotel-card shadow-sm h-100 border-0">
                <img src="{{ $hotel->image_url }}" class="card-img-top" alt="{{ $hotel->name }}" style="height: 210px; object-fit: cover;">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-1">{{ $hotel->name }}</h5>
                    <p class="text-muted small mb-2">{{ $hotel->city }}</p>
                    <p class="small text-muted flex-grow-1">{{ \Illuminate\Support\Str::limit($hotel->description, 120) }}</p>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div>
                            <div class="fw-semibold text-primary">৳{{ number_format($hotel->min_price ?? 0) }}</div>
                            <div class="small text-muted">per night</div>
                        </div>
                        <a href="{{ route('hotels.show', $hotel) }}" class="btn btn-primary btn-sm">View</a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5 text-muted">
            <i class="bi bi-heart display-4 opacity-25"></i>
            <p class="mt-3 mb-0">Your wishlist is empty.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $hotels->withQueryString()->links() }}
    </div>
</div>
@endsection
