<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // ── Register ─────────────────────────────────────────────
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100', 'regex:/^[\pL\s\-]+$/u'],
            'email'    => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\(\)\s]+$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user = User::create([
            'name'     => strip_tags($validated['name']),
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role'     => 'user',
        ]);

        Auth::login($user);

        return redirect()->route('hotels.index')
            ->with('success', 'Welcome to Roomora, ' . $user->name . '!');
    }

    // ── Login ────────────────────────────────────────────────
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->intended(route('hotels.index'))
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'These credentials do not match our records.']);
    }

    // ── Logout ───────────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}
