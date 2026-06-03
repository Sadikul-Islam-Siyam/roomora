<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('discount');
            $table->boolean('is_paid')->default(false)->after('payment_method');
            $table->timestamp('paid_at')->nullable()->after('is_paid');
            $table->text('billing_address')->nullable()->after('paid_at');
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'is_paid', 'paid_at', 'billing_address']);
        });
    }
};
