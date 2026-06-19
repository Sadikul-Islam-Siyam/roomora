<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Roomora') — Hotel Booking & Comparison</title>
    <meta name="description" content="@yield('meta_description', 'Find and compare the best hotels at the best prices.')">

    {{-- Bootstrap 5 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --roomora-primary:   #1a56db;
            --roomora-secondary: #f59e0b;
            --roomora-dark:      #0f172a;
            --roomora-light:     #f8fafc;
        }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f8fafc; }
        .navbar-brand { font-weight: 700; font-size: 1.5rem; color: var(--roomora-primary) !important; }
        .navbar-brand span { color: var(--roomora-secondary); }
        .btn-primary { background: var(--roomora-primary); border-color: var(--roomora-primary); }
        .btn-primary:hover { background: #1748c0; border-color: #1748c0; }
        .star-rating { color: #f59e0b; }
        .hotel-card { transition: transform .2s, box-shadow .2s; }
        .hotel-card:hover { transform: translateY(-4px); box-shadow: 0 8px 30px rgba(0,0,0,.12); }
        .hotel-card img { height: 200px; object-fit: cover; }
        .comparison-bar { position: fixed; bottom: 0; left: 0; right: 0; z-index: 1050;
            background: var(--roomora-dark); color: #fff; padding: 12px 20px;
            transform: translateY(100%); transition: transform .3s; }
        .comparison-bar.visible { transform: translateY(0); }
        .badge-star { background: #f59e0b; color: #fff; }
        footer { background: var(--roomora-dark); color: #94a3b8; }
        footer a { color: #94a3b8; text-decoration: none; }
        footer a:hover { color: #fff; }
    </style>
    @yield('styles')
    @stack('styles')
</head>
<body>

{{-- ── Navbar ──────────────────────────────────────────── --}}
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('hotels.index') }}">
            Room<span>ora</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            @if(!request()->routeIs('login') && !request()->routeIs('register') && !request()->is('login') && !request()->is('register'))
            <form class="d-flex flex-column flex-lg-row mx-auto position-relative align-items-stretch align-items-lg-center gap-2 my-2 my-lg-0 w-100" style="max-width:700px;"
                  action="{{ route('hotels.index') }}" method="GET">
                <div class="position-relative flex-grow-1">
                    <input class="form-control form-control-sm" type="search" name="search" id="navSearch"
                           placeholder="Search hotels, cities..." value="{{ request('search') }}"
                           autocomplete="off" style="width:100%;">
                    {{-- AJAX Suggestions dropdown --}}
                    <ul id="searchSuggestions"
                        class="list-group position-absolute top-100 start-0 shadow d-none w-100"
                        style="z-index:9999;"></ul>
                </div>
                <div class="d-flex gap-1 flex-grow-1">
                    <input class="form-control form-control-sm w-50" type="date" name="check_in" id="navCheckIn"
                           value="{{ request('check_in', today()->addDay()->format('Y-m-d')) }}" title="Check-in">
                    <input class="form-control form-control-sm w-50" type="date" name="check_out" id="navCheckOut"
                           value="{{ request('check_out', today()->addDays(2)->format('Y-m-d')) }}" title="Check-out">
                </div>
                <div class="d-flex gap-1">
                    <select name="guests" class="form-select form-select-sm" style="min-width: 100px;" title="Guests">
                        @for($i=1; $i<=10; $i++)
                            <option value="{{ $i }}" {{ request('guests', 2) == $i ? 'selected' : '' }}>{{ $i }} {{ Str::plural('Guest', $i) }}</option>
                        @endfor
                    </select>
                    <button class="btn btn-primary btn-sm px-3" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
            @endif


            <ul class="navbar-nav ms-auto align-items-center gap-1">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('hotels.index') }}">Hotels</a>
                </li>

                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('comparisons.index') }}">
                            <i class="bi bi-bar-chart-steps"></i> Compare
                            <span id="compareCount" class="badge bg-primary ms-1 d-none">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('favorites.index') }}">
                            <i class="bi bi-heart"></i> Wishlist
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#"
                           data-bs-toggle="dropdown">
                            <img src="{{ Auth::user()->avatar_url }}" alt="" width="30" height="30"
                                 class="rounded-circle object-fit-cover">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}">
                                <i class="bi bi-person me-2"></i>Profile
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('profile.bookings') }}">
                                <i class="bi bi-calendar-check me-2"></i>My Bookings
                            </a></li>
                            @if(Auth::user()->isAdmin())
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-primary" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i>Admin Panel
                            </a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="dropdown-item text-danger" type="submit">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="btn btn-outline-primary btn-sm" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Sign Up</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

