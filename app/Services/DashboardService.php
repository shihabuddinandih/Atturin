<?php

namespace App\Services;

use App\Enums\JoinStatus;
use App\Enums\PaymentStatus;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Get all dashboard metrics.
     */
    public function getDashboardData(int $adminId): array
    {
        return [
            'quickStats' => $this->getQuickStats($adminId),
            'statusSummary' => $this->getStatusSummary($adminId),
            'tasks' => $this->getTasks($adminId),
            'activity' => $this->getActivity($adminId),
            'chartData' => $this->getChartData($adminId),
            'upcomingEvent' => $this->getUpcomingEvent($adminId),
            'topMembers' => $this->getTopMembers($adminId),
            'urgentOutstanding' => $this->getUrgentOutstanding($adminId),
        ];
    }

    private function getQuickStats(int $adminId): array
    {
        $events = Event::where('admin_id', $adminId)->withCount([
            'players as joined_count' => function ($query) {
                $query->where('event_player.status_join', JoinStatus::JOINED->value);
            },
            'players as batal_count' => function ($query) {
                $query->where('event_player.status_join', JoinStatus::BATAL->value);
            }
        ])->get();

        $totalEvents = $events->count();
        $totalJoined = $events->sum('joined_count');
        $totalBatal = $events->sum('batal_count');
        $totalSlots = $events->sum('slot_max');
        $slotsAvailable = $totalSlots - $totalJoined;

        $dropoutRate = ($totalJoined + $totalBatal) > 0 
            ? round(($totalBatal / ($totalJoined + $totalBatal)) * 100) 
            : 0;

        if ($totalEvents === 0) {
            return [
                ['label' => 'Total Event', 'value' => '0', 'note' => 'Belum ada event', 'tone' => 'brand'],
                ['label' => 'Total Pendaftar', 'value' => '0', 'note' => '0 peserta', 'tone' => 'emerald'],
                ['label' => 'Slot Tersisa', 'value' => '0', 'note' => 'Belum ada slot', 'tone' => 'amber'],
                ['label' => 'Drop-out Rate', 'value' => '0%', 'note' => 'Rasio batal', 'tone' => 'rose'],
            ];
        }

        return [
            ['label' => 'Total Event', 'value' => (string) $totalEvents, 'note' => $totalEvents . ' aktif', 'tone' => 'brand'],
            ['label' => 'Total Pendaftar', 'value' => (string) $totalJoined, 'note' => 'dari semua event', 'tone' => 'emerald'],
            ['label' => 'Slot Tersisa', 'value' => (string) max(0, $slotsAvailable), 'note' => $totalSlots > 0 ? round(($totalJoined / $totalSlots) * 100) . '% terisi' : 'Belum ada slot', 'tone' => 'amber'],
            ['label' => 'Drop-out Rate', 'value' => $dropoutRate . '%', 'note' => 'Rasio batal', 'tone' => 'rose'],
        ];
    }

    private function getStatusSummary(int $adminId): array
    {
        $events = Event::where('admin_id', $adminId)->withCount([
            'players as joined_count' => function ($query) {
                $query->where('event_player.status_join', JoinStatus::JOINED->value);
            }
        ])->get();
        return [
            'open' => $events->filter(fn($e) => (int) $e->slot_max - (int) $e->joined_count > 0)->count(),
            'full' => $events->filter(fn($e) => (int) $e->slot_max - (int) $e->joined_count <= 0)->count(),
            'draft' => 0,
        ];
    }

    private function getTasks(int $adminId): array
    {
        // Simple mock for agenda
        $events = Event::where('admin_id', $adminId)->withCount([
            'players as joined_count' => function ($query) {
                $query->where('event_player.status_join', JoinStatus::JOINED->value);
            },
        ])->latest('tanggal')->latest('waktu')->take(3)->get();

        if ($events->isEmpty()) {
            return [
                ['title' => 'Buat event perdana', 'status' => 'Sekarang', 'priority' => 'Tinggi'],
            ];
        }

        return $events->map(function ($event) {
            $slotsLeft = (int) $event->slot_max - (int) $event->joined_count;
            return [
                'title' => 'Pantau ' . $event->nama_event,
                'status' => $slotsLeft <= 0 ? 'Penuh' : $slotsLeft . ' slot sisa',
                'priority' => $slotsLeft <= 3 ? 'Tinggi' : 'Sedang',
            ];
        })->values()->all();
    }

    private function getActivity(int $adminId): array
    {
        $activity = DB::table('event_player as mp')
            ->join('events as m', 'm.id', '=', 'mp.event_id')
            ->select('m.nama_event as nama_event', 'mp.payment_status', 'mp.created_at')
            ->where('m.admin_id', $adminId)
            ->whereNull('m.deleted_at')
            ->orderByDesc('mp.created_at')
            ->limit(4)
            ->get()
            ->map(function ($row) {
                $label = $row->payment_status === PaymentStatus::PAID->value ? 'Pembayaran diterima' : 'Pendaftaran baru';
                $timeLabel = $row->created_at
                    ? \Carbon\Carbon::parse($row->created_at)->diffForHumans()
                    : 'baru saja';

                return [
                    'title' => $row->nama_event,
                    'desc' => $label,
                    'time' => $timeLabel,
                ];
            })
            ->all();

        return empty($activity) ? [['title' => 'Belum ada aktivitas', 'desc' => 'Mulai dengan membuat event baru.', 'time' => 'baru saja']] : $activity;
    }

    private function getChartData(int $adminId): array
    {
        // Get last 7 days registration count
        $labels = [];
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($date)->translatedFormat('d M');
            
            $count = DB::table('event_player')
                ->join('events', 'events.id', '=', 'event_player.event_id')
                ->where('events.admin_id', $adminId)
                ->whereNull('events.deleted_at')
                ->whereDate('event_player.created_at', $date)
                ->count();
            
            $data[] = $count;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function getUpcomingEvent(int $adminId): ?Event
    {
        return Event::where('admin_id', $adminId)
            ->whereDate('tanggal', '>=', Carbon::today())
            ->withCount([
                'players as joined_count' => function ($query) {
                    $query->where('event_player.status_join', JoinStatus::JOINED->value);
                },
            ])
            ->orderBy('tanggal', 'asc')
            ->orderBy('waktu', 'asc')
            ->first();
    }

    private function getTopMembers(int $adminId): array
    {
        return DB::table('event_player as ep')
            ->join('events as e', 'e.id', '=', 'ep.event_id')
            ->join('players as p', 'p.id', '=', 'ep.player_id')
            ->where('e.admin_id', $adminId)
            ->whereNull('e.deleted_at')
            ->where('ep.status_join', JoinStatus::JOINED->value)
            ->select('p.nama', 'p.kontak', DB::raw('COUNT(ep.event_id) as total_join'))
            ->groupBy('p.id', 'p.nama', 'p.kontak')
            ->orderByDesc('total_join')
            ->limit(4)
            ->get()
            ->map(function ($member) {
                return [
                    'nama' => $member->nama,
                    'kontak' => $member->kontak,
                    'total_join' => $member->total_join,
                ];
            })->all();
    }

    private function getUrgentOutstanding(int $adminId): array
    {
        return DB::table('event_player as ep')
            ->join('events as e', 'e.id', '=', 'ep.event_id')
            ->join('players as p', 'p.id', '=', 'ep.player_id')
            ->where('e.admin_id', $adminId)
            ->whereNull('e.deleted_at')
            ->where('ep.status_join', JoinStatus::JOINED->value)
            ->where('ep.payment_status', PaymentStatus::PENDING->value)
            // Urgent meaning the event is within 3 days past or future
            ->whereDate('e.tanggal', '>=', Carbon::today()->subDays(3))
            ->whereDate('e.tanggal', '<=', Carbon::today()->addDays(7))
            ->select('p.nama', 'p.kontak', 'e.nama_event', 'e.iuran_per_pemain', 'e.slug')
            ->orderBy('e.tanggal', 'asc')
            ->limit(4)
            ->get()
            ->map(function ($row) {
                // Generate WA Link
                $cleaned = preg_replace('/[^0-9]/', '', $row->kontak);
                if (str_starts_with($cleaned, '08')) {
                    $cleaned = '628' . substr($cleaned, 2);
                }
                $message = "Halo *{$row->nama}*, ini pengingat tagihan Rp " . number_format($row->iuran_per_pemain, 0, ',', '.') . " untuk event *{$row->nama_event}*. Segera lunasi ya!";
                $waLink = "https://wa.me/{$cleaned}?text=" . urlencode($message);

                return [
                    'nama' => $row->nama,
                    'kontak' => $row->kontak,
                    'event' => $row->nama_event,
                    'amount' => $row->iuran_per_pemain,
                    'wa_link' => $waLink,
                ];
            })->all();
    }
}
