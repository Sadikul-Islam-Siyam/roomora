<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;

class BackfillBookingNids extends Command
{
    protected $signature = 'bookings:backfill-nids {--dry-run}';
    protected $description = 'Ensure all bookings guest_details entries include nid key (backfill empty nid)';

    public function handle()
    {
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
                        if (! $dry) {
                            $b->guest_details = $new;
                            $b->save();
                        }
                    }
                }
            }
        });

        $this->info(($dry ? 'Dry run: ' : '') . "Processed bookings; updated={$count}");

        return 0;
    }
}
