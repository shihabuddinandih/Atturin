<?php

namespace App\Http\Controllers;

use App\Models\AdminWalletWithdrawal;
use Illuminate\Http\Request;

class SuperAdminWithdrawalController extends Controller
{
    /**
     * Display a listing of all withdrawal requests.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = AdminWalletWithdrawal::with('admin')
            ->orderByDesc('requested_at')
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $withdrawals = $query->paginate(20)->withQueryString();

        // Calculate summary statistics
        $stats = [
            'total_pending_count' => AdminWalletWithdrawal::where('status', 'pending')->count(),
            'total_pending_amount' => (float) AdminWalletWithdrawal::where('status', 'pending')->sum('amount'),
            'total_completed_count' => AdminWalletWithdrawal::where('status', 'completed')->count(),
            'total_completed_amount' => (float) AdminWalletWithdrawal::where('status', 'completed')->sum('amount'),
        ];

        return view('superadmin.withdrawals.index', compact('withdrawals', 'status', 'stats'));
    }

    /**
     * Approve a withdrawal request (mark as completed/paid).
     */
    public function approve(Request $request, AdminWalletWithdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Hanya permintaan penarikan dengan status pending yang dapat disetujui.');
        }

        $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $withdrawal->update([
            'status' => 'completed',
            'note' => $request->input('note') ?? $withdrawal->note,
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Permintaan penarikan berhasil disetujui dan ditandai selesai.');
    }

    /**
     * Reject a withdrawal request (mark as failed/rejected).
     */
    public function reject(Request $request, AdminWalletWithdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Hanya permintaan penarikan dengan status pending yang dapat ditolak.');
        }

        $request->validate([
            'note' => ['required', 'string', 'max:255'], // Note is required for rejection to explain why
        ]);

        $withdrawal->update([
            'status' => 'failed', // failed/rejected is not processed/pending, releasing funds back to wallet
            'note' => $request->input('note'),
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Permintaan penarikan berhasil ditolak. Saldo dikembalikan ke dompet admin.');
    }
}
