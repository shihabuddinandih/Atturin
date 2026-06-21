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
        Schema::create('event_player', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->enum('status_join', ['joined', 'batal'])->default('joined');
            $table->boolean('hadir')->default(false);
            $table->string('payment_method')->default('online_banking');
            $table->string('role_name')->nullable();
            $table->decimal('payment_amount', 12, 2)->default(0);
            $table->string('payment_status')->default('pending');
            $table->string('payment_reference')->nullable();
            $table->timestamp('payment_paid_at')->nullable();
            $table->timestamp('payment_expires_at')->nullable();
            $table->string('payment_snap_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_player');
    }
};
