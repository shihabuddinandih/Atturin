<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('event_player', function (Blueprint $table) {
            $table->string('registration_token', 64)->nullable()->unique()->after('id');
        });

        DB::table('event_player')->whereNull('registration_token')->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('event_player')->where('id', $row->id)->update([
                        'registration_token' => Str::random(40),
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_player', function (Blueprint $table) {
            $table->dropColumn('registration_token');
        });
    }
};
