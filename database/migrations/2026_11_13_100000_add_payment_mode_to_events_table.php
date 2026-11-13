<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * payment_mode:
     *  - pay_later : daftar dulu (langsung joined), link pelunasan dikirim via WhatsApp.
     *  - pay_first : slot ditahan sebentar (status_join = hold) dan baru joined setelah lunas.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('payment_mode', 20)->default('pay_later')->after('metode_pembayaran');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('payment_mode');
        });
    }
};
