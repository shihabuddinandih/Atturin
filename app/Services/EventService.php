<?php

namespace App\Services;

use App\Enums\JoinStatus;
use App\Enums\PaymentStatus;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class EventService
{
    /**
     * Get paginated events list for admin with aggregated data & filtering.
     */
    public function getAdminEvents(int $adminId, string $status = 'all', int $perPage = 15)
    {
        $query = Event::where('admin_id', $adminId)
            ->withCount([
                'players as joined_count' => function ($query) {
                    $query->where('event_player.status_join', JoinStatus::JOINED->value);
                },
            ])
            ->withSum(
                ['players as total_pembayaran' => function ($query) {
                    $query->where('event_player.payment_status', PaymentStatus::PAID->value);
                }],
                \Illuminate\Support\Facades\DB::raw(
                    "CASE
                        WHEN event_player.payment_method != 'online_banking' THEN event_player.payment_amount
                        WHEN event_player.payment_amount <= 50500               THEN event_player.payment_amount - 1500
                        WHEN event_player.payment_amount <= 102000              THEN event_player.payment_amount - 3000
                        ELSE                                                         event_player.payment_amount / 1.03
                     END"
                )
            );

        if ($status === 'upcoming') {
            $query->where(function ($q) {
                $q->whereDate('tanggal', '>', now()->toDateString())
                  ->orWhere(function ($q2) {
                      $q2->whereDate('tanggal', '=', now()->toDateString())
                         ->whereTime('waktu', '>=', now()->toTimeString());
                  });
            });
        } elseif ($status === 'past') {
            $query->where(function ($q) {
                $q->whereDate('tanggal', '<', now()->toDateString())
                  ->orWhere(function ($q2) {
                      $q2->whereDate('tanggal', '=', now()->toDateString())
                         ->whereTime('waktu', '<', now()->toTimeString());
                  });
            });
        } elseif ($status === 'full') {
            $query->whereRaw('(select count(*) from event_player where event_player.event_id = events.id and event_player.status_join = ?) >= slot_max', [JoinStatus::JOINED->value]);
        }

        return $query->latest('tanggal')
            ->latest('waktu')
            ->paginate($perPage);
    }

    /**
     * Get summary metrics for the events index page.
     */
    public function getEventsSummary(int $adminId): array
    {
        $events = Event::where('admin_id', $adminId)
            ->withCount([
                'players as joined_count' => function ($query) {
                    $query->where('event_player.status_join', JoinStatus::JOINED->value);
                },
            ])
            ->get();

        $totalEvents = $events->count();
        $upcomingEventsCount = Event::where('admin_id', $adminId)
            ->where(function ($q) {
                $q->whereDate('tanggal', '>', now()->toDateString())
                  ->orWhere(function ($q2) {
                      $q2->whereDate('tanggal', '=', now()->toDateString())
                         ->whereTime('waktu', '>=', now()->toTimeString());
                  });
            })
            ->count();

        $todayEventsCount = Event::where('admin_id', $adminId)
            ->whereDate('tanggal', now()->toDateString())
            ->count();

        $totalJoined = $events->sum('joined_count');
        $totalSlots = $events->sum('slot_max');
        $occupancyRate = $totalSlots > 0 ? round(($totalJoined / $totalSlots) * 100) : 0;

        return [
            'upcoming_events' => $upcomingEventsCount,
            'today_events' => $todayEventsCount,
            'occupancy_rate' => $occupancyRate,
            'total_events' => $totalEvents,
        ];
    }

    /**
     * Build the live payload for an event (used in event detail & live polling).
     */
    public function buildLivePayload(Event $event): array
    {
        $event->load(['players' => function ($query) {
            $query->orderBy('event_player.created_at', 'asc');
        }]);

        $players = $event->players->map(function ($player) {
            $joinedAt = $player->pivot->created_at;

            return [
                'id' => $player->id,
                'nama' => $player->nama,
                'kontak' => $player->kontak,
                'joined_at_human' => $joinedAt ? $joinedAt->diffForHumans() : '-',
                'status_join' => $player->pivot->status_join,
                'hadir' => (bool) $player->pivot->hadir,
                'payment_method' => $player->pivot->payment_method,
                'payment_method_label' => $player->pivot->payment_method === 'online_banking' ? 'Online Banking' : 'Tunai',
                'payment_amount' => (float) $player->pivot->payment_amount,
                'payment_status' => $player->pivot->payment_status,
                'payment_reference' => $player->pivot->payment_reference,
            ];
        })->values();

        $joinedCount = $players->where('status_join', JoinStatus::JOINED->value)->count();
        $paidCount = $players->where('payment_status', PaymentStatus::PAID->value)->count();
        $pendingCount = $players->where('payment_status', PaymentStatus::PENDING->value)->count();
        $failedCount = $players->where('payment_status', PaymentStatus::FAILED->value)->count();
        $baseAmount = function (array $player): float {
            $amt = (float) $player['payment_amount'];
            if ($player['payment_method'] !== 'online_banking') {
                return $amt;
            }
            if ($amt <= 50500)  return $amt - 1500;
            if ($amt <= 102000) return $amt - 3000;
            return $amt / 1.03;
        };
        $totalCollected = $players
            ->where('payment_status', PaymentStatus::PAID->value)
            ->sum(fn($player) => $baseAmount($player));

        return [
            'metrics' => [
                'joined_count' => $joinedCount,
                'slot_max' => $event->slot_max,
                'paid_count' => $paidCount,
                'pending_count' => $pendingCount,
                'failed_count' => $failedCount,
                'total_collected' => $totalCollected,
            ],
            'settings' => [
                'show_joined_players_public' => (bool) $event->show_joined_players_public,
                'show_joined_player_contacts_public' => (bool) $event->show_joined_player_contacts_public,
            ],
            'players' => $players,
            'updated_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Count how many times a phone number has successfully joined events of an admin.
     */
    public function getPastJoinsCount(int $adminId, string $kontak, ?int $excludeEventId = null): int
    {
        $query = DB::table('event_player as ep')
            ->join('events as e', 'e.id', '=', 'ep.event_id')
            ->join('players as p', 'p.id', '=', 'ep.player_id')
            ->where('e.admin_id', $adminId)
            ->where('p.kontak', $kontak)
            ->where('ep.status_join', JoinStatus::JOINED->value)
            ->whereNull('e.deleted_at');

        if ($excludeEventId !== null) {
            $query->where('ep.event_id', '!=', $excludeEventId);
        }

        return $query->count();
    }

    /**
     * Get loyalty split weight based on number of past joins.
     */
    public function getWeightForPastJoins(int $pastJoins): float
    {
        if ($pastJoins >= 6) {
            return 0.70; // Very loyal: 30% discount
        } elseif ($pastJoins >= 3) {
            return 0.85; // Cukup aktif: 15% discount
        }
        return 1.0; // New player: Full price
    }

    /**
     * Calculate loyalty fee dynamically for a player joining an event.
     */
    public function calculateLoyaltyFee(Event $event, string $kontak): float
    {
        // Fallback to standard price if scheme is flat or total cost isn't set
        if ($event->skema_iuran !== 'loyalitas' || empty($event->biaya_total_event) || $event->biaya_total_event <= 0) {
            return (float) $event->iuran_per_pemain;
        }

        $contacts = $event->players()
            ->wherePivot('status_join', JoinStatus::JOINED->value)
            ->pluck('players.kontak')
            ->toArray();
        
        // Ensure the joining player is in the list
        if (!in_array($kontak, $contacts)) {
            $contacts[] = $kontak;
        }

        $joinedCount = count($contacts);
        $slotMax = $event->slot_max;

        // Calculate sum of weights for all slots (joined + assumed empty slots as new players with weight 1.0)
        $totalWeight = 0.0;
        foreach ($contacts as $c) {
            $pastJoins = $this->getPastJoinsCount($event->admin_id, $c, $event->id);
            $totalWeight += $this->getWeightForPastJoins($pastJoins);
        }

        // Empty slots get weight of 1.0
        $emptySlots = max(0, $slotMax - $joinedCount);
        $totalWeight += $emptySlots * 1.0;

        // Base unit price
        $biayaTotal = (float) $event->biaya_total_event;
        $basePrice = $totalWeight > 0 ? ($biayaTotal / $totalWeight) : 0;

        // Current joining player's past joins & weight
        $joiningPastJoins = $this->getPastJoinsCount($event->admin_id, $kontak, $event->id);
        $joiningWeight = $this->getWeightForPastJoins($joiningPastJoins);

        // Round to nearest Rp 100 to keep the currency friendly
        return round(($basePrice * $joiningWeight), -2);
    }
}
