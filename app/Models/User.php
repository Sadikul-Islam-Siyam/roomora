<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role', 'avatar', 'address',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Scopes ──────────────────────────────────────────────
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeRegularUsers($query)
    {
        return $query->where('role', 'user');
    }

    // ── Helpers ──────────────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff';
    }

    // ── Relationships ────────────────────────────────────────
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function activeBookings()
    {
        return $this->hasMany(Booking::class)->whereIn('status', ['pending', 'confirmed', 'checked_in']);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function comparisons()
    {
        return $this->hasMany(Comparison::class);
    }

    public function comparedHotels()
    {
        return $this->belongsToMany(Hotel::class, 'comparisons');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteHotels()
    {
        return $this->belongsToMany(Hotel::class, 'favorites');
    }

    public function hasFavorited(int $hotelId): bool
    {
        return $this->favorites()->where('hotel_id', $hotelId)->exists();
    }

    public function hasInComparison(int $hotelId): bool
    {
        return $this->comparisons()->where('hotel_id', $hotelId)->exists();
    }
}
