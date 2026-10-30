<?php

namespace Tests\Unit;

use App\Services\EventMatchScheduler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class EventMatchSchedulerTest extends TestCase
{
    private function pairKey(array $match): string
    {
        return min($match['home'], $match['away']) . '-' . max($match['home'], $match['away']);
    }

    private function gamesPerTeam(array $schedule, array $teams): array
    {
        $games = array_fill_keys($teams, 0);
        foreach ($schedule as $match) {
            $games[$match['home']]++;
            $games[$match['away']]++;
        }

        return $games;
    }

    private function pairCounts(array $schedule): array
    {
        $pairs = [];
        foreach ($schedule as $match) {
            $pairs[$this->pairKey($match)] = ($pairs[$this->pairKey($match)] ?? 0) + 1;
        }

        return $pairs;
    }

    public function test_three_teams_six_matches_is_double_round_robin(): void
    {
        for ($run = 0; $run < 30; $run++) {
            $schedule = (new EventMatchScheduler())->generate([1, 2, 3], 6);

            $this->assertSame([1 => 4, 2 => 4, 3 => 4], $this->gamesPerTeam($schedule, [1, 2, 3]));
            $this->assertSame([2, 2, 2], array_values($this->pairCounts($schedule)));
        }
    }

    public function test_two_teams_alternate_home_and_away(): void
    {
        $schedule = (new EventMatchScheduler())->generate([7, 9], 5);

        $this->assertCount(5, $schedule);
        $this->assertSame([1, 2, 3, 4, 5], array_column($schedule, 'putaran'));
        for ($i = 1; $i < 5; $i++) {
            $this->assertNotSame($schedule[$i - 1]['home'], $schedule[$i]['home']);
        }
    }

    public function test_single_match(): void
    {
        $schedule = (new EventMatchScheduler())->generate([1, 2, 3, 4], 1);

        $this->assertCount(1, $schedule);
        $this->assertSame(1, $schedule[0]['putaran']);
    }

    public static function fairnessProvider(): array
    {
        $cases = [];
        foreach ([2, 3, 4, 5, 6, 7, 8, 9, 10, 13, 26] as $n) {
            foreach ([1, 2, 3, 5, 6, 7, 10, 11, 15, 21, 36, 50, 200] as $laga) {
                $cases["$n tim, $laga laga"] = [$n, $laga];
            }
        }

        return $cases;
    }

    #[DataProvider('fairnessProvider')]
    public function test_schedule_is_fair(int $n, int $laga): void
    {
        $teams = range(101, 100 + $n);
        $pairTotal = intdiv($n * ($n - 1), 2);

        for ($run = 0; $run < 10; $run++) {
            $schedule = (new EventMatchScheduler())->generate($teams, $laga);
            $this->assertCount($laga, $schedule);

            // Selisih jumlah main antar tim maksimal 1.
            $games = $this->gamesPerTeam($schedule, $teams);
            $this->assertLessThanOrEqual(1, max($games) - min($games), 'jumlah main tidak seimbang');

            // Semua pasangan bertemu sebelum ada pertemuan ulang.
            $pairs = $this->pairCounts($schedule);
            $counts = array_values($pairs);
            if (count($pairs) < $pairTotal) {
                $counts[] = 0;
            }
            $this->assertLessThanOrEqual(1, max($counts) - min($counts), 'ada rematch sebelum semua pasangan bertemu');

            $seen = [];
            $homes = [];
            foreach ($schedule as $i => $match) {
                $this->assertContains($match['home'], $teams);
                $this->assertContains($match['away'], $teams);
                $this->assertNotSame($match['home'], $match['away']);

                $key = $this->pairKey($match);
                $seen[$key] = ($seen[$key] ?? 0) + 1;
                $this->assertSame($seen[$key], $match['putaran'], 'nomor pertemuan salah');

                // Pertemuan ulang membalik kandang/tandang.
                if (isset($homes[$key])) {
                    $this->assertNotSame($homes[$key], $match['home']);
                }
                $homes[$key] = $match['home'];

                // Tidak main beruntun bila >= 5 tim.
                if ($n >= 5 && $i > 0) {
                    $previous = [$schedule[$i - 1]['home'], $schedule[$i - 1]['away']];
                    $this->assertEmpty(array_intersect($previous, [$match['home'], $match['away']]), 'ada tim main beruntun');
                }
            }
        }
    }

    public function test_no_team_plays_more_than_twice_in_a_row(): void
    {
        // Dengan 3-4 tim main beruntun tidak terhindarkan, tapi maksimal 2 laga berturut-turut.
        foreach ([2 => 200, 3 => 2, 4 => 2] as $n => $maxStreak) {
            foreach ([3, 6, 7, 12, 13, 30] as $laga) {
                for ($run = 0; $run < 30; $run++) {
                    $teams = range(1, $n);
                    $streak = array_fill_keys($teams, 0);

                    foreach ((new EventMatchScheduler())->generate($teams, $laga) as $match) {
                        foreach ($teams as $team) {
                            $streak[$team] = in_array($team, [$match['home'], $match['away']], true) ? $streak[$team] + 1 : 0;
                            $this->assertLessThanOrEqual($maxStreak, $streak[$team], "$n tim, $laga laga");
                        }
                    }
                }
            }
        }
    }

    public function test_every_prefix_stays_balanced(): void
    {
        // Jadwal panjang tetap seimbang di setiap titik (bila acara berhenti di tengah).
        foreach ([5, 6, 7] as $n) {
            $teams = range(1, $n);
            $schedule = (new EventMatchScheduler())->generate($teams, 60);
            $games = array_fill_keys($teams, 0);

            foreach ($schedule as $match) {
                $games[$match['home']]++;
                $games[$match['away']]++;
                $this->assertLessThanOrEqual(1, max($games) - min($games));
            }
        }
    }

    public function test_duplicate_team_ids_are_ignored(): void
    {
        $schedule = (new EventMatchScheduler())->generate([1, 1, 2, 2, 3], 3);

        $this->assertSame(['1-2', '1-3', '2-3'], collect($schedule)->map(fn ($m) => $this->pairKey($m))->sort()->values()->all());
    }

    public function test_fair_counts(): void
    {
        $scheduler = new EventMatchScheduler();

        $three = $scheduler->fairCounts(3, 9);
        $this->assertSame([3, 6, 9], array_column($three, 'jumlah'));
        $this->assertSame([2, 4, 6], array_column($three, 'main_per_tim'));
        $this->assertSame([true, true, true], array_column($three, 'round_robin_penuh'));

        $four = $scheduler->fairCounts(4, 12);
        $this->assertSame([2, 4, 6, 8, 10, 12], array_column($four, 'jumlah'));
        $this->assertSame([false, false, true, false, false, true], array_column($four, 'round_robin_penuh'));

        $this->assertSame([5, 10], array_column($scheduler->fairCounts(5, 10), 'jumlah'));
        $this->assertSame([], $scheduler->fairCounts(1));
    }

    public function test_fair_counts_really_give_equal_games(): void
    {
        $scheduler = new EventMatchScheduler();

        foreach ([3, 4, 5, 6, 7] as $n) {
            foreach ($scheduler->fairCounts($n, 30) as $option) {
                $games = $this->gamesPerTeam($scheduler->generate(range(1, $n), $option['jumlah']), range(1, $n));
                $this->assertSame([$option['main_per_tim']], array_values(array_unique($games)));
            }
        }
    }

    public static function invalidProvider(): array
    {
        return [
            'satu tim' => [[1], 3],
            'tanpa tim' => [[], 3],
            'nol laga' => [[1, 2], 0],
            'laga negatif' => [[1, 2], -1],
            'melebihi batas' => [[1, 2], EventMatchScheduler::MAX_MATCHES + 1],
        ];
    }

    #[DataProvider('invalidProvider')]
    public function test_rejects_invalid_input(array $teams, int $laga): void
    {
        $this->expectException(RuntimeException::class);

        (new EventMatchScheduler())->generate($teams, $laga);
    }
}
