@forelse($hotels as $hotel)
<div class="card hotel-card shadow-sm mb-4">
    <div class="row g-0">

        {{-- Image --}}
        <div class="col-md-4 position-relative">
            <img src="{{ $hotel->image_url }}" alt="{{ $hotel->name }}"
                 class="img-fluid rounded-start h-100" style="object-fit:cover; min-height:200px" loading="lazy">

            {{-- Star Rating badge --}}
            <span class="position-absolute top-0 start-0 m-2 badge bg-warning text-dark">
                <i class="bi bi-star-fill me-1"></i>{{ $hotel->star_rating }} Stars
            </span>
        </div>

        {{-- Card Body --}}
        <div class="col-md-8">
            <div class="card-body h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="card-title mb-1">
                            <a href="{{ route('hotels.show', array_merge(['hotel' => $hotel->id], request()->only(['check_in', 'check_out', 'guests']))) }}" class="text-decoration-none text-dark">
                                {{ $hotel->name }}
                            </a>
                        </h5>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-geo-alt me-1"></i>{{ $hotel->city }}
                        </p>
                    </div>

                    {{-- User Rating --}}
                    @if($hotel->reviews_count > 0)
                    <div class="text-end">
                        <span class="rating-badge">{{ number_format($hotel->reviews_avg_rating, 1) }}</span>
                        <div class="text-muted" style="font-size:.7rem">{{ $hotel->reviews_count }} reviews</div>
                    </div>
                    @endif
                </div>

                {{-- Amenities --}}
                @if($hotel->amenities)
                <div class="mb-2 d-flex flex-wrap gap-1">
                    @foreach(array_slice($hotel->amenities, 0, 4) as $amenity)
                    <span class="badge bg-light text-secondary amenity-badge">{{ $amenity }}</span>
                    @endforeach
                    @if(count($hotel->amenities) > 4)
                    <span class="badge bg-light text-secondary amenity-badge">+{{ count($hotel->amenities) - 4 }} more</span>
                    @endif
                </div>
                @endif

                <p class="card-text text-muted small mb-3" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                    {{ $hotel->description }}
                </p>

                {{-- Free Cancellation Policy --}}
                <div class="text-success small mb-2"><i class="bi bi-check-circle me-1"></i>Free cancellation before check-in</div>

                {{-- Footer: Price + Actions --}}
                <div class="mt-auto d-flex justify-content-between align-items-center">
                    <div>
                        @php
                            $minPriceForDates = $hotel->getMinPriceForDates(request('check_in'), request('check_out'));
                        @endphp
                        @if($minPriceForDates)
                        <span class="text-muted small">From</span>
                        <span class="price-tag">BDT {{ number_format($minPriceForDates) }}</span>
                        <span class="text-muted small">/night</span>
                        @else
                        <span class="text-muted small">No rooms available</span>
                        @endif
                    </div>

                    <div class="d-flex gap-2">
                        {{-- Favorite button --}}
                        @auth
                        <button class="btn btn-sm btn-outline-danger favorite-btn"
                                data-hotel-id="{{ $hotel->id }}"
                                data-favorited="{{ Auth::user()->hasFavorited($hotel->id) ? '1' : '0' }}"
                                title="Add to wishlist">
                            <i class="bi {{ Auth::user()->hasFavorited($hotel->id) ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                        </button>

                        {{-- Compare button --}}
                        <button class="btn btn-sm btn-outline-primary compare-btn"
                                data-hotel-id="{{ $hotel->id }}"
                                data-hotel-name="{{ $hotel->name }}"
                                data-in-comparison="{{ Auth::user()->hasInComparison($hotel->id) ? '1' : '0' }}"
                                title="Add to comparison">
                            <i class="bi bi-bar-chart-steps"></i>
                        </button>
                        @endauth

                        <a href="{{ route('hotels.show', array_merge(['hotel' => $hotel->id], request()->only(['check_in', 'check_out', 'guests']))) }}" class="btn btn-sm btn-primary">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@empty
<div class="text-center py-5">
    <i class="bi bi-building display-1 text-muted opacity-25"></i>
    <h5 class="mt-3 text-muted">No hotels found</h5>
    <p class="text-muted">Try adjusting your search or filters.</p>
    <a href="{{ route('hotels.index') }}" class="btn btn-primary">View All Hotels</a>
</div>
@endforelse

<script id="hotelsData" type="application/json">
{!! json_encode($hotels->map(fn($h) => [
    'name' => $h->name,
    'lat' => (float) ($h->latitude ?? 0),
    'lng' => (float) ($h->longitude ?? 0),
    'stars' => (float) $h->star_rating,
    'price' => number_format($h->getMinPriceForDates(request('check_in'), request('check_out')) ?? 0),
    'url' => route('hotels.show', array_merge(['hotel' => $h->id], request()->only(['check_in', 'check_out', 'guests'])))
])) !!}
</script>

