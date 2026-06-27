@extends('layouts.admin')

@section('title', 'Search Analytics')

@section('breadcrumb')
    <li class="breadcrumb-item active">Search Analytics</li>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Search Analytics</h4>
            <small class="text-muted">Analyze customer search terms, target cities, and zero-result queries</small>
        </div>
    </div>

    {{-- Overview Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded p-3 me-3">
                        <i class="bi bi-search fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small text-uppercase fw-semibold">Total Queries Logged</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($totalQueries) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="bg-success bg-opacity-10 text-success rounded p-3 me-3">
                        <i class="bi bi-calendar-event fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small text-uppercase fw-semibold">Queries (Last 30 Days)</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($queriesLast30Days) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="bg-info bg-opacity-10 text-info rounded p-3 me-3">
                        <i class="bi bi-list-stars fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small text-uppercase fw-semibold">Avg. Results per Search</h6>
                        <h3 class="fw-bold mb-0">{{ $avgResults }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Analysis Tables Row --}}
    <div class="row g-4 mb-4">
        {{-- Popular Search Terms --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart me-2 text-primary"></i>Top Search Terms</h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Term</th>
                                <th>Count</th>
                                <th>Avg. Results</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topTerms as $t)
                            <tr>
                                <td><span class="badge bg-light text-dark border font-monospace">"{{ $t->term }}"</span></td>
                                <td><span class="fw-bold">{{ $t->count }}</span></td>
                                <td>{{ round($t->avg_results, 1) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">No search terms logged.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Popular Cities Searched --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-geo-alt me-2 text-success"></i>Top Searched Cities</h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>City</th>
                                <th>Count</th>
                                <th>Avg. Results</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topCities as $c)
                            <tr>
                                <td><span class="fw-semibold text-success">{{ $c->city }}</span></td>
                                <td><span class="fw-bold">{{ $c->count }}</span></td>
                                <td>{{ round($c->avg_results, 1) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">No cities logged.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Zero Result Search Terms --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 border-danger border-opacity-10">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-danger"><i class="bi bi-exclamation-octagon me-2"></i>Zero-Result Queries</h5>
                    <span class="badge bg-danger bg-opacity-10 text-danger">Inventory Gap</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Term</th>
                                <th>City</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($zeroResults as $z)
                            <tr>
                                <td><span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10">"{{ $z->term }}"</span></td>
                                <td><span class="text-muted small">{{ $z->city ?: '—' }}</span></td>
                                <td><span class="fw-bold text-danger">{{ $z->count }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">No zero-result searches! Excellent.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Query Logs --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="bi bi-list-columns-reverse me-2 text-secondary"></i>Recent Search Log History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date / Time</th>
                            <th>User (Guest)</th>
                            <th>Search Term</th>
                            <th>Target City</th>
                            <th>Results Count</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="small">{{ Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($log->user_id)
                                    <div><strong>{{ $log->user_name }}</strong></div>
                                    <small class="text-muted">{{ $log->user_email }}</small>
                                @else
                                    <span class="text-muted italic small">Anonymous Guest</span>
                                @endif
                            </td>
                            <td><span class="font-monospace">"{{ $log->term }}"</span></td>
                            <td>
                                @if($log->city)
                                    <span class="text-success fw-semibold"><i class="bi bi-geo-alt me-1"></i>{{ $log->city }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $log->result_count > 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $log->result_count > 0 ? 'success' : 'danger' }} px-2 py-1">
                                    {{ $log->result_count }} {{ Str::plural('hotel', $log->result_count) }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ $log->ip_address }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No search query logs found in the database.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
