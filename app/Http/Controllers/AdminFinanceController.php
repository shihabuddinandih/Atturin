<?php

namespace App\Http\Controllers;

use App\Services\FinanceService;
use Illuminate\Http\Request;

class AdminFinanceController extends Controller
{
    public function __construct(
        private FinanceService $financeService
    ) {}

    public function index(Request $request)
    {
        $adminId = auth()->id();
        $search = $request->get('search');
        $method = $request->get('method', 'all');

        if ($request->get('export') === 'csv') {
            $rows = $this->financeService->getFinanceRowsForExport($adminId, $search, $method);
            
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="Laporan_Keuangan_' . date('Ymd_His') . '.csv"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            $callback = function() use ($rows) {
                $file = fopen('php://output', 'w');
                // UTF-8 BOM for perfect Microsoft Excel compatibility
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                fputcsv($file, [
                    'Nama Event',
                    'Tanggal',
                    'Waktu',
                    'Tempat',
                    'Metode Pembayaran',
                    'Iuran per Pemain (Rp)',
                    'Total Pendaftar',
                    'Peserta Lunas',
                    'Target Pendapatan (Rp)',
                    'Total Pembayaran (Rp)',
                    'Belum Dibayar (Rp)'
                ]);

                foreach ($rows as $row) {
                    $pending = (float)$row->expected_amount - (float)$row->collected_amount;
                    fputcsv($file, [
                        $row->nama_event,
                        \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y'),
                        \Carbon\Carbon::parse($row->waktu)->format('H:i'),
                        $row->tempat,
                        $row->metode_pembayaran === 'online_banking' ? 'Online' : 'Tunai',
                        $row->iuran_per_pemain,
                        $row->joined_count,
                        $row->paid_count,
                        $row->expected_amount,
                        $row->collected_amount,
                        $pending
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        $financeRows = $this->financeService->getFinanceRows($adminId, $search, $method);

        // Add pending_amount to each row
        $financeRows->getCollection()->transform(function ($row) {
            $row->pending_amount = (float) $row->expected_amount - (float) $row->collected_amount;
            return $row;
        });

        $summary = $this->financeService->getSummary($adminId);

        return view('admin.finances.index', compact('financeRows', 'summary', 'search', 'method'));
    }
}
