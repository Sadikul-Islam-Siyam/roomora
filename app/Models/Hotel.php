<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Hotel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'city', 'address', 'description', 'star_rating', 'image', 'images',
        'phone', 'email', 'website', 'amenities', 'latitude', 'longitude',
        'check_in_time', 'check_out_time', 'is_active', 'created_by',
    ];

    protected $casts = [
        'star_rating'  => 'decimal:1',
        'images'       => 'array',
        'amenities'    => 'array',
        'is_active'    => 'boolean',
        'latitude'     => 'decimal:8',
        'longitude'    => 'decimal:8',
    ];

    // ── Scopes ──────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInCity($query, string $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }

    public function scopeWithMinRating($query, float $rating)
    {
        return $query->where('star_rating', '>=', $rating);
    }

    public function scopeSearch($query, string $term)
    {
        $term = trim($term);

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('city', 'like', "%{$term}%")
              ->orWhere('address', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function scopeWithStats($query)
    {
        return $query->withMin('availableRooms', 'price')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');
    }

    // ── Accessors ────────────────────────────────────────────
    public function getImageUrlAttribute(): string
    {
        if ($this->image && Storage::disk('public')->exists($this->image) && is_dir(public_path('storage'))) {
            return asset('storage/' . $this->image);
        }
        return asset('images/hotel-placeholder.svg');
    }

    public function getStarIconsAttribute(): string
    {
        $full  = floor($this->star_rating);
        $half  = ($this->star_rating - $full) >= 0.5 ? 1 : 0;
        $empty = 5 - $full - $half;

        return str_repeat('★', $full) . str_repeat('½', $half) . str_repeat('☆', $empty);
    }

    public function getMinPriceAttribute(): ?float
    {
        return $this->getAttribute('available_rooms_min_price')
            ?? $this->getAttribute('rooms_min_price')
            ?? $this->availableRooms()->min('price');
    }

    public function getMinPriceForDates(?string $checkIn, ?string $checkOut): ?float
    {
        if ($checkIn && $checkOut) {
            return $this->rooms()
                ->availableForDates($checkIn, $checkOut)
                ->min('price');
        }
        return $this->min_price;
    }

    // ── Relationships ────────────────────────────────────────
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function availableRooms()
    {
        return $this->hasMany(Room::class)->where('is_available', true);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function bookings()
    {
        return $this->hasManyThrough(Booking::class, Room::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    public function comparisons()
    {
        return $this->hasMany(Comparison::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }
}
