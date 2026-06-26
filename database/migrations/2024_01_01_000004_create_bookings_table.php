<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            if (DB::getDriverName() === 'oracle') {
                $table->foreignId('room_id')->constrained('rooms');
            } else {
                $table->foreignId('room_id')->constrained('rooms')->restrictOnDelete();
            }
            $table->string('booking_reference')->unique(); // e.g. RMR-2024-0001
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedInteger('nights')->default(0);
            $table->integer('guests')->default(1);
            $table->decimal('room_price', 10, 2);   // price per night at booking time
            $table->decimal('total_price', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'])->default('pending');
            $table->text('special_requests')->nullable();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('guest_phone', 20)->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->boolean('invoice_downloaded')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index('room_id');
            $table->index('status');
            $table->index('check_in');
            $table->index('check_out');
            $table->index('booking_reference');
            $table->index(['check_in', 'check_out', 'status']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE bookings ADD CONSTRAINT chk_bookings_dates CHECK (check_out > check_in)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
