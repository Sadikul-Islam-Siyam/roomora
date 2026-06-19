<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city');
            $table->string('address');
            $table->text('description')->nullable();
            $table->decimal('star_rating', 2, 1)->default(0);
            $table->string('image')->nullable();
            $table->json('images')->nullable();  // multiple gallery images
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->json('amenities')->nullable(); // WiFi, Pool, Gym, etc.
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('check_in_time')->default('14:00');
            $table->string('check_out_time')->default('12:00');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('city');
            $table->index('star_rating');
            $table->index('is_active');
            $table->index(['city', 'is_active']);
            if (DB::getDriverName() !== 'sqlite') {
                $table->fullText(['name', 'city', 'description']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
