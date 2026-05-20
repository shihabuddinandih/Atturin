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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('nama_event');
            $table->date('tanggal');
            $table->time('waktu');
            $table->string('tempat');
            $table->integer('slot_max');
            $table->string('metode_pembayaran')->default('tunai');
            $table->decimal('iuran_per_pemain', 12, 2)->default(0);
            $table->decimal('biaya_total_event', 15, 2)->nullable();
            $table->string('skema_iuran', 50)->default('flat');
            $table->boolean('show_joined_players_public')->default(true);
            $table->boolean('show_joined_player_contacts_public')->default(false);
            $table->string('slug')->unique();
            $table->string('join_code')->unique();
            $table->json('required_fields')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
