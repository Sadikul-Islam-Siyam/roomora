<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HotelController extends Controller
{
    /**
     * Hotel listing with search, filter, sort, pagination.
     */
    public function index(Request $request)
    {
        $query = Hotel::active()->withStats();
        $checkIn = $request->get('check_in');
        $checkOut = $request->get('check_out');

        // Search
        if ($search = $request->get('search')) {
            $query->search($search);
        }

        // City filter
        if ($city = $request->get('city')) {
            $query->inCity($city);
        }

        // Star rating filter
        if ($stars = $request->get('stars')) {
            $query->where('star_rating', '>=', (float) $stars);
        }

        // Price range (on rooms)
        if ($maxPrice = $request->get('max_price')) {
            $query->whereHas('rooms', function($q) use ($maxPrice, $checkIn, $checkOut) {
                $q->where('price', '<=', (float) $maxPrice);
                if ($checkIn && $checkOut) {
                    $q->availableForDates($checkIn, $checkOut);
                } else {
                    $q->where('is_available', true);
                }
            });
        }

        if ($minPrice = $request->get('min_price')) {
            $query->whereHas('rooms', function($q) use ($minPrice, $checkIn, $checkOut) {
                $q->where('price', '>=', (float) $minPrice);
                if ($checkIn && $checkOut) {
                    $q->availableForDates($checkIn, $checkOut);
                } else {
                    $q->where('is_available', true);
                }
            });
        }

        // Date-aware availability filter
        if ($checkIn && $checkOut) {
            $query->whereHas('rooms', function($q) use ($checkIn, $checkOut) {
                $q->availableForDates($checkIn, $checkOut);
            });
        }

        // Amenities filter
        if ($amenities = $request->get('amenities')) {
            $driver = DB::connection()->getDriverName();
            foreach ((array) $amenities as $amenity) {
                if (in_array($driver, ['oracle', 'sqlite'])) {
                    $query->where('amenities', 'LIKE', '%"' . $amenity . '"%');
                } else {
                    $query->whereJsonContains('amenities', $amenity);
                }
            }
        }

        // Sorting
        $sort = $request->get('sort', 'rating');
        if ($checkIn && $checkOut) {
            match ($sort) {
                'price_low'  => $query->orderByRaw("(SELECT MIN(price) FROM rooms WHERE hotel_id = hotels.id AND is_available = 1 AND quantity > (SELECT COUNT(*) FROM bookings WHERE bookings.room_id = rooms.id AND bookings.deleted_at IS NULL AND status IN ('pending', 'confirmed', 'checked_in') AND check_in < ? AND check_out > ?)) ASC", [$checkOut, $checkIn]),
                'price_high' => $query->orderByRaw("(SELECT MIN(price) FROM rooms WHERE hotel_id = hotels.id AND is_available = 1 AND quantity > (SELECT COUNT(*) FROM bookings WHERE bookings.room_id = rooms.id AND bookings.deleted_at IS NULL AND status IN ('pending', 'confirmed', 'checked_in') AND check_in < ? AND check_out > ?)) DESC", [$checkOut, $checkIn]),
                'name'       => $query->orderBy('name'),
                'newest'     => $query->latest(),
                default      => $query->orderByDesc('reviews_avg_rating'),
            };
        } else {
            match ($sort) {
                'price_low'  => $query->orderByRaw('(SELECT MIN(price) FROM rooms WHERE hotel_id = hotels.id AND is_available = 1) ASC'),
                'price_high' => $query->orderByRaw('(SELECT MIN(price) FROM rooms WHERE hotel_id = hotels.id AND is_available = 1) DESC'),
                'name'       => $query->orderBy('name'),
                'newest'     => $query->latest(),
                default      => $query->orderByDesc('reviews_avg_rating'),
            };
        }

        $hotels = $query->paginate(9)->withQueryString();

        if ($search = $request->get('search')) {
            DB::table('search_logs')->insert([
                'term'         => $search,
                'result_count' => $hotels->total(),
                'city'         => $request->get('city'),
                'user_id'      => Auth::id(),
                'ip_address'   => $request->ip(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // Data for filters
        $cities = Hotel::active()->distinct()->pluck('city')->sort()->values();

        // Recently viewed hotels
        $recentlyViewedIds = json_decode($request->cookie('recently_viewed_hotels', '[]'), true);
        $recentlyViewedHotels = collect();
        if (is_array($recentlyViewedIds) && !empty($recentlyViewedIds)) {
            $recentlyViewedHotels = Hotel::active()
                ->whereIn('id', $recentlyViewedIds)
                ->withStats()
                ->get()
                ->sortBy(function($h) use ($recentlyViewedIds) {
                    return array_search($h->id, $recentlyViewedIds);
                })
                ->values();
        }

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html'       => view('hotels.partials.hotel-cards', compact('hotels'))->render(),
                'pagination' => (string) $hotels->withQueryString()->links(),
                'total'      => $hotels->total(),
            ]);
        }

        return view('hotels.index', compact('hotels', 'cities', 'recentlyViewedHotels'));
    }

    /**
     * Single hotel detail page.
     */
    public function show(Hotel $hotel)
    {
        abort_unless($hotel->is_active, 404);

        // Update recently viewed hotels cookie
        $recentlyViewed = json_decode(request()->cookie('recently_viewed_hotels', '[]'), true);
        if (!is_array($recentlyViewed)) {
            $recentlyViewed = [];
        }
        $recentlyViewed = array_filter($recentlyViewed, fn($id) => $id != $hotel->id);
        array_unshift($recentlyViewed, $hotel->id);
        $recentlyViewed = array_slice($recentlyViewed, 0, 10);
        cookie()->queue('recently_viewed_hotels', json_encode(array_values($recentlyViewed)), 60 * 24 * 30);

        $hotel->load([
            'rooms' => fn($q) => $q->where('is_available', true),
            'approvedReviews.user',
        ])->loadCount('reviews')->loadAvg('reviews', 'rating')->loadMin('rooms', 'price');

        $hotel->setAttribute('reviews_count', $hotel->approvedReviews->count());
        $hotel->setAttribute('reviews_avg_rating', round($hotel->approvedReviews->avg('rating') ?? 0, 1));

        $avgRating      = $hotel->approvedReviews->avg('rating');
        $ratingBreakdown= $hotel->approvedReviews->groupBy('rating')
            ->map->count()
            ->sortKeysDesc();

        $userReview     = Auth::check()
            ? $hotel->reviews()->where('user_id', Auth::id())->first()
            : null;

        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();

        $isFavorited  = $currentUser ? $currentUser->hasFavorited($hotel->id) : false;
        $inComparison = $currentUser ? $currentUser->hasInComparison($hotel->id) : false;

        // Related hotels (same city)
        $relatedHotels  = Hotel::active()
            ->inCity($hotel->city)
            ->where('id', '!=', $hotel->id)
            ->withStats()
            ->limit(3)
            ->get();

        return view('hotels.show', compact(
            'hotel', 'avgRating', 'ratingBreakdown',
            'userReview', 'isFavorited', 'inComparison', 'relatedHotels'
        ));
    }

    /**
     * AJAX: Search suggestions.
     */
    public function searchSuggestions(Request $request)
    {
        $term = $request->get('q', '');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $hotels = Hotel::active()
            ->search($term)
            ->select('id', 'name', 'city', 'star_rating', 'image')
            ->limit(5)
            ->get()
            ->map(fn($h) => [
                'id'         => $h->id,
                'name'       => $h->name,
                'city'       => $h->city,
                'stars'      => $h->star_rating,
                'image'      => $h->image_url,
                'url'        => route('hotels.show', $h),
            ]);

        return response()->json($hotels);
    }
}
