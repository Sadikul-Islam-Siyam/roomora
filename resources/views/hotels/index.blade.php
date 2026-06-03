@extends('layouts.app')

@section('title', 'Browse Hotels')

@push('styles')
<style>
.filter-sidebar { position: sticky; top: 80px; }
.hotel-card .card-img-top { height: 200px; object-fit: cover; }
.price-tag { font-size: 1.3rem; font-weight: 700; color: #1a56db; }
.rating-badge { background: #1a56db; color: #fff; border-radius: 6px; padding: 2px 8px; font-weight: 600; }
.amenity-badge { font-size: .7rem; }
#loadingSpinner { display: none; }
</style>
@endpush

@section('content')
<div class="container py-4">

    {{-- Hero / Page Header --}}
    <div class="row mb-4">
        <div class="col">
            <h1 class="fw-bold">Find Your Perfect Hotel</h1>
            <p class="text-muted"><span id="resultsCount">{{ $hotels->total() }}</span> hotels found
                @if(request('search')) for "<strong>{{ request('search') }}</strong>" @endif
                @if(request('city')) in <strong>{{ request('city') }}</strong> @endif
            </p>
        </div>
    </div>

    <div class="row g-4">

        {{-- ── Filter Sidebar ──────────────────────────────── --}}
        <div class="col-lg-3">
            <div class="card shadow-sm filter-sidebar" id="filterForm">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-funnel me-2"></i>Filters
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('hotels.index') }}" id="searchForm">

                        {{-- City --}}
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">City</label>
                            <select name="city" class="form-select form-select-sm filter-input">
                                <option value="">All Cities</option>
                                @foreach($cities as $city)
                                <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>
                                    {{ $city }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Star Rating --}}
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Minimum Stars</label>
                            @for($i = 1; $i <= 5; $i++)
                            <div class="form-check">
                                <input class="form-check-input filter-input" type="radio" name="stars"
                                       id="stars{{ $i }}" value="{{ $i }}"
                                       {{ request('stars') == $i ? 'checked' : '' }}>
                                <label class="form-check-label star-rating" for="stars{{ $i }}">
                                    {{ str_repeat('★', $i) . str_repeat('☆', 5-$i) }}
                                    <span class="text-muted">({{ $i }}+)</span>
                                </label>
                            </div>
                            @endfor
                        </div>

                        {{-- Price Range --}}
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Price per Night</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="min_price" class="form-control form-control-sm filter-input"
                                           placeholder="Min ৳" value="{{ request('min_price') }}" min="0">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" class="form-control form-control-sm filter-input"
                                           placeholder="Max ৳" value="{{ request('max_price') }}" min="0">
                                </div>
                            </div>
                        </div>

                        {{-- Amenities --}}
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Amenities</label>
                            @foreach(['WiFi', 'Pool', 'Gym', 'Parking', 'Restaurant', 'Spa', 'Airport Shuttle'] as $amenity)
                            <div class="form-check">
                                <input class="form-check-input filter-input" type="checkbox"
                                       name="amenities[]" id="am_{{ Str::slug($amenity) }}"
                                       value="{{ $amenity }}"
                                       {{ in_array($amenity, (array) request('amenities', [])) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="am_{{ Str::slug($amenity) }}">
                                    {{ $amenity }}
                                </label>
                            </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100">Apply Filters</button>
                        <a href="{{ route('hotels.index') }}" class="btn btn-outline-secondary btn-sm w-100 mt-2">
                            Clear All
                        </a>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Hotel Listing ────────────────────────────────── --}}
        <div class="col-lg-9">

            {{-- Sort & Results bar --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small">
                    Showing {{ $hotels->firstItem() }}–{{ $hotels->lastItem() }} of {{ $hotels->total() }} hotels
                </span>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">Sort:</span>
                    <select class="form-select form-select-sm" style="width:auto" id="sortSelect"
                            >
                        <option value="rating" {{ request('sort','rating')=='rating' ? 'selected':'' }}>Best Rating</option>
                        <option value="price_low" {{ request('sort')=='price_low' ? 'selected':'' }}>Price: Low to High</option>
                        <option value="price_high" {{ request('sort')=='price_high' ? 'selected':'' }}>Price: High to Low</option>
                        <option value="newest" {{ request('sort')=='newest' ? 'selected':'' }}>Newest</option>
                        <option value="name" {{ request('sort')=='name' ? 'selected':'' }}>Name A–Z</option>
                    </select>
                </div>
            </div>

            {{-- Loading Spinner --}}
            <div id="loadingSpinner" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>

            {{-- Hotel Cards Grid --}}
            <div id="hotelCards">
                @include('hotels.partials.hotel-cards', ['hotels' => $hotels])
            </div>

            {{-- Pagination --}}
            <div id="paginationLinks" class="mt-4">
                {{ $hotels->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const searchForm = document.getElementById('searchForm');
const hotelCards = document.getElementById('hotelCards');
const paginationLinks = document.getElementById('paginationLinks');
const loadingSpinner = document.getElementById('loadingSpinner');
const resultsCount = document.getElementById('resultsCount');
const compareBar = document.getElementById('comparisonBar');
const compareNames = document.getElementById('compareHotelNames');
const compareCount = document.getElementById('compareCount');

function currentUrl() {
    const url = new URL(searchForm.action, window.location.origin);
    const formData = new FormData(searchForm);
    for (const [key, value] of formData.entries()) {
        if (value !== '') url.searchParams.append(key, value);
    }
    const sortValue = document.getElementById('sortSelect').value;
    if (sortValue) url.searchParams.set('sort', sortValue);
    return url.toString();
}

function syncCompareBar() {
    const selectedButtons = [...document.querySelectorAll('.compare-btn[data-in-comparison="1"]')];
    const names = selectedButtons.map(button => button.dataset.hotelName).filter(Boolean);
    if (compareCount) {
        compareCount.textContent = selectedButtons.length;
        compareCount.classList.toggle('d-none', selectedButtons.length === 0);
    }
    if (compareBar) {
        compareBar.classList.toggle('visible', selectedButtons.length > 0);
    }
    if (compareNames) {
        compareNames.textContent = names.length ? names.join(', ') : 'None selected';
    }
}

async function loadHotels(url) {
    loadingSpinner.style.display = 'block';
    hotelCards.classList.add('d-none');
    paginationLinks.classList.add('d-none');

    try {
        const response = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
        });
        const data = await response.json();
        hotelCards.innerHTML = data.html;
        paginationLinks.innerHTML = data.pagination;
        resultsCount.textContent = data.total;
        syncCompareBar();
    } catch (error) {
        console.error(error);
    } finally {
        loadingSpinner.style.display = 'none';
        hotelCards.classList.remove('d-none');
        paginationLinks.classList.remove('d-none');
    }
}

searchForm.addEventListener('submit', function (event) {
    event.preventDefault();
    loadHotels(currentUrl());
});

document.querySelectorAll('.filter-input').forEach(input => {
    input.addEventListener('change', () => loadHotels(currentUrl()));
});

document.getElementById('sortSelect').addEventListener('change', () => loadHotels(currentUrl()));

paginationLinks.addEventListener('click', function (event) {
    const link = event.target.closest('a');
    if (!link) return;
    event.preventDefault();
    loadHotels(link.href);
});

hotelCards.addEventListener('click', async function (event) {
    const favoriteBtn = event.target.closest('.favorite-btn');
    const compareBtn = event.target.closest('.compare-btn');

    if (favoriteBtn) {
        event.preventDefault();
        const hotelId = favoriteBtn.dataset.hotelId;
        const icon = favoriteBtn.querySelector('i');

        const response = await fetch(`/favorites/toggle/${hotelId}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
        });

        const data = await response.json();
        icon.className = data.favorited ? 'bi bi-heart-fill' : 'bi bi-heart';
        favoriteBtn.dataset.favorited = data.favorited ? '1' : '0';
    }

    if (compareBtn) {
        event.preventDefault();
        const hotelId = compareBtn.dataset.hotelId;

        const response = await fetch(`/compare/toggle/${hotelId}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
        });

        const data = await response.json();
        if (!data.success) {
            alert(data.message);
            return;
        }

        compareBtn.dataset.inComparison = data.added ? '1' : '0';
        compareBtn.classList.toggle('btn-primary', data.added);
        compareBtn.classList.toggle('btn-outline-primary', !data.added);
        syncCompareBar();
    }
});

syncCompareBar();
</script>
@endpush
