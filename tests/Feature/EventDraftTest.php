<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventDraft;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Hanya berjalan di database khusus test agar RefreshDatabase tidak menghapus database pengembangan:
 *   $env:DB_CONNECTION='sqlite'; $env:DB_DATABASE=':memory:'; php artisan test --filter=EventDraftTest
 */
class EventDraftTest extends TestCase
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

        Storage::fake('public');
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_autosave_creates_and_updates_partial_draft(): void
    {
        $res = $this->actingAs($this->admin)->postJson(route('admin.events.drafts.store'), [
            'nama_event' => 'Mabar Jumat',
            'roles' => [['name' => 'Kiper', 'slots' => 2, 'price' => 25000]],
            'step' => 2,
        ])->assertOk()->assertJsonStructure(['id', 'saved_at']);

        $draft = EventDraft::findOrFail($res->json('id'));
        $this->assertSame('Mabar Jumat', $draft->nama_event);
        $this->assertSame(2, $draft->payload['step']);

        $this->actingAs($this->admin)->postJson(route('admin.events.drafts.update', $draft), [
            'nama_event' => 'Mabar Sabtu',
            'tempat' => 'GOR Sukamaju',
            'banner_image' => UploadedFile::fake()->image('b.jpg'),
        ])->assertOk();

        $draft->refresh();
        $this->assertSame('Mabar Sabtu', $draft->nama_event);
        $this->assertSame('GOR Sukamaju', $draft->payload['tempat']);
        Storage::disk('public')->assertExists($draft->banner_image);

        // Draf tidak membuat event (tidak ada slug/join link)
        $this->assertSame(0, Event::count());
    }

    public function test_other_admin_cannot_touch_draft(): void
    {
        $draft = EventDraft::create(['admin_id' => $this->admin->id, 'nama_event' => 'Rahasia']);
        $other = User::factory()->create(['role' => 'admin']);

        $this->actingAs($other)->postJson(route('admin.events.drafts.update', $draft), ['nama_event' => 'x'])->assertNotFound();
        $this->actingAs($other)->deleteJson(route('admin.events.drafts.destroy', $draft))->assertNotFound();
        $this->actingAs($other)->get(route('admin.events.create', ['draft' => $draft->id]))->assertRedirect(route('admin.events.create'));

        $this->assertDatabaseHas('event_drafts', ['id' => $draft->id, 'nama_event' => 'Rahasia']);
    }

    public function test_create_page_prefills_from_draft_and_index_lists_it(): void
    {
        $draft = EventDraft::create([
            'admin_id' => $this->admin->id,
            'nama_event' => 'Draf Futsal Malam',
            'payload' => ['tempat' => 'Arena X', 'roles' => [['name' => 'Pemain', 'slots' => 10, 'price' => 20000]]],
        ]);

        $this->actingAs($this->admin)->get(route('admin.events.create', ['draft' => $draft->id]))
            ->assertOk()->assertSee('Draf Futsal Malam')->assertSee('Arena X');

        $this->actingAs($this->admin)->get(route('admin.events.index'))
            ->assertOk()->assertSee('Draf Futsal Malam')->assertSee('Lanjutkan');
    }

    public function test_publishing_from_draft_creates_event_reuses_banner_and_deletes_draft(): void
    {
        Storage::disk('public')->put('banners/drafts/abc.jpg', 'img');
        $draft = EventDraft::create([
            'admin_id' => $this->admin->id,
            'nama_event' => 'Mabar',
            'banner_image' => 'banners/drafts/abc.jpg',
        ]);

        $this->actingAs($this->admin)->post(route('admin.events.store'), [
            'draft_id' => $draft->id,
            'nama_event' => 'Mabar',
            'tanggal' => now()->addDay()->toDateString(),
            'waktu' => '19:00',
            'tempat' => 'GOR',
            'metode_pembayaran' => 'online_banking',
            'roles' => [['name' => 'Pemain', 'slots' => 10, 'price' => 20000]],
            'show_joined_players_public' => '1',
        ])->assertRedirect(route('admin.events.index'));

        $event = Event::firstOrFail();
        $this->assertSame('banners/abc.jpg', $event->banner_image);
        Storage::disk('public')->assertExists('banners/abc.jpg');
        $this->assertDatabaseMissing('event_drafts', ['id' => $draft->id]);
    }

    public function test_deleting_draft_removes_banner_file(): void
    {
        Storage::disk('public')->put('banners/drafts/x.jpg', 'img');
        $draft = EventDraft::create(['admin_id' => $this->admin->id, 'banner_image' => 'banners/drafts/x.jpg']);

        $this->actingAs($this->admin)->delete(route('admin.events.drafts.destroy', $draft))
            ->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseMissing('event_drafts', ['id' => $draft->id]);
        Storage::disk('public')->assertMissing('banners/drafts/x.jpg');
    }
}
