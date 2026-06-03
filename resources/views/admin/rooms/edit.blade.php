@extends('layouts.admin')

@section('title', 'Edit Room')

@section('content')
<div class="container-fluid" style="max-width: 860px;">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-1">Edit Room</h4>
            <p class="text-muted mb-4">Hotel: {{ $room->hotel->name ?? '—' }}</p>

            <form action="{{ route('admin.rooms.update', $room) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Room Type</label><input name="room_type" class="form-control" value="{{ old('room_type', $room->room_type) }}" required></div>
                    <div class="col-md-4"><label class="form-label">Room Number</label><input name="room_number" class="form-control" value="{{ old('room_number', $room->room_number) }}"></div>
                    <div class="col-md-4"><label class="form-label">Price</label><input name="price" type="number" step="0.01" class="form-control" value="{{ old('price', $room->price) }}" required></div>
                    <div class="col-md-4"><label class="form-label">Capacity</label><input name="capacity" type="number" class="form-control" value="{{ old('capacity', $room->capacity) }}" required></div>
                    <div class="col-md-4"><label class="form-label">Quantity</label><input name="quantity" type="number" class="form-control" value="{{ old('quantity', $room->quantity) }}" required></div>
                    <div class="col-md-4"><label class="form-label">Size (sqm)</label><input name="size_sqm" type="number" class="form-control" value="{{ old('size_sqm', $room->size_sqm) }}"></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4">{{ old('description', $room->description) }}</textarea></div>
                    <div class="col-12"><label class="form-label">Facilities</label><input name="facilities[]" class="form-control mb-2" value="{{ old('facilities.0', $room->facilities[0] ?? '') }}"><input name="facilities[]" class="form-control mb-2" value="{{ old('facilities.1', $room->facilities[1] ?? '') }}"><input name="facilities[]" class="form-control" value="{{ old('facilities.2', $room->facilities[2] ?? '') }}"></div>
                    <div class="col-md-6"><label class="form-label">Replace Image</label><input name="image" type="file" class="form-control"></div>
                    <div class="col-md-6 form-check ms-2 align-self-end"><input class="form-check-input" type="checkbox" name="is_available" value="1" id="availableRoom" {{ old('is_available', $room->is_available) ? 'checked' : '' }}><label class="form-check-label" for="availableRoom">Visible and available</label></div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.hotels.rooms.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button class="btn btn-primary" type="submit">Update Room</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
