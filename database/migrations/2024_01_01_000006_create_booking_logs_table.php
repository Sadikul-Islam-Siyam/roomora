<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->timestamps();

            $table->index(['booking_id', 'created_at']);
            $table->index(['changed_by', 'created_at']);
        });

        if (Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            Illuminate\Support\Facades\DB::unprepared(<<<SQL
CREATE TRIGGER trg_bookings_status_after_update
AFTER UPDATE ON bookings
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO booking_logs (booking_id, changed_by, from_status, to_status, created_at, updated_at)
        VALUES (NEW.id, NEW.user_id, OLD.status, NEW.status, NOW(), NOW());
    END IF;
END;
SQL);
        }
    }

    public function down(): void
    {
        if (Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            Illuminate\Support\Facades\DB::unprepared('DROP TRIGGER IF EXISTS trg_bookings_status_after_update');
        }
        Schema::dropIfExists('booking_logs');
    }
};
