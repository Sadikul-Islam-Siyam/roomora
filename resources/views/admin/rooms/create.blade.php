@extends('layouts.admin')

@section('title', 'Add Room')

@section('content')
<div class="container-fluid" style="max-width: 860px;">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-1">Add Room</h4>
            <p class="text-muted mb-4">Hotel: {{ $hotel->name }}</p>

            <form action="{{ route('admin.hotels.rooms.store', $hotel) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Room Type</label><input name="room_type" class="form-control" placeholder="standard" value="{{ old('room_type') }}" required></div>
                    <div class="col-md-4"><label class="form-label">Room Number</label><input name="room_number" class="form-control" value="{{ old('room_number') }}"></div>
                    <div class="col-md-4"><label class="form-label">Price</label><input name="price" type="number" step="0.01" class="form-control" value="{{ old('price') }}" required></div>
                    <div class="col-md-4"><label class="form-label">Capacity</label><input name="capacity" type="number" class="form-control" value="{{ old('capacity', 2) }}" required></div>
                    <div class="col-md-4"><label class="form-label">Quantity</label><input name="quantity" type="number" class="form-control" value="{{ old('quantity', 1) }}" required></div>
                    <div class="col-md-4"><label class="form-label">Size (sqm)</label><input name="size_sqm" type="number" class="form-control" value="{{ old('size_sqm') }}"></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea></div>
                    <div class="col-12"><label class="form-label">Facilities</label><input name="facilities[]" class="form-control mb-2" placeholder="AC"><input name="facilities[]" class="form-control mb-2" placeholder="WiFi"><input name="facilities[]" class="form-control" placeholder="TV"></div>
                    <div class="col-md-6"><label class="form-label">Image</label><input name="image" type="file" class="form-control"></div>
                    <div class="col-md-6 form-check ms-2 align-self-end"><input class="form-check-input" type="checkbox" name="is_available" value="1" checked id="availableRoom"><label class="form-check-label" for="availableRoom">Visible and available</label></div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.hotels.rooms.index', $hotel) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button class="btn btn-primary" type="submit">Save Room</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
