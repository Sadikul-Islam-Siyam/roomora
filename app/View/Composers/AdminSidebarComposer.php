<?php

namespace App\View\Composers;

use App\Models\Booking;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class AdminSidebarComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view)
    {
        $pendingBookingsCount = Cache::remember('admin.pending_bookings_count', 60, function () {
            return Booking::where('status', 'pending')->count();
        });

        $view->with('pendingBookingsCount', $pendingBookingsCount);
    }
}
