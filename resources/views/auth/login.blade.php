@extends('layouts.app')

@section('title', 'Login')

@push('styles')
<style>
.auth-wrapper { min-height: calc(100vh - 80px); display: flex; align-items: center; background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); }
.auth-card { border: 0; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.25); overflow: hidden; }
.auth-brand { font-size: 2rem; font-weight: 800; color: #1a56db; }
.auth-brand span { color: #f59e0b; }
.form-control:focus { border-color: #1a56db; box-shadow: 0 0 0 3px rgba(26,86,219,.1); }
.btn-auth { background: #1a56db; color: #fff; font-weight: 600; padding: 12px; border: 0; border-radius: 10px; transition: all .2s; }
.btn-auth:hover { background: #1748c0; color: #fff; transform: translateY(-1px); }
.divider { position: relative; text-align: center; margin: 20px 0; }
.divider::before { content: ''; position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: #e2e8f0; }
.divider span { background: #fff; padding: 0 12px; color: #94a3b8; font-size: .85rem; position: relative; }
</style>
@endpush

@section('content')
<div class="auth-wrapper py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">

                <div class="auth-card card">
                    <div class="card-body p-4 p-md-5">

                        {{-- Brand --}}
                        <div class="text-center mb-4">
                            <div class="auth-brand">Room<span>ora</span></div>
                            <p class="text-muted small mb-0">Sign in to your account</p>
                        </div>

                        {{-- Validation Errors --}}
                        @if($errors->any())
                        <div class="alert alert-danger py-2 small">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            {{ $errors->first() }}
                        </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label small fw-semibold" for="email">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input id="email" type="email" name="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}" required autofocus
                                           placeholder="you@example.com">
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label small fw-semibold" for="password">Password</label>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input id="password" type="password" name="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           required placeholder="••••••••">
                                    <button class="btn btn-outline-secondary" type="button"
                                            onclick="togglePass()">
                                        <i class="bi bi-eye" id="passEyeIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                    <label class="form-check-label small" for="remember">Remember me</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-auth w-100 mb-3">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                            </button>

                            <div class="divider"><span>or</span></div>

                            <div class="text-center mt-3">
                                <span class="text-muted small">Don't have an account?</span>
                                <a href="{{ route('register') }}" class="ms-1 small fw-semibold">Create one</a>
                            </div>
                        </form>

                        {{-- Demo Credentials --}}
                        <div class="mt-4 p-3 bg-light rounded small text-muted">
                            <strong class="d-block mb-1">Demo credentials:</strong>
                            <div>Admin: <code>admin@roomora.com</code> / <code>password</code></div>
                            <div>User: <code>rahim@example.com</code> / <code>password</code></div>
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
function togglePass() {
    const p = document.getElementById('password');
    const i = document.getElementById('passEyeIcon');
    p.type = p.type === 'password' ? 'text' : 'password';
    i.className = p.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
@endpush
