<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reviews table
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->string('title')->nullable();
            $table->boolean('is_approved')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // One review per user per hotel
            $table->unique(['user_id', 'hotel_id']);
            $table->index('hotel_id');
            $table->index('rating');
        });

        // Comparisons table
        Schema::create('comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'hotel_id']);
            $table->index('user_id');
        });

        // Favorites / Wishlist table
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'hotel_id']);
            $table->index('user_id');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE reviews ADD CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5)');
            DB::unprepared(<<<SQL
CREATE TRIGGER trg_comparisons_limit_before_insert
BEFORE INSERT ON comparisons
FOR EACH ROW
BEGIN
    IF (SELECT COUNT(*) FROM comparisons WHERE user_id = NEW.user_id) >= 4 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'You can compare up to 4 hotels at a time.';
    END IF;
END
SQL);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_comparisons_limit_before_insert');
        }
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('comparisons');
        Schema::dropIfExists('reviews');
    }
};
