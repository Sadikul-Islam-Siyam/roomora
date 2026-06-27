<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Models\Room;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditFixesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_blocks_double_payments_on_already_paid_or_cancelled_bookings()
    {
        $user = User::create([
            'name' => 'Normal User',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $hotel = Hotel::create([
            'name' => 'Test Hotel',
            'city' => 'Dhaka',
            'address' => 'Test Address',
            'star_rating' => 4.0,
            'is_active' => true,
        ]);

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'room_type' => 'standard',
            'price' => 5000,
            'capacity' => 2,
            'quantity' => 5,
            'is_available' => true,
            'room_number' => '101',
        ]);
        
        $booking = Booking::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'check_in' => now()->addDay()->toDateString(),
            'check_out' => now()->addDays(3)->toDateString(),
            'nights' => 2,
            'guests' => 1,
            'room_price' => 5000,
            'total_price' => 10000,
            'status' => 'pending',
            'guest_name' => $user->name,
            'guest_email' => $user->email,
            'guest_phone' => '01711111111',
            'is_paid' => true, // Already paid!
        ]);

        $response = $this->actingAs($user)->post(route('bookings.pay', $booking), [
            'payment_method' => 'bkash',
            'billing_address' => 'Test Billing Address',
        ]);

        $response->assertStatus(302); // redirects back with error
        $response->assertSessionHas('error');

        // Test with cancelled status
        $booking->update([
            'is_paid' => false,
            'status' => 'cancelled',
        ]);

        $response = $this->actingAs($user)->post(route('bookings.pay', $booking), [
            'payment_method' => 'bkash',
            'billing_address' => 'Test Billing Address',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }

    /** @test */
    public function admin_cannot_deactivate_self_via_toggle()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.users.toggle', $admin));

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'You cannot deactivate your own account.'
        ]);
    }

    /** @test */
    public function validate_booking_status_transitions()
    {
        $this->assertTrue(Booking::isValidTransition('pending', 'confirmed'));
        $this->assertTrue(Booking::isValidTransition('pending', 'cancelled'));
        $this->assertFalse(Booking::isValidTransition('pending', 'checked_in'));
        $this->assertFalse(Booking::isValidTransition('confirmed', 'checked_in'));
        $this->assertFalse(Booking::isValidTransition('confirmed', 'checked_out'));
        $this->assertFalse(Booking::isValidTransition('checked_in', 'checked_out'));
        $this->assertFalse(Booking::isValidTransition('checked_out', 'confirmed'));
        $this->assertFalse(Booking::isValidTransition('cancelled', 'confirmed'));
    }

    /** @test */
    public function database_seeder_throws_exception_in_production()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Database seeding is disabled in production environment for security.');

        app()->detectEnvironment(fn() => 'production');

        $seeder = new \Database\Seeders\DatabaseSeeder();
        $seeder->run();
    }
}
