<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'amount', 'currency', 'method', 'gateway', 'transaction_id', 'status', 'metadata', 'paid_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'paid_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
