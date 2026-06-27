@extends('layouts.app')

@section('title', 'Complete Your Booking')

@push('styles')
<style>
.booking-form { max-width: 900px; margin: 0 auto; }
.summary-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }
.price-breakdown td { padding: 6px 0; }
.price-breakdown .total-row td { font-weight: 700; border-top: 2px solid #e2e8f0; padding-top: 12px; }
.step-indicator { display: flex; align-items: center; gap: 8px; margin-bottom: 24px; }
.step { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 700; }
.step.done { background: #1a56db; color: #fff; }
.step.current { background: #1a56db; color: #fff; outline: 3px solid rgba(26,86,219,.2); }
.step.pending { background: #e2e8f0; color: #94a3b8; }
.step-line { flex: 1; height: 2px; background: #e2e8f0; }
.step-line.done { background: #1a56db; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="booking-form">

        {{-- Steps --}}
        <div class="step-indicator">
            <div class="step done">1</div>
            <div class="step-line done"></div>
            <div class="step current">2</div>
            <div class="step-line"></div>
            <div class="step pending">3</div>
        </div>

        <h4 class="fw-bold mb-4">Complete Your Booking</h4>

        @if(!$isAvailable)
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            This room may not be available for your selected dates. Please go back and choose different dates.
        </div>
        @endif

        <div class="row g-4">

            {{-- ── Booking Form ─────────────────────────────── --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3">
                            <i class="bi bi-person-lines-fill me-2 text-primary"></i>Guest Information
                        </h5>

                        <form action="{{ route('bookings.store') }}" method="POST" id="bookingForm">
                            @csrf
                            <input type="hidden" name="room_id" value="{{ $room->id }}">

                            {{-- Dates --}}
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Check-in Date</label>
                                    <input type="date" name="check_in" id="checkIn"
                                           class="form-control @error('check_in') is-invalid @enderror"
                                           value="{{ old('check_in', $checkIn) }}"
                                           min="{{ today()->addDay()->format('Y-m-d') }}" required>
                                    @error('check_in')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Check-out Date</label>
                                    <input type="date" name="check_out" id="checkOut"
                                           class="form-control @error('check_out') is-invalid @enderror"
                                           value="{{ old('check_out', $checkOut) }}"
                                           min="{{ today()->addDays(2)->format('Y-m-d') }}" required>
                                    @error('check_out')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Number of Guests</label>
                                <select name="guests" class="form-select @error('guests') is-invalid @enderror"
                                        id="guestsSelect">
                                    @for($i = 1; $i <= $room->capacity; $i++)
                                    <option value="{{ $i }}" {{ old('guests', $guests) == $i ? 'selected' : '' }}>
                                        {{ $i }} Guest{{ $i > 1 ? 's' : '' }}
                                    </option>
                                    @endfor
                                </select>
                                <div class="form-text">Max capacity: {{ $room->capacity }} guests</div>
                                @error('guests')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <hr>
                            <h6 class="fw-semibold mb-3">Contact Details</h6>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Full Name</label>
                                <input type="text" name="guest_name"
                                       class="form-control @error('guest_name') is-invalid @enderror"
                                       value="{{ old('guest_name', Auth::user()->name) }}" required
                                       maxlength="100">
                                @error('guest_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Email</label>
                                    <input type="email" name="guest_email"
                                           class="form-control @error('guest_email') is-invalid @enderror"
                                           value="{{ old('guest_email', Auth::user()->email) }}" required>
                                    @error('guest_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Phone</label>
                                    <input type="tel" name="guest_phone"
                                           class="form-control @error('guest_phone') is-invalid @enderror"
                                           value="{{ old('guest_phone', Auth::user()->phone) }}" required>
                                    @error('guest_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">National ID (NID)</label>
                                <input type="text" name="guest_nid"
                                       class="form-control @error('guest_nid') is-invalid @enderror"
                                       value="{{ old('guest_nid') }}" required
                                       placeholder="Enter your National ID (NID) number"
                                       maxlength="50">
                                @error('guest_nid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold">
                                    Special Requests <span class="text-muted fw-normal">(optional)</span>
                                </label>
                                <textarea name="special_requests" class="form-control" rows="3"
                                          placeholder="Early check-in, late checkout, dietary requirements..."
                                          maxlength="500">{{ old('special_requests') }}</textarea>
                            </div>

                            {{-- Availability status indicator --}}
                            <div id="availabilityStatus" class="alert d-none mb-3 py-2 small"></div>

                            {{-- Validation errors --}}
                            @if($errors->any())
                            <div class="alert alert-danger py-2 small">
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                                </ul>
                            </div>
                            @endif

                            <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn"
                                    {{ !$isAvailable ? 'disabled' : '' }}>
                                <i class="bi bi-calendar-check me-2"></i>Confirm Booking
                            </button>
                            <p class="text-center small text-muted mt-2">
                                <i class="bi bi-shield-check me-1 text-success"></i>
                                Your information is protected by 256-bit encryption.
                            </p>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── Booking Summary ──────────────────────────── --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm sticky-top" style="top:90px">
                    <div class="card-header bg-primary text-white fw-semibold">
                        <i class="bi bi-receipt me-2"></i>Booking Summary
                    </div>
                    <div class="card-body">
                        {{-- Hotel Info --}}
                        <div class="d-flex gap-3 mb-3">
                            <img src="{{ $room->hotel->image_url }}" alt=""
                                 width="70" height="70" class="rounded" style="object-fit:cover">
                            <div>
                                <div class="fw-semibold">{{ $room->hotel->name }}</div>
                                <div class="text-muted small">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $room->hotel->city }}
                                </div>
                                <div class="badge bg-warning text-dark small">
                                    {{ $room->hotel->star_rating }} ★ Stars
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- Room Info --}}
                        <div class="mb-3">
                            <div class="fw-semibold small">{{ $room->type_name }}</div>
                            @if($room->facilities)
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @foreach(array_slice($room->facilities, 0, 4) as $f)
                                <span class="badge bg-light text-secondary" style="font-size:.65rem">{{ $f }}</span>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <hr>

                        {{-- Price Breakdown --}}
                        <table class="table table-sm price-breakdown mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted">Price per night</td>
                                    <td class="text-end">৳{{ number_format($room->price) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Duration</td>
                                    <td class="text-end" id="summaryNights">{{ $nights }} night{{ $nights > 1 ? 's' : '' }}</td>
                                </tr>
                                <tr class="total-row">
                                    <td>Total</td>
                                    <td class="text-end text-primary fs-5" id="summaryTotal">
                                        ৳{{ number_format($totalPrice) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="mt-3 p-2 bg-success bg-opacity-10 rounded small text-success">
                            <i class="bi bi-check-circle me-1"></i>Free cancellation before check-in
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const pricePerNight = {{ $room->price }};
const availUrl      = '{{ route('rooms.availability', $room) }}';
const csrfToken     = document.querySelector('meta[name="csrf-token"]').content;
let checkTimeout;

function updateSummary() {
    const checkIn  = document.getElementById('checkIn').value;
    const checkOut = document.getElementById('checkOut').value;
    if (!checkIn || !checkOut) return;

    clearTimeout(checkTimeout);
    checkTimeout = setTimeout(async () => {
        try {
            const res  = await fetch(availUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ check_in: checkIn, check_out: checkOut })
            });
            const data = await res.json();
            const statusEl = document.getElementById('availabilityStatus');
            const submitBtn = document.getElementById('submitBtn');

            document.getElementById('summaryNights').textContent = data.nights + ' night' + (data.nights > 1 ? 's' : '');
            document.getElementById('summaryTotal').textContent  = '৳' + data.total_price.toLocaleString();

            statusEl.classList.remove('d-none', 'alert-success', 'alert-danger');
            if (data.available) {
                statusEl.classList.add('alert-success');
                statusEl.innerHTML = '<i class="bi bi-check-circle me-1"></i>' + data.message;
                submitBtn.disabled = false;
            } else {
                statusEl.classList.add('alert-danger');
                statusEl.innerHTML = '<i class="bi bi-x-circle me-1"></i>' + data.message;
                submitBtn.disabled = true;
            }
        } catch(e) {}
    }, 400);
}

document.getElementById('checkIn')?.addEventListener('change', () => {
    const checkIn = document.getElementById('checkIn').value;
    document.getElementById('checkOut').min = checkIn;
    updateSummary();
});
document.getElementById('checkOut')?.addEventListener('change', updateSummary);
</script>
@endpush
