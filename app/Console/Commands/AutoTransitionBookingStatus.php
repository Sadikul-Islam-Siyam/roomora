<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class AutoTransitionBookingStatus extends Command
{
    protected $signature = 'bookings:auto-transition-status';
    protected $description = 'Automatically transition bookings from confirmed to checked_in, and from checked_in to checked_out based on dates';

    public function handle()
    {
        $today = today()->toDateString();
        $this->info("Running booking status auto-transition for date: {$today}...");

        $checkedInCount = 0;
        $checkedOutCount = 0;

        // 1. Confirmed -> Checked In
        Booking::where('status', Booking::STATUS_CONFIRMED)
            ->whereDate('check_in', '<=', $today)
            ->chunk(100, function ($bookings) use (&$checkedInCount) {
                foreach ($bookings as $booking) {
                    DB::transaction(function () use ($booking, &$checkedInCount) {
                        $lockedBooking = Booking::whereKey($booking->id)->lockForUpdate()->first();
                        
                        if ($lockedBooking && $lockedBooking->status === Booking::STATUS_CONFIRMED) {
                            $lockedBooking->update([
                                'status' => Booking::STATUS_CHECKED_IN,
                            ]);
                            
                            \App\Models\BookingLog::create([
                                'booking_id'  => $lockedBooking->id,
                                'changed_by'  => $lockedBooking->user_id,
                                'from_status' => Booking::STATUS_CONFIRMED,
                                'to_status'   => Booking::STATUS_CHECKED_IN,
                            ]);

                            $checkedInCount++;
                            $this->line("Auto-transitioned booking to checked_in: #{$lockedBooking->id} ({$lockedBooking->booking_reference})");
                        }
                    });
                }
            });

        // 2. Checked In -> Checked Out
        Booking::where('status', Booking::STATUS_CHECKED_IN)
            ->whereDate('check_out', '<', $today)
            ->chunk(100, function ($bookings) use (&$checkedOutCount) {
                foreach ($bookings as $booking) {
                    DB::transaction(function () use ($booking, &$checkedOutCount) {
                        $lockedBooking = Booking::whereKey($booking->id)->lockForUpdate()->first();
                        
                        if ($lockedBooking && $lockedBooking->status === Booking::STATUS_CHECKED_IN) {
                            $lockedBooking->update([
                                'status' => Booking::STATUS_CHECKED_OUT,
                            ]);

                            \App\Models\BookingLog::create([
                                'booking_id'  => $lockedBooking->id,
                                'changed_by'  => $lockedBooking->user_id,
                                'from_status' => Booking::STATUS_CHECKED_IN,
                                'to_status'   => Booking::STATUS_CHECKED_OUT,
                            ]);

                            $checkedOutCount++;
                            $this->line("Auto-transitioned booking to checked_out: #{$lockedBooking->id} ({$lockedBooking->booking_reference})");
                        }
                    });
                }
            });

        $this->info("Completed auto-transitions. Checked In: {$checkedInCount}, Checked Out: {$checkedOutCount}");

        return 0;
    }
}
