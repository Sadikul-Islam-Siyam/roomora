@extends('layouts.app')

@section('title', 'Booking ' . $booking->booking_reference)

@push('styles')
<style>
.booking-hero { background: linear-gradient(135deg, #0f172a, #1e3a5f); color: #fff; padding: 40px 0; }
.timeline-item { position: relative; padding-left: 30px; }
.timeline-item::before { content: ''; position: absolute; left: 8px; top: 0; bottom: 0; width: 2px; background: #e2e8f0; }
.timeline-dot { position: absolute; left: 0; top: 4px; width: 18px; height: 18px; border-radius: 50%; border: 2px solid #1a56db; background: #fff; z-index: 1; }
.timeline-dot.active { background: #1a56db; }
.detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
.detail-row:last-child { border-bottom: none; }
</style>
@endpush

@section('content')

{{-- Hero --}}
<div class="booking-hero">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="text-white-50 small mb-1">Booking Reference</div>
                <h2 class="fw-bold mb-1">{{ $booking->booking_reference }}</h2>
                <div><x-status-badge :status="$booking->status" /></div>
            </div>
            <div class="d-flex gap-2">
                @if($booking->is_paid || $booking->status === 'confirmed')
                <a href="{{ route('bookings.invoice', $booking) }}" class="btn btn-light btn-sm">
                    <i class="bi bi-download me-1"></i>Download Invoice
                </a>
                @else
                <button class="btn btn-light btn-sm disabled" title="Invoice available after payment">
                    <i class="bi bi-download me-1"></i>Download Invoice
                </button>
                @endif
                @if($booking->canBeCancelled())
                <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i class="bi bi-x-circle me-1"></i>Cancel Booking
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    <div class="row g-4">

        <div class="col-lg-8">

            {{-- Hotel & Room --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex gap-3 align-items-start">
                        <img src="{{ $booking->room->hotel->image_url }}" alt=""
                             width="100" height="80" class="rounded" style="object-fit:cover">
                        <div>
                            <h5 class="fw-bold mb-1">{{ $booking->room->hotel->name }}</h5>
                            <p class="text-muted small mb-1">
                                <i class="bi bi-geo-alt me-1"></i>{{ $booking->room->hotel->address }}
                            </p>
                            <span class="badge bg-warning text-dark">{{ $booking->room->hotel->star_rating }} ★ Stars</span>
                            <span class="badge bg-light text-secondary ms-1">{{ $booking->room->type_name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Booking Details --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-calendar3 me-2 text-primary"></i>Booking Details
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-6 col-md-3 text-center">
                            <div class="text-muted small">Check-in</div>
                            <div class="fw-bold">{{ $booking->check_in->format('D, M j') }}</div>
                            <div class="small text-muted">{{ $booking->check_in->format('Y') }}</div>
                            <div class="small text-primary">After {{ $booking->room->hotel->check_in_time }}</div>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="text-muted small">Check-out</div>
                            <div class="fw-bold">{{ $booking->check_out->format('D, M j') }}</div>
                            <div class="small text-muted">{{ $booking->check_out->format('Y') }}</div>
                            <div class="small text-primary">Before {{ $booking->room->hotel->check_out_time }}</div>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="text-muted small">Duration</div>
                            <div class="fw-bold fs-4">{{ $booking->nights }}</div>
                            <div class="small text-muted">Night{{ $booking->nights > 1 ? 's' : '' }}</div>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="text-muted small">Guests</div>
                            <div class="fw-bold fs-4">{{ $booking->guests }}</div>
                            <div class="small text-muted">Person{{ $booking->guests > 1 ? 's' : '' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Guest Details --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-person me-2 text-primary"></i>Guest Information</span>
                    @if($booking->guests > 1)
                    <button class="btn btn-sm btn-outline-primary" id="toggleEditGuestsBtn" onclick="toggleEditGuests()">
                        <i class="bi bi-pencil-square me-1"></i>Edit Guest List
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    {{-- Validation Errors Display --}}
                    @if ($errors->any())
                    <div class="alert alert-danger mb-3">
                        <ul class="mb-0 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- Static View --}}
                    <div id="staticGuestsView">
                        <div class="detail-row">
                            <span class="text-muted">Primary Guest Name</span><span class="fw-semibold">{{ $booking->guest_name }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="text-muted">Email</span><span>{{ $booking->guest_email }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="text-muted">Phone</span><span>{{ $booking->guest_phone }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="text-muted">National ID (NID)</span><span>{{ $booking->guest_nid ?? '—' }}</span>
                        </div>
                        @if($booking->special_requests)
                        <div class="detail-row">
                            <span class="text-muted">Special Requests</span>
                            <span class="text-end" style="max-width:60%">{{ $booking->special_requests }}</span>
                        </div>
                        @endif

                        @if($booking->guests > 1)
                        <div class="mt-4">
                            <h6 class="fw-semibold mb-3 text-muted"><i class="bi bi-people me-2"></i>Additional Guest Details</h6>
                            @php $details = $booking->guest_details ?? []; @endphp
                            @for($i = 0; $i < $booking->guests - 1; $i++)
                            <div class="p-3 border rounded mb-2 bg-light bg-opacity-50">
                                <div class="row g-2">
                                    <div class="col-md-3"><strong>Guest {{ $i + 2 }}:</strong> {{ $details[$i]['name'] ?? 'Not set' }}</div>
                                    <div class="col-md-3"><span class="text-muted small">Email:</span> {{ $details[$i]['email'] ?? '—' }}</div>
                                    <div class="col-md-3"><span class="text-muted small">Phone:</span> {{ $details[$i]['phone'] ?? '—' }}</div>
                                    <div class="col-md-3"><span class="text-muted small">NID:</span> {{ $details[$i]['nid'] ?? '—' }}</div>
                                </div>
                            </div>
                            @endfor
                        </div>
                        @endif
                    </div>

                    {{-- Inline Form View (hidden by default) --}}
                    @if($booking->guests > 1)
                    <div id="editGuestsFormView" class="d-none">
                        <form method="POST" action="{{ route('bookings.guests.update', $booking) }}">
                            @csrf
                            <input type="hidden" name="guests" value="{{ $booking->guests }}">

                            <p class="small text-muted mb-3"><i class="bi bi-info-circle me-1"></i>Please specify the details for each additional guest. All fields are required. NIDs are mandatory for multiple guests.</p>
                            @php $details = $booking->guest_details ?? []; @endphp
                            @for($i = 0; $i < $booking->guests - 1; $i++)
                            <div class="p-3 border rounded mb-3 bg-light">
                                <div class="fw-bold mb-2 text-primary">Guest {{ $i + 2 }} Details</div>
                                <div class="row g-2">
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label small mb-1">Full Name</label>
                                        <input name="guest_details[{{ $i }}][name]" value="{{ $details[$i]['name'] ?? '' }}" class="form-control form-control-sm" placeholder="Full name" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label small mb-1">Email Address</label>
                                        <input type="email" name="guest_details[{{ $i }}][email]" value="{{ $details[$i]['email'] ?? '' }}" class="form-control form-control-sm" placeholder="Email" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label small mb-1">Phone Number</label>
                                        <input name="guest_details[{{ $i }}][phone]" value="{{ $details[$i]['phone'] ?? '' }}" class="form-control form-control-sm" placeholder="Phone" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label small mb-1">National ID (NID)</label>
                                        <input name="guest_details[{{ $i }}][nid]" value="{{ $details[$i]['nid'] ?? '' }}" class="form-control form-control-sm" placeholder="NID number" required>
                                    </div>
                                </div>
                            </div>
                            @endfor
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleEditGuests()">Cancel</button>
                                <button type="submit" class="btn btn-sm btn-primary">Save Guest Details</button>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>
            </div>

            @if($booking->status === 'cancelled' && $booking->cancellation_reason)
            <div class="alert alert-danger mt-3">
                <strong>Cancellation Reason:</strong> {{ $booking->cancellation_reason }}<br>
                <small class="text-muted">Cancelled on: {{ $booking->cancelled_at?->format('M j, Y H:i') }}</small>
            </div>
            @endif
        </div>

        {{-- Price Summary --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top:90px">
                <div class="card-header bg-primary text-white fw-semibold">
                    <i class="bi bi-receipt me-2"></i>Price Summary
                </div>
                <div class="card-body">
                    <div class="detail-row">
                        <span class="text-muted">Room ({{ $booking->room->type_name }})</span>
                        <span>৳{{ number_format($booking->room_price) }}/night</span>
                    </div>
                    <div class="detail-row">
                        <span class="text-muted">Duration</span>
                        <span>× {{ $booking->nights }} nights</span>
                    </div>
                    @if($booking->discount > 0)
                    <div class="detail-row text-success">
                        <span>Discount</span>
                        <span>- ৳{{ number_format($booking->discount) }}</span>
                    </div>
                    @endif
                    <div class="detail-row" style="border-bottom:2px solid #e2e8f0">
                        <strong>Total</strong>
                        <strong class="text-primary fs-5">৳{{ number_format($booking->total_price) }}</strong>
                    </div>

                    <div class="mt-3 d-grid gap-2">
                        @if($booking->is_paid || $booking->status === 'confirmed')
                        <a href="{{ route('bookings.invoice', $booking) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Download Invoice (PDF)
                        </a>
                        @else
                        <form method="POST" action="{{ route('bookings.pay', $booking) }}">
                            @csrf
                            <div class="mb-2">
                                <select name="payment_method" class="form-select form-select-sm" required>
                                    <option value="">Select payment method</option>
                                    <option value="card_visa">Card (Visa)</option>
                                    <option value="card_master">Card (Mastercard)</option>
                                    <option value="bkash">bKash</option>
                                    <option value="cash">Pay at Hotel</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success btn-sm">Pay & Confirm</button>
                        </form>
                        @endif
                        <a href="{{ route('profile.bookings') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>All Bookings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Cancel Modal --}}
@if($booking->canBeCancelled())
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Cancel Booking
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('bookings.cancel', $booking) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to cancel booking <strong>{{ $booking->booking_reference }}</strong>?</p>
                    <div class="mb-3">
                        <label class="form-label small">Reason (optional)</label>
                        <textarea name="reason" class="form-control" rows="3"
                                  placeholder="Why are you cancelling?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Booking</button>
                    <button type="submit" class="btn btn-danger">Yes, Cancel It</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
function toggleEditGuests() {
    const staticView = document.getElementById('staticGuestsView');
    const formView = document.getElementById('editGuestsFormView');
    const btn = document.getElementById('toggleEditGuestsBtn');
    if (staticView && formView) {
        staticView.classList.toggle('d-none');
        formView.classList.toggle('d-none');
        if (formView.classList.contains('d-none')) {
            btn.innerHTML = '<i class="bi bi-pencil-square me-1"></i>Edit Guest List';
            btn.className = 'btn btn-sm btn-outline-primary';
        } else {
            btn.innerHTML = '<i class="bi bi-x-circle me-1"></i>Cancel Editing';
            btn.className = 'btn btn-sm btn-outline-secondary';
        }
    }
}
</script>
@endpush

@endsection
