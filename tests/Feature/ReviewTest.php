<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Hotel $hotel;
    protected Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'user']);
        
        $admin = User::factory()->create(['role' => 'admin']);
        
        $this->hotel = Hotel::create([
            'name' => 'Test Hotel',
            'city' => 'Dhaka',
            'address' => 'Test Address',
            'description' => 'Test Description',
            'star_rating' => 4.0,
            'amenities' => ['WiFi', 'Pool'],
            'check_in_time' => '14:00',
            'check_out_time' => '12:00',
            'phone' => '+8801700000000',
            'email' => 'test@hotel.com',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->room = Room::create([
            'hotel_id' => $this->hotel->id,
            'room_type' => 'standard',
            'price' => 5000,
            'capacity' => 2,
            'quantity' => 5,
            'facilities' => ['AC', 'WiFi'],
            'size_sqm' => 25,
            'is_available' => true,
            'room_number' => '101',
        ]);
    }

    public function test_guest_cannot_submit_review(): void
    {
        $response = $this->post(route('reviews.store', $this->hotel), [
            'rating' => 5,
            'title' => 'Great Stay',
            'comment' => 'Very clean room.',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseEmpty('reviews');
    }

    public function test_user_who_did_not_stay_cannot_submit_review(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('reviews.store', $this->hotel), [
                'rating' => 5,
                'title' => 'Great Stay',
                'comment' => 'Very clean room.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['review']);
        $this->assertDatabaseEmpty('reviews');
    }

    public function test_user_with_pending_booking_cannot_submit_review(): void
    {
        Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'check_in' => now()->subDays(2)->toDateString(),
            'check_out' => now()->addDays(2)->toDateString(),
            'nights' => 4,
            'guests' => 2,
            'room_price' => $this->room->price,
            'total_price' => $this->room->price * 4,
            'status' => 'pending', // pending
            'guest_name' => $this->user->name,
            'guest_email' => $this->user->email,
            'guest_phone' => $this->user->phone ?? '+8801700000000',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('reviews.store', $this->hotel), [
                'rating' => 5,
                'title' => 'Great Stay',
                'comment' => 'Very clean room.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['review']);
        $this->assertDatabaseEmpty('reviews');
    }

    public function test_user_who_has_confirmed_booking_can_submit_review(): void
    {
        Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'check_in' => now()->subDays(5)->toDateString(),
            'check_out' => now()->subDays(2)->toDateString(),
            'nights' => 3,
            'guests' => 2,
            'room_price' => $this->room->price,
            'total_price' => $this->room->price * 3,
            'status' => 'confirmed', // confirmed!
            'guest_name' => $this->user->name,
            'guest_email' => $this->user->email,
            'guest_phone' => $this->user->phone ?? '+8801700000000',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('reviews.store', $this->hotel), [
                'rating' => 5,
                'title' => 'Great Stay',
                'comment' => 'Very clean room.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success', 'Thank you for your review!');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->user->id,
            'hotel_id' => $this->hotel->id,
            'rating' => 5,
            'title' => 'Great Stay',
            'comment' => 'Very clean room.',
        ]);
    }

    public function test_user_cannot_submit_duplicate_review_for_same_hotel(): void
    {
        Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'check_in' => now()->subDays(5)->toDateString(),
            'check_out' => now()->subDays(2)->toDateString(),
            'nights' => 3,
            'guests' => 2,
            'room_price' => $this->room->price,
            'total_price' => $this->room->price * 3,
            'status' => 'confirmed',
            'guest_name' => $this->user->name,
            'guest_email' => $this->user->email,
            'guest_phone' => $this->user->phone ?? '+8801700000000',
        ]);

        Review::create([
            'user_id' => $this->user->id,
            'hotel_id' => $this->hotel->id,
            'rating' => 4,
            'title' => 'Good',
            'comment' => 'Nice place.',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('reviews.store', $this->hotel), [
                'rating' => 5,
                'title' => 'Great Stay',
                'comment' => 'Very clean room.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['review']);
        $this->assertCount(1, Review::all());
    }
}
