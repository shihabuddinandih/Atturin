<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Hanya berjalan di database khusus test agar RefreshDatabase tidak menghapus database pengembangan:
 *   $env:DB_CONNECTION='sqlite'; $env:DB_DATABASE=':memory:'; php artisan test --filter=EventEditTest
 */
class EventEditTest extends TestCase
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

    private function makeEvent(): Event
    {
        return Event::create([
            'admin_id' => $this->admin->id,
            'nama_event' => 'Mabar Jumat',
            'tanggal' => now()->addDays(3)->toDateString(),
            'waktu' => '19:00',
            'tempat' => 'GOR Sukamaju',
            'slot_max' => 10,
            'metode_pembayaran' => 'online_banking',
            'payment_mode' => Event::PAYMENT_MODE_PAY_LATER,
            'iuran_per_pemain' => 20000,
            'biaya_total_event' => 200000,
            'skema_iuran' => 'custom',
            'roles' => [['name' => 'Pemain', 'slots' => 10, 'price' => 20000]],
        ]);
    }

    public function test_owner_can_open_edit_page(): void
    {
        $event = $this->makeEvent();

        $this->actingAs($this->admin)
            ->get(route('admin.events.edit', $event))
            ->assertOk()
            ->assertSee('Mabar Jumat')
            ->assertSee('Terkunci');
    }

    public function test_owner_can_update_details_but_not_payment_fields(): void
    {
        $event = $this->makeEvent();
        $slug = $event->slug;
        $joinCode = $event->join_code;

        $this->actingAs($this->admin)
            ->put(route('admin.events.update', $event), [
                'nama_event' => 'Mabar Sabtu',
                'tanggal' => now()->addDays(5)->toDateString(),
                'waktu' => '20:30',
                'tempat' => 'GOR Baru',
                'facilities' => [['key' => array_key_first(\App\Support\FacilityCatalog::options()), 'note' => '  dua  ']],
                'enable_waiting_list' => '1',
                'show_joined_players_public' => '0',
                'show_joined_player_contacts_public' => '1',
                // Field terkunci: harus diabaikan
                'roles' => [['name' => 'Kiper', 'slots' => 2, 'price' => 999]],
                'slot_max' => 2,
                'biaya_total_event' => 1,
                'iuran_per_pemain' => 1,
                'payment_mode' => Event::PAYMENT_MODE_PAY_FIRST,
                'slug' => 'diubah',
            ])
            ->assertRedirect(route('admin.events.show', $event));

        $event->refresh();

        $this->assertSame('Mabar Sabtu', $event->nama_event);
        $this->assertSame('GOR Baru', $event->tempat);
        $this->assertSame('dua', $event->facilities[0]['note']);
        $this->assertTrue($event->enable_waiting_list);
        $this->assertFalse($event->show_joined_players_public);
        $this->assertFalse($event->show_joined_player_contacts_public);

        $this->assertSame([['name' => 'Pemain', 'slots' => 10, 'price' => 20000]], $event->roles);
        $this->assertSame(10, (int) $event->slot_max);
        $this->assertEquals(200000, (float) $event->biaya_total_event);
        $this->assertEquals(20000, (float) $event->iuran_per_pemain);
        $this->assertSame(Event::PAYMENT_MODE_PAY_LATER, $event->payment_mode);
        $this->assertSame($slug, $event->slug);
        $this->assertSame($joinCode, $event->join_code);
    }

    public function test_other_admin_cannot_edit(): void
    {
        $event = $this->makeEvent();
        $other = User::factory()->create(['role' => 'admin']);

        $this->actingAs($other)->get(route('admin.events.edit', $event))->assertForbidden();
        $this->actingAs($other)->put(route('admin.events.update', $event), [
            'nama_event' => 'Dibajak',
            'tanggal' => now()->toDateString(),
            'waktu' => '10:00',
            'tempat' => 'X',
        ])->assertForbidden();

        $this->assertSame('Mabar Jumat', $event->fresh()->nama_event);
    }
}
