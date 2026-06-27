@extends('layouts.app')

@section('title', $hotel->name)
@section('meta_description', Str::limit($hotel->description, 160))

@push('styles')
<style>
.hotel-hero { position: relative; height: 400px; overflow: hidden; }
.hotel-hero img { width: 100%; height: 100%; object-fit: cover; }
.hotel-hero-overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,.2) 0%, rgba(0,0,0,.7) 100%); }
.hotel-hero-content { position: absolute; bottom: 0; left: 0; right: 0; padding: 30px; color: #fff; }
.sticky-booking { position: sticky; top: 90px; z-index: 10; background: #fff; }
.room-card { border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; transition: box-shadow .2s; }
.room-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.1); }
.review-stars { color: #f59e0b; font-size: 1.1rem; }
.rating-bar-fill { background: #1a56db; height: 8px; border-radius: 4px; }
.amenity-pill { background: #f1f5f9; color: #475569; border-radius: 20px; padding: 4px 14px; font-size: .82rem; display: inline-block; margin: 3px; }
.nav-tabs .nav-link { color: #64748b; font-weight: 500; }
.nav-tabs .nav-link.active { color: #1a56db; border-bottom: 2px solid #1a56db; }
</style>
@endpush

@section('content')

{{-- ── Hero Image ───────────────────────────────────── --}}
<div class="hotel-hero">
    <img src="{{ $hotel->image_url }}" alt="{{ $hotel->name }}">
    <div class="hotel-hero-overlay"></div>
    <div class="hotel-hero-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <div class="star-rating mb-1" style="font-size:1.1rem">
                        @for($i = 1; $i <= 5; $i++)
                            <span style="color:{{ $i <= $hotel->star_rating ? '#f59e0b' : 'rgba(255,255,255,.4)' }}">★</span>
                        @endfor
                        <span class="ms-1 text-white-50 small">{{ $hotel->star_rating }}-Star Hotel</span>
                    </div>
                    <h1 class="fw-bold mb-1" style="font-size:2rem">{{ $hotel->name }}</h1>
                    <p class="mb-0 text-white-50">
                        <i class="bi bi-geo-alt me-1"></i>{{ $hotel->address }}, {{ $hotel->city }}
                    </p>
                </div>
                <div class="text-end d-none d-md-block">
                    @if($avgRating)
                    <div style="background:rgba(255,255,255,.15);border-radius:12px;padding:12px 20px;">
                        <div style="font-size:2.5rem;font-weight:800;line-height:1">{{ number_format($avgRating,1) }}</div>
                        <div class="small text-white-50">{{ $hotel->reviews_count }} reviews</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
    <x-hotel-gallery :images="$hotel->images" :fallback="$hotel->image_url" :alt="$hotel->name" />
</div>

{{-- ── Main Content ──────────────────────────────────── --}}
<div class="container py-4">
    <div class="row g-4">

        {{-- Left Column --}}
        <div class="col-lg-8">

            {{-- Action Buttons --}}
            @auth
            <div class="d-flex gap-2 mb-4">
                <button class="btn btn-outline-danger btn-sm favorite-btn"
                        data-hotel-id="{{ $hotel->id }}"
                        data-favorited="{{ $isFavorited ? '1' : '0' }}"
                        id="favBtn">
                    <i class="bi {{ $isFavorited ? 'bi-heart-fill' : 'bi-heart' }} me-1"></i>
                    <span id="favLabel">{{ $isFavorited ? 'Saved' : 'Save' }}</span>
                </button>
                <button class="btn {{ $inComparison ? 'btn-primary' : 'btn-outline-primary' }} btn-sm compare-btn"
                        data-hotel-id="{{ $hotel->id }}"
                        data-hotel-name="{{ $hotel->name }}"
                        data-in-comparison="{{ $inComparison ? '1' : '0' }}"
                        id="compareBtn">
                    <i class="bi bi-bar-chart-steps me-1"></i>
                    <span id="compareLabel">{{ $inComparison ? 'In Comparison' : 'Compare' }}</span>
                </button>
                <a href="{{ route('comparisons.index') }}" class="btn btn-outline-secondary btn-sm">
                    View Comparison List
                </a>
            </div>
            @endauth

            {{-- Tabs --}}
            <ul class="nav nav-tabs border-bottom mb-4" id="hotelTabs">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabOverview">Overview</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabRooms">Rooms</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabReviews">
                    Reviews <span class="badge bg-secondary ms-1">{{ $hotel->reviews_count }}</span>
                </button></li>
            </ul>

            <div class="tab-content">

                {{-- ── Tab: Overview ──────────────────────────── --}}
                <div class="tab-pane fade show active" id="tabOverview">
                    <h5 class="fw-semibold mb-3">About this Hotel</h5>
                    <p class="text-muted lh-lg">{{ $hotel->description }}</p>

                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3 text-center">
                            <div class="fw-bold fs-4 text-primary">{{ $hotel->star_rating }}</div>
                            <div class="small text-muted">Star Rating</div>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="fw-bold fs-4 text-primary">{{ $hotel->check_in_time }}</div>
                            <div class="small text-muted">Check-in</div>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="fw-bold fs-4 text-primary">{{ $hotel->check_out_time }}</div>
                            <div class="small text-muted">Check-out</div>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="fw-bold fs-4 text-primary">{{ $hotel->rooms->count() }}</div>
                            <div class="small text-muted">Room Types</div>
                        </div>
                    </div>

                    @if($hotel->amenities)
                    <h6 class="fw-semibold mt-4 mb-2">Amenities & Facilities</h6>
                    <div class="mb-3">
                        @foreach($hotel->amenities as $amenity)
                        <span class="amenity-pill">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>{{ $amenity }}
                        </span>
                        @endforeach
                    </div>
                    @endif

                    @if($hotel->phone || $hotel->email)
                    <h6 class="fw-semibold mt-4 mb-2">Contact</h6>
                    @if($hotel->phone)
                    <p class="mb-1"><i class="bi bi-telephone me-2 text-primary"></i>{{ $hotel->phone }}</p>
                    @endif
                    @if($hotel->email)
                    <p class="mb-1"><i class="bi bi-envelope me-2 text-primary"></i>{{ $hotel->email }}</p>
                    @endif
                    @endif
                </div>

                {{-- ── Tab: Rooms ──────────────────────────────── --}}
                <div class="tab-pane fade" id="tabRooms">
                    {{-- Quick date selector --}}
                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Check-in</label>
                                    <input type="date" id="roomCheckIn" class="form-control form-control-sm"
                                           min="{{ today()->format('Y-m-d') }}"
                                           value="{{ request('check_in', today()->addDay()->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Check-out</label>
                                    <input type="date" id="roomCheckOut" class="form-control form-control-sm"
                                           min="{{ today()->format('Y-m-d') }}"
                                           value="{{ request('check_out', today()->addDays(2)->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Guests</label>
                                    <select id="roomGuests" class="form-select form-select-sm">
                                        @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ request('guests', 2) == $i ? 'selected' : '' }}>{{ $i }} Guest{{ $i > 1 ? 's' : '' }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    @forelse($hotel->rooms as $room)
                    <div class="room-card mb-3">
                        <div class="row g-0">
                            <div class="col-md-3">
                                <img src="{{ $room->image_url }}" alt="{{ $room->type_name }}"
                                     class="img-fluid h-100" style="object-fit:cover;min-height:160px" loading="lazy">
                            </div>
                            <div class="col-md-9">
                                <div class="p-3 d-flex flex-column h-100">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="fw-bold mb-1">
                                                {{ $room->type_name }}
                                                @if($room->remaining_quantity <= 3)
                                                <span class="badge bg-danger text-white ms-2">Only {{ $room->remaining_quantity }} left!</span>
                                                @endif
                                            </h6>
                                            <span class="text-muted small">
                                                <i class="bi bi-people me-1"></i>Up to {{ $room->capacity }} guests
                                                @if($room->size_sqm)
                                                · <i class="bi bi-arrows-fullscreen me-1"></i>{{ $room->size_sqm }} m²
                                                @endif
                                            </span>
                                            <div class="text-success small mt-1"><i class="bi bi-check-circle me-1"></i>Free cancellation before check-in</div>
                                        </div>
                                        <div class="text-end">
                                            <div style="font-size:1.4rem;font-weight:800;color:#1a56db">
                                                BDT {{ number_format($room->price) }}
                                            </div>
                                            <div class="text-muted" style="font-size:.75rem">per night</div>
                                        </div>
                                    </div>

                                    @if($room->facilities)
                                    <div class="mt-2 d-flex flex-wrap gap-1">
                                        @foreach(array_slice($room->facilities, 0, 5) as $f)
                                        <span class="badge bg-light text-secondary" style="font-size:.7rem">{{ $f }}</span>
                                        @endforeach
                                    </div>
                                    @endif

                                    <div class="mt-auto pt-2 d-flex justify-content-between align-items-center">
                                        <div id="availMsg_{{ $room->id }}" class="small"></div>
                                        @auth
                                        <a href="{{ route('bookings.create', $room) }}?check_in={{ request('check_in', today()->addDay()->format('Y-m-d')) }}&check_out={{ request('check_out', today()->addDays(2)->format('Y-m-d')) }}&guests={{ request('guests', 2) }}"
                                           class="btn btn-primary btn-sm book-room-btn"
                                           data-room-id="{{ $room->id }}"
                                           data-avail-url="{{ route('rooms.availability', $room) }}">
                                             Book Now
                                         </a>
                                        @else
                                        <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                                            Login to Book
                                        </a>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-door-closed display-4 opacity-25"></i>
                        <p class="mt-2">No rooms currently available.</p>
                    </div>
                    @endforelse
                </div>

                {{-- ── Tab: Reviews ──────────────────────────────── --}}
                <div class="tab-pane fade" id="tabReviews">

                    {{-- Review Alerts --}}
                    @if ($errors->has('review'))
                        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-2 mb-3" role="alert" style="background-color: #fef2f2; color: #dc2626; border-left: 4px solid #dc2626; border-radius: 8px;">
                            <i class="bi bi-exclamation-circle-fill fs-5"></i>
                            <div class="small fw-semibold">
                                {{ $errors->first('review') }}
                            </div>
                        </div>
                    @endif

                    @if ($errors->has('rating') || $errors->has('title') || $errors->has('comment'))
                        <div class="alert alert-danger border-0 shadow-sm d-flex flex-column gap-1 mb-3" role="alert" style="background-color: #fef2f2; color: #dc2626; border-left: 4px solid #dc2626; border-radius: 8px;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                                <strong class="small fw-semibold">Please correct the following errors:</strong>
                            </div>
                            <ul class="mb-0 ps-4 small">
                                @foreach (['rating', 'title', 'comment'] as $field)
                                    @if ($errors->has($field))
                                        <li>{{ $errors->first($field) }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('success') && Str::contains(session('success'), ['review', 'Review']))
                        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2 mb-3" role="alert" style="background-color: #f0fdf4; color: #16a34a; border-left: 4px solid #16a34a; border-radius: 8px;">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                            <div class="small fw-semibold">
                                {{ session('success') }}
                            </div>
                        </div>
                    @endif

                    {{-- Rating Summary --}}
                    @if($avgRating)
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 text-center">
                            <div style="font-size:3rem;font-weight:800;color:#1a56db">{{ number_format($avgRating,1) }}</div>
                            <div class="review-stars">
                                @for($i = 1; $i <= 5; $i++)★@endfor
                            </div>
                            <div class="text-muted small">{{ $hotel->reviews_count }} reviews</div>
                        </div>
                        <div class="col-md-9">
                            @foreach([5,4,3,2,1] as $star)
                            @php $count = $ratingBreakdown[$star] ?? 0; $pct = $hotel->reviews_count ? ($count/$hotel->reviews_count)*100 : 0; @endphp
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="small" style="width:12px">{{ $star }}</span>
                                <i class="bi bi-star-fill text-warning small"></i>
                                <div class="progress flex-grow-1" style="height:8px">
                                    <div class="progress-bar bg-warning" style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="small text-muted" style="width:25px">{{ $count }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Add Review form --}}
                    @auth
                    @if(!$userReview)
                    <div class="card border-0 bg-light mb-4">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3">Write a Review</h6>
                            <form action="{{ route('reviews.store', $hotel) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small">Rating</label>
                                    <div class="d-flex gap-2">
                                        @for($i = 1; $i <= 5; $i++)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="rating"
                                                   id="r{{ $i }}" value="{{ $i }}" required>
                                            <label class="form-check-label star-rating" for="r{{ $i }}">{{ $i }} ★</label>
                                        </div>
                                        @endfor
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="title" class="form-control form-control-sm"
                                           placeholder="Review title (optional)" maxlength="100">
                                </div>
                                <div class="mb-3">
                                    <textarea name="comment" class="form-control form-control-sm" rows="3"
                                              placeholder="Share your experience..." maxlength="1000"></textarea>
                                </div>
                                <button class="btn btn-primary btn-sm" type="submit">Submit Review</button>
                            </form>
                        </div>
                    </div>
                    @elseif($userReview)
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="bi bi-check-circle me-1"></i>You've already reviewed this hotel.
                    </div>
                    @endif
                    @endauth

                    {{-- Reviews List --}}
                    @forelse($hotel->approvedReviews as $review)
                    <div class="d-flex gap-3 mb-4 pb-4 border-bottom">
                        <img src="{{ $review->user->avatar_url }}" alt="" width="44" height="44"
                             class="rounded-circle flex-shrink-0" style="object-fit:cover">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong class="small">{{ $review->user->name }}</strong>
                                    <div class="review-stars small">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5-$review->rating) }}</div>
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    <span class="text-muted" style="font-size:.75rem">{{ $review->created_at->diffForHumans() }}</span>
                                    @auth
                                    @if(Auth::id() === $review->user_id)
                                    <form action="{{ route('reviews.destroy', $review) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-link btn-sm text-danger p-0" type="submit"
                                                onclick="return confirm('Delete this review?')">
                                            <i class="bi bi-trash small"></i>
                                        </button>
                                    </form>
                                    @endif
                                    @endauth
                                </div>
                            </div>
                            @if($review->title)<p class="fw-semibold mb-1 small">{{ $review->title }}</p>@endif
                            <p class="text-muted small mb-0">{{ $review->comment }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-chat-square display-4 opacity-25"></i>
                        <p class="mt-2">No reviews yet. Be the first!</p>
                    </div>
                    @endforelse
                </div>

            </div>{{-- /tab-content --}}

            {{-- Related Hotels --}}
            @if($relatedHotels->count())
            <div class="mt-5">
                <h5 class="fw-bold mb-3"><i class="bi bi-building me-2 text-primary"></i>Other Hotels in {{ $hotel->city }}</h5>
                <div class="row g-3">
                    @foreach($relatedHotels as $related)
                    <div class="col-md-4">
                        <a href="{{ route('hotels.show', array_merge(['hotel' => $related->id], request()->only(['check_in', 'check_out', 'guests']))) }}" class="text-decoration-none text-dark">
                            <div class="card border-0 shadow-sm h-100 hotel-card">
                                <img src="{{ $related->image_url }}" alt="" class="card-img-top" style="height: 140px; object-fit: cover;" loading="lazy">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold mb-1 text-truncate" style="font-size: 15px;">{{ $related->name }}</h6>
                                    <p class="text-muted small mb-2"><i class="bi bi-geo-alt me-1"></i>{{ $related->city }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-warning small">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span style="color:{{ $i <= $related->star_rating ? '#f59e0b' : '#dee2e6' }}">★</span>
                                            @endfor
                                        </div>
                                        @if($related->min_price)
                                        <div class="small text-primary fw-bold">from BDT {{ number_format($related->min_price) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column: Booking Widget --}}
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-booking">
                <div class="card-header bg-primary text-white fw-semibold">
                    <i class="bi bi-calendar-check me-2"></i>Book a Room
                </div>
                <div class="card-body">
                    @php
                        $minPriceForDates = $hotel->getMinPriceForDates(request('check_in'), request('check_out'));
                    @endphp
                    @if($minPriceForDates || $hotel->min_price)
                    <p class="text-muted small mb-2">Starting from</p>
                    <p class="mb-3">
                        <span style="font-size:1.8rem;font-weight:800;color:#1a56db">BDT {{ number_format($minPriceForDates ?: $hotel->min_price) }}</span>
                        <span class="text-muted">/night</span>
                    </p>
                    @endif

                    <div class="d-grid gap-2">
                        <a href="#tabRooms" onclick="document.querySelector('[data-bs-target=\'#tabRooms\']').click()"
                           class="btn btn-primary">
                            <i class="bi bi-door-open me-2"></i>View Available Rooms
                        </a>
                        @auth
                        <button class="btn btn-outline-danger {{ $isFavorited ? 'active' : '' }} favorite-btn"
                                data-hotel-id="{{ $hotel->id }}"
                                data-favorited="{{ $isFavorited ? '1' : '0' }}">
                            <i class="bi {{ $isFavorited ? 'bi-heart-fill' : 'bi-heart' }} me-2"></i>
                            <span class="fav-widget-label">{{ $isFavorited ? 'Saved to Wishlist' : 'Save to Wishlist' }}</span>
                        </button>
                        @endauth
                    </div>

                    <hr>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i>Free cancellation (varies)</li>
                        <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i>Instant booking confirmation</li>
                        <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i>PDF invoice download</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Email confirmation</li>
                    </ul>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;



// Room availability check on date change
function checkRoomAvailability() {
    const checkIn  = document.getElementById('roomCheckIn')?.value;
    const checkOut = document.getElementById('roomCheckOut')?.value;
    const guests   = document.getElementById('roomGuests')?.value || 1;
    if (!checkIn || !checkOut) return;

    document.querySelectorAll('.book-room-btn').forEach(async btn => {
        const url       = btn.dataset.availUrl;
        const roomId    = btn.dataset.roomId;
        const msgEl     = document.getElementById(`availMsg_${roomId}`);

        try {
            const res  = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With':'XMLHttpRequest' },
                body: JSON.stringify({ check_in: checkIn, check_out: checkOut })
            });
            const data = await res.json();
            if (msgEl) {
                msgEl.innerHTML = data.available
                    ? `<span class="text-success"><i class="bi bi-check-circle"></i> Available · ${data.nights} nights · BDT ${data.total_price.toLocaleString()}</span>`
                    : `<span class="text-danger"><i class="bi bi-x-circle"></i> Not available</span>`;
            }
            // Update book button href with all inputs (dates & guest count)
            const urlObj = new URL(btn.href, window.location.origin);
            urlObj.searchParams.set('check_in', checkIn);
            urlObj.searchParams.set('check_out', checkOut);
            urlObj.searchParams.set('guests', guests);
            btn.href = urlObj.pathname + urlObj.search;
        } catch(e) {}
    });
}

document.getElementById('roomCheckIn')?.addEventListener('change', checkRoomAvailability);
document.getElementById('roomCheckOut')?.addEventListener('change', checkRoomAvailability);
document.getElementById('roomGuests')?.addEventListener('change', checkRoomAvailability);
document.addEventListener('DOMContentLoaded', checkRoomAvailability);

// Auto-activate reviews tab if there are review errors or success
@if($errors->has('review') || $errors->has('rating') || $errors->has('title') || $errors->has('comment') || (session('success') && Str::contains(session('success'), ['review', 'Review'])))
document.addEventListener("DOMContentLoaded", function() {
    const reviewTabBtn = document.querySelector('button[data-bs-target="#tabReviews"]');
    if (reviewTabBtn) {
        const tab = bootstrap.Tab.getOrCreateInstance(reviewTabBtn);
        if (tab) {
            tab.show();
            reviewTabBtn.scrollIntoView({ behavior: 'smooth' });
        }
    }
});
@endif
</script>
@endpush
