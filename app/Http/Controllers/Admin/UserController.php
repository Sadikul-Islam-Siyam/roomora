<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $query = User::withCount(['bookings', 'reviews']);

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        $users = $query->latest()->paginate(20)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['bookings.room.hotel', 'reviews.hotel']);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'role'  => ['required', 'in:user,admin'],
        ]);

        $user->update($validated);
        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }
        $user->delete();
        return back()->with('success', 'User deleted.');
    }

    public function toggle(User $user)
    {
        // Toggle between user and deactivated (soft delete mechanism)
        if ($user->trashed()) {
            $user->restore();
            $msg = 'User restored.';
        } else {
            $user->delete();
            $msg = 'User deactivated.';
        }
        return response()->json(['success' => true, 'message' => $msg]);
    }
}
