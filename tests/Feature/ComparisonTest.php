<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\User;
use App\Models\Comparison;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComparisonTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Hotel $hotel1;
    protected Hotel $hotel2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->hotel1 = Hotel::create([
            'name' => 'Hotel One',
            'city' => 'Dhaka',
            'address' => 'Addr 1',
            'description' => 'Desc 1',
            'star_rating' => 4.0,
            'amenities' => ['WiFi'],
            'check_in_time' => '14:00',
            'check_out_time' => '12:00',
            'phone' => '+8801700000001',
            'email' => 'one@hotel.com',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->hotel2 = Hotel::create([
            'name' => 'Hotel Two',
            'city' => 'Dhaka',
            'address' => 'Addr 2',
            'description' => 'Desc 2',
            'star_rating' => 5.0,
            'amenities' => ['WiFi', 'Pool'],
            'check_in_time' => '14:00',
            'check_out_time' => '12:00',
            'phone' => '+8801700000002',
            'email' => 'two@hotel.com',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);
    }

    public function test_ajax_comparison_toggle_returns_json(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('comparisons.toggle', $this->hotel1), [], [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'added' => true,
            'count' => 1
        ]);

        $this->assertDatabaseHas('comparisons', [
            'user_id' => $this->user->id,
            'hotel_id' => $this->hotel1->id,
        ]);
    }

    public function test_standard_post_comparison_toggle_redirects_back(): void
    {
        Comparison::create([
            'user_id' => $this->user->id,
            'hotel_id' => $this->hotel1->id,
        ]);

        $response = $this->actingAs($this->user)
            ->from(route('comparisons.index'))
            ->post(route('comparisons.toggle', $this->hotel1));

        $response->assertRedirect(route('comparisons.index'));
        $response->assertSessionHas('success', 'Removed from comparison.');

        $this->assertDatabaseMissing('comparisons', [
            'user_id' => $this->user->id,
            'hotel_id' => $this->hotel1->id,
        ]);
    }
}
