@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="container-fluid" style="max-width: 760px;">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-4">Edit User</h4>
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $user->name) }}" required></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required></div>
                    <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ old('phone', $user->phone) }}"></div>
                    <div class="col-md-6"><label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>User</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button class="btn btn-primary" type="submit">Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
