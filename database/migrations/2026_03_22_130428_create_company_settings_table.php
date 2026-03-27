<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('auto_confirm_bookings')->default(true);
            $table->boolean('allow_client_cancellation')->default(true);
            $table->unsignedSmallInteger('cancellation_window_hours')->default(12);
            $table->unsignedSmallInteger('booking_confirmation_ttl_minutes')->nullable();
            $table->boolean('reminder_24h_enabled')->default(true);
            $table->boolean('reminder_2h_enabled')->default(true);
            $table->string('timezone', 60)->default('America/Sao_Paulo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
