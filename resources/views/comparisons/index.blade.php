@extends('layouts.app')

@section('title', 'Compare Hotels')

@push('styles')
<style>
.compare-table th { background: #0f172a; color: #fff; font-weight: 600; text-align: center; vertical-align: middle; }
.compare-table td { text-align: center; vertical-align: middle; padding: 12px; }
.compare-table tr:nth-child(even) { background: #f8fafc; }
.compare-table .row-label { font-weight: 600; text-align: left !important; background: #fff !important; color: #374151; width: 160px; }
.hotel-header { min-width: 200px; }
.hotel-thumb { width: 80px; height: 60px; object-fit: cover; border-radius: 8px; }
.star-filled { color: #f59e0b; }
.star-empty  { color: #d1d5db; }
.badge-yes { background: #10b981; color: #fff; border-radius: 20px; padding: 2px 10px; font-size: .75rem; }
.badge-no  { background: #ef4444; color: #fff; border-radius: 20px; padding: 2px 10px; font-size: .75rem; }
.compare-best { background: rgba(26,86,219,.06) !important; }
.section-divider th { background: #f1f5f9; color: #374151; font-size: .75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; }
</style>
@endpush

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Hotel Comparison</h4>
            <p class="text-muted small mb-0">Comparing {{ $hotels->count() }} hotel{{ $hotels->count() > 1 ? 's' : '' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('hotels.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-plus me-1"></i>Add More
            </a>
            <form action="{{ route('comparisons.clear') }}" method="POST">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm" type="submit">
                    <i class="bi bi-x-circle me-1"></i>Clear All
                </button>
            </form>
        </div>
    </div>

    @if($hotels->isEmpty())
    <div class="text-center py-5">
        <i class="bi bi-bar-chart-steps display-1 text-muted opacity-25"></i>
        <h5 class="mt-3 text-muted">No hotels in your comparison list</h5>
        <p class="text-muted">Browse hotels and click the compare button to add them here.</p>
        <a href="{{ route('hotels.index') }}" class="btn btn-primary">Browse Hotels</a>
    </div>
    @else

    <div class="table-responsive">
        <table class="table compare-table table-bordered align-middle">

            {{-- ── Hotel Headers ──────────────────────────────── --}}
            <thead>
                <tr>
                    <th class="row-label" style="background:#0f172a!important"></th>
                    @foreach($hotels as $hotel)
                    <th class="hotel-header">
                        <img src="{{ $hotel->image_url }}" alt="{{ $hotel->name }}" class="hotel-thumb mb-2"><br>
                        <a href="{{ route('hotels.show', $hotel) }}" class="text-white text-decoration-none">
                            {{ $hotel->name }}
                        </a>
                        <div class="small text-white-50 mt-1">
                            <i class="bi bi-geo-alt me-1"></i>{{ $hotel->city }}
                        </div>
                        <div class="mt-2">
                            <form action="{{ route('comparisons.toggle', $hotel) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-outline-light btn-sm" type="submit"
                                        onclick="return confirm('Remove from comparison?')">
                                    <i class="bi bi-x"></i> Remove
                                </button>
                            </form>
                        </div>
                    </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>

                {{-- ── General Info ──────────────────────────────── --}}
                <tr class="section-divider">
                    <th colspan="{{ $hotels->count() + 1 }}">General Information</th>
                </tr>

                {{-- Star Rating --}}
                <tr>
                    <td class="row-label">Star Rating</td>
                    @php $maxStars = $hotels->max('star_rating'); @endphp
                    @foreach($hotels as $hotel)
                    <td class="{{ $hotel->star_rating == $maxStars ? 'compare-best' : '' }}">
                        @for($i = 1; $i <= 5; $i++)
                        <span class="{{ $i <= $hotel->star_rating ? 'star-filled' : 'star-empty' }}">★</span>
                        @endfor
                        <div class="small text-muted">({{ $hotel->star_rating }})</div>
                    </td>
                    @endforeach
                </tr>

                {{-- Guest Rating --}}
                <tr>
                    <td class="row-label">Guest Rating</td>
                    @php $maxRating = $hotels->max('reviews_avg_rating'); @endphp
                    @foreach($hotels as $hotel)
                    <td class="{{ $hotel->reviews_avg_rating == $maxRating && $maxRating > 0 ? 'compare-best' : '' }}">
                        @if($hotel->reviews_avg_rating)
                        <span class="fw-bold text-primary" style="font-size:1.3rem">{{ number_format($hotel->reviews_avg_rating,1) }}</span>
                        <div class="small text-muted">{{ $hotel->reviews_count }} reviews</div>
                        @else
                        <span class="text-muted small">No reviews</span>
                        @endif
                    </td>
                    @endforeach
                </tr>

                {{-- Check-in / Check-out --}}
                <tr>
                    <td class="row-label">Check-in / out</td>
                    @foreach($hotels as $hotel)
                    <td>
                        <div class="small">
                            <i class="bi bi-box-arrow-in-right text-primary me-1"></i>{{ $hotel->check_in_time }}<br>
                            <i class="bi bi-box-arrow-right text-danger me-1"></i>{{ $hotel->check_out_time }}
                        </div>
                    </td>
                    @endforeach
                </tr>

                {{-- ── Pricing ──────────────────────────────────── --}}
                <tr class="section-divider">
                    <th colspan="{{ $hotels->count() + 1 }}">Pricing</th>
                </tr>

                <tr>
                    <td class="row-label">Starting Price</td>
                    @php $minPrice = $hotels->filter(fn($h) => $h->min_price)->min('min_price'); @endphp
                    @foreach($hotels as $hotel)
                    <td class="{{ $hotel->min_price == $minPrice && $minPrice ? 'compare-best' : '' }}">
                        @if($hotel->min_price)
                        <span class="fw-bold text-primary" style="font-size:1.2rem">
                            ৳{{ number_format($hotel->min_price) }}
                        </span>
                        <div class="small text-muted">per night</div>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    @endforeach
                </tr>

                {{-- Available Rooms --}}
                <tr>
                    <td class="row-label">Available Rooms</td>
                    @php $maxRooms = $hotels->max(fn($h) => $h->rooms->count()); @endphp
                    @foreach($hotels as $hotel)
                    <td class="{{ $hotel->rooms->count() == $maxRooms ? 'compare-best' : '' }}">
                        <span class="fw-bold" style="font-size:1.4rem">{{ $hotel->rooms->count() }}</span>
                        <div class="small text-muted">room types</div>
                    </td>
                    @endforeach
                </tr>

                {{-- ── Amenities ─────────────────────────────────── --}}
                <tr class="section-divider">
                    <th colspan="{{ $hotels->count() + 1 }}">Amenities & Facilities</th>
                </tr>

                @foreach($allAmenities->take(12) as $amenity)
                <tr>
                    <td class="row-label">{{ $amenity }}</td>
                    @foreach($hotels as $hotel)
                    @php $has = in_array($amenity, $hotel->amenities ?? []); @endphp
                    <td>
                        @if($has)
                        <span class="badge-yes"><i class="bi bi-check-lg me-1"></i>Yes</span>
                        @else
                        <span class="badge-no"><i class="bi bi-x-lg me-1"></i>No</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach

                {{-- ── Room Types ───────────────────────────────── --}}
                <tr class="section-divider">
                    <th colspan="{{ $hotels->count() + 1 }}">Room Types Available</th>
                </tr>

                @foreach(\App\Models\Room::TYPES as $typeKey => $typeName)
                <tr>
                    <td class="row-label">{{ $typeName }}</td>
                    @foreach($hotels as $hotel)
                    @php $hasRoom = $hotel->rooms->where('room_type', $typeKey)->isNotEmpty(); @endphp
                    <td>
                        @if($hasRoom)
                        @php $room = $hotel->rooms->where('room_type', $typeKey)->first(); @endphp
                        <span class="badge-yes"><i class="bi bi-check-lg me-1"></i>Yes</span>
                        <div class="small text-muted mt-1">৳{{ number_format($room->price) }}/night</div>
                        @else
                        <span class="badge-no"><i class="bi bi-x-lg me-1"></i>No</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach

                {{-- ── CTA Row ──────────────────────────────────── --}}
                <tr>
                    <td class="row-label fw-semibold">Book Now</td>
                    @foreach($hotels as $hotel)
                    <td>
                        <a href="{{ route('hotels.show', $hotel) }}" class="btn btn-primary btn-sm">
                            View Hotel
                        </a>
                    </td>
                    @endforeach
                </tr>

            </tbody>
        </table>
    </div>

    <div class="alert alert-info small mt-3">
        <i class="bi bi-info-circle me-1"></i>
        Highlighted cells indicate the best value for that category.
        You can compare up to 4 hotels at once.
    </div>

    @endif
</div>
@endsection
