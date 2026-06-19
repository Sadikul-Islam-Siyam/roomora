<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class ExpirePendingBookings extends Command
{
    protected $signature = 'bookings:expire-pending';
    protected $description = 'Find all pending bookings that have not been paid in time and mark them as cancelled';

    public function handle()
    {
        $minutes = config('booking.pending_expiry_minutes', 30);
        $thresholdTime = now()->subMinutes($minutes);

        $this->info("Scanning for pending bookings older than {$minutes} minutes (created before {$thresholdTime->toDateTimeString()})...");

        $count = 0;

        Booking::where('status', Booking::STATUS_PENDING)
            ->where('created_at', '<', $thresholdTime)
            ->chunk(100, function ($bookings) use (&$count) {
                foreach ($bookings as $booking) {
                    DB::transaction(function () use ($booking, &$count) {
                        $lockedBooking = Booking::whereKey($booking->id)->lockForUpdate()->first();
                        
                        if ($lockedBooking && $lockedBooking->status === Booking::STATUS_PENDING) {
                            $lockedBooking->update([
                                'status' => Booking::STATUS_CANCELLED,
                                'cancelled_at' => now(),
                                'cancellation_reason' => 'Payment not completed in time',
                            ]);
                            $count++;
                            $this->line("Cancelled expired pending booking: #{$lockedBooking->id} ({$lockedBooking->booking_reference})");
                        }
                    });
                }
            });

        $this->info("Finished expiring pending bookings. Total cancelled: {$count}");

        return 0;
    }
}
