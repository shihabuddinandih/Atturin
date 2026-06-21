<?php

namespace App\Services;

use App\Enums\JoinStatus;
use App\Enums\PaymentStatus;
use App\Models\AdminWalletWithdrawal;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    /**
     * Get paginated finance rows for admin with search and filter capabilities.
     */
    public function getFinanceRows(int $adminId, ?string $search = null, ?string $method = null, int $perPage = 15)
    {
        $query = DB::table('events as m')
            ->leftJoin('event_player as mp', 'mp.event_id', '=', 'm.id')
            ->select(
                'm.id',
                'm.nama_event as nama_event',
                'm.tanggal',
                'm.waktu',
                'm.tempat',
                'm.iuran_per_pemain',
                'm.metode_pembayaran',
                DB::raw("SUM(CASE WHEN mp.status_join = '" . JoinStatus::JOINED->value . "' THEN 1 ELSE 0 END) as joined_count"),
                DB::raw("SUM(CASE WHEN mp.payment_status = '" . PaymentStatus::PAID->value . "' THEN 1 ELSE 0 END) as paid_count"),
                DB::raw('m.biaya_total_event as expected_amount'),
                DB::raw("SUM(CASE WHEN mp.payment_status = '" . PaymentStatus::PAID->value . "' THEN
                    CASE
                        WHEN mp.payment_method != 'online_banking' THEN mp.payment_amount
                        WHEN mp.payment_amount <= 50500             THEN mp.payment_amount - 1500
                        WHEN mp.payment_amount <= 102000            THEN mp.payment_amount - 3000
                        ELSE                                             mp.payment_amount / 1.03
                    END
                ELSE 0 END) as collected_amount")
            )
            ->where('m.admin_id', $adminId)
            ->whereNull('m.deleted_at');

        if (!empty($search)) {
            $query->where('m.nama_event', 'like', '%' . $search . '%');
        }

        if (!empty($method) && $method !== 'all') {
            $query->where('m.metode_pembayaran', $method);
        }

        return $query->groupBy('m.id', 'm.nama_event', 'm.tanggal', 'm.waktu', 'm.tempat', 'm.iuran_per_pemain', 'm.metode_pembayaran', 'm.biaya_total_event')
            ->orderByDesc('m.tanggal')
            ->orderByDesc('m.waktu')
            ->paginate($perPage);
    }

    /**
     * Get all finance rows for exporting reports.
     */
    public function getFinanceRowsForExport(int $adminId, ?string $search = null, ?string $method = null)
    {
        $query = DB::table('events as m')
            ->leftJoin('event_player as mp', 'mp.event_id', '=', 'm.id')
            ->select(
                'm.nama_event as nama_event',
                'm.tanggal',
                'm.waktu',
                'm.tempat',
                'm.iuran_per_pemain',
                'm.metode_pembayaran',
                DB::raw("SUM(CASE WHEN mp.status_join = '" . JoinStatus::JOINED->value . "' THEN 1 ELSE 0 END) as joined_count"),
                DB::raw("SUM(CASE WHEN mp.payment_status = '" . PaymentStatus::PAID->value . "' THEN 1 ELSE 0 END) as paid_count"),
                DB::raw('m.biaya_total_event as expected_amount'),
                DB::raw("SUM(CASE WHEN mp.payment_status = '" . PaymentStatus::PAID->value . "' THEN
                    CASE
                        WHEN mp.payment_method != 'online_banking' THEN mp.payment_amount
                        WHEN mp.payment_amount <= 50500             THEN mp.payment_amount - 1500
                        WHEN mp.payment_amount <= 102000            THEN mp.payment_amount - 3000
                        ELSE                                             mp.payment_amount / 1.03
                    END
                ELSE 0 END) as collected_amount")
            )
            ->where('m.admin_id', $adminId)
            ->whereNull('m.deleted_at');

        if (!empty($search)) {
            $query->where('m.nama_event', 'like', '%' . $search . '%');
        }

        if (!empty($method) && $method !== 'all') {
            $query->where('m.metode_pembayaran', $method);
        }

        return $query->groupBy('m.id', 'm.nama_event', 'm.tanggal', 'm.waktu', 'm.tempat', 'm.iuran_per_pemain', 'm.metode_pembayaran', 'm.biaya_total_event')
            ->orderByDesc('m.tanggal')
            ->orderByDesc('m.waktu')
            ->get();
    }

    /**
     * Calculate summary totals from finance rows.
     */
    public function getSummary(int $adminId): array
    {
        $totals = DB::table('events as m')
            ->leftJoin('event_player as mp', 'mp.event_id', '=', 'm.id')
            ->select(
                DB::raw('COALESCE(SUM(DISTINCT m.biaya_total_event), 0) as total_expected'),
                DB::raw("SUM(CASE WHEN mp.payment_status = '" . PaymentStatus::PAID->value . "' THEN
                    CASE
                        WHEN mp.payment_method != 'online_banking' THEN mp.payment_amount
                        WHEN mp.payment_amount <= 50500             THEN mp.payment_amount - 1500
                        WHEN mp.payment_amount <= 102000            THEN mp.payment_amount - 3000
                        ELSE                                             mp.payment_amount / 1.03
                    END
                ELSE 0 END) as total_collected"),
                DB::raw("SUM(CASE WHEN mp.payment_status = '" . PaymentStatus::PAID->value . "' THEN 1 ELSE 0 END) as total_paid_players")
            )
            ->where('m.admin_id', $adminId)
            ->whereNull('m.deleted_at')
            ->first();

        return [
            'total_expected' => (float) ($totals->total_expected ?? 0),
            'total_collected' => (float) ($totals->total_collected ?? 0),
            'total_pending' => (float) ($totals->total_expected ?? 0) - (float) ($totals->total_collected ?? 0),
            'total_paid_players' => (int) ($totals->total_paid_players ?? 0),
        ];
    }

    /**
     * Get recent transactions for dashboard.
     */
    public function getRecentTransactions(int $adminId, int $limit = 5)
    {
        return DB::table('event_player as mp')
            ->join('players as p', 'p.id', '=', 'mp.player_id')
            ->join('events as m', 'm.id', '=', 'mp.event_id')
            ->select(
                'm.id as event_id',
                'm.nama_event as nama_event',
                'p.nama as player_name',
                'p.kontak as player_contact',
                'mp.payment_amount',
                'mp.payment_status',
                'mp.created_at as joined_at'
            )
            ->where('m.admin_id', $adminId)
            ->whereNull('m.deleted_at')
            ->where('mp.status_join', JoinStatus::JOINED->value)
            ->orderByDesc('mp.created_at')
            ->limit($limit)
            ->get();
    }

    public function getWalletSummary(int $adminId): array
    {
        $totals = DB::table('events as m')
            ->leftJoin('event_player as mp', 'mp.event_id', '=', 'm.id')
            ->select(
                DB::raw("SUM(CASE WHEN mp.payment_status = '" . PaymentStatus::PAID->value . "' THEN
                    CASE
                        WHEN mp.payment_method != 'online_banking' THEN mp.payment_amount
                        WHEN mp.payment_amount <= 50500             THEN mp.payment_amount - 1500
                        WHEN mp.payment_amount <= 102000            THEN mp.payment_amount - 3000
                        ELSE                                             mp.payment_amount / 1.03
                    END
                ELSE 0 END) as total_collected")
            )
            ->where('m.admin_id', $adminId)
            ->whereNull('m.deleted_at')
            ->first();

        $withdrawals = DB::table('admin_wallet_withdrawals')
            ->where('admin_id', $adminId)
            ->select(
                DB::raw("SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_completed"),
                DB::raw("SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as total_pending")
            )
            ->first();

        $totalCollected = (float) ($totals->total_collected ?? 0);
        $totalCompleted = (float) ($withdrawals->total_completed ?? 0);
        $totalPending = (float) ($withdrawals->total_pending ?? 0);
        $available = max(0, $totalCollected - $totalCompleted - $totalPending);

        return [
            'total_collected' => $totalCollected,
            'total_withdrawn' => $totalCompleted,
            'total_pending' => $totalPending,
            'available' => $available,
        ];
    }

    public function getWithdrawalHistory(int $adminId, int $limit = 8)
    {
        return AdminWalletWithdrawal::where('admin_id', $adminId)
            ->orderByDesc('requested_at')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function createWithdrawal(int $adminId, array $data)
    {
        return AdminWalletWithdrawal::create([
            'admin_id' => $adminId,
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'payment_account' => $data['payment_account'],
            'note' => $data['note'] ?? null,
            'status' => 'pending',
            'requested_at' => now(),
        ]);
    }
}
