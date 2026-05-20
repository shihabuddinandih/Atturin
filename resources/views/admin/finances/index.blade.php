@extends('layouts.admin')

@section('content')
<div class="space-y-6 print:space-y-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between print:hidden">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Keuangan</h1>
            <p class="text-sm text-gray-500 mt-1">Monitoring target pendapatan, pembayaran terkumpul, dan tunggakan per event.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Laporan
            </button>
            <a href="{{ route('admin.finances.index', array_merge(request()->query(), ['export' => 'csv'])) }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600 shadow-lg shadow-brand-500/20 transition-colors">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Ekspor CSV
            </a>
        </div>
    </div>

    {{-- Printable Title header (visible only during print) --}}
    <div class="hidden print:block border-b border-gray-300 pb-4 mb-4">
        <h1 class="text-2xl font-bold text-gray-900">Laporan Keuangan Admin - Atturin</h1>
        <p class="text-xs text-gray-500 mt-1">Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WIB</p>
    </div>

    {{-- 1. Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 print:grid-cols-4">
        <div class="pro-card p-5 print:border print:border-gray-200">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Target Pendapatan</p>
            <p class="text-xl font-bold text-gray-900 mt-1">Rp {{ number_format($summary['total_expected'], 0, ',', '.') }}</p>
        </div>
        <div class="pro-card p-5 print:border print:border-gray-200">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Total Pembayaran</p>
            <p class="text-xl font-bold text-emerald-600 mt-1">Rp {{ number_format($summary['total_collected'], 0, ',', '.') }}</p>
        </div>
        <div class="pro-card p-5 print:border print:border-gray-200">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Belum Dibayar</p>
            <p class="text-xl font-bold text-amber-600 mt-1">Rp {{ number_format($summary['total_pending'], 0, ',', '.') }}</p>
        </div>
        <div class="pro-card p-5 print:border print:border-gray-200">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Peserta Lunas</p>
            <p class="text-xl font-bold text-brand-500 mt-1">{{ $summary['total_paid_players'] }}</p>
        </div>
    </div>

    {{-- 2. Filter & Search Panel --}}
    <form action="{{ route('admin.finances.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between print:hidden">
        <div class="flex-1 max-w-md relative">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama event..." 
                   class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            <span class="absolute left-3.5 top-2.5 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
        </div>
        <div class="flex items-center gap-2">
            <select name="method" onchange="this.form.submit()" 
                    class="px-4 py-2 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                <option value="all" {{ $method === 'all' ? 'selected' : '' }}>Semua Metode</option>
                <option value="online_banking" {{ $method === 'online_banking' ? 'selected' : '' }}>Online Banking</option>
                <option value="tunai" {{ $method === 'tunai' ? 'selected' : '' }}>Tunai</option>
            </select>

            @if(!empty($search) || $method !== 'all')
                <a href="{{ route('admin.finances.index') }}" class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:border-brand-200 transition-colors">
                    Reset
                </a>
            @endif
        </div>
    </form>

    {{-- 3. Table Rows --}}
    <div class="pro-card overflow-hidden print:border print:border-gray-200 print:shadow-none">
        <div class="px-6 py-4 border-b border-gray-100 print:px-4 print:py-2">
            <h3 class="text-base font-semibold text-gray-900">Laporan Keuangan per Event</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/20">
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider print:px-4">Event</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider print:px-4">Metode</th>
                        <th class="px-6 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider print:px-4">Pendaftar/Lunas</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider print:px-4">Target Pendapatan</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider print:px-4">Total Pembayaran</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider print:px-4">Belum Dibayar</th>
                        <th class="px-6 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider print:hidden">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($financeRows as $row)
                        @php
                            $expected = (float) $row->expected_amount;
                            $collected = (float) $row->collected_amount;
                            $pending = (float) $row->pending_amount;
                            
                            // Collection progress rate
                            $progressRate = $expected > 0 ? round(($collected / $expected) * 100) : 0;
                            
                            // High Outstanding Alert (unpaid amount > 40% of target)
                            $isHighOutstanding = $expected > 0 && ($pending / $expected) > 0.40;
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap print:px-4">
                                <p class="text-sm font-semibold text-gray-800">{{ $row->nama_event }}</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d M Y') }} • {{ $row->tempat }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap print:px-4">
                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                    {{ $row->metode_pembayaran === 'online_banking' ? 'bg-indigo-50 text-indigo-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $row->metode_pembayaran === 'online_banking' ? 'Online' : 'Tunai' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700 font-semibold print:px-4">
                                {{ $row->joined_count }} / <span class="text-emerald-600 font-bold">{{ $row->paid_count }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-800 print:px-4">
                                Rp {{ number_format($expected, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right print:px-4 w-44">
                                <p class="text-sm font-bold text-emerald-600">Rp {{ number_format($collected, 0, ',', '.') }}</p>
                                {{-- Mini progress bar keterkumpulan kas --}}
                                <div class="mt-1 flex items-center gap-1.5 justify-end print:hidden">
                                    <div class="h-1 w-24 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $progressRate }}%"></div>
                                    </div>
                                    <span class="text-[9px] text-gray-400 font-bold">{{ $progressRate }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm print:px-4 font-semibold">
                                @if($isHighOutstanding && $pending > 0)
                                    <span class="inline-flex items-center gap-1 text-rose-600 font-bold" title="Tunggakan tinggi (>40%)">
                                        <span>Rp {{ number_format($pending, 0, ',', '.') }}</span>
                                        <svg class="w-3.5 h-3.5 text-rose-500 print:hidden animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    </span>
                                @else
                                    <span class="text-gray-800 font-semibold">Rp {{ number_format($pending, 0, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center print:hidden">
                                <a href="{{ route('admin.events.show', $row->id) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-500 hover:text-brand-700 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-lg transition-colors">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-400">
                                @if(!empty($search) || $method !== 'all')
                                    Tidak ada data laporan yang cocok dengan kriteria pencarian.
                                @else
                                    Belum ada data finansial.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if ($financeRows instanceof \Illuminate\Contracts\Pagination\Paginator && $financeRows->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 print:hidden">
                {{ $financeRows->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    @media print {
        body {
            background: white !important;
            color: black !important;
        }
        /* Hide sidebar, navigation, header, etc. */
        header, sidebar, nav, aside, footer, .sidebar, .navbar, .print\:hidden {
            display: none !important;
        }
        /* Ensure table content expanded correctly */
        main, .content, .container {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .pro-card {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
    }
</style>
@endsection
