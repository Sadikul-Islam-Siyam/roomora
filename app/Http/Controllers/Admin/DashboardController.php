<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        // ── Overview Stats ──────────────────────────────────
        $stats = [
            'total_users'    => User::where('role', 'user')->count(),
            'total_hotels'   => Hotel::count(),
            'total_rooms'    => Room::count(),
            'total_bookings' => Booking::count(),
            'monthly_revenue'=> Booking::thisMonth()
                ->whereNotIn('status', ['cancelled'])
                ->sum('total_price'),
            'pending_bookings'=> Booking::where('status', 'pending')->count(),
        ];

        // ── Monthly Revenue (last 12 months) ─────────────────
        $monthlyRevenue = Booking::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as count')
            )
            ->whereNotIn('status', ['cancelled'])
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(fn($row) => [
                'label'   => date('M Y', mktime(0, 0, 0, $row->month, 1, $row->year)),
                'revenue' => (float) $row->revenue,
                'count'   => $row->count,
            ]);

        // ── Most Booked Hotels (top 5) ───────────────────────
        $topHotels = Hotel::select('hotels.id', 'hotels.name', 'hotels.city')
            ->join('rooms', 'hotels.id', '=', 'rooms.hotel_id')
            ->join('bookings', 'rooms.id', '=', 'bookings.room_id')
            ->whereNotIn('bookings.status', ['cancelled'])
            ->groupBy('hotels.id', 'hotels.name', 'hotels.city')
            ->orderByRaw('COUNT(bookings.id) DESC')
            ->selectRaw('COUNT(bookings.id) as booking_count')
            ->limit(5)
            ->get();

        // ── Room Type Popularity ─────────────────────────────
        $roomTypeStats = Room::select('rooms.room_type', DB::raw('COUNT(bookings.id) as booking_count'))
            ->leftJoin('bookings', function($join) {
                $join->on('rooms.id', '=', 'bookings.room_id')
                     ->whereNotIn('bookings.status', ['cancelled'])
                     ->whereNull('bookings.deleted_at');
            })
            ->whereNull('rooms.deleted_at')
            ->groupBy('rooms.room_type')
            ->orderByDesc('booking_count')
            ->get();

        // ── Booking Status Distribution ──────────────────────
        $statusDistribution = Booking::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // ── Recent Bookings ──────────────────────────────────
        $recentBookings = Booking::with(['user', 'room.hotel'])
            ->latest()
            ->limit(10)
            ->get();

        // ── Recent Users ─────────────────────────────────────
        $recentUsers = User::where('role', 'user')->latest()->limit(5)->get();

        return view('admin.dashboard', compact(
            'stats', 'monthlyRevenue', 'topHotels', 'roomTypeStats',
            'statusDistribution', 'recentBookings', 'recentUsers'
        ));
    }

    /**
     * AJAX: Refresh dashboard stats.
     */
    public function refreshStats()
    {
        return response()->json([
            'total_users'     => User::where('role', 'user')->count(),
            'total_bookings'  => Booking::count(),
            'monthly_revenue' => Booking::thisMonth()->whereNotIn('status', ['cancelled'])->sum('total_price'),
            'pending_bookings'=> Booking::where('status', 'pending')->count(),
        ]);
    }
}
