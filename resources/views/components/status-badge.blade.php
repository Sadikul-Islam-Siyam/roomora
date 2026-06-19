@props(['status'])

@php
    $color = \App\Models\Booking::STATUS_COLORS[$status] ?? 'secondary';
    $label = ucfirst($status);
@endphp

<span class="badge bg-{{ $color }}">{{ $label }}</span>
