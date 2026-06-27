<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function update(User $user, Review $review): bool
    {
        return (int) $user->id === (int) $review->user_id;
    }

    public function delete(User $user, Review $review): bool
    {
        return (int) $user->id === (int) $review->user_id || $user->isAdmin();
    }
}
