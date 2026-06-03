@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Reports</h4>
            <small class="text-muted">Annual revenue, top hotels, and search analytics.</small>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Revenue</div><div class="fs-4 fw-bold">৳{{ number_format($totalRevenue) }}</div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Bookings</div><div class="fs-4 fw-bold">{{ number_format($totalBookings) }}</div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Cancelled</div><div class="fs-4 fw-bold">{{ number_format($totalCancelled) }}</div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Avg Booking</div><div class="fs-4 fw-bold">৳{{ number_format($avgBookingValue, 2) }}</div></div></div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Monthly Booking Revenue</div>
                <div class="card-body"><canvas id="revenueChart" height="110"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Top Revenue Hotels</div>
                <div class="card-body"><canvas id="hotelChart" height="110"></canvas></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Most Searched Terms</div>
                <div class="card-body"><canvas id="termChart" height="120"></canvas></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Most Searched Cities</div>
                <div class="card-body"><canvas id="cityChart" height="120"></canvas></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const monthlyStats = @json($monthlyStats);
const topRevenue   = @json($topRevenue);
const searchTerms  = @json($topSearchTerms);
const searchCities = @json($topSearchCities);

new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: monthlyStats.map(item => item.label),
        datasets: [{
            label: 'Revenue',
            data: monthlyStats.map(item => item.revenue),
            borderColor: '#1a56db',
            backgroundColor: 'rgba(26,86,219,.08)',
            fill: true,
            tension: .35,
        }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

new Chart(document.getElementById('hotelChart'), {
    type: 'bar',
    data: {
        labels: topRevenue.map(item => item.name),
        datasets: [{ label: 'Revenue', data: topRevenue.map(item => item.revenue), backgroundColor: '#10b981' }]
    },
    options: { indexAxis: 'y', plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('termChart'), {
    type: 'bar',
    data: {
        labels: searchTerms.map(item => item.term),
        datasets: [{ label: 'Searches', data: searchTerms.map(item => item.searches), backgroundColor: '#f59e0b' }]
    },
    options: { plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('cityChart'), {
    type: 'bar',
    data: {
        labels: searchCities.map(item => item.city),
        datasets: [{ label: 'Searches', data: searchCities.map(item => item.searches), backgroundColor: '#06b6d4' }]
    },
    options: { plugins: { legend: { display: false } } }
});
</script>
@endpush
