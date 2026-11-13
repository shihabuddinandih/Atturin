<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tambah nilai 'hold' pada enum event_player.status_join (mode "bayar dulu").
 * Tanpa doctrine/dbal, jadi diubah langsung per driver.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->setStatuses(['joined', 'batal', 'hold'], ['joined', 'batal']);
    }

    public function down(): void
    {
        // Peserta yang masih menahan slot dianggap batal sebelum nilai 'hold' dihapus
        DB::table('event_player')->where('status_join', 'hold')->update(['status_join' => 'batal']);

        $this->setStatuses(['joined', 'batal'], ['joined', 'batal', 'hold']);
    }

    private function setStatuses(array $to, array $from): void
    {
        $driver = DB::connection()->getDriverName();
        $list = implode(', ', array_map(fn ($v) => "'{$v}'", $to));

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE event_player MODIFY status_join ENUM({$list}) NOT NULL DEFAULT 'joined'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE event_player DROP CONSTRAINT IF EXISTS event_player_status_join_check');
            DB::statement("ALTER TABLE event_player ADD CONSTRAINT event_player_status_join_check CHECK (status_join IN ({$list}))");
            return;
        }

        if ($driver === 'sqlite') {
            $this->rebuildSqliteTable($from, $to);
        }
    }

    /**
     * SQLite tidak bisa mengubah CHECK constraint, jadi tabel dibangun ulang dari definisi aslinya.
     */
    private function rebuildSqliteTable(array $from, array $to): void
    {
        $table = DB::selectOne("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = 'event_player'");
        $indexes = DB::select("SELECT sql FROM sqlite_master WHERE type = 'index' AND tbl_name = 'event_player' AND sql IS NOT NULL");

        $quote = fn (array $values) => implode(', ', array_map(fn ($v) => "'{$v}'", $values));
        $createSql = str_replace("in ({$quote($from)})", "in ({$quote($to)})", $table->sql);
        $createSql = preg_replace('/^CREATE TABLE "?event_player"?/i', 'CREATE TABLE "event_player__tmp"', $createSql);

        DB::statement('PRAGMA foreign_keys = OFF');
        DB::statement($createSql);
        DB::statement('INSERT INTO "event_player__tmp" SELECT * FROM "event_player"');
        DB::statement('DROP TABLE "event_player"');
        DB::statement('ALTER TABLE "event_player__tmp" RENAME TO "event_player"');
        foreach ($indexes as $index) {
            DB::statement($index->sql);
        }
        DB::statement('PRAGMA foreign_keys = ON');
    }
};
