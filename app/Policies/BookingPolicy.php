<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        return (int) $user->id === (int) $booking->user_id || $user->isAdmin();
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return (int) $user->id === (int) $booking->user_id && $booking->canBeCancelled();
    }

    public function download(User $user, Booking $booking): bool
    {
        return (int) $user->id === (int) $booking->user_id || $user->isAdmin();
    }

    public function update(User $user, Booking $booking): bool
    {
        return (int) $user->id === (int) $booking->user_id || $user->isAdmin();
    }
}
