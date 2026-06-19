<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pending Booking Expiry
    |--------------------------------------------------------------------------
    |
    | The number of minutes after which an unpaid pending booking is
    | considered expired and cancelled automatically.
    |
    */
    'pending_expiry_minutes' => env('BOOKING_PENDING_EXPIRY_MINUTES', 30),
];
