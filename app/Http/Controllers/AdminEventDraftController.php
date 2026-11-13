<?php

namespace App\Http\Controllers;

use App\Models\EventDraft;
use App\Support\FacilityCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Auto-save & simpan draf form "Buat Event Baru".
 * Semua field bersifat opsional karena form bisa disimpan saat belum lengkap.
 */
class AdminEventDraftController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $draft = new EventDraft(['admin_id' => auth()->id()]);

        return $this->persist($request, $draft);
    }

    public function update(Request $request, EventDraft $draft): JsonResponse
    {
        $this->ensureOwner($draft);

        return $this->persist($request, $draft);
    }

    public function destroy(Request $request, EventDraft $draft): JsonResponse|RedirectResponse
    {
        $this->ensureOwner($draft);

        $draft->delete();

        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()->route('admin.events.index')->with('success', 'Draf event berhasil dihapus.');
    }

    private function persist(Request $request, EventDraft $draft): JsonResponse
    {
        $validated = $request->validate([
            'nama_event' => 'nullable|string|max:255',
            'tanggal' => 'nullable|date',
            'waktu' => 'nullable|string|max:8',
            'tempat' => 'nullable|string|max:255',
            'roles' => 'nullable|array|max:50',
            'roles.*.name' => 'nullable|string|max:255',
            'roles.*.slots' => 'nullable|integer|min:0|max:10000',
            'roles.*.price' => 'nullable|numeric|min:0',
            'facilities' => 'nullable|array',
            'facilities.*.key' => 'required_with:facilities|string|in:' . implode(',', array_keys(FacilityCatalog::options())),
            'facilities.*.note' => 'nullable|string|max:255',
            'show_joined_players_public' => 'nullable|boolean',
            'show_joined_player_contacts_public' => 'nullable|boolean',
            'payment_mode' => 'nullable|in:pay_later,pay_first',
            'enable_waiting_list' => 'nullable|boolean',
            'step' => 'nullable|integer|min:1|max:3',
            'banner_image' => 'nullable|image|max:2048',
            'remove_banner' => 'nullable|boolean',
        ]);

        if ($request->hasFile('banner_image')) {
            $this->deleteBanner($draft);
            $draft->banner_image = $request->file('banner_image')->store('banners/drafts', 'public');
        } elseif ($request->boolean('remove_banner')) {
            $this->deleteBanner($draft);
            $draft->banner_image = null;
        }

        $showPlayers = $request->boolean('show_joined_players_public', true);

        $draft->nama_event = $validated['nama_event'] ?? null;
        $draft->payload = [
            'tanggal' => $validated['tanggal'] ?? null,
            'waktu' => $validated['waktu'] ?? null,
            'tempat' => $validated['tempat'] ?? null,
            'roles' => array_values($validated['roles'] ?? []),
            'facilities' => array_values($validated['facilities'] ?? []),
            'show_joined_players_public' => $showPlayers,
            'show_joined_player_contacts_public' => $showPlayers && $request->boolean('show_joined_player_contacts_public'),
            'payment_mode' => $validated['payment_mode'] ?? 'pay_later',
            'enable_waiting_list' => $request->boolean('enable_waiting_list'),
            'step' => (int) ($validated['step'] ?? 1),
        ];
        $draft->touch();

        return response()->json([
            'id' => $draft->id,
            'saved_at' => $draft->updated_at->toIso8601String(),
            'update_url' => route('admin.events.drafts.update', $draft),
            'destroy_url' => route('admin.events.drafts.destroy', $draft),
            'banner_url' => $draft->banner_image ? asset('storage/' . $draft->banner_image) : null,
        ]);
    }

    private function deleteBanner(EventDraft $draft): void
    {
        if ($draft->banner_image) {
            Storage::disk('public')->delete($draft->banner_image);
        }
    }

    private function ensureOwner(EventDraft $draft): void
    {
        abort_unless((int) $draft->admin_id === (int) auth()->id(), 404);
    }
}
