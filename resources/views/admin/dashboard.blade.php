@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Dashboard</h4>
            <small class="text-muted">{{ now()->format('l, F j, Y') }}</small>
        </div>
        <button class="btn btn-outline-primary btn-sm" onclick="refreshStats()">
            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
        </button>
    </div>

    {{-- ── Stat Cards ──────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Users</div>
                        <div class="fs-3 fw-bold" id="statUsers">{{ number_format($stats['total_users']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-building fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Hotels</div>
                        <div class="fs-3 fw-bold">{{ number_format($stats['total_hotels']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-calendar-check fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Bookings</div>
                        <div class="fs-3 fw-bold" id="statBookings">{{ number_format($stats['total_bookings']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-currency-dollar fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Monthly Revenue</div>
                        <div class="fs-3 fw-bold" id="statRevenue">
                            ৳{{ number_format($stats['monthly_revenue']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Charts Row ───────────────────────────────────── --}}
    <div class="row g-3 mb-4">

        {{-- Revenue Chart --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 fw-semibold">
                    <i class="bi bi-graph-up me-2 text-primary"></i>Monthly Revenue (Last 12 Months)
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="90"></canvas>
                </div>
            </div>
        </div>

        {{-- Booking Status Pie --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 fw-semibold">
                    <i class="bi bi-pie-chart me-2 text-primary"></i>Booking Status
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">

        {{-- Top Hotels Bar Chart --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 fw-semibold">
                    <i class="bi bi-trophy me-2 text-warning"></i>Top 5 Most Booked Hotels
                </div>
                <div class="card-body">
                    <canvas id="topHotelsChart" height="140"></canvas>
                </div>
            </div>
        </div>

        {{-- Room Type Popularity --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 fw-semibold">
                    <i class="bi bi-door-open me-2 text-success"></i>Popular Room Types
                </div>
                <div class="card-body">
                    <canvas id="roomTypeChart" height="140"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Recent Bookings Table ─────────────────────────── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-table me-2 text-primary"></i>Recent Bookings</span>
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Reference</th>
                            <th>Guest</th>
                            <th>Hotel / Room</th>
                            <th>Check-in</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentBookings as $booking)
                        <tr>
                            <td>
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="fw-semibold small text-primary text-decoration-none">
                                    {{ $booking->booking_reference }}
                                </a>
                            </td>
                            <td class="small">{{ $booking->user->name }}</td>
                            <td class="small">
                                {{ $booking->room->hotel->name ?? '—' }}<br>
                                <span class="text-muted">{{ $booking->room->room_type }}</span>
                            </td>
                            <td class="small">{{ $booking->check_in->format('M j, Y') }}</td>
                            <td class="small fw-semibold">৳{{ number_format($booking->total_price) }}</td>
                            <td>{!! $booking->status_badge !!}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Segoe UI', system-ui, sans-serif";
Chart.defaults.color = '#64748b';

// ── Revenue Line Chart ────────────────────────────────
const monthlyData = @json($monthlyRevenue);
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: monthlyData.map(m => m.label),
        datasets: [{
            label: 'Revenue (৳)',
            data: monthlyData.map(m => m.revenue),
            borderColor: '#1a56db',
            backgroundColor: 'rgba(26,86,219,0.08)',
            borderWidth: 2,
            pointBackgroundColor: '#1a56db',
            fill: true,
            tension: 0.4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => '৳' + v.toLocaleString() } }
        }
    }
});

// ── Status Doughnut Chart ────────────────────────────
const statusData = @json($statusDistribution);
const statusColors = {
    pending:      '#f59e0b',
    confirmed:    '#10b981',
    checked_in:   '#06b6d4',
    checked_out:  '#94a3b8',
    cancelled:    '#ef4444',
};
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(statusData).map(k => k.charAt(0).toUpperCase() + k.slice(1).replace('_', ' ')),
        datasets: [{
            data: Object.values(statusData),
            backgroundColor: Object.keys(statusData).map(k => statusColors[k] || '#94a3b8'),
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true } } }
    }
});

// ── Top Hotels Bar Chart ─────────────────────────────
const topHotels = @json($topHotels);
new Chart(document.getElementById('topHotelsChart'), {
    type: 'bar',
    data: {
        labels: topHotels.map(h => h.name),
        datasets: [{
            label: 'Bookings',
            data: topHotels.map(h => h.booking_count),
            backgroundColor: 'rgba(26,86,219,0.7)',
            borderRadius: 6,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});

// ── Room Type Polar Chart ────────────────────────────
const roomTypes = @json($roomTypeStats);
new Chart(document.getElementById('roomTypeChart'), {
    type: 'bar',
    data: {
        labels: roomTypes.map(r => r.room_type),
        datasets: [{
            label: 'Bookings',
            data: roomTypes.map(r => r.booking_count),
            backgroundColor: ['#1a56db','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'],
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});

// ── AJAX Refresh ─────────────────────────────────────
async function refreshStats() {
    try {
        const res  = await fetch('{{ route("admin.stats") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        document.getElementById('statUsers').textContent    = parseInt(data.total_users).toLocaleString();
        document.getElementById('statBookings').textContent = parseInt(data.total_bookings).toLocaleString();
        document.getElementById('statRevenue').textContent  = '৳' + parseFloat(data.monthly_revenue).toLocaleString();
    } catch(e) {}
}
</script>
@endpush
