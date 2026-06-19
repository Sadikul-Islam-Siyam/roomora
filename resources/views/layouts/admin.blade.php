<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Roomora Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --sidebar-w: 250px; }
        body { background: #f1f5f9; }
        .sidebar {
            width: var(--sidebar-w); height: 100vh; position: fixed; top: 0; left: 0;
            background: #0f172a; z-index: 1040; overflow-y: auto;
            transition: transform .3s;
        }
        .sidebar-brand { padding: 20px; font-size: 1.4rem; font-weight: 700; color: #fff; border-bottom: 1px solid rgba(255,255,255,.1); }
        .sidebar-brand span { color: #f59e0b; }
        .sidebar .nav-link { color: #94a3b8; padding: 10px 20px; border-radius: 6px; margin: 2px 8px; transition: all .2s; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,.1); color: #fff; }
        .sidebar .nav-link i { width: 20px; }
        .sidebar-section { padding: 8px 20px; font-size: .7rem; text-transform: uppercase; letter-spacing: 1px; color: #475569; }
        .main-content { margin-left: var(--sidebar-w); padding: 24px; }
        .topbar { background: #fff; border-bottom: 1px solid #e2e8f0; padding: 12px 24px; margin: -24px -24px 24px; display: flex; justify-content: space-between; align-items: center; }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
        @media(max-width:768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- Sidebar --}}
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">Room<span>ora</span> <small class="d-block" style="font-size:.7rem;font-weight:400;color:#64748b">Admin Panel</small></div>

    <nav class="nav flex-column mt-2">
        <div class="sidebar-section">Main</div>
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
           href="{{ route('admin.dashboard') }}">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </a>

        <div class="sidebar-section mt-2">Manage</div>
        <a class="nav-link {{ request()->routeIs('admin.hotels.*') ? 'active' : '' }}"
           href="{{ route('admin.hotels.index') }}">
            <i class="bi bi-building me-2"></i>Hotels
        </a>
        <a class="nav-link {{ request()->routeIs('admin.rooms.*') || request()->routeIs('admin.hotels.rooms.*') ? 'active' : '' }}"
           href="{{ route('admin.rooms.index') }}">
            <i class="bi bi-door-open me-2"></i>Rooms
        </a>
        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
           href="{{ route('admin.users.index') }}">
            <i class="bi bi-people me-2"></i>Users
        </a>
        <a class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}"
           href="{{ route('admin.bookings.index') }}">
            <i class="bi bi-calendar-check me-2"></i>Bookings
            @php $pending = \App\Models\Booking::where('status','pending')->count() @endphp
            @if($pending > 0)
            <span class="badge bg-warning text-dark ms-auto">{{ $pending }}</span>
            @endif
        </a>

        <div class="sidebar-section mt-2">Analytics</div>
        <a class="nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}"
           href="{{ route('admin.reports') }}">
            <i class="bi bi-graph-up me-2"></i>Reports
        </a>

        <div class="sidebar-section mt-2">Account</div>
        <a class="nav-link" href="{{ route('hotels.index') }}" target="_blank">
            <i class="bi bi-box-arrow-up-right me-2"></i>View Site
        </a>
        <form action="{{ route('logout') }}" method="POST" class="px-2">
            @csrf
            <button class="nav-link btn btn-link text-start w-100 text-danger border-0">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </button>
        </form>
    </nav>
</div>

{{-- Main --}}
<div class="main-content">
    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-outline-secondary d-md-none" type="button" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-2">
            <img src="{{ Auth::user()->avatar_url }}" alt="" width="32" height="32" class="rounded-circle">
            <span class="small fw-semibold d-none d-md-inline">{{ Auth::user()->name }}</span>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
    document.getElementById('sidebar')?.classList.toggle('show');
    document.body.classList.toggle('sidebar-open');
}

document.addEventListener('click', function (event) {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar || !document.body.classList.contains('sidebar-open')) return;
    if (event.target.closest('.sidebar') || event.target.closest('[onclick="toggleSidebar()"]')) return;
    sidebar.classList.remove('show');
    document.body.classList.remove('sidebar-open');
});
</script>
@stack('scripts')
</body>
</html>
