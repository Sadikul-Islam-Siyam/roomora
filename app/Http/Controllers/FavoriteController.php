<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $hotels = $user
            ->favoriteHotels()
            ->withStats()
            ->paginate(12);

        return view('favorites.index', compact('hotels'));
    }

    public function toggle(Hotel $hotel)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $existing = Favorite::where('user_id', $user->id)->where('hotel_id', $hotel->id)->first();

        if ($existing) {
            $existing->delete();
            $favorited = false;
        } else {
            Favorite::create(['user_id' => $user->id, 'hotel_id' => $hotel->id]);
            $favorited = true;
        }

        if (request()->ajax()) {
            return response()->json([
                'success'   => true,
                'favorited' => $favorited,
                'message'   => $favorited ? 'Added to wishlist.' : 'Removed from wishlist.',
            ]);
        }

        return back()->with('success', $favorited ? 'Added to wishlist.' : 'Removed from wishlist.');
    }
}
