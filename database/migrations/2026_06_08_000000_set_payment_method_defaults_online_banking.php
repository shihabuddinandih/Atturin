<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('metode_pembayaran')->default('online_banking')->change();
        });

        Schema::table('event_player', function (Blueprint $table) {
            $table->string('payment_method')->default('online_banking')->change();
        });

        DB::table('events')
            ->where('metode_pembayaran', 'tunai')
            ->update(['metode_pembayaran' => 'online_banking']);

        DB::table('event_player')
            ->where('payment_method', 'tunai')
            ->where('payment_status', '!=', 'paid')
            ->update(['payment_method' => 'online_banking']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('metode_pembayaran')->default('tunai')->change();
        });

        Schema::table('event_player', function (Blueprint $table) {
            $table->string('payment_method')->default('tunai')->change();
        });
    }
};
