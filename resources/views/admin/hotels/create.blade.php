@extends('layouts.admin')

@section('title', 'Add Hotel')

@section('content')
<div class="container-fluid" style="max-width: 980px;">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-4">Add Hotel</h4>
            <form action="{{ route('admin.hotels.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name') }}" required></div>
                    <div class="col-md-3"><label class="form-label">City</label><input name="city" class="form-control" value="{{ old('city') }}" required></div>
                    <div class="col-md-3"><label class="form-label">Star Rating</label><input name="star_rating" type="number" step="0.1" min="1" max="5" class="form-control" value="{{ old('star_rating', 4) }}" required></div>
                    <div class="col-12"><label class="form-label">Address</label><input name="address" class="form-control" value="{{ old('address') }}" required></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea></div>
                    <div class="col-md-4"><label class="form-label">Check-in Time</label><input name="check_in_time" class="form-control" placeholder="14:00" value="{{ old('check_in_time', '14:00') }}"></div>
                    <div class="col-md-4"><label class="form-label">Check-out Time</label><input name="check_out_time" class="form-control" placeholder="12:00" value="{{ old('check_out_time', '12:00') }}"></div>
                    <div class="col-md-4"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ old('phone') }}"></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input name="email" type="email" class="form-control" value="{{ old('email') }}"></div>
                    <div class="col-md-6"><label class="form-label">Website</label><input name="website" class="form-control" value="{{ old('website') }}"></div>
                    <div class="col-12"><label class="form-label">Amenities</label><input name="amenities[]" class="form-control mb-2" placeholder="WiFi"><input name="amenities[]" class="form-control mb-2" placeholder="Pool"><input name="amenities[]" class="form-control" placeholder="Gym"></div>
                    <div class="col-md-6"><label class="form-label">Main Image</label><input name="image" type="file" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Gallery Images</label><input name="gallery[]" type="file" class="form-control" multiple></div>
                    <div class="col-12 form-check ms-2"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="activeHotel"><label class="form-check-label" for="activeHotel">Active</label></div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.hotels.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button class="btn btn-primary" type="submit">Save Hotel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
