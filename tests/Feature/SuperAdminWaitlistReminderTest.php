<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventWaitlist;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SuperAdminWaitlistReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_waitlist_reminder_in_whatsapp(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $event = Event::create([
            'admin_id' => $superAdmin->id,
            'nama_event' => 'Liga Futsal Mingguan',
            'tanggal' => now()->addDay()->toDateString(),
            'waktu' => '20:00:00',
            'tempat' => 'Lapangan Utama',
            'slot_max' => 10,
            'metode_pembayaran' => 'transfer',
            'iuran_per_pemain' => 100000,
            'biaya_total_event' => 1000000,
            'enable_waiting_list' => true,
        ]);

        $player = Player::create([
            'nama' => 'Budi',
            'kontak' => '081234567890',
        ]);

        $waitlist = EventWaitlist::create([
            'event_id' => $event->id,
            'player_id' => $player->id,
            'phone' => '081234567890',
            'status' => 'waiting',
            'payment_amount' => 100000,
        ]);

        $response = $this->actingAs($superAdmin)
            ->get(route('superadmin.waitlist-reminders.remind', $waitlist));

        $response->assertRedirectContains('https://wa.me/6281234567890');
        $response->assertSessionHas('success');

        $waitlist->refresh();
        $this->assertSame('contacted', $waitlist->status);
        $this->assertNotNull($waitlist->token);
        $this->assertNotNull($waitlist->expires_at);
    }

    public function test_reminder_page_shows_all_waitlist_entries_for_super_admin(): void
    {
        $superAdmin = User::factory()->create(['role' => 'superadmin']);

        $event = Event::create([
            'admin_id' => $superAdmin->id,
            'nama_event' => 'Turnamen Mingguan',
            'tanggal' => now()->addDay()->toDateString(),
            'waktu' => '21:00:00',
            'tempat' => 'Lapangan B',
            'slot_max' => 8,
            'metode_pembayaran' => 'transfer',
            'iuran_per_pemain' => 120000,
            'biaya_total_event' => 960000,
            'enable_waiting_list' => true,
        ]);

        $waitingPlayer = Player::create(['nama' => 'Citra', 'kontak' => '081111111111']);
        $contactedPlayer = Player::create(['nama' => 'Dina', 'kontak' => '081222222222']);

        EventWaitlist::create([
            'event_id' => $event->id,
            'player_id' => $waitingPlayer->id,
            'phone' => '081111111111',
            'status' => 'waiting',
            'payment_amount' => 120000,
        ]);

        EventWaitlist::create([
            'event_id' => $event->id,
            'player_id' => $contactedPlayer->id,
            'phone' => '081222222222',
            'status' => 'contacted',
            'token' => 'token-ready',
            'payment_amount' => 120000,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('superadmin.waitlist-reminders.index'));

        $response->assertOk();
        $response->assertSee('Dina');
        $response->assertSee('Citra');
        $response->assertSee('Belum di-Reminder');
        $response->assertSee('Sudah di-Reminder');
    }

    public function test_reminder_sends_email_notification_to_super_admin(): void
    {
        Mail::fake();

        $superAdmin = User::factory()->create([
            'role' => 'superadmin',
            'email' => 'superadmin@example.com',
        ]);

        $event = Event::create([
            'admin_id' => $superAdmin->id,
            'nama_event' => 'Email Reminder Test',
            'tanggal' => now()->addDay()->toDateString(),
            'waktu' => '22:00:00',
            'tempat' => 'Lapangan Email',
            'slot_max' => 4,
            'metode_pembayaran' => 'transfer',
            'iuran_per_pemain' => 75000,
            'biaya_total_event' => 300000,
            'enable_waiting_list' => true,
        ]);

        $player = Player::create([
            'nama' => 'Rina',
            'kontak' => '081987654321',
        ]);

        $waitlist = EventWaitlist::create([
            'event_id' => $event->id,
            'player_id' => $player->id,
            'phone' => '081987654321',
            'status' => 'waiting',
            'payment_amount' => 75000,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('superadmin.waitlist-reminders.remind', $waitlist));

        Mail::assertSent(\App\Mail\WaitlistReminderAlertMail::class, function ($mail) use ($superAdmin, $waitlist, $event) {
            return $mail->hasTo($superAdmin->email) &&
                $mail->waitlist->id === $waitlist->id &&
                $mail->event->id === $event->id;
        });
    }

    public function test_super_admin_can_view_payment_reminder_page(): void
    {
        $superAdmin = User::factory()->create(['role' => 'superadmin']);

        $event = Event::create([
            'admin_id' => $superAdmin->id,
            'nama_event' => 'Event Pembayaran Tertunda',
            'tanggal' => now()->addDay()->toDateString(),
            'waktu' => '20:00:00',
            'tempat' => 'Lapangan Beta',
            'slot_max' => 5,
            'metode_pembayaran' => 'transfer',
            'iuran_per_pemain' => 100000,
            'biaya_total_event' => 500000,
            'enable_waiting_list' => false,
        ]);

        $player = Player::create([
            'nama' => 'Fajar',
            'kontak' => '081222333444',
        ]);

        $event->players()->attach($player->id, [
            'status_join' => 'joined',
            'payment_method' => 'transfer',
            'payment_amount' => 100000,
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($superAdmin)->get(route('superadmin.payment-reminders.index'));

        $response->assertOk();
        $response->assertSee('Reminder Pembayaran');
        $response->assertSee('Fajar');
        $response->assertSee('Rp 100.000');
    }

    public function test_super_admin_can_open_payment_reminder_in_whatsapp(): void
    {
        Mail::fake();

        $superAdmin = User::factory()->create([
            'role' => 'superadmin',
            'email' => 'superadmin@example.com',
        ]);

        $event = Event::create([
            'admin_id' => $superAdmin->id,
            'nama_event' => 'Event Pembayaran Tertunda',
            'tanggal' => now()->addDay()->toDateString(),
            'waktu' => '20:00:00',
            'tempat' => 'Lapangan Beta',
            'slot_max' => 5,
            'metode_pembayaran' => 'transfer',
            'iuran_per_pemain' => 100000,
            'biaya_total_event' => 500000,
            'enable_waiting_list' => false,
        ]);

        $player = Player::create([
            'nama' => 'Fajar',
            'kontak' => '081222333444',
        ]);

        $event->players()->attach($player->id, [
            'status_join' => 'joined',
            'payment_method' => 'transfer',
            'payment_amount' => 100000,
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($superAdmin)
            ->get(route('superadmin.payment-reminders.remind', ['event' => $event, 'player' => $player]));

        $response->assertRedirectContains('https://wa.me/6281222333444');
        $response->assertSessionHas('success');

        Mail::assertSent(\App\Mail\PaymentReminderAlertMail::class, function ($mail) use ($superAdmin, $event, $player) {
            return $mail->hasTo($superAdmin->email) &&
                $mail->event->id === $event->id &&
                $mail->player->id === $player->id;
        });
    }

    public function test_new_registration_is_forced_into_waitlist_when_only_one_slot_remains(): void
    {
        $event = Event::create([
            'admin_id' => User::factory()->create(['role' => 'admin'])->id,
            'nama_event' => 'Event Dengan Satu Slot Tersisa',
            'tanggal' => now()->addDay()->toDateString(),
            'waktu' => '19:00:00',
            'tempat' => 'Lapangan D',
            'slot_max' => 2,
            'metode_pembayaran' => 'transfer',
            'iuran_per_pemain' => 100000,
            'biaya_total_event' => 500000,
            'enable_waiting_list' => true,
        ]);

        $joinedPlayer = Player::create(['nama' => 'Adit', 'kontak' => '081000000000']);
        $event->players()->attach($joinedPlayer->id, ['status_join' => 'joined']);

        $response = $this->post(route('player.join.store', $event->slug), [
            'nama' => 'Yeni',
            'kontak' => '081234567890',
        ]);

        $response->assertSessionHas('info');
        $this->assertStringContainsString('waiting list', strtolower((string) $response->getSession()->get('info')));
        $this->assertSame(1, EventWaitlist::where('event_id', $event->id)->count());
    }

    public function test_new_registration_is_forced_into_waitlist_when_event_already_has_waitlist_entries(): void
    {
        $event = Event::create([
            'admin_id' => User::factory()->create(['role' => 'admin'])->id,
            'nama_event' => 'Event Dengan Antrean',
            'tanggal' => now()->addDay()->toDateString(),
            'waktu' => '19:00:00',
            'tempat' => 'Lapangan C',
            'slot_max' => 5,
            'metode_pembayaran' => 'transfer',
            'iuran_per_pemain' => 100000,
            'biaya_total_event' => 500000,
            'enable_waiting_list' => true,
        ]);

        EventWaitlist::create([
            'event_id' => $event->id,
            'phone' => '081111111111',
            'status' => 'waiting',
            'payment_amount' => 100000,
        ]);

        $response = $this->post(route('player.join.store', $event->slug), [
            'nama' => 'Yeni',
            'kontak' => '081234567890',
        ]);

        $response->assertSessionHas('info');
        $this->assertSame(2, EventWaitlist::where('event_id', $event->id)->count());
        $this->assertCount(0, $event->players()->get());
    }
}
