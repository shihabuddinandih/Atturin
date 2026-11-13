<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Hanya berjalan di database khusus test agar RefreshDatabase tidak menghapus database pengembangan:
 *   $env:DB_CONNECTION='sqlite'; $env:DB_DATABASE=':memory:'; php artisan test --filter=EventPaymentModeTest
 */
class EventPaymentModeTest extends TestCase
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

    private function makeEvent(string $mode, int $slots = 1, bool $waitlist = false): Event
    {
        return Event::create([
            'admin_id' => $this->admin->id,
            'nama_event' => 'Mabar Jumat',
            'tanggal' => now()->addDays(3)->toDateString(),
            'waktu' => '19:00',
            'tempat' => 'GOR Sukamaju',
            'slot_max' => $slots,
            'metode_pembayaran' => 'online_banking',
            'payment_mode' => $mode,
            'enable_waiting_list' => $waitlist,
            'iuran_per_pemain' => 20000,
            'biaya_total_event' => 20000 * $slots,
            'skema_iuran' => 'custom',
            'roles' => [['name' => 'Pemain', 'slots' => $slots, 'price' => 20000]],
        ]);
    }

    private function register(Event $event, string $kontak, string $nama = 'Budi')
    {
        return $this->post(route('player.join.store', $event->slug), [
            'role_name' => 'Pemain',
            'nama' => $nama,
            'kontak' => $kontak,
        ]);
    }

    private function pivotFor(Event $event, string $kontak)
    {
        return $event->players()->where('kontak', $kontak)->first()?->pivot;
    }

    /** Lewati masa tahan + jeda verifikasi sehingga slot benar-benar dilepas. */
    private function travelPastHold(): void
    {
        $this->travel(Event::HOLD_MINUTES * 60 + Event::HOLD_GRACE_SECONDS + 1)->seconds();
    }

    public function test_slot_stays_reserved_during_grace_period_after_countdown(): void
    {
        $event = $this->makeEvent(Event::PAYMENT_MODE_PAY_FIRST, slots: 1);
        $this->register($event, '081111');

        // Hitung mundur 3 menit sudah habis, tapi masih dalam jeda verifikasi pembayaran
        $this->travel(Event::HOLD_MINUTES * 60 + 30)->seconds();
        $this->assertSame(1, $event->occupiedSlotCount());
        $this->register($event, '082222', 'Andi')->assertSessionHas('error');

        // Pembayaran detik terakhir masih diterima
        $token = $this->pivotFor($event, '081111')->registration_token;
        $this->post(route('registration.simulatePayment', $token));
        $this->assertSame('joined', $this->pivotFor($event, '081111')->status_join);
    }

    public function test_finish_callback_cannot_mark_paid_from_browser_data(): void
    {
        $event = $this->makeEvent(Event::PAYMENT_MODE_PAY_LATER);
        $this->register($event, '081111');
        $pivot = $this->pivotFor($event, '081111');

        // Peserta memalsukan callback Snap: status tetap harus pending
        $this->postJson(route('registration.midtrans.finish', $pivot->registration_token), [
            'transaction_status' => 'settlement',
            'order_id' => 'FAKE-ORDER',
        ]);

        $pivot = $this->pivotFor($event, '081111');
        $this->assertSame('pending', $pivot->payment_status);
        $this->assertNull($pivot->payment_reference);
    }

    public function test_simulate_payment_is_disabled_in_production(): void
    {
        $event = $this->makeEvent(Event::PAYMENT_MODE_PAY_LATER);
        $this->register($event, '081111');
        $token = $this->pivotFor($event, '081111')->registration_token;

        $this->app['env'] = 'production';
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->post(route('registration.simulatePayment', $token))->assertNotFound();
        $this->post(route('player.join.simulatePayment', $event->slug))->assertNotFound();

        $this->assertSame('pending', $this->pivotFor($event, '081111')->payment_status);
    }

    public function test_free_position_is_registered_as_paid_without_admin_fee(): void
    {
        $event = $this->makeEvent(Event::PAYMENT_MODE_PAY_FIRST, slots: 2);
        $event->update(['roles' => [['name' => 'Pemain', 'slots' => 2, 'price' => 0]]]);

        $this->register($event, '081111')->assertRedirect();

        $pivot = $this->pivotFor($event, '081111');
        $this->assertSame('joined', $pivot->status_join);
        $this->assertSame('paid', $pivot->payment_status);
        $this->assertEquals(0, (float) $pivot->payment_amount);
    }

    /** Simulasikan HTTP Notification Midtrans dengan signature yang valid. */
    private function notify(string $orderId, string $status, string $gross, ?string $signature = null)
    {
        config(['services.midtrans.server_key' => 'SB-TEST-KEY']);
        $statusCode = $status === 'settlement' ? '200' : '407';

        return $this->postJson(route('midtrans.notification'), [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $gross,
            'transaction_status' => $status,
            'signature_key' => $signature ?? hash('sha512', $orderId . $statusCode . $gross . 'SB-TEST-KEY'),
        ]);
    }

    private function giveOrder(Event $event, string $kontak, string $orderId): void
    {
        $player = Player::where('kontak', $kontak)->firstOrFail();
        $event->players()->updateExistingPivot($player->id, ['payment_reference' => $orderId]);
    }

    public function test_webhook_marks_pay_first_participant_joined_even_if_page_closed(): void
    {
        $event = $this->makeEvent(Event::PAYMENT_MODE_PAY_FIRST);
        $this->register($event, '081111');
        $this->giveOrder($event, '081111', 'PSH-1');

        // Rp 20.000 + biaya admin Rp 1.500
        $this->notify('PSH-1', 'settlement', '21500.00')->assertOk()->assertJson(['status' => 'paid']);

        $pivot = $this->pivotFor($event, '081111');
        $this->assertSame('joined', $pivot->status_join);
        $this->assertSame('paid', $pivot->payment_status);
    }

    public function test_webhook_rejects_forged_signature_and_wrong_amount(): void
    {
        $event = $this->makeEvent(Event::PAYMENT_MODE_PAY_LATER);
        $this->register($event, '081111');
        $this->giveOrder($event, '081111', 'PSH-2');

        $this->notify('PSH-2', 'settlement', '21500.00', signature: 'palsu')->assertForbidden();
        $this->notify('PSH-2', 'settlement', '1000.00')->assertStatus(422);

        $this->assertSame('pending', $this->pivotFor($event, '081111')->payment_status);
    }

    public function test_webhook_expire_releases_hold_but_never_downgrades_paid(): void
    {
        $event = $this->makeEvent(Event::PAYMENT_MODE_PAY_FIRST, slots: 2);
        $this->register($event, '081111');
        $this->giveOrder($event, '081111', 'PSH-3');
        $this->notify('PSH-3', 'expire', '21500.00')->assertOk();
        $this->assertSame('batal', $this->pivotFor($event, '081111')->status_join);

        $this->register($event, '082222', 'Andi');
        $this->giveOrder($event, '082222', 'PSH-4');
        $this->notify('PSH-4', 'settlement', '21500.00');
        $this->notify('PSH-4', 'expire', '21500.00'); // notifikasi terlambat
        $this->assertSame('paid', $this->pivotFor($event, '082222')->payment_status);
        $this->assertSame('joined', $this->pivotFor($event, '082222')->status_join);
    }

    public function test_public_event_page_renders_positions_roster_and_price_breakdown(): void
    {
        $event = $this->makeEvent(Event::PAYMENT_MODE_PAY_LATER, slots: 3);
        $event->update(['show_joined_players_public' => true, 'show_joined_player_contacts_public' => false]);
        $this->register($event, '081111', 'Budi <script>');

        $this->get(route('player.join.show', $event->slug))
            ->assertOk()
            ->assertSee('Pilih Posisi')
            ->assertSee('Siapa yang Ikut?')
            ->assertSee('Budi &lt;script&gt;', false)   // nama di-escape
            ->assertDontSee('081111')                    // kontak disembunyikan
            ->assertSee('Klaim')
            ->assertSee('Rp 21.500');                    // 20.000 + biaya layanan 1.500
    }

    public function test_full_event_without_waitlist_hides_form(): void
    {
        $event = $this->makeEvent(Event::PAYMENT_MODE_PAY_LATER, slots: 1);
        $this->register($event, '081111');

        $this->get(route('player.join.show', $event->slug))
            ->assertOk()
            ->assertSee('Slot sudah penuh')
            ->assertDontSee('id="join-form"', false);
    }

    public function test_paid_registration_shows_ticket(): void
    {
        $event = $this->makeEvent(Event::PAYMENT_MODE_PAY_FIRST);
        $this->register($event, '081111');
        $token = $this->pivotFor($event, '081111')->registration_token;
        $this->post(route('registration.simulatePayment', $token));

        $this->get(route('registration.show', $token))
            ->assertOk()
            ->assertSee('Anda Terdaftar!')
            ->assertSee('calendar.google.com', false)
            ->assertDontSee('id="midtrans-pay-button"', false);
    }

    public function test_admin_fee_rules(): void
    {
        $this->assertSame(0.0, \App\Support\PaymentFee::adminFee(0));
        $this->assertSame(1500.0, \App\Support\PaymentFee::adminFee(20000));
        $this->assertSame(3000.0, \App\Support\PaymentFee::adminFee(50000));
        $this->assertSame(3000.0, \App\Support\PaymentFee::adminFee(100000));
        $this->assertSame(0.0, \App\Support\PaymentFee::adminFee(20000, 'tunai'));
    }

    public function test_pay_later_registers_immediately_as_joined(): void
    {
        $event = $this->makeEvent(Event::PAYMENT_MODE_PAY_LATER);

        $this->register($event, '081111')->assertRedirect();

        $pivot = $this->pivotFor($event, '081111');
        $this->assertSame('joined', $pivot->status_join);
        $this->assertSame('pending', $pivot->payment_status);
        $this->assertNull($pivot->payment_expires_at);
    }

    public function test_pay_first_holds_slot_and_blocks_others_until_expired(): void
    {
        $event = $this->makeEvent(Event::PAYMENT_MODE_PAY_FIRST, slots: 1);

        $this->register($event, '081111')->assertRedirect();
        $pivot = $this->pivotFor($event, '081111');
        $this->assertSame('hold', $pivot->status_join);
        $this->assertNotNull($pivot->payment_expires_at);

        // Belum dianggap terdaftar (roster/statistik), tapi slot terpakai
        $this->assertSame(0, $event->players()->wherePivot('status_join', 'joined')->count());
        $this->assertSame(1, $event->occupiedSlotCount());

        // Orang lain tidak bisa mengambil slot yang sedang ditahan
        $this->register($event, '082222', 'Andi')->assertSessionHas('error');
        $this->assertNull($this->pivotFor($event, '082222'));

        // Setelah 3 menit lewat, slot kembali tersedia
        $this->travelPastHold();
        $this->assertSame(0, $event->occupiedSlotCount());
        $this->register($event, '082222', 'Andi')->assertRedirect();
        $this->assertSame('hold', $this->pivotFor($event, '082222')->status_join);
    }

    public function test_expired_hold_is_cancelled_when_registration_page_is_opened(): void
    {
        $event = $this->makeEvent(Event::PAYMENT_MODE_PAY_FIRST);
        $this->register($event, '081111');
        $token = $this->pivotFor($event, '081111')->registration_token;

        $this->get(route('registration.show', $token))->assertOk()->assertSee('Selesaikan Pembayaran');

        $this->travelPastHold();
        $this->get(route('registration.show', $token))->assertOk()->assertSee('Pendaftaran Batal');

        $pivot = $this->pivotFor($event, '081111');
        $this->assertSame('batal', $pivot->status_join);
        $this->assertSame('failed', $pivot->payment_status);
    }

    public function test_paying_within_hold_makes_participant_joined(): void
    {
        $event = $this->makeEvent(Event::PAYMENT_MODE_PAY_FIRST);
        $this->register($event, '081111');
        $token = $this->pivotFor($event, '081111')->registration_token;

        $this->post(route('registration.simulatePayment', $token))->assertRedirect();

        $pivot = $this->pivotFor($event, '081111');
        $this->assertSame('joined', $pivot->status_join);
        $this->assertSame('paid', $pivot->payment_status);
    }

    public function test_paying_after_hold_expired_is_rejected(): void
    {
        $event = $this->makeEvent(Event::PAYMENT_MODE_PAY_FIRST);
        $this->register($event, '081111');
        $token = $this->pivotFor($event, '081111')->registration_token;

        $this->travelPastHold();
        $this->post(route('registration.simulatePayment', $token))->assertSessionHas('error');

        $this->assertSame('batal', $this->pivotFor($event, '081111')->status_join);
    }

    public function test_superadmin_can_drop_registration_with_invalid_whatsapp(): void
    {
        $event = $this->makeEvent(Event::PAYMENT_MODE_PAY_LATER, slots: 1);
        $this->register($event, '0800');
        $player = Player::where('kontak', '0800')->firstOrFail();
        $this->assertTrue($event->fresh()->isFull());

        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($superadmin)
            ->post(route('superadmin.registration-confirmations.invalidate', ['event' => $event, 'player' => $player]))
            ->assertSessionHas('success');

        $pivot = $this->pivotFor($event, '0800');
        $this->assertSame('batal', $pivot->status_join);
        $this->assertSame('failed', $pivot->payment_status);
        $this->assertSame(0, $event->occupiedSlotCount());
    }

    public function test_paid_registration_cannot_be_dropped(): void
    {
        $event = $this->makeEvent(Event::PAYMENT_MODE_PAY_LATER);
        $this->register($event, '0800');
        $player = Player::where('kontak', '0800')->firstOrFail();
        $event->players()->updateExistingPivot($player->id, ['payment_status' => 'paid']);

        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($superadmin)
            ->post(route('superadmin.registration-confirmations.invalidate', ['event' => $event, 'player' => $player]))
            ->assertSessionHas('error');

        $this->assertSame('joined', $this->pivotFor($event, '0800')->status_join);
    }

    public function test_admin_sets_payment_mode_and_waiting_list_when_creating_event(): void
    {
        $this->actingAs($this->admin)->post(route('admin.events.store'), [
            'nama_event' => 'Futsal Malam',
            'tanggal' => now()->addDay()->toDateString(),
            'waktu' => '19:00',
            'tempat' => 'GOR',
            'metode_pembayaran' => 'online_banking',
            'payment_mode' => 'pay_first',
            'enable_waiting_list' => '1',
            'roles' => [['name' => 'Pemain', 'slots' => 10, 'price' => 20000]],
        ])->assertRedirect(route('admin.events.index'));

        $event = Event::where('nama_event', 'Futsal Malam')->firstOrFail();
        $this->assertSame('pay_first', $event->payment_mode);
        $this->assertTrue($event->enable_waiting_list);
    }
}
