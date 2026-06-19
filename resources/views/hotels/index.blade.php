@extends('layouts.app')

@section('title', 'Browse Hotels')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
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

    {{-- Recently Viewed Hotels Carousel --}}
    @if(isset($recentlyViewedHotels) && $recentlyViewedHotels->isNotEmpty())
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Recently Viewed</h5>
            <div class="d-flex gap-3 overflow-x-auto pb-2" style="scrollbar-width: thin; -webkit-overflow-scrolling: touch;">
                @foreach($recentlyViewedHotels as $rvh)
                <div class="card border-0 shadow-sm flex-shrink-0" style="width: 240px; border-radius: 8px; overflow: hidden;">
                    <img src="{{ $rvh->image_url }}" class="card-img-top" alt="{{ $rvh->name }}" style="height: 120px; object-fit: cover;" loading="lazy">
                    <div class="card-body p-3">
                        <h6 class="fw-bold text-truncate mb-1" style="font-size:14px;">
                            <a href="{{ route('hotels.show', array_merge(['hotel' => $rvh->id], request()->only(['check_in', 'check_out', 'guests']))) }}" class="text-decoration-none text-dark">
                                {{ $rvh->name }}
                            </a>
                        </h6>
                        <p class="text-muted small mb-2"><i class="bi bi-geo-alt me-1"></i>{{ $rvh->city }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-warning small">★ {{ number_format($rvh->star_rating, 1) }}</span>
                            <span class="fw-bold text-primary" style="font-size:13px;">from BDT {{ number_format($rvh->getMinPriceForDates(request('check_in'), request('check_out'))) }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

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

                        {{-- Search Input (preserved) --}}
                        <input type="hidden" name="search" value="{{ request('search') }}">

                        {{-- Dates & Guests --}}
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Check-in Date</label>
                            <input type="date" name="check_in" id="filterCheckIn" class="form-control form-control-sm filter-input"
                                   value="{{ request('check_in', today()->addDay()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Check-out Date</label>
                            <input type="date" name="check_out" id="filterCheckOut" class="form-control form-control-sm filter-input"
                                   value="{{ request('check_out', today()->addDays(2)->format('Y-m-d')) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Guests</label>
                            <select name="guests" class="form-select form-select-sm filter-input">
                                @for($i=1; $i<=10; $i++)
                                    <option value="{{ $i }}" {{ request('guests', 2) == $i ? 'selected' : '' }}>{{ $i }} {{ Str::plural('Guest', $i) }}</option>
                                @endfor
                            </select>
                        </div>

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
                                           placeholder="Min BDT" value="{{ request('min_price') }}" min="0">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" class="form-control form-control-sm filter-input"
                                           placeholder="Max BDT" value="{{ request('max_price') }}" min="0">
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
                    <button id="mapToggleBtn" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-map"></i> <span>Show Map</span>
                    </button>
                    <span class="text-muted small">Sort:</span>
                    <select class="form-select form-select-sm" style="width:auto" id="sortSelect">
                        <option value="rating" {{ request('sort','rating')=='rating' ? 'selected':'' }}>Best Rating</option>
                        <option value="price_low" {{ request('sort')=='price_low' ? 'selected':'' }}>Price: Low to High</option>
                        <option value="price_high" {{ request('sort')=='price_high' ? 'selected':'' }}>Price: High to Low</option>
                        <option value="newest" {{ request('sort')=='newest' ? 'selected':'' }}>Newest</option>
                        <option value="name" {{ request('sort')=='name' ? 'selected':'' }}>Name A–Z</option>
                    </select>
                </div>
            </div>

            {{-- Leaflet Map container --}}
            <div id="mapContainer" class="d-none mb-4" style="height: 400px; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; position: relative;">
                <div id="map" style="height: 100%; width: 100%;"></div>
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
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

const filterCheckIn = document.getElementById('filterCheckIn');
const filterCheckOut = document.getElementById('filterCheckOut');
if (filterCheckIn && filterCheckOut) {
    filterCheckIn.addEventListener('change', () => {
        if (filterCheckOut.value <= filterCheckIn.value) {
            const nextDate = new Date(filterCheckIn.value);
            nextDate.setDate(nextDate.getDate() + 1);
            filterCheckOut.value = nextDate.toISOString().split('T')[0];
        }
    });
}

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

let map, markerGroup;
function initMap() {
    const hotelsDataEl = document.getElementById('hotelsData');
    if (!hotelsDataEl) return;
    const hotels = JSON.parse(hotelsDataEl.textContent);
    
    const validHotels = hotels.filter(h => h.lat && h.lng);
    const container = document.getElementById('mapContainer');
    if (container.classList.contains('d-none') || validHotels.length === 0) {
        if (map) {
            markerGroup.clearLayers();
        }
        return;
    }

    if (!map) {
        map = L.map('map').setView([validHotels[0].lat, validHotels[0].lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        markerGroup = L.layerGroup().addTo(map);
    } else {
        markerGroup.clearLayers();
    }

    const bounds = [];
    validHotels.forEach(h => {
        const marker = L.marker([h.lat, h.lng])
            .bindPopup(`
                <div style="min-width:140px;">
                    <h6 class="fw-bold mb-1" style="font-size:13px;margin:0;">${h.name}</h6>
                    <div class="text-warning mb-1" style="font-size:11px;">${'★'.repeat(Math.floor(h.stars))}</div>
                    <div class="fw-bold text-primary mb-2" style="font-size:12px;">from BDT ${h.price}/night</div>
                    <a href="${h.url}" class="btn btn-primary btn-sm py-0 px-2 text-white" style="font-size:10px;">View Details</a>
                </div>
            `);
        markerGroup.addLayer(marker);
        bounds.push([h.lat, h.lng]);
    });

    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [30, 30] });
    }
}

document.getElementById('mapToggleBtn')?.addEventListener('click', function() {
    const container = document.getElementById('mapContainer');
    const span = this.querySelector('span');
    const icon = this.querySelector('i');
    if (container.classList.contains('d-none')) {
        container.classList.remove('d-none');
        span.textContent = 'Hide Map';
        icon.className = 'bi bi-map-fill';
        initMap();
        if (map) {
            setTimeout(() => map.invalidateSize(), 100);
        }
    } else {
        container.classList.add('d-none');
        span.textContent = 'Show Map';
        icon.className = 'bi bi-map';
    }
});

async function loadHotels(url, pushState = true) {
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
        initMap();
        const resultsHeader = document.querySelector('h1.fw-bold');
        if (resultsHeader) {
            resultsHeader.scrollIntoView({ behavior: 'smooth' });
        } else {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        if (pushState) {
            history.pushState({}, '', url);
        }
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

window.addEventListener('popstate', function () {
    loadHotels(window.location.href, false);
});

syncCompareBar();
</script>
@endpush
