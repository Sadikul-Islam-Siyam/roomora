@props([
    'images' => [],
    'fallback' => null,
    'alt' => 'Hotel gallery',
])

@php
    $galleryImages = collect($images ?? [])->filter()->values();
@endphp

<div class="card shadow-sm border-0 overflow-hidden">
    @if($galleryImages->isNotEmpty())
        <div id="hotelGalleryCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                @foreach($galleryImages as $index => $image)
                    <button type="button"
                            data-bs-target="#hotelGalleryCarousel"
                            data-bs-slide-to="{{ $index }}"
                            class="{{ $index === 0 ? 'active' : '' }}"
                            aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                            aria-label="Slide {{ $index + 1 }}"></button>
                @endforeach
            </div>
            <div class="carousel-inner">
                @foreach($galleryImages as $index => $image)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <img src="{{ asset('storage/' . $image) }}" class="d-block w-100" alt="{{ $alt }}" style="height: 420px; object-fit: cover;">
                    </div>
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#hotelGalleryCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#hotelGalleryCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    @else
        <img src="{{ $fallback ?: asset('images/hotel-placeholder.svg') }}" class="img-fluid w-100" alt="{{ $alt }}" style="height: 420px; object-fit: cover;">
    @endif
</div>
