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
        $driver = DB::connection()->getDriverName();
        $yearExpr = match ($driver) {
            'sqlite' => 'CAST(strftime(\'%Y\', created_at) AS INTEGER)',
            'mysql' => 'YEAR(created_at)',
            default => 'EXTRACT(YEAR FROM created_at)',
        };
        $monthExpr = match ($driver) {
            'sqlite' => 'CAST(strftime(\'%m\', created_at) AS INTEGER)',
            'mysql' => 'MONTH(created_at)',
            default => 'EXTRACT(MONTH FROM created_at)',
        };

        $monthlyRevenue = Booking::select(
                DB::raw("$yearExpr as year"),
                DB::raw("$monthExpr as month"),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as count')
            )
            ->whereNotIn('status', ['cancelled'])
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupByRaw("$yearExpr, $monthExpr")
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(fn($row) => [
                'label'   => date('M Y', mktime(0, 0, 0, (int) $row->month, 1, (int) $row->year)),
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

    /**
     * View search query analytics and zero-result search log statistics.
     */
    public function searchAnalytics(Request $request)
    {
        $totalQueries = DB::table('search_logs')->count();

        $queriesLast30Days = DB::table('search_logs')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $avgResults = round(DB::table('search_logs')->avg('result_count') ?? 0, 1);

        $topTerms = DB::table('search_logs')
            ->select('term', DB::raw('COUNT(*) as count'), DB::raw('AVG(result_count) as avg_results'))
            ->groupBy('term')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $topCities = DB::table('search_logs')
            ->select('city', DB::raw('COUNT(*) as count'), DB::raw('AVG(result_count) as avg_results'))
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->groupBy('city')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $zeroResults = DB::table('search_logs')
            ->select('term', 'city', DB::raw('COUNT(*) as count'))
            ->where('result_count', 0)
            ->groupBy('term', 'city')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $logs = DB::table('search_logs')
            ->leftJoin('users', 'search_logs.user_id', '=', 'users.id')
            ->select('search_logs.*', 'users.name as user_name', 'users.email as user_email')
            ->orderByDesc('search_logs.created_at')
            ->paginate(20);

        return view('admin.search-analytics', compact(
            'totalQueries', 'queriesLast30Days', 'avgResults',
            'topTerms', 'topCities', 'zeroResults', 'logs'
        ));
    }
}
