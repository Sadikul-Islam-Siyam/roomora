<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Review;
use App\Models\Comparison;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// ═══════════════════════════════════════════════════════
// REVIEW CONTROLLER
// ═══════════════════════════════════════════════════════
class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, Hotel $hotel)
    {
        $validated = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'title'   => ['nullable', 'string', 'max:100'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        // One review per user per hotel
        $existing = Review::where('user_id', Auth::id())->where('hotel_id', $hotel->id)->first();

        if ($existing) {
            return back()->withErrors(['review' => 'You have already reviewed this hotel.']);
        }

        // User must have stayed at the hotel
        $hasStayed = Auth::user()
            ->bookings()
            ->whereHas('room', fn($q) => $q->where('hotel_id', $hotel->id))
            ->where('status', 'checked_out')
            ->exists();

        if (!$hasStayed) {
            return back()->withErrors(['review' => 'You can only review hotels you have stayed at.']);
        }

        Review::create([
            'user_id'  => Auth::id(),
            'hotel_id' => $hotel->id,
            'rating'   => $validated['rating'],
            'title'    => strip_tags($validated['title'] ?? ''),
            'comment'  => strip_tags($validated['comment'] ?? ''),
        ]);

        return back()->with('success', 'Thank you for your review!');
    }

    public function update(Request $request, Review $review)
    {
        $this->authorize('update', $review);

        $validated = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'title'   => ['nullable', 'string', 'max:100'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $review->update([
            'rating'  => $validated['rating'],
            'title'   => strip_tags($validated['title'] ?? ''),
            'comment' => strip_tags($validated['comment'] ?? ''),
        ]);

        return back()->with('success', 'Review updated.');
    }

    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);
        $review->delete();

        return back()->with('success', 'Review deleted.');
    }
}
