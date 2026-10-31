<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventMatch;
use App\Models\EventTeam;
use App\Models\EventTeamPlayer;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Hanya berjalan di database khusus test agar RefreshDatabase tidak menghapus database pengembangan:
 *   $env:DB_CONNECTION='sqlite'; $env:DB_DATABASE=':memory:'; php artisan test --filter=EventTeamDrawTest
 *   $env:DB_DATABASE='atturin_testing'; php artisan test --filter=EventTeamDrawTest   (MySQL)
 */
class EventTeamDrawTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        $database = (string) getenv('DB_DATABASE');
        $isSqliteMemory = getenv('DB_CONNECTION') === 'sqlite' && $database === ':memory:';
        if (! $isSqliteMemory && ! str_ends_with($database, '_testing')) {
            $this->markTestSkipped('Jalankan dengan DB_DATABASE=:memory: (sqlite) atau database berakhiran _testing.');
        }

        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function makeEvent(array $attributes = []): Event
    {
        return Event::create(array_merge([
            'admin_id' => $this->admin->id,
            'nama_event' => 'Mabar Jumat',
            'tanggal' => now()->toDateString(),
            'waktu' => '19:00',
            'tempat' => 'GOR Sukamaju',
            'slot_max' => 30,
            'metode_pembayaran' => 'online_banking',
            'iuran_per_pemain' => 10000,
            'biaya_total_event' => 300000,
            'skema_iuran' => 'flat',
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $pivot
     */
    private function addPlayers(Event $event, int $count, array $pivot = [], string $prefix = 'Pemain'): array
    {
        static $sequence = 0;
        $ids = [];

        for ($i = 1; $i <= $count; $i++) {
            $sequence++;
            $player = Player::create(['nama' => "$prefix $sequence", 'kontak' => '0812' . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT)]);
            $event->players()->attach($player->id, array_merge(['status_join' => 'joined', 'hadir' => false], $pivot));
            $ids[] = $player->id;
        }

        return $ids;
    }

    private function draw(Event $event, array $payload = [])
    {
        return $this->actingAs($this->admin)->post(route('admin.events.teamDraw.draw', $event), array_merge([
            'jumlah_tim' => 3,
        ], $payload));
    }

    private function schedule(Event $event, array $payload = [])
    {
        return $this->actingAs($this->admin)->post(route('admin.events.teamDraw.schedule', $event), array_merge([
            'jumlah_pertandingan' => 6,
        ], $payload));
    }

    private function publish(Event $event, bool $publish = true)
    {
        return $this->actingAs($this->admin)->post(route('admin.events.teamDraw.publish', $event), ['publish' => $publish ? 1 : 0]);
    }

    // ─── Akses ────────────────────────────────────────────────────

    public function test_guest_is_redirected_to_login(): void
    {
        $event = $this->makeEvent();

        $this->get(route('admin.events.teamDraw.show', $event))->assertRedirect(route('login'));
        $this->post(route('admin.events.teamDraw.draw', $event), ['jumlah_tim' => 2])->assertRedirect(route('login'));
    }

    public function test_regular_user_is_forbidden(): void
    {
        $event = $this->makeEvent();
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get(route('admin.events.teamDraw.show', $event))->assertForbidden();
    }

    public function test_other_admin_cannot_touch_the_event(): void
    {
        $event = $this->makeEvent();
        $this->addPlayers($event, 10);
        $this->draw($event, ['jumlah_tim' => 2]);
        $other = User::factory()->create(['role' => 'admin']);

        $this->actingAs($other)->get(route('admin.events.teamDraw.show', $event))->assertForbidden();
        $this->actingAs($other)->post(route('admin.events.teamDraw.draw', $event), ['jumlah_tim' => 2])->assertForbidden();
        $this->actingAs($other)->post(route('admin.events.teamDraw.schedule', $event), ['jumlah_pertandingan' => 3])->assertForbidden();
        $this->actingAs($other)->post(route('admin.events.teamDraw.publish', $event), ['publish' => 1])->assertForbidden();
        $this->actingAs($other)->delete(route('admin.events.teamDraw.reset', $event))->assertForbidden();

        $this->assertSame(2, $event->teams()->count());
    }

    // ─── Halaman admin ────────────────────────────────────────────

    public function test_page_renders_for_event_without_players(): void
    {
        $event = $this->makeEvent();

        $this->actingAs($this->admin)->get(route('admin.events.teamDraw.show', $event))
            ->assertOk()
            ->assertSee('Acak Pemain ke Tim')
            ->assertSee('Belum ada peserta.')
            ->assertDontSee('Buat Jadwal Pertandingan');
    }

    public function test_event_detail_links_to_team_draw(): void
    {
        $event = $this->makeEvent();

        $this->actingAs($this->admin)->get(route('admin.events.show', $event))
            ->assertOk()
            ->assertSee('Kelola Tim')
            ->assertSee(route('admin.events.teamDraw.show', $event));
    }

    // ─── Acak tim ─────────────────────────────────────────────────

    public function test_draw_puts_every_player_in_a_team_without_cadangan(): void
    {
        $event = $this->makeEvent();
        $this->addPlayers($event, 17);

        $response = $this->draw($event)->assertRedirect(route('admin.events.teamDraw.show', $event));

        $teams = $event->teams()->withCount('members')->get();
        $sizes = $teams->pluck('members_count')->all();
        $response->assertSessionHas('success', 'Pemain berhasil diacak ke 3 tim (' . implode('/', $sizes) . ' pemain).');

        $this->assertSame(['Tim A', 'Tim B', 'Tim C'], $teams->pluck('nama_tim')->all());
        sort($sizes);
        $this->assertSame([5, 6, 6], $sizes);
        $this->assertSame(0, $event->teamPlayers()->whereNull('event_team_id')->count());
        $this->assertSame(17, $event->teamPlayers()->distinct('player_id')->count('player_id'));
        // MySQL JSON tidak menjaga urutan key, jadi bandingkan setelah diurutkan.
        $config = $event->fresh()->team_draw_config;
        ksort($config);
        $this->assertSame(['hanya_hadir' => false, 'jumlah_tim' => 3], $config);
    }

    public function test_extra_pemain_per_tim_input_is_ignored(): void
    {
        $event = $this->makeEvent();
        $this->addPlayers($event, 20);

        $this->draw($event, ['jumlah_tim' => 2, 'pemain_per_tim' => 5])->assertSessionHas('success');

        $this->assertSame([10, 10], $event->teams()->withCount('members')->pluck('members_count')->all());
        $this->assertArrayNotHasKey('pemain_per_tim', $event->fresh()->team_draw_config);
    }

    public function test_draw_spreads_keepers(): void
    {
        $event = $this->makeEvent(['skema_iuran' => 'custom', 'roles' => [
            ['name' => 'Kiper', 'slots' => 3, 'price' => 0],
            ['name' => 'Pemain', 'slots' => 12, 'price' => 0],
        ]]);
        $this->addPlayers($event, 3, ['role_name' => 'Kiper']);
        $this->addPlayers($event, 12, ['role_name' => 'Pemain']);

        $this->draw($event)->assertSessionHas('success');

        foreach ($event->teams as $team) {
            $this->assertSame(1, $team->members()->where('role_name', 'Kiper')->count());
        }
    }

    public function test_draw_only_uses_joined_players(): void
    {
        $event = $this->makeEvent();
        $this->addPlayers($event, 6);
        $cancelled = $this->addPlayers($event, 4, ['status_join' => 'batal']);

        $this->draw($event, ['jumlah_tim' => 2])->assertSessionHas('success');

        $this->assertSame(6, $event->teamPlayers()->count());
        $this->assertSame(0, $event->teamPlayers()->whereIn('player_id', $cancelled)->count());
    }

    public function test_draw_can_be_limited_to_present_players(): void
    {
        $event = $this->makeEvent();
        $present = $this->addPlayers($event, 6, ['hadir' => true]);
        $this->addPlayers($event, 5, ['hadir' => false]);

        $this->draw($event, ['jumlah_tim' => 2, 'hanya_hadir' => 1])->assertSessionHas('success');

        $this->assertEqualsCanonicalizing($present, $event->teamPlayers()->pluck('player_id')->all());
        $this->assertTrue($event->fresh()->team_draw_config['hanya_hadir']);
    }

    public function test_redraw_replaces_previous_result_and_schedule(): void
    {
        $event = $this->makeEvent();
        $this->addPlayers($event, 12);
        $this->draw($event);
        $this->schedule($event);
        $oldTeamIds = $event->teams()->pluck('id')->all();

        $this->draw($event, ['jumlah_tim' => 4])->assertSessionHas('success');

        $this->assertSame(4, $event->teams()->count());
        $this->assertSame(0, EventTeam::whereIn('id', $oldTeamIds)->count());
        $this->assertSame(12, $event->teamPlayers()->count());
        $this->assertSame(0, $event->matches()->count());
    }

    public function test_draw_fails_when_players_are_fewer_than_teams(): void
    {
        $event = $this->makeEvent();
        $this->addPlayers($event, 2);

        $this->draw($event)->assertSessionHas('error', 'Jumlah pemain (2) kurang dari jumlah tim (3). Setiap tim minimal harus memiliki 1 pemain.');

        $this->assertSame(0, $event->teams()->count());
    }

    public function test_failed_redraw_keeps_previous_result(): void
    {
        $event = $this->makeEvent();
        $this->addPlayers($event, 6);
        $this->draw($event, ['jumlah_tim' => 2]);

        $this->draw($event, ['jumlah_tim' => 10])->assertSessionHas('error');

        $this->assertSame(2, $event->teams()->count());
        $this->assertSame(6, $event->teamPlayers()->count());
    }

    public function test_draw_fails_when_no_present_players(): void
    {
        $event = $this->makeEvent();
        $this->addPlayers($event, 8, ['hadir' => false]);

        $this->draw($event, ['hanya_hadir' => 1])->assertSessionHas('error');
    }

    public static function invalidDrawProvider(): array
    {
        return [
            'tim kosong' => [['jumlah_tim' => ''], 'jumlah_tim'],
            'satu tim' => [['jumlah_tim' => 1], 'jumlah_tim'],
            'tim terlalu banyak' => [['jumlah_tim' => 27], 'jumlah_tim'],
            'tim bukan angka' => [['jumlah_tim' => 'abc'], 'jumlah_tim'],
            'tim desimal' => [['jumlah_tim' => 2.5], 'jumlah_tim'],
            'tim nol' => [['jumlah_tim' => 0], 'jumlah_tim'],
            'tim negatif' => [['jumlah_tim' => -3], 'jumlah_tim'],
        ];
    }

    #[DataProvider('invalidDrawProvider')]
    public function test_draw_validation(array $payload, string $field): void
    {
        $event = $this->makeEvent();
        $this->addPlayers($event, 10);

        $this->draw($event, $payload)->assertSessionHasErrors($field);
        $this->assertSame(0, $event->teams()->count());
    }

    public function test_validation_messages_are_in_indonesian(): void
    {
        $event = $this->makeEvent();

        $this->draw($event, ['jumlah_tim' => 1])->assertSessionHasErrors([
            'jumlah_tim' => 'Kolom jumlah tim minimal 2.',
        ]);
        $this->draw($event, ['jumlah_tim' => ''])->assertSessionHasErrors([
            'jumlah_tim' => 'Kolom jumlah tim wajib diisi.',
        ]);
        $this->schedule($event, ['jumlah_pertandingan' => 'enam'])->assertSessionHasErrors([
            'jumlah_pertandingan' => 'Kolom jumlah pertandingan harus berupa bilangan bulat.',
        ]);
    }

    // ─── Jadwal ───────────────────────────────────────────────────

    public function test_schedule_three_teams_six_matches_with_times(): void
    {
        $event = $this->makeEvent(['waktu' => '19:00:00']);
        $this->addPlayers($event, 15);
        $this->draw($event);

        $this->schedule($event, ['durasi_laga' => 15])
            ->assertRedirect(route('admin.events.teamDraw.show', $event))
            ->assertSessionHas('success', '6 pertandingan berhasil dijadwalkan.');

        $matches = $event->matches()->get();
        $this->assertSame(range(1, 6), $matches->pluck('urutan')->all());
        $this->assertSame(['19:00:00', '19:15:00', '19:30:00', '19:45:00', '20:00:00', '20:15:00'], $matches->pluck('perkiraan_mulai')->all());

        $games = [];
        foreach ($matches as $match) {
            $this->assertNotSame($match->home_team_id, $match->away_team_id);
            $games[$match->home_team_id] = ($games[$match->home_team_id] ?? 0) + 1;
            $games[$match->away_team_id] = ($games[$match->away_team_id] ?? 0) + 1;
        }
        $this->assertSame([4, 4, 4], array_values($games));

        $config = $event->fresh()->team_draw_config;
        $this->assertSame(6, $config['jumlah_pertandingan']);
        $this->assertSame(15, $config['durasi_laga']);
        $this->assertSame(3, $config['jumlah_tim'], 'config acak tim tetap tersimpan');
    }

    public function test_schedule_without_duration_has_no_times(): void
    {
        $event = $this->makeEvent();
        $this->addPlayers($event, 10);
        $this->draw($event, ['jumlah_tim' => 2]);

        $this->schedule($event, ['jumlah_pertandingan' => 3])->assertSessionHas('success');

        $this->assertSame([null, null, null], $event->matches()->pluck('perkiraan_mulai')->all());
    }

    public function test_schedule_times_can_pass_midnight(): void
    {
        $event = $this->makeEvent(['waktu' => '23:30']);
        $this->addPlayers($event, 10);
        $this->draw($event, ['jumlah_tim' => 2]);

        $this->schedule($event, ['jumlah_pertandingan' => 3, 'durasi_laga' => 20])->assertSessionHas('success');

        $this->assertSame(['23:30:00', '23:50:00', '00:10:00'], $event->matches()->pluck('perkiraan_mulai')->all());
    }

    public function test_reschedule_replaces_matches(): void
    {
        $event = $this->makeEvent();
        $this->addPlayers($event, 12);
        $this->draw($event, ['jumlah_tim' => 4]);
        $this->schedule($event);

        $this->schedule($event, ['jumlah_pertandingan' => 12])->assertSessionHas('success');

        $this->assertSame(12, $event->matches()->count());
        $this->assertSame(12, EventMatch::where('event_id', $event->id)->count());
    }

    public function test_schedule_requires_teams(): void
    {
        $event = $this->makeEvent();

        $this->schedule($event)->assertSessionHas('error', 'Minimal harus ada 2 tim untuk membuat jadwal.');
        $this->assertSame(0, $event->matches()->count());
    }

    public static function invalidScheduleProvider(): array
    {
        return [
            'laga kosong' => [['jumlah_pertandingan' => ''], 'jumlah_pertandingan'],
            'laga nol' => [['jumlah_pertandingan' => 0], 'jumlah_pertandingan'],
            'laga terlalu banyak' => [['jumlah_pertandingan' => 201], 'jumlah_pertandingan'],
            'laga bukan angka' => [['jumlah_pertandingan' => 'enam'], 'jumlah_pertandingan'],
            'durasi nol' => [['durasi_laga' => 0], 'durasi_laga'],
            'durasi terlalu lama' => [['durasi_laga' => 601], 'durasi_laga'],
        ];
    }

    #[DataProvider('invalidScheduleProvider')]
    public function test_schedule_validation(array $payload, string $field): void
    {
        $event = $this->makeEvent();
        $this->addPlayers($event, 10);
        $this->draw($event, ['jumlah_tim' => 2]);

        $this->schedule($event, $payload)->assertSessionHasErrors($field);
        $this->assertSame(0, $event->matches()->count());
    }

    public function test_admin_page_shows_teams_schedule_and_fair_suggestions(): void
    {
        $event = $this->makeEvent();
        $this->addPlayers($event, 15);
        $this->draw($event);
        $this->schedule($event, ['durasi_laga' => 10]);

        $this->actingAs($this->admin)->get(route('admin.events.teamDraw.show', $event))
            ->assertOk()
            ->assertSee('Tim A')->assertSee('Tim C')
            ->assertSee('Jadwal Pertandingan (6 laga)')
            ->assertSee('4 laga')
            ->assertSee('semua saling bertemu')
            ->assertSee('19:50')
            ->assertSee('Acak Ulang Tim')
            ->assertSee('Belum dipublikasi')
            ->assertDontSee('Cadangan')
            ->assertDontSee('Pemain per Tim');
    }

    public function test_cancelled_player_after_draw_is_flagged(): void
    {
        $event = $this->makeEvent();
        [$first] = $this->addPlayers($event, 10);
        $this->draw($event, ['jumlah_tim' => 2]);

        $event->players()->updateExistingPivot($first, ['status_join' => 'batal']);

        $this->actingAs($this->admin)->get(route('admin.events.teamDraw.show', $event))
            ->assertOk()
            ->assertSee('Batal');
    }

    // ─── Publikasi & halaman publik ───────────────────────────────

    public function test_publish_requires_teams(): void
    {
        $event = $this->makeEvent();

        $this->publish($event)->assertSessionHas('error');
        $this->assertNull($event->fresh()->team_draw_published_at);
    }

    public function test_public_page_is_hidden_until_published(): void
    {
        $event = $this->makeEvent();
        $this->addPlayers($event, 10);
        $this->draw($event, ['jumlah_tim' => 2]);

        $this->get(route('player.teams.show', $event->slug))->assertNotFound();
        $this->get(route('player.join.show', $event->slug))->assertOk()->assertDontSee(route('player.teams.show', $event->slug));

        $this->publish($event)->assertSessionHas('success');

        $this->get(route('player.teams.show', $event->slug))->assertOk();
        $this->get(route('player.join.show', $event->slug))->assertOk()->assertSee(route('player.teams.show', $event->slug));

        $this->publish($event, false)->assertSessionHas('success');
        $this->get(route('player.teams.show', $event->slug))->assertNotFound();
    }

    public function test_public_page_shows_teams_and_schedule_without_contacts(): void
    {
        $event = $this->makeEvent();
        $this->addPlayers($event, 17);
        $this->draw($event);
        $this->schedule($event, ['durasi_laga' => 15]);
        $this->publish($event);

        $response = $this->get(route('player.teams.show', $event->slug))->assertOk();
        $response->assertSee('Mabar Jumat')
            ->assertSee('Tim A')->assertSee('Tim B')->assertSee('Tim C')
            ->assertSee('Jadwal Pertandingan')
            ->assertSee('Laga 6')
            ->assertSee('20:15')
            ->assertDontSee('Cadangan');

        // Semua pemain tampil, tanpa kontak.
        foreach (EventTeamPlayer::where('event_id', $event->id)->with('player')->get() as $member) {
            $response->assertSee($member->player->nama)->assertDontSee($member->player->kontak);
        }
    }

    public function test_public_page_without_schedule(): void
    {
        $event = $this->makeEvent();
        $this->addPlayers($event, 10);
        $this->draw($event, ['jumlah_tim' => 2]);
        $this->publish($event);

        $this->get(route('player.teams.show', $event->slug))
            ->assertOk()
            ->assertSee('Tim A')
            ->assertDontSee('Jadwal Pertandingan')
            ->assertDontSee('Cadangan');
    }

    public function test_public_page_escapes_player_names(): void
    {
        $event = $this->makeEvent();
        $this->addPlayers($event, 3);
        $this->addPlayers($event, 1, [], '<script>alert(1)</script>');
        $this->draw($event, ['jumlah_tim' => 2]);
        $this->publish($event);

        $this->get(route('player.teams.show', $event->slug))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_public_page_unknown_or_deleted_event_is_404(): void
    {
        $this->get(route('player.teams.show', 'tidak-ada'))->assertNotFound();

        $event = $this->makeEvent();
        $this->addPlayers($event, 4);
        $this->draw($event, ['jumlah_tim' => 2]);
        $this->publish($event);
        $event->delete();

        $this->get(route('player.teams.show', $event->slug))->assertNotFound();
    }

    // ─── Reset & integritas data ─────────────────────────────────

    public function test_reset_clears_everything(): void
    {
        $event = $this->makeEvent();
        $this->addPlayers($event, 10);
        $this->draw($event, ['jumlah_tim' => 2]);
        $this->schedule($event, ['jumlah_pertandingan' => 3]);
        $this->publish($event);

        $this->actingAs($this->admin)->delete(route('admin.events.teamDraw.reset', $event))
            ->assertRedirect(route('admin.events.teamDraw.show', $event))
            ->assertSessionHas('success');

        $event->refresh();
        $this->assertSame(0, $event->teams()->count());
        $this->assertSame(0, $event->teamPlayers()->count());
        $this->assertSame(0, $event->matches()->count());
        $this->assertNull($event->team_draw_config);
        $this->assertNull($event->team_draw_published_at);
        $this->get(route('player.teams.show', $event->slug))->assertNotFound();
    }

    public function test_draws_of_different_events_are_isolated(): void
    {
        $first = $this->makeEvent();
        $second = $this->makeEvent(['nama_event' => 'Mabar Sabtu']);
        $this->addPlayers($first, 10);
        $this->addPlayers($second, 8);
        $this->draw($first, ['jumlah_tim' => 2]);
        $this->draw($second, ['jumlah_tim' => 4]);
        $this->schedule($second, ['jumlah_pertandingan' => 6]);

        $this->draw($first, ['jumlah_tim' => 2]);
        $this->actingAs($this->admin)->delete(route('admin.events.teamDraw.reset', $first));

        $this->assertSame(4, $second->teams()->count());
        $this->assertSame(8, $second->teamPlayers()->count());
        $this->assertSame(6, $second->matches()->count());
    }

    public function test_same_player_in_two_events(): void
    {
        $first = $this->makeEvent();
        $second = $this->makeEvent(['nama_event' => 'Mabar Sabtu']);
        $ids = $this->addPlayers($first, 4);
        foreach ($ids as $id) {
            $second->players()->attach($id, ['status_join' => 'joined']);
        }

        $this->draw($first, ['jumlah_tim' => 2])->assertSessionHas('success');
        $this->draw($second, ['jumlah_tim' => 2])->assertSessionHas('success');

        $this->assertSame(4, $first->teamPlayers()->count());
        $this->assertSame(4, $second->teamPlayers()->count());
    }
}