{{-- ── Flash Messages ──────────────────────────────────── --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible border-0 rounded-0 mb-0" role="alert">
    <div class="container">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible border-0 rounded-0 mb-0" role="alert">
    <div class="container">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
@endif

{{-- ── Main Content ─────────────────────────────────────── --}}
<main>
    @yield('content')
</main>

{{-- ── Comparison Bar ───────────────────────────────────── --}}
@auth
<div class="comparison-bar" id="comparisonBar">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <strong><i class="bi bi-bar-chart-steps me-2"></i>Comparison List:</strong>
            <span id="compareHotelNames" class="ms-2 text-warning">None selected</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('comparisons.index') }}" class="btn btn-warning btn-sm">Compare Now</a>
            <form action="{{ route('comparisons.clear') }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button class="btn btn-outline-light btn-sm" type="submit">Clear</button>
            </form>
        </div>
    </div>
</div>
@endauth

{{-- ── Footer ───────────────────────────────────────────── --}}
<footer class="py-5 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="text-white fw-bold">Room<span style="color:#f59e0b">ora</span></h5>
                <p class="small">Find and compare the best hotels worldwide. Transparent pricing, genuine reviews.</p>
            </div>
            <div class="col-lg-2">
                <h6 class="text-white">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li><a href="{{ route('hotels.index') }}">Browse Hotels</a></li>
                    @auth
                    <li><a href="{{ route('comparisons.index') }}">Compare</a></li>
                    <li><a href="{{ route('favorites.index') }}">Wishlist</a></li>
                    @endauth
                </ul>
            </div>
            <div class="col-lg-2">
                <h6 class="text-white">Account</h6>
                <ul class="list-unstyled small">
                    @guest
                    <li><a href="{{ route('login') }}">Login</a></li>
                    <li><a href="{{ route('register') }}">Register</a></li>
                    @endguest
                    @auth
                    <li><a href="{{ route('profile.show') }}">Profile</a></li>
                    <li><a href="{{ route('profile.bookings') }}">My Bookings</a></li>
                    @endauth
                </ul>
            </div>
        </div>
        <hr class="border-secondary mt-4">
        <p class="text-center small mb-0">
            &copy; {{ date('Y') }} Roomora. All rights reserved.
        </p>
    </div>
</footer>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- AJAX Search Suggestions --}}
<script>
const searchInput = document.getElementById('navSearch');
const suggestions = document.getElementById('searchSuggestions');
let searchTimeout;

