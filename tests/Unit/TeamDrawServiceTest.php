<?php

namespace Tests\Unit;

use App\Services\TeamDrawService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TeamDrawServiceTest extends TestCase
{
    private function pool(array $roles): array
    {
        $pool = [];
        $id = 1;
        foreach ($roles as $role => $count) {
            for ($i = 0; $i < $count; $i++) {
                $pool[] = ['player_id' => $id++, 'role_name' => $role === '' ? null : $role];
            }
        }

        return $pool;
    }

    private function roleCount(array $members, ?string $role): int
    {
        return count(array_filter($members, fn ($m) => $m['role_name'] === $role));
    }

    public function test_teams_are_balanced_when_players_are_fewer_than_capacity(): void
    {
        for ($run = 0; $run < 50; $run++) {
            $result = (new TeamDrawService())->assign($this->pool(['' => 10]), 3, 5);
            $sizes = array_map('count', $result['teams']);

            $this->assertSame(10, array_sum($sizes));
            $this->assertLessThanOrEqual(1, max($sizes) - min($sizes));
            $this->assertSame([], $result['cadangan']);
        }
    }

    public function test_exact_capacity_has_no_cadangan(): void
    {
        $result = (new TeamDrawService())->assign($this->pool(['' => 12]), 3, 4);

        $this->assertSame([4, 4, 4], array_map('count', $result['teams']));
        $this->assertSame([], $result['cadangan']);
    }

    public function test_extra_players_become_cadangan(): void
    {
        $result = (new TeamDrawService())->assign($this->pool(['' => 14]), 3, 4);

        $this->assertSame([4, 4, 4], array_map('count', $result['teams']));
        $this->assertCount(2, $result['cadangan']);
    }

    public function test_players_equal_to_teams_gives_one_player_each(): void
    {
        $result = (new TeamDrawService())->assign($this->pool(['' => 4]), 4, 5);

        $this->assertSame([1, 1, 1, 1], array_map('count', $result['teams']));
    }

    public function test_one_player_per_team(): void
    {
        $result = (new TeamDrawService())->assign($this->pool(['' => 6]), 2, 1);

        $this->assertSame([1, 1], array_map('count', $result['teams']));
        $this->assertCount(4, $result['cadangan']);
    }

    public function test_rare_roles_are_spread_across_teams(): void
    {
        for ($run = 0; $run < 50; $run++) {
            $result = (new TeamDrawService())->assign($this->pool(['Kiper' => 3, 'Pemain' => 12]), 3, 5);

            foreach ($result['teams'] as $members) {
                $this->assertSame(1, $this->roleCount($members, 'Kiper'));
                $this->assertCount(5, $members);
            }
        }
    }

    public function test_fewer_keepers_than_teams_never_doubles_up(): void
    {
        for ($run = 0; $run < 50; $run++) {
            $result = (new TeamDrawService())->assign($this->pool(['Kiper' => 2, 'Pemain' => 13]), 3, 5);
            $keepers = array_map(fn ($m) => $this->roleCount($m, 'Kiper'), $result['teams']);

            sort($keepers);
            $this->assertSame([0, 1, 1], $keepers);
        }
    }

    public function test_more_keepers_than_teams_are_spread_evenly(): void
    {
        for ($run = 0; $run < 50; $run++) {
            $result = (new TeamDrawService())->assign($this->pool(['Kiper' => 5, 'Pemain' => 10]), 3, 5);
            $keepers = array_map(fn ($m) => $this->roleCount($m, 'Kiper'), $result['teams']);

            $this->assertLessThanOrEqual(1, max($keepers) - min($keepers));
        }
    }

    public function test_keepers_are_kept_when_there_are_cadangan(): void
    {
        for ($run = 0; $run < 30; $run++) {
            $result = (new TeamDrawService())->assign($this->pool(['Kiper' => 2, 'Pemain' => 20]), 2, 5);

            foreach ($result['teams'] as $members) {
                $this->assertSame(1, $this->roleCount($members, 'Kiper'));
            }
            $this->assertCount(12, $result['cadangan']);
            $this->assertSame(0, $this->roleCount($result['cadangan'], 'Kiper'));
        }
    }

    public function test_players_without_role_are_mixed_with_roles(): void
    {
        $result = (new TeamDrawService())->assign($this->pool(['Kiper' => 2, '' => 8]), 2, 5);

        foreach ($result['teams'] as $members) {
            $this->assertSame(1, $this->roleCount($members, 'Kiper'));
            $this->assertSame(4, $this->roleCount($members, null));
        }
    }

    public function test_every_player_is_assigned_exactly_once(): void
    {
        $result = (new TeamDrawService())->assign($this->pool(['Kiper' => 4, 'Pemain' => 17]), 4, 5);

        $ids = array_column(array_merge(array_merge(...$result['teams']), $result['cadangan']), 'player_id');
        sort($ids);
        $this->assertSame(range(1, 21), $ids);
    }

    public function test_draw_is_random(): void
    {
        $signatures = [];
        for ($run = 0; $run < 20; $run++) {
            $result = (new TeamDrawService())->assign($this->pool(['' => 10]), 2, 5);
            $first = array_column($result['teams'][0], 'player_id');
            sort($first);
            $signatures[implode(',', $first)] = true;
        }

        $this->assertGreaterThan(1, count($signatures));
    }

    public function test_random_pools_stay_balanced(): void
    {
        $roles = ['Kiper', 'Bek', 'Pemain', null];

        for ($run = 0; $run < 300; $run++) {
            $teams = random_int(2, 8);
            $perTeam = random_int(1, 8);
            $total = random_int($teams, $teams * $perTeam + 6);
            $pool = [];
            for ($i = 1; $i <= $total; $i++) {
                $pool[] = ['player_id' => $i, 'role_name' => $roles[array_rand($roles)]];
            }

            $result = (new TeamDrawService())->assign($pool, $teams, $perTeam);
            $sizes = array_map('count', $result['teams']);

            $this->assertCount($teams, $result['teams']);
            $this->assertSame(min($total, $teams * $perTeam), array_sum($sizes));
            $this->assertSame($total, array_sum($sizes) + count($result['cadangan']));
            $this->assertLessThanOrEqual(1, max($sizes) - min($sizes));
            $this->assertGreaterThanOrEqual(1, min($sizes));
            $this->assertLessThanOrEqual($perTeam, max($sizes));

            foreach ($roles as $role) {
                $counts = array_map(fn ($m) => $this->roleCount($m, $role), $result['teams']);
                $this->assertLessThanOrEqual(1, max($counts) - min($counts));
            }
        }
    }

    public static function invalidProvider(): array
    {
        return [
            'pemain kurang dari tim' => [2, 3, 5],
            'tanpa pemain' => [0, 2, 5],
            'satu tim' => [10, 1, 5],
            'nol tim' => [10, 0, 5],
            'melebihi batas tim' => [100, TeamDrawService::MAX_TEAMS + 1, 2],
            'nol pemain per tim' => [10, 2, 0],
        ];
    }

    #[DataProvider('invalidProvider')]
    public function test_rejects_invalid_input(int $players, int $teams, int $perTeam): void
    {
        $this->expectException(RuntimeException::class);

        (new TeamDrawService())->assign($this->pool(['' => $players]), $teams, $perTeam);
    }

    public function test_error_message_mentions_minimum_one_player(): void
    {
        try {
            (new TeamDrawService())->assign($this->pool(['' => 2]), 3, 5);
            $this->fail('Exception tidak dilempar');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('minimal harus memiliki 1 pemain', $e->getMessage());
        }
    }
}
