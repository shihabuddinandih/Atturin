<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventMatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EventMatchScheduler
{
    public const MAX_MATCHES = 200;

    /**
     * Susun jadwal adil (murni, tanpa DB). Jaminan untuk setiap jumlah laga:
     * - semua pasangan bertemu sekali sebelum ada pertemuan ulang (round-robin berulang),
     * - selisih jumlah main antar tim maksimal 1,
     * - pertemuan ulang membalik kandang/tandang,
     * - tidak ada tim yang main beruntun bila jumlah tim >= 5 (3-4 tim: maksimal 2 laga berturut-turut).
     *
     * Jumlah tim genap memakai metode lingkaran (tiap ronde semua tim main sekali). Jumlah tim
     * ganjil memakai dekomposisi Walecki menjadi siklus Hamilton yang laganya diurutkan agar
     * selisih jumlah main tetap maksimal 1 di setiap titik.
     *
     * @param  array<int, int>  $teamIds
     * @return array<int, array{home:int, away:int, putaran:int}>
     */
    public function generate(array $teamIds, int $jumlahLaga): array
    {
        $teamIds = array_values(array_unique($teamIds));
        $n = count($teamIds);

        if ($n < 2) {
            throw new RuntimeException('Minimal harus ada 2 tim untuk membuat jadwal.');
        }

        if ($jumlahLaga < 1 || $jumlahLaga > self::MAX_MATCHES) {
            throw new RuntimeException('Jumlah pertandingan harus antara 1 dan ' . self::MAX_MATCHES . '.');
        }

        $meetings = [];
        $lastHome = [];
        $homeGames = array_fill_keys($teamIds, 0);
        $streak = array_fill_keys($teamIds, 0);
        $schedule = [];

        while (true) {
            // Label diacak setiap putaran agar susunan tidak monoton.
            $ids = $teamIds;
            shuffle($ids);

            $blocks = $n % 2 === 0 ? $this->circleRounds($ids) : $this->waleckiCycles($ids);

            foreach ($blocks as $block) {
                $ordered = $n % 2 === 0
                    ? $this->arrangeRound($block, $streak)
                    : $this->arrangeCycle($block, $streak);

                foreach ($ordered as [$a, $b]) {
                    $pairKey = min($a, $b) . '-' . max($a, $b);

                    // Pertemuan pertama: tuan rumah tim dengan laga kandang lebih sedikit; berikutnya bergantian.
                    if (isset($lastHome[$pairKey])) {
                        $home = $lastHome[$pairKey] === $a ? $b : $a;
                    } else {
                        $home = $homeGames[$a] <= $homeGames[$b] ? $a : $b;
                    }
                    $away = $home === $a ? $b : $a;

                    $meetings[$pairKey] = ($meetings[$pairKey] ?? 0) + 1;
                    $lastHome[$pairKey] = $home;
                    $homeGames[$home]++;
                    foreach ($streak as $team => $count) {
                        $streak[$team] = ($team === $a || $team === $b) ? $count + 1 : 0;
                    }

                    $schedule[] = ['home' => $home, 'away' => $away, 'putaran' => $meetings[$pairKey]];

                    if (count($schedule) === $jumlahLaga) {
                        return $schedule;
                    }
                }
            }
        }
    }

    /**
     * Ronde round-robin metode lingkaran (jumlah tim genap): tiap ronde berisi laga yang saling lepas.
     *
     * @return array<int, array<int, array{0:int, 1:int}>>
     */
    private function circleRounds(array $ids): array
    {
        $n = count($ids);
        $fixed = $ids[$n - 1];
        $rotating = $n - 1;
        $rounds = [];

        for ($r = 0; $r < $rotating; $r++) {
            $round = [[$fixed, $ids[$r]]];
            for ($k = 1; $k < intdiv($n, 2); $k++) {
                $round[] = [$ids[($r + $k) % $rotating], $ids[($r - $k + $rotating) % $rotating]];
            }
            $rounds[] = $round;
        }

        return $rounds;
    }

    /**
     * Dekomposisi Walecki (jumlah tim ganjil): K_n dipecah menjadi (n-1)/2 siklus Hamilton
     * yang tidak berbagi pasangan. Setiap siklus dikembalikan sebagai urutan tim.
     *
     * @return array<int, array<int, int>>
     */
    private function waleckiCycles(array $ids): array
    {
        $n = count($ids);
        $hub = $ids[$n - 1];
        $size = $n - 1;
        $m = intdiv($size, 2);
        $cycles = [];

        for ($i = 0; $i < $m; $i++) {
            $sequence = [$hub, $ids[$i]];
            for ($k = 1; $k < $m; $k++) {
                $sequence[] = $ids[($i + $k) % $size];
                $sequence[] = $ids[($i - $k + $size) % $size];
            }
            $sequence[] = $ids[($i + $m) % $size];
            $cycles[] = $sequence;
        }

        return $cycles;
    }

    /**
     * Skor beban laga: [main beruntun terpanjang, total main beruntun] kedua tim. Makin kecil makin segar.
     */
    private function fatigue(int $a, int $b, array $streak): array
    {
        return [max($streak[$a], $streak[$b]), $streak[$a] + $streak[$b]];
    }

    /**
     * Urutkan laga dalam satu ronde: laga pertama dipilih yang timnya paling segar
     * (idealnya tidak main di laga sebelumnya). Laga dalam satu ronde saling lepas.
     */
    private function arrangeRound(array $round, array $streak): array
    {
        shuffle($round);

        $best = 0;
        foreach ($round as $index => [$a, $b]) {
            if ($this->fatigue($a, $b, $streak) < $this->fatigue($round[$best][0], $round[$best][1], $streak)) {
                $best = $index;
            }
        }

        $first = $round[$best];
        unset($round[$best]);

        return array_merge([$first], array_values($round));
    }

    /**
     * Ubah siklus Hamilton v0..v(n-1) menjadi urutan laga e0, e2, ..., e(n-3), e(n-1), e1, e3, ..., e(n-2)
     * dengan ei = (vi, vi+1). Urutan ini menjaga selisih jumlah main maksimal 1 dan laga berurutan
     * saling lepas (n >= 5). Rotasi/arah siklus dipilih yang menghasilkan main beruntun terpendek.
     */
    private function arrangeCycle(array $cycle, array $streak): array
    {
        $n = count($cycle);
        $candidates = [];
        foreach ([$cycle, array_reverse($cycle)] as $base) {
            for ($shift = 0; $shift < $n; $shift++) {
                $candidates[] = $this->cycleOrder(array_merge(array_slice($base, $shift), array_slice($base, 0, $shift)));
            }
        }
        shuffle($candidates);

        $best = null;
        $bestKey = null;
        foreach ($candidates as $order) {
            $key = [$this->peakStreak($order, $streak), $this->fatigue($order[0][0], $order[0][1], $streak)];
            if ($bestKey === null || $key < $bestKey) {
                $best = $order;
                $bestKey = $key;
            }
        }

        return $best;
    }

    private function cycleOrder(array $cycle): array
    {
        $n = count($cycle);
        $edge = fn (int $i) => [$cycle[$i], $cycle[($i + 1) % $n]];
        $order = [];
        for ($i = 0; $i <= $n - 3; $i += 2) {
            $order[] = $edge($i);
        }
        $order[] = $edge($n - 1);
        for ($i = 1; $i <= $n - 2; $i += 2) {
            $order[] = $edge($i);
        }

        return $order;
    }

    /**
     * Main beruntun terpanjang bila urutan laga ini dijalankan setelah kondisi $streak saat ini.
     */
    private function peakStreak(array $order, array $streak): int
    {
        $peak = 0;
        foreach ($order as [$a, $b]) {
            foreach ($streak as $team => $count) {
                $streak[$team] = ($team === $a || $team === $b) ? $count + 1 : 0;
            }
            $peak = max($peak, $streak[$a], $streak[$b]);
        }

        return $peak;
    }

    /**
     * Saran jumlah laga di mana setiap tim bermain sama banyak.
     *
     * @return array<int, array{jumlah:int, main_per_tim:int, round_robin_penuh:bool}>
     */
    public function fairCounts(int $n, int $max = 30): array
    {
        if ($n < 2) {
            return [];
        }

        $pairCount = intdiv($n * ($n - 1), 2);
        $result = [];

        for ($jumlah = 1; $jumlah <= $max; $jumlah++) {
            if ((2 * $jumlah) % $n !== 0) {
                continue;
            }

            $result[] = [
                'jumlah' => $jumlah,
                'main_per_tim' => intdiv(2 * $jumlah, $n),
                'round_robin_penuh' => $jumlah % $pairCount === 0,
            ];
        }

        return $result;
    }

    /**
     * Buat & simpan jadwal untuk tim hasil acak (menggantikan jadwal sebelumnya).
     */
    public function schedule(Event $event, int $jumlahLaga, ?int $durasiLaga = null): void
    {
        $teamIds = $event->teams()->pluck('id')->all();
        $matches = $this->generate($teamIds, $jumlahLaga);

        $start = null;
        if ($durasiLaga && $event->waktu) {
            try {
                $start = Carbon::parse($event->waktu);
            } catch (\Throwable) {
                $start = null;
            }
        }

        DB::transaction(function () use ($event, $matches, $start, $durasiLaga, $jumlahLaga) {
            EventMatch::where('event_id', $event->id)->delete();

            foreach ($matches as $index => $match) {
                $event->matches()->create([
                    'urutan' => $index + 1,
                    'putaran' => $match['putaran'],
                    'home_team_id' => $match['home'],
                    'away_team_id' => $match['away'],
                    'perkiraan_mulai' => $start?->copy()->addMinutes($index * $durasiLaga)->format('H:i:s'),
                ]);
            }

            $event->update([
                'team_draw_config' => array_merge($event->team_draw_config ?? [], [
                    'jumlah_pertandingan' => $jumlahLaga,
                    'durasi_laga' => $durasiLaga,
                ]),
            ]);
        });
    }
}
