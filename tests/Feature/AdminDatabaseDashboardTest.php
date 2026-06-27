<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDatabaseDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear search logs
        DB::table('search_logs')->truncate();
    }

    public function test_guest_is_redirected_from_search_analytics()
    {
        $response = $this->get(route('admin.search-analytics'));
        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_search_analytics()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.search-analytics'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_search_analytics_with_logged_data()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Insert mock search logs
        DB::table('search_logs')->insert([
            [
                'term' => 'luxury',
                'city' => 'Dhaka',
                'result_count' => 3,
                'ip_address' => '127.0.0.1',
                'user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'term' => 'pool',
                'city' => 'Sylhet',
                'result_count' => 0,
                'ip_address' => '127.0.0.1',
                'user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        $response = $this->actingAs($admin)->get(route('admin.search-analytics'));

        $response->assertOk();
        $response->assertViewHas('totalQueries', 2);
        $response->assertViewHas('avgResults', 1.5);
        $response->assertSee('luxury');
        $response->assertSee('Sylhet');
        $response->assertSee('Dhaka');
    }

    public function test_admin_can_view_hotel_show_with_performance_analytics()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $hotel = Hotel::create([
            'name' => 'The Grand Palace',
            'city' => 'Dhaka',
            'address' => 'Dhaka Bangladesh',
            'description' => 'A grand hotel',
            'star_rating' => 5,
            'amenities' => ['WiFi', 'Pool'],
            'check_in_time' => '14:00',
            'check_out_time' => '12:00',
            'phone' => '+8801700000000',
            'email' => 'grand@hotel.com',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'room_type' => 'Deluxe Suite',
            'price' => 5000,
            'capacity' => 2,
            'quantity' => 10,
            'facilities' => ['AC', 'WiFi'],
            'size_sqm' => 45,
            'is_available' => true,
            'room_number' => '501',
        ]);

        // Create booking
        $user = User::factory()->create(['role' => 'user']);
        Booking::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'guest_name' => $user->name,
            'guest_email' => $user->email,
            'check_in' => now()->addDay()->toDateString(),
            'check_out' => now()->addDays(3)->toDateString(),
            'nights' => 2,
            'guests' => 2,
            'room_price' => 5000,
            'total_price' => 10000,
            'status' => 'confirmed',
            'is_paid' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.hotels.show', $hotel));

        $response->assertOk();
        $response->assertViewHas('activeBookingsCount', 1);
        $response->assertViewHas('totalRevenue', 10000.0);
        $response->assertSee('Dhaka');
        $response->assertSee('Deluxe Suite');
        $response->assertSee('৳10,000');
    }
}
