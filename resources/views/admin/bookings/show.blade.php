@extends('layouts.admin')

@section('title', 'Booking Details')

@section('content')
<div class="container-fluid" style="max-width: 1100px;">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="fw-bold mb-1">{{ $booking->booking_reference }}</h4>
                            <p class="text-muted mb-0">{{ $booking->guest_name }} · {{ $booking->guest_email }}</p>
                        </div>
                        <span><x-status-badge :status="$booking->status" /></span>
                    </div>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Hotel</dt><dd class="col-sm-8">{{ $booking->room->hotel->name ?? '—' }}</dd>
                        <dt class="col-sm-4">Room</dt><dd class="col-sm-8">{{ $booking->room->type_name }}</dd>
                        <dt class="col-sm-4">Stay</dt><dd class="col-sm-8">{{ $booking->check_in->format('M d, Y') }} - {{ $booking->check_out->format('M d, Y') }} ({{ $booking->nights }} nights)</dd>
                        <dt class="col-sm-4">Guests</dt><dd class="col-sm-8">{{ $booking->guests }}</dd>
                        <dt class="col-sm-4">Total</dt><dd class="col-sm-8">৳{{ number_format($booking->total_price) }}</dd>
                    </dl>
                    @if(is_array($booking->guest_details) && count($booking->guest_details))
                        <hr>
                        <h6 class="mt-3">Guest Details</h6>
                        <table class="table table-sm mt-2">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>NID</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($booking->guest_details as $i => $g)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $g['name'] ?? '—' }}</td>
                                        <td>{{ $g['email'] ?? '—' }}</td>
                                        <td>{{ $g['phone'] ?? '—' }}</td>
                                        <td>{{ $g['nid'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">Update Status</h5>
                    <form action="{{ route('admin.bookings.status', $booking) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <select name="status" class="form-select mb-3">
                            @foreach(['pending','confirmed','checked_in','checked_out','cancelled'] as $status)
                                <option value="{{ $status }}" {{ $booking->status === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary w-100" type="submit">Save Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
