@extends('layouts.app')

@section('title', 'Create Account')

@push('styles')
<style>
.auth-wrapper { min-height: calc(100vh - 80px); display: flex; align-items: center; background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); }
.auth-card { border: 0; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.25); }
.auth-brand { font-size: 2rem; font-weight: 800; color: #1a56db; }
.auth-brand span { color: #f59e0b; }
.form-control:focus { border-color: #1a56db; box-shadow: 0 0 0 3px rgba(26,86,219,.1); }
.btn-auth { background: #1a56db; color: #fff; font-weight: 600; padding: 12px; border: 0; border-radius: 10px; transition: all .2s; }
.btn-auth:hover { background: #1748c0; color: #fff; transform: translateY(-1px); }
.strength-bar { height: 4px; border-radius: 2px; transition: all .3s; }
</style>
@endpush

@section('content')
<div class="auth-wrapper py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">

                <div class="auth-card card">
                    <div class="card-body p-4 p-md-5">

                        <div class="text-center mb-4">
                            <div class="auth-brand">Room<span>ora</span></div>
                            <p class="text-muted small mb-0">Create your free account</p>
                        </div>

                        @if($errors->any())
                        <div class="alert alert-danger py-2 small">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label small fw-semibold" for="name">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input id="name" type="text" name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}" required autofocus
                                           placeholder="John Doe">
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold" for="email">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input id="email" type="email" name="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}" required
                                           placeholder="you@example.com">
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold" for="phone">
                                    Phone <span class="text-muted fw-normal">(optional)</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input id="phone" type="tel" name="phone"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone') }}"
                                           placeholder="+880 1700 000000">
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold" for="password">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input id="password" type="password" name="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           required placeholder="Min. 8 characters"
                                           oninput="checkStrength(this.value)">
                                    <button class="btn btn-outline-secondary" type="button"
                                            onclick="togglePass('password','eyeIcon1')">
                                        <i class="bi bi-eye" id="eyeIcon1"></i>
                                    </button>
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Strength bar --}}
                                <div class="mt-1 d-flex gap-1" id="strengthBars">
                                    <div class="strength-bar bg-secondary flex-fill" id="sb1"></div>
                                    <div class="strength-bar bg-secondary flex-fill" id="sb2"></div>
                                    <div class="strength-bar bg-secondary flex-fill" id="sb3"></div>
                                    <div class="strength-bar bg-secondary flex-fill" id="sb4"></div>
                                </div>
                                <small id="strengthLabel" class="text-muted"></small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold" for="password_confirmation">
                                    Confirm Password
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input id="password_confirmation" type="password"
                                           name="password_confirmation"
                                           class="form-control" required placeholder="Re-enter password">
                                    <button class="btn btn-outline-secondary" type="button"
                                            onclick="togglePass('password_confirmation','eyeIcon2')">
                                        <i class="bi bi-eye" id="eyeIcon2"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-auth w-100 mb-3">
                                <i class="bi bi-person-plus me-2"></i>Create Account
                            </button>

                            <div class="text-center">
                                <span class="text-muted small">Already have an account?</span>
                                <a href="{{ route('login') }}" class="ms-1 small fw-semibold">Sign in</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePass(id, iconId) {
    const p = document.getElementById(id);
    const i = document.getElementById(iconId);
    p.type = p.type === 'password' ? 'text' : 'password';
    i.className = p.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

function checkStrength(val) {
    const bars  = ['sb1','sb2','sb3','sb4'];
    const label = document.getElementById('strengthLabel');
    let score   = 0;

    if (val.length >= 8)              score++;
    if (/[A-Z]/.test(val))           score++;
    if (/[0-9]/.test(val))           score++;
    if (/[^A-Za-z0-9]/.test(val))   score++;

    const colors  = ['bg-danger','bg-warning','bg-info','bg-success'];
    const labels  = ['Weak','Fair','Good','Strong'];

    bars.forEach((id, i) => {
        const el = document.getElementById(id);
        el.className = `strength-bar flex-fill ${i < score ? colors[score-1] : 'bg-secondary'}`;
    });

    label.textContent  = val.length ? labels[score-1] || '' : '';
    label.style.color  = score === 4 ? '#10b981' : score >= 2 ? '#f59e0b' : '#ef4444';
}
</script>
@endpush
