<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('term');
            $table->string('city')->nullable();
            $table->unsignedInteger('result_count')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['term', 'created_at']);
            $table->index(['city', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_logs');
    }
};
