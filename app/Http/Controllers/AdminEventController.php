<?php

namespace App\Http\Controllers;

use App\Enums\JoinStatus;
use App\Enums\PaymentStatus;
use App\Jobs\SendWaitlistOfferJob;
use App\Models\Event;
use App\Models\EventDraft;
use App\Models\PlayerContactRequest;
use App\Services\EventService;
use App\Support\FacilityCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $drafts = EventDraft::ownedBy($adminId)->latest('updated_at')->get();

        return view('admin.events.index', compact('events', 'summary', 'status', 'drafts'));
    }

    public function create(Request $request)
    {
        $draft = null;

        if ($request->filled('draft')) {
            $draft = EventDraft::ownedBy(auth()->id())->find($request->integer('draft'));

            if (!$draft) {
                return redirect()->route('admin.events.create')->with('error', 'Draf tidak ditemukan atau sudah dipublikasikan.');
            }
        }

        $draftState = $draft?->toFormState();

        return view('admin.events.create', compact('draftState'));
    }

    public function store(Request $request)
    {
        $request->merge(['skema_iuran' => 'custom']);

        $draft = null;
        if ($request->filled('draft_id')) {
            $draft = EventDraft::ownedBy(auth()->id())->find($request->integer('draft_id'));
        }

        $validated = $request->validate([
            'nama_event' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'tempat' => 'required|string|max:255',
            'metode_pembayaran' => 'required|in:online_banking',
            'payment_mode' => 'nullable|in:' . Event::PAYMENT_MODE_PAY_LATER . ',' . Event::PAYMENT_MODE_PAY_FIRST,
            'skema_iuran' => 'required|in:custom',
            'roles' => 'required|array|min:1',
            'roles.*.name' => 'required|string|max:255',
            'roles.*.slots' => 'required|integer|min:1',
            'roles.*.price' => 'required|numeric|min:0',
            'facilities' => 'nullable|array',
            'facilities.*.key' => 'required_with:facilities|string|in:' . implode(',', array_keys(FacilityCatalog::options())),
            'facilities.*.note' => 'nullable|string|max:255',
            'show_joined_players_public' => 'nullable|boolean',
            'show_joined_player_contacts_public' => 'nullable|boolean',
            'enable_waiting_list' => 'nullable|boolean',
            'banner_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('banner_image')) {
            $path = $request->file('banner_image')->store('banners', 'public');
            $validated['banner_image'] = $path;
        } elseif ($draft?->banner_image && Storage::disk('public')->exists($draft->banner_image)) {
            // Pakai banner yang sudah diunggah saat draf; pindahkan agar tidak ikut terhapus bersama draf.
            $path = 'banners/' . basename($draft->banner_image);
            Storage::disk('public')->move($draft->banner_image, $path);
            $draft->banner_image = null;
            $validated['banner_image'] = $path;
        }

        $validated['payment_mode'] = $validated['payment_mode'] ?? Event::PAYMENT_MODE_PAY_LATER;
        $validated['enable_waiting_list'] = $request->boolean('enable_waiting_list');
        $validated['show_joined_players_public'] = $request->boolean('show_joined_players_public', true);
        $validated['show_joined_player_contacts_public'] = $request->boolean('show_joined_player_contacts_public');

        if (!$validated['show_joined_players_public']) {
            $validated['show_joined_player_contacts_public'] = false;
        }

        $validated['admin_id'] = auth()->id();

        $roles = array_values(array_filter($validated['roles'], function ($role) {
            return isset($role['name']) && trim($role['name']) !== '';
        }));

        $totalSlots = array_sum(array_column($roles, 'slots'));
        $validated['slot_max'] = max(1, $totalSlots);
        $validated['roles'] = $roles;
        $validated['biaya_total_event'] = array_sum(array_map(function ($role) {
            return ((float) $role['price']) * ((int) $role['slots']);
        }, $roles));

        $validated['facilities'] = $this->normalizeFacilities($validated['facilities'] ?? []);

        // Calculate iuran_per_pemain for both schemes
        $validated['iuran_per_pemain'] = $validated['slot_max'] > 0
            ? round(($validated['biaya_total_event'] ?? 0) / $validated['slot_max'], -2)
            : 0;

        Event::create($validated);

        $draft?->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil dibuat.');
    }

    public function show(Event $event)
    {
        $this->authorize('view', $event);

        $livePayload = $this->eventService->buildLivePayload($event);
        $joinedCount = $livePayload['metrics']['joined_count'];

        return view('admin.events.show', compact('event', 'joinedCount', 'livePayload'));
    }

    public function edit(Event $event)
    {
        $this->authorize('update', $event);

        return view('admin.events.edit', compact('event'));
    }

    /**
     * Perbarui detail event. Posisi, kuota, harga, dan alur pembayaran sengaja tidak bisa
     * diubah di sini karena terkait pembayaran peserta yang mungkin sudah berjalan.
     */
    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'nama_event' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'tempat' => 'required|string|max:255',
            'facilities' => 'nullable|array',
            'facilities.*.key' => 'required_with:facilities|string|in:' . implode(',', array_keys(FacilityCatalog::options())),
            'facilities.*.note' => 'nullable|string|max:255',
            'show_joined_players_public' => 'nullable|boolean',
            'show_joined_player_contacts_public' => 'nullable|boolean',
            'enable_waiting_list' => 'nullable|boolean',
            'banner_image' => 'nullable|image|max:2048',
            'remove_banner' => 'nullable|boolean',
        ]);

        $payload = [
            'nama_event' => $validated['nama_event'],
            'tanggal' => $validated['tanggal'],
            'waktu' => $validated['waktu'],
            'tempat' => $validated['tempat'],
            'facilities' => $this->normalizeFacilities($validated['facilities'] ?? []),
            'enable_waiting_list' => $request->boolean('enable_waiting_list'),
            'show_joined_players_public' => $request->boolean('show_joined_players_public'),
            'show_joined_player_contacts_public' => $request->boolean('show_joined_player_contacts_public'),
        ];

        if (! $payload['show_joined_players_public']) {
            $payload['show_joined_player_contacts_public'] = false;
        }

        $oldBanner = $event->banner_image;
        if ($request->hasFile('banner_image')) {
            $payload['banner_image'] = $request->file('banner_image')->store('banners', 'public');
        } elseif ($request->boolean('remove_banner')) {
            $payload['banner_image'] = null;
        }

        $event->update($payload);

        if (array_key_exists('banner_image', $payload) && $oldBanner && $oldBanner !== $payload['banner_image']) {
            Storage::disk('public')->delete($oldBanner);
        }

        return redirect()->route('admin.events.show', $event)->with('success', 'Detail event berhasil diperbarui.');
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
            'status_join' => 'required|in:' . implode(',', JoinStatus::adminSettable())
        ]);

        $player = $event->players()->where('players.id', $playerId)->first();
        if ($player) {
            $previousStatus = $player->pivot->status_join;
            $newStatus = $validated['status_join'];

            $event->players()->updateExistingPivot($playerId, [
                'status_join' => $newStatus
            ]);

            if ($previousStatus === 'joined' && $newStatus !== 'joined') {
                $this->promoteNextWaitingPlayer($event);
            }
        }

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
            $payload['status_join'] = 'joined';
            if (empty($player->pivot->payment_reference)) {
                $prefix = $player->pivot->payment_method === 'online_banking' ? 'ADMIN-MB' : 'ADMIN-CASH';
                $payload['payment_reference'] = $prefix . '-' . strtoupper(Str::random(8));
            }
        } else {
            $payload['payment_paid_at'] = null;
            $payload['status_join'] = 'batal';
        }

        $player = $event->players()->where('players.id', $playerId)->first();
        $previousStatusJoin = $player?->pivot->status_join;

        $event->players()->updateExistingPivot($playerId, $payload);

        if ($previousStatusJoin === 'joined' && ($payload['status_join'] ?? $previousStatusJoin) !== 'joined') {
            $this->promoteNextWaitingPlayer($event);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Status pembayaran berhasil diupdate.',
                'live' => $this->eventService->buildLivePayload($event),
            ]);
        }

        return back()->with('success', 'Status pembayaran berhasil diupdate.');
    }

    public function requestContact(Request $request, Event $event, $playerId)
    {
        $this->authorize('update', $event);

        if (!$event->players()->where('players.id', $playerId)->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Pemain tidak ditemukan pada event ini.'], 404);
            }

            return back()->with('error', 'Pemain tidak ditemukan pada event ini.');
        }

        $contactRequest = PlayerContactRequest::firstOrCreate(
            ['event_id' => $event->id, 'player_id' => $playerId, 'status' => 'pending'],
            ['requested_by' => auth()->id()]
        );

        $message = $contactRequest->wasRecentlyCreated
            ? 'Permintaan chat terkirim ke Super Admin.'
            : 'Permintaan chat untuk peserta ini masih menunggu diproses Super Admin.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'live' => $this->eventService->buildLivePayload($event),
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * @param  array<int, array{key: string, note?: string|null}>  $facilities
     * @return array<int, array{key: string, note: string|null}>|null
     */
    private function normalizeFacilities(array $facilities): ?array
    {
        $normalized = array_values(array_map(function ($facility) {
            $note = trim((string) ($facility['note'] ?? ''));

            return [
                'key' => $facility['key'],
                'note' => $note !== '' ? $note : null,
            ];
        }, $facilities));

        return $normalized ?: null;
    }

    private function promoteNextWaitingPlayer(Event $event): void
    {
        if (! $event->enable_waiting_list) {
            return;
        }

        $next = $event->waitlists()->where('status', 'waiting')->oldest()->first();
        if ($next) {
            SendWaitlistOfferJob::dispatch($next);
        }
    }
}