if (searchInput) {
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        const q = this.value.trim();
        if (q.length < 2) { suggestions.classList.add('d-none'); return; }

        searchTimeout = setTimeout(async () => {
            try {
                const res  = await fetch(`{{ route('hotels.search') }}?q=${encodeURIComponent(q)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (!data.length) { suggestions.classList.add('d-none'); return; }

                suggestions.innerHTML = data.map(h => `
                    <li>
                        <a class="list-group-item list-group-item-action d-flex align-items-center gap-2" href="${h.url}">
                            <img src="${h.image}" width="40" height="40" style="object-fit:cover;border-radius:4px">
                            <div>
                                <div class="fw-semibold small">${h.name}</div>
                                <div class="text-muted" style="font-size:.75rem">${h.city} · ${'★'.repeat(Math.floor(h.stars))}</div>
                            </div>
                        </a>
                    </li>`).join('');

                suggestions.classList.remove('d-none');
            } catch (e) {}
        }, 300);
    });

    document.addEventListener('click', e => {
        if (!e.target.closest('form')) suggestions.classList.add('d-none');
    });
}

function syncComparisonBar() {
    const compareBar = document.getElementById('comparisonBar');
    const compareNames = document.getElementById('compareHotelNames');
    const compareCount = document.getElementById('compareCount');
    const selectedButtons = [...document.querySelectorAll('.compare-btn[data-in-comparison="1"]')];
    const names = selectedButtons.map(button => button.dataset.hotelName).filter(Boolean);

    if (compareCount) {
        compareCount.textContent = selectedButtons.length;
        compareCount.classList.toggle('d-none', selectedButtons.length === 0);
    }

    if (compareBar) {
        compareBar.classList.toggle('visible', selectedButtons.length > 0);
    }

    if (compareNames) {
        compareNames.textContent = names.length ? names.join(', ') : 'None selected';
    }
}

function setFavoriteState(hotelId, favorited) {
    document.querySelectorAll(`.favorite-btn[data-hotel-id="${hotelId}"]`).forEach(button => {
        button.dataset.favorited = favorited ? '1' : '0';
        button.classList.toggle('active', favorited);
        const icon = button.querySelector('i');
        if (icon) {
            icon.className = favorited ? 'bi bi-heart-fill' : 'bi bi-heart';
            // If the icon has me-1 or me-2, keep it
            if (icon.classList.contains('me-1')) {
                icon.className = favorited ? 'bi bi-heart-fill me-1' : 'bi bi-heart me-1';
            } else if (icon.classList.contains('me-2')) {
                icon.className = favorited ? 'bi bi-heart-fill me-2' : 'bi bi-heart me-2';
            }
        }

        const label = button.querySelector('#favLabel');
        const widgetLabel = button.querySelector('.fav-widget-label');
        if (label) {
            label.textContent = favorited ? 'Saved' : 'Save';
        } else if (widgetLabel) {
            widgetLabel.textContent = favorited ? 'Saved to Wishlist' : 'Save to Wishlist';
        } else {
            const span = button.querySelector('span');
            if (span) {
                span.textContent = favorited ? 'Saved' : 'Save';
            } else {
                button.lastChild && button.lastChild.nodeType === Node.TEXT_NODE && (button.lastChild.textContent = favorited ? ' Saved' : ' Save');
            }
        }
    });
}

function setCompareState(hotelId, added) {
    document.querySelectorAll(`.compare-btn[data-hotel-id="${hotelId}"]`).forEach(button => {
        button.dataset.inComparison = added ? '1' : '0';
        button.classList.toggle('btn-primary', added);
        button.classList.toggle('btn-outline-primary', !added);

        const label = button.querySelector('#compareLabel');
        if (label) {
            label.textContent = added ? 'In Comparison' : 'Compare';
        } else {
            const span = button.querySelector('span');
            if (span) {
                span.textContent = added ? 'In Comparison' : 'Compare';
            }
        }
    });
}

syncComparisonBar();

const navCheckIn = document.getElementById('navCheckIn');
const navCheckOut = document.getElementById('navCheckOut');
if (navCheckIn && navCheckOut) {
    navCheckIn.addEventListener('change', () => {
        if (navCheckOut.value <= navCheckIn.value) {
            const nextDate = new Date(navCheckIn.value);
            nextDate.setDate(nextDate.getDate() + 1);
            navCheckOut.value = nextDate.toISOString().split('T')[0];
        }
    });
}

document.addEventListener('click', async event => {
    const favoriteBtn = event.target.closest('.favorite-btn');
    const compareBtn = event.target.closest('.compare-btn');

    if (!favoriteBtn && !compareBtn) {
        return;
    }

    event.preventDefault();

    const showError = (message) => {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '1100';
            document.body.appendChild(container);
        }
        const toastId = 'toast_' + Date.now();
        container.innerHTML += `
            <div id="${toastId}" class="toast align-items-center text-white bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
              <div class="d-flex">
                <div class="toast-body">
                  <i class="bi bi-exclamation-triangle-fill me-2"></i> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
              </div>
            </div>`;
        setTimeout(() => {
            const toastEl = document.getElementById(toastId);
            if (toastEl) {
                toastEl.remove();
            }
        }, 5000);
    };

    if (favoriteBtn) {
        try {
            const response = await fetch(`/favorites/toggle/${favoriteBtn.dataset.hotelId}`, {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
            });

            if (!response.ok) {
                throw new Error('Server returned an error. Please try again.');
            }

            const data = await response.json();
            setFavoriteState(favoriteBtn.dataset.hotelId, data.favorited);
        } catch (error) {
            showError(error.message || 'Unable to update wishlist.');
        }
        return;
    }

    if (compareBtn) {
        try {
            const response = await fetch(`/compare/toggle/${compareBtn.dataset.hotelId}`, {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
            });

            if (!response.ok) {
                throw new Error('Server returned an error. Please try again.');
            }

            const data = await response.json();
            if (!data.success) {
                showError(data.message || 'Unable to update comparison list.');
                return;
            }

            setCompareState(compareBtn.dataset.hotelId, data.added);
            syncComparisonBar();
        } catch (error) {
            showError(error.message || 'Unable to update comparison list.');
        }
    }
});
</script>

@stack('scripts')
@yield('scripts')
</body>
</html>
