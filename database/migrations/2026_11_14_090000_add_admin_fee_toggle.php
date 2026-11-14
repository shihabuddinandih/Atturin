<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - app_settings           : pengaturan global platform (mis. saklar biaya admin) yang diubah superadmin.
     * - event_player.admin_fee : biaya admin yang benar-benar dibebankan saat mendaftar, supaya
     *   laporan keuangan tetap akurat walau saklar diubah. NULL = data lama (pakai rumus tier).
     */
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
        Schema::table('event_player', function (Blueprint $table) {
            $table->decimal('admin_fee', 12, 2)->nullable()->after('payment_amount');
        });
    }

    public function down(): void
    {
        Schema::table('event_player', function (Blueprint $table) {
            $table->dropColumn('admin_fee');
        });
        Schema::dropIfExists('app_settings');
    }
};
