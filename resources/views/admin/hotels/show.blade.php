@extends('layouts.admin')

@section('title', $hotel->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.hotels.index') }}">Hotels</a></li>
    <li class="breadcrumb-item active">{{ $hotel->name }}</li>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">{{ $hotel->name }}</h4>
            <small class="text-muted"><i class="bi bi-geo-alt-fill me-1"></i>{{ $hotel->address }}, {{ $hotel->city }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.hotels.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back to Hotels
            </a>
            <a href="{{ route('admin.hotels.edit', $hotel) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil me-1"></i>Edit Hotel
            </a>
            <a href="{{ route('admin.hotels.rooms.create', $hotel) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Add Room
            </a>
        </div>
    </div>

    {{-- ── Real-Time Performance Widget Metrics ────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xxl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded p-3 me-3">
                        <i class="bi bi-journal-check fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small text-uppercase fw-semibold">Total Bookings</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($hotel->bookings_count) }}</h3>
                        <small class="text-success text-xs"><i class="bi bi-check-circle me-1"></i>{{ $activeBookingsCount }} Active / Pending</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xxl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="bg-success bg-opacity-10 text-success rounded p-3 me-3">
                        <i class="bi bi-currency-dollar fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small text-uppercase fw-semibold">Net Revenue</h6>
                        <h3 class="fw-bold mb-0">৳{{ number_format($totalRevenue) }}</h3>
                        <small class="text-muted text-xs">Avg. ৳{{ number_format($avgRevenuePerBooking, 1) }} / booking</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xxl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="bg-danger bg-opacity-10 text-danger rounded p-3 me-3">
                        <i class="bi bi-x-circle fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small text-uppercase fw-semibold">Cancellation Rate</h6>
                        <h3 class="fw-bold mb-0">{{ $cancellationRate }}%</h3>
                        <small class="text-danger text-xs"><i class="bi bi-exclamation-circle me-1"></i>{{ $cancelledBookingsCount }} Cancelled bookings</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xxl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="bg-warning bg-opacity-10 text-warning rounded p-3 me-3">
                        <i class="bi bi-clock-history fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small text-uppercase fw-semibold">Avg. Stay Duration</h6>
                        <h3 class="fw-bold mb-0">{{ $avgStayNights }} <span class="fs-6 fw-normal text-muted">Nights</span></h3>
                        <small class="text-muted text-xs">Active guest length average</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <ul class="nav nav-pills mb-4 gap-2" id="hotelShowTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active border-0 px-4 shadow-sm" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab" aria-selected="true">
                <i class="bi bi-grid-fill me-2"></i>Overview & Rooms
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link border-0 px-4 shadow-sm" id="analytics-tab" data-bs-toggle="pill" data-bs-target="#analytics" type="button" role="tab" aria-selected="false">
                <i class="bi bi-bar-chart-line-fill me-2"></i>Revenue Timeline
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link border-0 px-4 shadow-sm" id="calendar-tab" data-bs-toggle="pill" data-bs-target="#calendar" type="button" role="tab" aria-selected="false">
                <i class="bi bi-calendar3-range-fill me-2"></i>Occupancy & 14-Day Calendar
            </button>
        </li>
    </ul>

    <div class="tab-content" id="hotelShowTabsContent">
        {{-- ── TAB 1: Overview & Rooms ─────────────────────────── --}}
        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
            <div class="row g-4">
                {{-- Left Details Card --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <img src="{{ $hotel->image_url }}" alt="{{ $hotel->name }}" class="card-img-top rounded-top" style="max-height: 240px; object-fit: cover;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-{{ $hotel->is_active ? 'success' : 'secondary' }}">{{ $hotel->is_active ? 'Active' : 'Inactive' }}</span>
                                <div class="text-warning">
                                    {!! $hotel->star_icons !!}
                                </div>
                            </div>
                            
                            <h5 class="fw-bold mb-3">Hotel Information</h5>
                            <ul class="list-unstyled mb-4 text-muted small">
                                <li class="mb-2"><i class="bi bi-telephone text-primary me-2"></i>{{ $hotel->phone ?: 'No phone contact' }}</li>
                                <li class="mb-2"><i class="bi bi-envelope text-primary me-2"></i>{{ $hotel->email ?: 'No email contact' }}</li>
                                <li class="mb-2"><i class="bi bi-globe text-primary me-2"></i>
                                    @if($hotel->website)
                                        <a href="{{ $hotel->website }}" target="_blank" class="text-decoration-none">{{ $hotel->website }}</a>
                                    @else
                                        No website
                                    @endif
                                </li>
                                <li class="mb-2"><i class="bi bi-clock-fill text-primary me-2"></i>Check-in: {{ $hotel->check_in_time ?: '14:00' }} / Out: {{ $hotel->check_out_time ?: '12:00' }}</li>
                            </ul>

                            <h5 class="fw-bold mb-2">Description</h5>
                            <p class="text-muted small mb-4" style="line-height:1.6;">{{ $hotel->description ?: 'No description provided.' }}</p>

                            <h5 class="fw-bold mb-2">Amenities</h5>
                            <div>
                                @forelse($hotel->amenities ?? [] as $amenity)
                                    <span class="badge bg-light text-dark border me-1 mb-1">{{ $amenity }}</span>
                                @empty
                                    <span class="text-muted small">No amenities specified.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Rooms & Bookings --}}
                <div class="col-lg-8">
                    {{-- Rooms List --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="fw-bold mb-0">Rooms Inventory</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Room Type</th>
                                        <th>Room Number</th>
                                        <th>Price</th>
                                        <th>Capacity</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                        <th>Bookings</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($hotel->rooms as $room)
                                    <tr>
                                        <td><strong>{{ $room->room_type }}</strong></td>
                                        <td>{{ $room->room_number ?: '—' }}</td>
                                        <td>৳{{ number_format($room->price) }}</td>
                                        <td>{{ $room->capacity }} guest(s)</td>
                                        <td>{{ $room->quantity }}</td>
                                        <td>
                                            <span class="badge bg-{{ $room->is_available ? 'success' : 'secondary' }}">
                                                {{ $room->is_available ? 'Available' : 'Hidden' }}
                                            </span>
                                        </td>
                                        <td><span class="badge bg-light text-dark border">{{ $room->bookings_count }}</span></td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.rooms.edit', $room) }}" class="btn btn-outline-primary">Edit</a>
                                                <form action="{{ route('admin.rooms.destroy', $room) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this room?')" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-outline-danger" type="submit">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No rooms added yet. <a href="{{ route('admin.hotels.rooms.create', $hotel) }}">Add one now</a>.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Recent Bookings --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="fw-bold mb-0">Recent Bookings</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Reference</th>
                                        <th>Guest</th>
                                        <th>Dates</th>
                                        <th>Room</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($hotel->bookings as $booking)
                                    <tr>
                                        <td><span class="font-monospace fw-semibold">{{ $booking->booking_reference }}</span></td>
                                        <td>
                                            <div><strong>{{ $booking->guest_name }}</strong></div>
                                            <small class="text-muted">{{ $booking->guest_email }}</small>
                                        </td>
                                        <td>
                                            <div>{{ $booking->check_in->format('Y-m-d') }}</div>
                                            <small class="text-muted">to {{ $booking->check_out->format('Y-m-d') }}</small>
                                        </td>
                                        <td>{{ $booking->room->room_type }}</td>
                                        <td>৳{{ number_format($booking->total_price) }}</td>
                                        <td>
                                            <x-status-badge :status="$booking->status" />
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-link btn-sm text-decoration-none fw-semibold">Details</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No bookings record found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── TAB 2: Revenue Timeline ────────────────────────── --}}
        <div class="tab-pane fade" id="analytics" role="tabpanel" aria-labelledby="analytics-tab">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0 fw-bold">
                    <i class="bi bi-graph-up me-2 text-primary"></i>Hotel Booking & Revenue Trend (Last 12 Months)
                </div>
                <div class="card-body">
                    <canvas id="hotelRevenueChart" height="100"></canvas>
                </div>
            </div>
        </div>

        {{-- ── TAB 3: Occupancy & 14-Day Calendar ──────────────── --}}
        <div class="tab-pane fade" id="calendar" role="tabpanel" aria-labelledby="calendar-tab">
            {{-- Room Occupancy Indicators --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-percent me-2 text-primary"></i>30-Day Room Occupancy Rates</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @forelse($hotel->rooms as $room)
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>{{ $room->room_type }}</strong>
                                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ $room->occupancy_rate }}% Occupied</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $room->occupancy_rate }}%" aria-valuenow="{{ $room->occupancy_rate }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted d-block mt-2">Capacity: {{ $room->quantity }} rooms · {{ $room->bookings_count }} bookings overall</small>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center text-muted">No rooms to calculate occupancy.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- 14-Day Occupancy Calendar --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-calendar3 me-2 text-success"></i>Interactive 14-Day Room Availability Calendar</h5>
                    <span class="badge bg-success bg-opacity-10 text-success">Real-Time Database Sync</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start" style="min-width: 160px; z-index: 10; position: sticky; left: 0; background: #f8fafc;">Room Type</th>
                                    @foreach($calendarDates as $d)
                                    <th class="small py-2" style="min-width: 90px;">
                                        {{ Carbon\Carbon::parse($d)->format('D') }}<br>
                                        <span class="fw-bold">{{ Carbon\Carbon::parse($d)->format('M j') }}</span>
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hotel->rooms as $room)
                                <tr>
                                    <td class="text-start fw-bold" style="position: sticky; left: 0; background: #fff; border-right: 2px solid #dee2e6;">
                                        {{ $room->room_type }}<br>
                                        <small class="text-muted fw-normal" style="font-size: 0.75rem">Qty: {{ $room->quantity }}</small>
                                    </td>
                                    @foreach($calendarDates as $d)
                                        @php
                                            $dayData = $roomCalendar[$room->id][$d] ?? ['booked' => 0, 'available' => $room->quantity, 'percent' => 0];
                                        @endphp
                                        <td class="py-3 px-1 @if($dayData['percent'] >= 100) bg-danger bg-opacity-10 @elseif($dayData['booked'] > 0) bg-warning bg-opacity-10 @endif">
                                            @if($dayData['percent'] >= 100)
                                                <span class="text-danger fw-bold d-block text-xs">SOLD OUT</span>
                                            @else
                                                <span class="fw-semibold d-block text-sm" style="font-size: 0.9rem;">
                                                    {{ $dayData['available'] }} free
                                                </span>
                                            @endif
                                            <span class="text-muted d-block text-xs" style="font-size: 0.75rem;">
                                                ({{ $dayData['booked'] }} / {{ $room->quantity }} booked)
                                            </span>
                                        </td>
                                    @endforeach
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="15" class="text-muted py-3">No rooms configured.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Chart.defaults.font.family = "'Segoe UI', system-ui, sans-serif";
    Chart.defaults.color = '#64748b';

    // Timeline Chart
    const monthlyStats = @json($monthlyStats);
    const chartContext = document.getElementById('hotelRevenueChart');
    if (chartContext) {
        new Chart(chartContext, {
            type: 'line',
            data: {
                labels: monthlyStats.map(m => m.label),
                datasets: [
                    {
                        label: 'Net Revenue (৳)',
                        data: monthlyStats.map(m => m.revenue),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.06)',
                        borderWidth: 2,
                        pointBackgroundColor: '#10b981',
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Bookings Count',
                        data: monthlyStats.map(m => m.count),
                        borderColor: '#1a56db',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        pointBackgroundColor: '#1a56db',
                        pointStyle: 'circle',
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        ticks: { callback: v => '৳' + v.toLocaleString() },
                        title: { display: true, text: 'Revenue (৳)' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                        ticks: { precision: 0 },
                        title: { display: true, text: 'Bookings Count' }
                    }
                }
            }
        });
    }
});
</script>
@endpush
