<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show()
    {
        $user = Auth::user()->load([
            'bookings.room.hotel',
            'reviews.hotel',
            'favoriteHotels',
        ]);

        $bookingStats = [
            'total'     => $user->bookings()->count(),
            'upcoming'  => $user->bookings()->upcoming()->count(),
            'completed' => $user->bookings()->where('status', 'checked_out')->count(),
            'cancelled' => $user->bookings()->where('status', 'cancelled')->count(),
        ];

        return view('profile.show', compact('user', 'bookingStats'));
    }

    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'avatar'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')
                ->store('avatars', 'public');
        }

        $user->update($validated);

        return redirect()->route('profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function bookingHistory()
    {
        $bookings = Auth::user()
            ->bookings()
            ->with(['room.hotel'])
            ->latest()
            ->paginate(10);

        return view('profile.bookings', compact('bookings'));
    }
}
