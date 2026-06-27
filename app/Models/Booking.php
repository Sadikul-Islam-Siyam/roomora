<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'room_id', 'booking_reference', 'check_in', 'check_out',
        'guests', 'guest_details', 'nights', 'room_price', 'total_price', 'discount', 'status',
        'special_requests', 'guest_name', 'guest_email', 'guest_phone', 'guest_nid',
        'cancelled_at', 'cancellation_reason', 'invoice_downloaded',
        'payment_method', 'is_paid', 'paid_at', 'billing_address',
    ];

    protected $casts = [
        'user_id'            => 'integer',
        'room_id'            => 'integer',
        'check_in'           => 'date',
        'check_out'          => 'date',
        'nights'             => 'integer',
        'room_price'         => 'decimal:2',
        'total_price'        => 'decimal:2',
        'discount'           => 'decimal:2',
        'cancelled_at'       => 'datetime',
        'invoice_downloaded' => 'boolean',
        'paid_at'            => 'datetime',
        'is_paid'            => 'boolean',
        'guest_details'      => 'array',
    ];

    const STATUS_PENDING    = 'pending';
    const STATUS_CONFIRMED  = 'confirmed';
    const STATUS_CHECKED_IN = 'checked_in';
    const STATUS_CHECKED_OUT= 'checked_out';
    const STATUS_CANCELLED  = 'cancelled';

    const STATUS_COLORS = [
        'pending'     => 'warning',
        'confirmed'   => 'success',
        'checked_in'  => 'info',
        'checked_out' => 'secondary',
        'cancelled'   => 'danger',
    ];

    const TRANSITION_MAP = [
        self::STATUS_PENDING    => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED  => [self::STATUS_CANCELLED],
        self::STATUS_CANCELLED  => [],
    ];

    public static function isValidTransition(string $from, string $to): bool
    {
        if ($from === $to) {
            return true;
        }
        $allowed = self::TRANSITION_MAP[$from] ?? [];
        return in_array($to, $allowed);
    }

    // ── Boot ─────────────────────────────────────────────────
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_reference)) {
                $booking->booking_reference = self::generateReference();
            }
        });
    }

    // ── Helpers ──────────────────────────────────────────────
    public static function generateReference(): string
    {
        do {
            $ref = 'RMR-' . date('Y') . '-' . strtoupper(Str::random(6));
        } while (self::where('booking_reference', $ref)->exists());

        return $ref;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED])
            && $this->check_in->isFuture();
    }

    public function getHotelAttribute()
    {
        return $this->room?->hotel;
    }



    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    // ── Scopes ──────────────────────────────────────────────
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED])
                     ->where('check_in', '>=', now());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    // ── Relationships ────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
