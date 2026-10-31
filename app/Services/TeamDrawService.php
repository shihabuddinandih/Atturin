<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventMatch;
use App\Models\EventTeam;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TeamDrawService
{
    public const MAX_TEAMS = 26;

    /** Warna tim (urutan sesuai Tim A, Tim B, ...). */
    public const COLORS = [
        '#0052FF', '#E11D48', '#ABD600', '#F59E0B', '#7C3AED', '#0891B2',
        '#EA580C', '#16A34A', '#DB2777', '#475569',
    ];

    /**
     * Bagi semua pemain ke tim secara acak & seimbang (murni, tanpa DB). Tidak ada cadangan:
     * setiap pemain masuk tim dan selisih jumlah anggota antar tim maksimal 1.
     *
     * @param  array<int, array{player_id:int, role_name:?string}>  $pool
     * @return array{teams: array<int, array<int, array{player_id:int, role_name:?string}>>}
     */
    public function assign(array $pool, int $jumlahTim): array
    {
        if ($jumlahTim < 2) {
            throw new RuntimeException('Jumlah tim minimal 2.');
        }

        if ($jumlahTim > self::MAX_TEAMS) {
            throw new RuntimeException('Jumlah tim maksimal ' . self::MAX_TEAMS . '.');
        }

        if (count($pool) < $jumlahTim) {
            throw new RuntimeException('Jumlah pemain (' . count($pool) . ') kurang dari jumlah tim (' . $jumlahTim . '). Setiap tim minimal harus memiliki 1 pemain.');
        }

        $pool = array_values($pool);
        shuffle($pool);

        // Kelompokkan per peran, peran paling langka dibagikan lebih dulu.
        $groups = [];
        foreach ($pool as $entry) {
            $groups[(string) ($entry['role_name'] ?? '')][] = $entry;
        }
        uasort($groups, fn (array $a, array $b) => count($a) <=> count($b));

        // Bagikan per gelombang: tiap gelombang maksimal $jumlahTim pemain dari setiap peran.
        $ordered = [];
        $waves = (int) ceil(max(array_map('count', $groups)) / $jumlahTim);
        for ($wave = 0; $wave < $waves; $wave++) {
            foreach ($groups as $members) {
                array_push($ordered, ...array_slice($members, $wave * $jumlahTim, $jumlahTim));
            }
        }

        $teams = array_fill(0, $jumlahTim, []);
        $roleCounts = array_fill(0, $jumlahTim, []);

        foreach ($ordered as $entry) {
            $role = (string) ($entry['role_name'] ?? '');
            $candidates = [];
            $bestKey = null;

            // Masuk ke tim dengan anggota paling sedikit, lalu yang paling sedikit memiliki peran ini.
            foreach ($teams as $index => $members) {
                $key = [count($members), $roleCounts[$index][$role] ?? 0];
                if ($bestKey === null || $key < $bestKey) {
                    $bestKey = $key;
                    $candidates = [$index];
                } elseif ($key === $bestKey) {
                    $candidates[] = $index;
                }
            }

            $target = $candidates[array_rand($candidates)];
            $teams[$target][] = $entry;
            $roleCounts[$target][$role] = ($roleCounts[$target][$role] ?? 0) + 1;
        }

        return ['teams' => $teams];
    }

    /**
     * Ambil peserta event, acak ke tim, lalu simpan (menggantikan hasil sebelumnya).
     *
     * @param  array{jumlah_tim:int, hanya_hadir:bool}  $config
     */
    public function draw(Event $event, array $config): array
    {
        $query = $event->players()->wherePivot('status_join', 'joined');
        if (! empty($config['hanya_hadir'])) {
            $query->wherePivot('hadir', true);
        }

        $pool = $query->get()->map(fn ($player) => [
            'player_id' => $player->id,
            'role_name' => $player->pivot->role_name,
        ])->all();

        $result = $this->assign($pool, (int) $config['jumlah_tim']);

        DB::transaction(function () use ($event, $config, $result) {
            EventMatch::where('event_id', $event->id)->delete();
            $event->teamPlayers()->delete();
            EventTeam::where('event_id', $event->id)->delete();

            foreach ($result['teams'] as $index => $members) {
                $team = $event->teams()->create([
                    'nama_tim' => 'Tim ' . chr(65 + $index),
                    'warna' => self::COLORS[$index % count(self::COLORS)],
                    'urutan' => $index + 1,
                ]);

                foreach ($members as $member) {
                    $event->teamPlayers()->create([
                        'event_team_id' => $team->id,
                        'player_id' => $member['player_id'],
                        'role_name' => $member['role_name'],
                    ]);
                }
            }

            $event->update([
                'team_draw_config' => [
                    'jumlah_tim' => (int) $config['jumlah_tim'],
                    'hanya_hadir' => (bool) ($config['hanya_hadir'] ?? false),
                ],
            ]);
        });

        return $result;
    }

    public function reset(Event $event): void
    {
        DB::transaction(function () use ($event) {
            EventMatch::where('event_id', $event->id)->delete();
            $event->teamPlayers()->delete();
            EventTeam::where('event_id', $event->id)->delete();
            $event->update(['team_draw_config' => null, 'team_draw_published_at' => null]);
        });
    }
}
