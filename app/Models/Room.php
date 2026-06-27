<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'hotel_id', 'room_type', 'room_number', 'price', 'capacity',
        'facilities', 'quantity', 'image', 'images', 'description',
        'size_sqm', 'is_available',
    ];

    protected $casts = [
        'hotel_id'     => 'integer',
        'price'        => 'decimal:2',
        'facilities'   => 'array',
        'images'       => 'array',
        'is_available' => 'boolean',
    ];

    // Room type constants
    const TYPES = [
        'standard'    => 'Standard',
        'deluxe'      => 'Deluxe',
        'suite'       => 'Suite',
        'presidential'=> 'Presidential Suite',
        'family'      => 'Family Room',
        'twin'        => 'Twin Room',
    ];

    // ── Scopes ──────────────────────────────────────────────
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)->where('quantity', '>', 0);
    }

    public function scopeForGuests($query, int $guests)
    {
        return $query->where('capacity', '>=', $guests);
    }

    public function scopePriceBetween($query, float $min, float $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    public function scopeAvailableForDates($query, $checkIn, $checkOut)
    {
        return $query->where('is_available', true)
            ->where('quantity', '>', 0)
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereRaw("quantity > (
                    select count(*) from bookings
                    where bookings.room_id = rooms.id
                    and bookings.deleted_at is null
                    and status in ('pending', 'confirmed', 'checked_in')
                    and check_in < ?
                    and check_out > ?
                )", [$checkOut, $checkIn]);
            });
    }

    // ── Helpers ──────────────────────────────────────────────
    /**
     * Check if this room is available for the given date range.
     * Considers both quantity and overlapping bookings.
     */
    public function isAvailableForDates(string $checkIn, string $checkOut): bool
    {
        if (!$this->is_available) return false;

        $bookedCount = $this->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn)
            ->count();

        return $bookedCount < $this->quantity;
    }

    /**
     * Get remaining quantity of rooms for searched dates or today
     */
    public function getRemainingQuantityAttribute(): int
    {
        $checkIn = request('check_in');
        $checkOut = request('check_out');

        if ($checkIn && $checkOut) {
            $bookedCount = $this->bookings()
                ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                ->where('check_in', '<', $checkOut)
                ->where('check_out', '>', $checkIn)
                ->count();
            return max(0, $this->quantity - $bookedCount);
        }

        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();
        $bookedCount = $this->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where('check_in', '<', $tomorrow)
            ->where('check_out', '>', $today)
            ->count();
        return max(0, $this->quantity - $bookedCount);
    }

    /**
     * Calculate total price for given dates.
     */
    public function calculateTotalPrice(string $checkIn, string $checkOut): float
    {
        $nights = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));
        return round($this->price * $nights, 2);
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image && Storage::disk('public')->exists($this->image) && is_dir(public_path('storage'))) {
            return asset('storage/' . $this->image);
        }
        return asset('images/room-placeholder.svg');
    }

    public function getTypeNameAttribute(): string
    {
        $key = Str::of($this->room_type)->lower()->replace([' ', '-'], '_')->toString();

        return self::TYPES[$key] ?? ucfirst($this->room_type);
    }

    // ── Relationships ────────────────────────────────────────
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function activeBookings()
    {
        return $this->hasMany(Booking::class)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in']);
    }
}
