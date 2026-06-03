<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Comparison;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// ═══════════════════════════════════════════════════════
// COMPARISON CONTROLLER
// ═══════════════════════════════════════════════════════
class ComparisonController extends Controller
{
    const MAX_COMPARE = 4;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $hotels = $user
            ->comparedHotels()
            ->withStats()
            ->with(['rooms' => fn($q) => $q->where('is_available', true)])
            ->get();

        // Gather all unique amenities across compared hotels
        $allAmenities = $hotels->flatMap(fn($h) => $h->amenities ?? [])->unique()->values();

        return view('comparisons.index', compact('hotels', 'allAmenities'));
    }

    public function toggle(Request $request, Hotel $hotel)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $existing = Comparison::where('user_id', $user->id)->where('hotel_id', $hotel->id)->first();

        if ($existing) {
            $existing->delete();
            $added = false;
        } else {
            // Limit to MAX_COMPARE
            $count = Comparison::where('user_id', $user->id)->count();
            if ($count >= self::MAX_COMPARE) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can compare up to ' . self::MAX_COMPARE . ' hotels at a time.',
                ], 422);
            }

            Comparison::create(['user_id' => $user->id, 'hotel_id' => $hotel->id]);
            $added = true;
        }

        $count = Comparison::where('user_id', $user->id)->count();

        return response()->json([
            'success' => true,
            'added'   => $added,
            'count'   => $count,
            'message' => $added ? 'Added to comparison.' : 'Removed from comparison.',
        ]);
    }

    public function clear()
    {
        Comparison::where('user_id', Auth::id())->delete();
        return back()->with('success', 'Comparison list cleared.');
    }
}
