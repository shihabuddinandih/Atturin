<?php

namespace App\Http\Controllers;

use App\Enums\JoinStatus;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminMemberController extends Controller
{
    public function index(Request $request)
    {
        $adminId = auth()->id();
        $search = $request->get('search');
        $sort = $request->get('sort', 'most_joined');

        $query = DB::table('players as p')
            ->leftJoin('event_player as mp', 'mp.player_id', '=', 'p.id')
            ->leftJoin('events as e', function ($join) use ($adminId) {
                $join->on('e.id', '=', 'mp.event_id')
                    ->where('e.admin_id', $adminId)
                    ->whereNull('e.deleted_at');
            })
            ->select(
                'p.id',
                'p.nama',
                'p.kontak',
                DB::raw("SUM(CASE WHEN mp.status_join = '" . JoinStatus::JOINED->value . "' THEN 1 ELSE 0 END) as joined_events"),
                DB::raw("SUM(CASE WHEN mp.payment_status = '" . PaymentStatus::PAID->value . "' THEN 1 ELSE 0 END) as paid_events"),
                DB::raw("SUM(CASE WHEN mp.payment_status = '" . PaymentStatus::PAID->value . "' THEN
                    CASE
                        WHEN mp.payment_method != 'online_banking' THEN mp.payment_amount
                        WHEN mp.payment_amount <= 50500             THEN mp.payment_amount - 1500
                        WHEN mp.payment_amount <= 102000            THEN mp.payment_amount - 3000
                        ELSE                                             mp.payment_amount / 1.03
                    END
                ELSE 0 END) as total_paid_amount"),
                DB::raw('MAX(mp.created_at) as last_joined_at')
            )
            ->whereNotNull('e.id');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('p.nama', 'like', '%' . $search . '%')
                  ->orWhere('p.kontak', 'like', '%' . $search . '%');
            });
        }

        $query->groupBy('p.id', 'p.nama', 'p.kontak');

        if ($sort === 'highest_payment') {
            $query->orderByDesc('total_paid_amount');
        } elseif ($sort === 'newest') {
            $query->orderByDesc('last_joined_at');
        } else {
            $query->orderByDesc('joined_events');
        }
        
        $query->orderBy('p.nama');

        $members = $query->paginate(20)->withQueryString();

        $summaryQuery = DB::table('players as p')
            ->leftJoin('event_player as mp', 'mp.player_id', '=', 'p.id')
            ->leftJoin('events as e', function ($join) use ($adminId) {
                $join->on('e.id', '=', 'mp.event_id')
                    ->where('e.admin_id', $adminId)
                    ->whereNull('e.deleted_at');
            })
            ->whereNotNull('e.id')
            ->select(
                DB::raw('COUNT(DISTINCT p.id) as total_members'),
                DB::raw("COUNT(DISTINCT CASE WHEN mp.status_join = '" . JoinStatus::JOINED->value . "' THEN p.id END) as active_members"),
                DB::raw("SUM(CASE WHEN mp.payment_status = '" . PaymentStatus::PAID->value . "' THEN
                    CASE
                        WHEN mp.payment_method != 'online_banking' THEN mp.payment_amount
                        WHEN mp.payment_amount <= 50500             THEN mp.payment_amount - 1500
                        WHEN mp.payment_amount <= 102000            THEN mp.payment_amount - 3000
                        ELSE                                             mp.payment_amount / 1.03
                    END
                ELSE 0 END) as total_paid_amount"),
                DB::raw("SUM(CASE WHEN mp.status_join = '" . JoinStatus::JOINED->value . "' THEN 1 ELSE 0 END) as total_joined_count"),
                DB::raw("SUM(CASE WHEN mp.payment_status = '" . PaymentStatus::PAID->value . "' THEN 1 ELSE 0 END) as total_paid_count")
            )
            ->first();

        $totalJoined = (int) ($summaryQuery->total_joined_count ?? 0);
        $totalPaid = (int) ($summaryQuery->total_paid_count ?? 0);
        $collectionRate = $totalJoined > 0 ? round(($totalPaid / $totalJoined) * 100) : 0;

        $summary = [
            'total_members' => (int) ($summaryQuery->total_members ?? 0),
            'active_members' => (int) ($summaryQuery->active_members ?? 0),
            'total_paid_amount' => (float) ($summaryQuery->total_paid_amount ?? 0),
            'collection_rate' => $collectionRate,
        ];

        return view('admin.members.index', compact('members', 'summary', 'search', 'sort'));
    }
}
