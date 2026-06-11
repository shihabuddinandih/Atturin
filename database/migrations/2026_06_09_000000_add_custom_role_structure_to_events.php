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
        Schema::table('events', function (Blueprint $table) {
            $table->json('roles')->nullable()->after('skema_iuran');
        });

        Schema::table('event_player', function (Blueprint $table) {
            $table->string('role_name')->nullable()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_player', function (Blueprint $table) {
            $table->dropColumn('role_name');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('roles');
        });
    }
};
