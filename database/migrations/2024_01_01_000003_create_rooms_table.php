<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->string('room_type'); // Standard, Deluxe, Suite, Presidential
            $table->string('room_number')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('capacity')->default(2);
            $table->json('facilities')->nullable(); // AC, TV, Mini-bar, Balcony, etc.
            $table->integer('quantity')->default(1);
            $table->string('image')->nullable();
            $table->json('images')->nullable();
            $table->text('description')->nullable();
            $table->integer('size_sqm')->nullable(); // room size in square meters
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('hotel_id');
            $table->index('room_type');
            $table->index('price');
            $table->index('is_available');
            $table->index(['hotel_id', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
