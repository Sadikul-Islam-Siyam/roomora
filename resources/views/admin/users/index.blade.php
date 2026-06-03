@extends('layouts.admin')

@section('title', 'Users')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Users</h4>
            <small class="text-muted">Manage customer and admin accounts.</small>
        </div>
    </div>

    <form class="row g-2 mb-4" method="GET">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select">
                <option value="">All Roles</option>
                <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-outline-primary w-100" type="submit">Filter</button></div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Bookings</th>
                        <th>Reviews</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $user->name }}</div>
                            <div class="text-muted small">{{ $user->phone ?? 'No phone' }}</div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge bg-{{ $user->role === 'admin' ? 'dark' : 'primary' }}">{{ ucfirst($user->role) }}</span></td>
                        <td>{{ $user->bookings_count }}</td>
                        <td>{{ $user->reviews_count }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-secondary">View</a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary">Edit</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $users->withQueryString()->links() }}</div>
</div>
@endsection
