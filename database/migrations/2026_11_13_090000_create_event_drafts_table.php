<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Draf event disimpan terpisah dari tabel `events` agar tidak pernah
     * mendapat slug/join_code dan tidak ikut terbaca di halaman publik/statistik.
     */
    public function up(): void
    {
        Schema::create('event_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_event')->nullable();
            $table->json('payload')->nullable();
            $table->string('banner_image')->nullable();
            $table->timestamps();

            $table->index(['admin_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_drafts');
    }
};
