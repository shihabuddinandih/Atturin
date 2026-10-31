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

    private function sortedSizes(array $result): array
    {
        $sizes = array_map('count', $result['teams']);
        sort($sizes);

        return $sizes;
    }

    public function test_result_has_no_cadangan(): void
    {
        $result = (new TeamDrawService())->assign($this->pool(['' => 17]), 3);

        $this->assertSame(['teams'], array_keys($result));
    }

    public static function sizeProvider(): array
    {
        return [
            '17 pemain, 3 tim' => [17, 3, [5, 6, 6]],
            '10 pemain, 3 tim' => [10, 3, [3, 3, 4]],
            '12 pemain, 3 tim' => [12, 3, [4, 4, 4]],
            '10 pemain, 2 tim' => [10, 2, [5, 5]],
            '11 pemain, 2 tim' => [11, 2, [5, 6]],
            '4 pemain, 4 tim' => [4, 4, [1, 1, 1, 1]],
            '5 pemain, 4 tim' => [5, 4, [1, 1, 1, 2]],
            '30 pemain, 4 tim' => [30, 4, [7, 7, 8, 8]],
        ];
    }

    #[DataProvider('sizeProvider')]
    public function test_all_players_are_spread_evenly(int $players, int $teams, array $expected): void
    {
        for ($run = 0; $run < 20; $run++) {
            $result = (new TeamDrawService())->assign($this->pool(['' => $players]), $teams);

            $this->assertCount($teams, $result['teams']);
            $this->assertSame($expected, $this->sortedSizes($result));
        }
    }

    public function test_every_player_is_assigned_exactly_once(): void
    {
        $result = (new TeamDrawService())->assign($this->pool(['Kiper' => 4, 'Pemain' => 17]), 4);

        $ids = array_column(array_merge(...$result['teams']), 'player_id');
        sort($ids);
        $this->assertSame(range(1, 21), $ids);
    }

    public function test_rare_roles_are_spread_across_teams(): void
    {
        for ($run = 0; $run < 50; $run++) {
            $result = (new TeamDrawService())->assign($this->pool(['Kiper' => 3, 'Pemain' => 14]), 3);

            foreach ($result['teams'] as $members) {
                $this->assertSame(1, $this->roleCount($members, 'Kiper'));
            }
            $this->assertSame([5, 6, 6], $this->sortedSizes($result));
        }
    }

    public function test_fewer_keepers_than_teams_never_doubles_up(): void
    {
        for ($run = 0; $run < 50; $run++) {
            $result = (new TeamDrawService())->assign($this->pool(['Kiper' => 2, 'Pemain' => 13]), 3);
            $keepers = array_map(fn ($m) => $this->roleCount($m, 'Kiper'), $result['teams']);

            sort($keepers);
            $this->assertSame([0, 1, 1], $keepers);
        }
    }

    public function test_more_keepers_than_teams_are_spread_evenly(): void
    {
        for ($run = 0; $run < 50; $run++) {
            $result = (new TeamDrawService())->assign($this->pool(['Kiper' => 5, 'Pemain' => 10]), 3);
            $keepers = array_map(fn ($m) => $this->roleCount($m, 'Kiper'), $result['teams']);

            $this->assertLessThanOrEqual(1, max($keepers) - min($keepers));
        }
    }

    public function test_players_without_role_are_mixed_with_roles(): void
    {
        $result = (new TeamDrawService())->assign($this->pool(['Kiper' => 2, '' => 8]), 2);

        foreach ($result['teams'] as $members) {
            $this->assertSame(1, $this->roleCount($members, 'Kiper'));
            $this->assertSame(4, $this->roleCount($members, null));
        }
    }

    public function test_draw_is_random(): void
    {
        $signatures = [];
        for ($run = 0; $run < 20; $run++) {
            $result = (new TeamDrawService())->assign($this->pool(['' => 10]), 2);
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
            $teams = random_int(2, 10);
            $total = random_int($teams, 60);
            $pool = [];
            for ($i = 1; $i <= $total; $i++) {
                $pool[] = ['player_id' => $i, 'role_name' => $roles[array_rand($roles)]];
            }

            $result = (new TeamDrawService())->assign($pool, $teams);
            $sizes = array_map('count', $result['teams']);

            $this->assertCount($teams, $result['teams']);
            $this->assertSame($total, array_sum($sizes), 'ada pemain yang tidak masuk tim');
            $this->assertLessThanOrEqual(1, max($sizes) - min($sizes));
            $this->assertGreaterThanOrEqual(1, min($sizes));

            $ids = array_column(array_merge(...$result['teams']), 'player_id');
            $this->assertCount($total, array_unique($ids));

            foreach ($roles as $role) {
                $counts = array_map(fn ($m) => $this->roleCount($m, $role), $result['teams']);
                $this->assertLessThanOrEqual(1, max($counts) - min($counts));
            }
        }
    }

    public static function invalidProvider(): array
    {
        return [
            'pemain kurang dari tim' => [2, 3],
            'tanpa pemain' => [0, 2],
            'satu tim' => [10, 1],
            'nol tim' => [10, 0],
            'melebihi batas tim' => [100, TeamDrawService::MAX_TEAMS + 1],
        ];
    }

    #[DataProvider('invalidProvider')]
    public function test_rejects_invalid_input(int $players, int $teams): void
    {
        $this->expectException(RuntimeException::class);

        (new TeamDrawService())->assign($this->pool(['' => $players]), $teams);
    }

    public function test_error_message_mentions_minimum_one_player(): void
    {
        try {
            (new TeamDrawService())->assign($this->pool(['' => 2]), 3);
            $this->fail('Exception tidak dilempar');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('minimal harus memiliki 1 pemain', $e->getMessage());
        }
    }
}
