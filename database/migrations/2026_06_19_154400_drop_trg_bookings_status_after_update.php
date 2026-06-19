<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_bookings_status_after_update');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('
                CREATE TRIGGER trg_bookings_status_after_update
                AFTER UPDATE ON bookings
                FOR EACH ROW
                BEGIN
                    IF OLD.status != NEW.status THEN
                        INSERT INTO booking_logs (booking_id, changed_by, from_status, to_status, created_at, updated_at)
                        VALUES (NEW.id, NEW.user_id, OLD.status, NEW.status, NOW(), NOW());
                    END IF;
                END;
            ');
        }
    }
};
