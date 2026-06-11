<?php

namespace App\Http\Controllers;

use App\Enums\JoinStatus;
use App\Enums\PaymentStatus;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminEventController extends Controller
{
    public function __construct(
        private EventService $eventService
    ) {}

    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $adminId = auth()->id();
        
        $events = $this->eventService->getAdminEvents($adminId, $status);
        $summary = $this->eventService->getEventsSummary($adminId);

        return view('admin.events.index', compact('events', 'summary', 'status'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_event' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'tempat' => 'required|string|max:255',
            'slot_max' => 'required_if:skema_iuran,flat|integer|min:1',
            'metode_pembayaran' => 'required|in:online_banking',
            'biaya_total_event' => 'required_if:skema_iuran,flat|numeric|min:0',
            'skema_iuran' => 'required|in:flat,custom',
            'roles' => 'required_if:skema_iuran,custom|array|min:1',
            'roles.*.name' => 'required_if:skema_iuran,custom|string|max:255',
            'roles.*.slots' => 'required_if:skema_iuran,custom|integer|min:1',
            'roles.*.price' => 'required_if:skema_iuran,custom|numeric|min:0',
            'show_joined_players_public' => 'nullable|boolean',
            'show_joined_player_contacts_public' => 'nullable|boolean',
        ]);

        $validated['show_joined_players_public'] = $request->boolean('show_joined_players_public', true);
        $validated['show_joined_player_contacts_public'] = $request->boolean('show_joined_player_contacts_public');

        if (!$validated['show_joined_players_public']) {
            $validated['show_joined_player_contacts_public'] = false;
        }

        $validated['admin_id'] = auth()->id();

        if ($validated['skema_iuran'] === 'custom') {
            $roles = array_values(array_filter($validated['roles'], function ($role) {
                return isset($role['name']) && trim($role['name']) !== '';
            }));

            $totalSlots = array_sum(array_column($roles, 'slots'));
            $validated['slot_max'] = max(1, $totalSlots);
            $validated['roles'] = $roles;
            $validated['biaya_total_event'] = array_sum(array_map(function ($role) {
                return ((float) $role['price']) * ((int) $role['slots']);
            }, $roles));
        }

        // Calculate iuran_per_pemain for both schemes
        $validated['iuran_per_pemain'] = $validated['slot_max'] > 0
            ? round(($validated['biaya_total_event'] ?? 0) / $validated['slot_max'], -2)
            : 0;

        Event::create($validated);

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil dibuat.');
    }

    public function show(Event $event)
    {
        $this->authorize('view', $event);

        $livePayload = $this->eventService->buildLivePayload($event);
        $joinedCount = $livePayload['metrics']['joined_count'];

        return view('admin.events.show', compact('event', 'joinedCount', 'livePayload'));
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil dihapus.');
    }

    public function live(Event $event)
    {
        $this->authorize('view', $event);

        return response()->json($this->eventService->buildLivePayload($event));
    }

    public function updateJoinVisibility(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $request->validate([
            'show_joined_players_public' => 'nullable|boolean',
            'show_joined_player_contacts_public' => 'nullable|boolean',
        ]);

        $payload = [
            'show_joined_players_public' => $request->boolean('show_joined_players_public'),
            'show_joined_player_contacts_public' => $request->boolean('show_joined_player_contacts_public'),
        ];

        if (!$payload['show_joined_players_public']) {
            $payload['show_joined_player_contacts_public'] = false;
        }

        $event->update($payload);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Pengaturan visibilitas join berhasil diupdate.',
                'settings' => $payload,
            ]);
        }

        return back()->with('success', 'Pengaturan visibilitas join berhasil diupdate.');
    }

    public function updateAttendance(Request $request, Event $event, $playerId)
    {
        $this->authorize('update', $event);

        $event->players()->updateExistingPivot($playerId, [
            'hadir' => $request->has('hadir') ? true : false
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Status kehadiran berhasil diupdate.',
                'live' => $this->eventService->buildLivePayload($event),
            ]);
        }

        return back()->with('success', 'Status kehadiran berhasil diupdate.');
    }

    public function updateStatus(Request $request, Event $event, $playerId)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'status_join' => 'required|in:' . implode(',', array_column(JoinStatus::cases(), 'value'))
        ]);

        $event->players()->updateExistingPivot($playerId, [
            'status_join' => $validated['status_join']
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Status join berhasil diupdate.',
                'live' => $this->eventService->buildLivePayload($event),
            ]);
        }

        return back()->with('success', 'Status join berhasil diupdate.');
    }

    public function updatePayment(Request $request, Event $event, $playerId)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'payment_status' => 'required|in:' . implode(',', array_column(PaymentStatus::cases(), 'value')),
        ]);

        $player = $event->players()->where('players.id', $playerId)->first();
        if (!$player) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pemain tidak ditemukan pada event ini.',
                ], 404);
            }

            return back()->with('error', 'Pemain tidak ditemukan pada event ini.');
        }

        $payload = [
            'payment_status' => $validated['payment_status'],
        ];

        if ($validated['payment_status'] === PaymentStatus::PAID->value) {
            $payload['payment_paid_at'] = now();
            if (empty($player->pivot->payment_reference)) {
                $prefix = $player->pivot->payment_method === 'online_banking' ? 'ADMIN-MB' : 'ADMIN-CASH';
                $payload['payment_reference'] = $prefix . '-' . strtoupper(Str::random(8));
            }
        } else {
            $payload['payment_paid_at'] = null;
        }

        $event->players()->updateExistingPivot($playerId, $payload);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Status pembayaran berhasil diupdate.',
                'live' => $this->eventService->buildLivePayload($event),
            ]);
        }

        return back()->with('success', 'Status pembayaran berhasil diupdate.');
    }
}
