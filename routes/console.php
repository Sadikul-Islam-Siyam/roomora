<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule commands
Schedule::command('bookings:expire-pending')->everyFiveMinutes();
Schedule::command('bookings:auto-transition-status')->daily();

use App\Models\Booking;

Artisan::command('bookings:backfill-nids {--dry-run}', function () {
    $dry = $this->option('dry-run');
    $this->info('Scanning bookings for missing NID fields...');
    $count = 0;

    Booking::chunk(100, function ($bookings) use (&$count, $dry) {
        foreach ($bookings as $b) {
            $updated = false;
            $details = $b->guest_details ?? [];
            if (is_array($details) && count($details)) {
                $new = [];
                foreach ($details as $g) {
                    if (!array_key_exists('nid', $g)) {
                        $g['nid'] = '';
                        $updated = true;
                    }
                    $new[] = $g;
                }

                if ($updated) {
                    $count++;
                    $this->line('Will update booking #' . $b->id . ' (' . $b->booking_reference . ')');
                    if (!$dry) {
                        $b->guest_details = $new;
                        $b->save();
                    }
                }
            }
        }
    });

    $this->info(($dry ? 'Dry run: ' : '') . "Processed bookings; updated={$count}");

})->describe('Backfill missing NID keys in booking guest_details');
