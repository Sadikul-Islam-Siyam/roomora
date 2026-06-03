<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Review;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake();

        // ── Admin User ───────────────────────────────────────
        $admin = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@roomora.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'phone'    => '+8801700000000',
        ]);

        // ── Regular Users ────────────────────────────────────
        $createdUsers = collect(range(1, 50))->map(function (int $index) use ($faker) {
            return User::create([
                'name'     => $faker->name(),
                'email'    => 'user' . $index . '@example.com',
                'password' => Hash::make('password'),
                'role'     => 'user',
                'phone'    => '+88017' . str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT),
            ]);
        });

        // ── Hotels ───────────────────────────────────────────
        $cities = ['Dhaka', 'Chittagong', "Cox's Bazar", 'Sylhet', 'Khulna'];
        $hotelNames = [
            'Pan Pacific Sonargaon', 'Radisson Blu Water Garden', 'Hotel Agrabad', 'Amari Dhaka',
            'Long Beach Hotel', 'Ruposhi Bangla', 'Grand Sylhet', 'The Peninsula Chittagong',
            'Seagull Hotel', 'Nirvana Inn', 'Hotel Royal', 'Best Western Heritage',
            'City Garden Hotel', 'Le Meridien Dhaka', 'Hotel Star Pacific', 'Hotel Sea Crown',
            'Royal Tulip Sea Pearl', 'Hotel Crystal Palace', 'Hotel Castle Salam', 'Grand Oriental'
        ];

        $amenityPool = ['WiFi', 'Pool', 'Gym', 'Restaurant', 'Spa', 'Parking', 'Airport Shuttle', 'Bar', 'Laundry', 'Conference Room'];

        $hotels = collect(range(1, 20))->map(function (int $index) use ($hotelNames, $cities, $amenityPool, $admin) {
            $city = $cities[($index - 1) % count($cities)];
            $name = $hotelNames[$index - 1] . ' ' . $city;
            $amenities = collect($amenityPool)->shuffle()->take(random_int(4, 7))->values()->all();

            return Hotel::create([
                'name'          => $name,
                'city'          => $city,
                'address'       => fake()->streetAddress() . ', ' . $city,
                'description'   => fake()->paragraph(2),
                'star_rating'   => [3.0, 3.5, 4.0, 4.5, 5.0][($index - 1) % 5],
                'amenities'     => $amenities,
                'check_in_time' => '14:00',
                'check_out_time'=> '12:00',
                'phone'         => '+880-2-' . random_int(7000000, 9999999),
                'email'         => 'info' . $index . '@roomora.test',
                'website'       => 'https://example.com',
                'is_active'     => true,
                'created_by'    => $admin->id,
            ]);
        });

        // ── Rooms ─────────────────────────────────────────────
        $roomTemplates = [
            ['room_type' => 'standard', 'price' => 4500, 'capacity' => 2, 'quantity' => 10, 'facilities' => ['AC', 'TV', 'WiFi', 'En-suite Bathroom'], 'size_sqm' => 25],
            ['room_type' => 'deluxe', 'price' => 6500, 'capacity' => 2, 'quantity' => 8, 'facilities' => ['AC', 'TV', 'WiFi', 'Mini-bar', 'City View'], 'size_sqm' => 30],
            ['room_type' => 'suite', 'price' => 12000, 'capacity' => 4, 'quantity' => 4, 'facilities' => ['AC', 'TV', 'WiFi', 'Mini-bar', 'Living Room', 'Balcony'], 'size_sqm' => 55],
            ['room_type' => 'presidential', 'price' => 22000, 'capacity' => 6, 'quantity' => 2, 'facilities' => ['AC', 'TV', 'WiFi', 'Mini-bar', 'Jacuzzi', 'Butler Service'], 'size_sqm' => 85],
            ['room_type' => 'family', 'price' => 9000, 'capacity' => 5, 'quantity' => 5, 'facilities' => ['AC', 'TV', 'WiFi', 'Two Bathrooms', 'Living Area'], 'size_sqm' => 45],
            ['room_type' => 'twin', 'price' => 5200, 'capacity' => 2, 'quantity' => 6, 'facilities' => ['AC', 'TV', 'WiFi', 'Twin Beds'], 'size_sqm' => 28],
        ];

        foreach ($hotels as $hotel) {
            foreach ($roomTemplates as $template) {
                Room::create(array_merge($template, [
                    'hotel_id'     => $hotel->id,
                    'is_available' => true,
                    'price'        => round($template['price'] * ($hotel->star_rating / 4), 2),
                ]));
            }
        }

        // ── Sample Bookings ───────────────────────────────────
        $rooms = Room::all();
        $statuses = ['confirmed', 'checked_out', 'checked_out', 'checked_out', 'pending', 'cancelled'];

        collect(range(1, 200))->each(function () use ($createdUsers, $rooms, $statuses) {
            $user = $createdUsers->random();
            $room = $rooms->random();
            $checkIn = Carbon::now()->subDays(random_int(1, 340))->toDateString();
            $nights = random_int(1, 7);
            $checkOut = Carbon::parse($checkIn)->addDays($nights)->toDateString();
            $status = $statuses[array_rand($statuses)];
            $createdAt = Carbon::parse($checkIn)->subDays(random_int(1, 30))->setTime(random_int(8, 18), random_int(0, 59));

            $booking = Booking::create([
                'user_id'       => $user->id,
                'room_id'       => $room->id,
                'check_in'      => $checkIn,
                'check_out'     => $checkOut,
                'nights'        => $nights,
                'guests'        => random_int(1, $room->capacity),
                'room_price'    => $room->price,
                'total_price'   => round($room->price * $nights, 2),
                'status'        => $status,
                'guest_name'    => $user->name,
                'guest_email'   => $user->email,
                'guest_phone'   => $user->phone ?? '+8801700000000',
            ]);

            $booking->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->saveQuietly();
        });

        // ── Reviews ───────────────────────────────────────────
        foreach ($createdUsers as $user) {
            $completedHotels = $user->bookings()
                ->where('status', 'checked_out')
                ->with('room.hotel')
                ->get()
                ->pluck('room.hotel')
                ->filter()
                ->unique('id')
                ->values();

            $reviewHotels = $completedHotels->isNotEmpty()
                ? $completedHotels->take(random_int(1, min(2, $completedHotels->count())))
                : $hotels->random(random_int(1, 2));

            foreach ($reviewHotels as $hotel) {
                Review::firstOrCreate(
                    ['user_id' => $user->id, 'hotel_id' => $hotel->id],
                    [
                        'rating'  => random_int(3, 5),
                        'title'   => $faker->sentence(3),
                        'comment' => $faker->paragraph(2),
                    ]
                );
            }
        }

        $this->command->info('✅ Roomora database seeded successfully!');
        $this->command->info('   Admin login: admin@roomora.com / password');
        $this->command->info('   User login:  user1@example.com / password');
    }
}
