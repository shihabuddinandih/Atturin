@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Daftar Pendaftar</h1>
            <p class="text-sm text-gray-500 mt-1">Ringkasan aktivitas pemain berdasarkan riwayat join dan pembayaran.</p>
        </div>
    </div>

    {{-- 1. Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Total Pendaftar</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $summary['total_members'] }}</p>
            <p class="text-xs text-gray-400 mt-1">pemain terdaftar</p>
        </div>
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Pendaftar Aktif</p>
            <p class="text-2xl font-bold text-brand-500 mt-1">{{ $summary['active_members'] }}</p>
            <p class="text-xs text-gray-400 mt-1">pernah mendaftar</p>
        </div>
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Total Transaksi</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($summary['total_paid_amount'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">iuran berhasil terkumpul</p>
        </div>
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Rasio Pelunasan</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $summary['collection_rate'] }}%</p>
            <p class="text-xs text-gray-400 mt-1">tingkat kelancaran bayar</p>
        </div>
    </div>

    {{-- 2. Filter & Search Bar --}}
    <form action="{{ route('admin.members.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
        <div class="flex-1 max-w-md relative">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau nomor telepon..." 
                   class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            <span class="absolute left-3.5 top-2.5 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
        </div>
        <div class="flex items-center gap-2">
            <select name="sort" onchange="this.form.submit()" 
                    class="px-4 py-2 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                <option value="most_joined" {{ $sort === 'most_joined' ? 'selected' : '' }}>Terbanyak Join</option>
                <option value="highest_payment" {{ $sort === 'highest_payment' ? 'selected' : '' }}>Transaksi Terbesar</option>
                <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Terbaru Mendaftar</option>
            </select>

            @if(!empty($search))
                <a href="{{ route('admin.members.index') }}" class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:border-brand-200 transition-colors">
                    Reset
                </a>
            @endif
        </div>
    </form>

    {{-- 3. Table Pendaftar --}}
    <div class="pro-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/20">
                        <th class="px-6 py-4 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Pendaftar</th>
                        <th class="px-6 py-4 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Keandalan Bayar</th>
                        <th class="px-6 py-4 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Kontak</th>
                        <th class="px-6 py-4 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Join Event</th>
                        <th class="px-6 py-4 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Paid Event</th>
                        <th class="px-6 py-4 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Pembayaran</th>
                        <th class="px-6 py-4 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Terakhir Join</th>
                        <th class="px-6 py-4 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($members as $member)
                        @php
                            $joined = (int) $member->joined_events;
                            $paid = (int) $member->paid_events;
                            $rate = $joined > 0 ? ($paid / $joined) : 0;
                            
                            // Determine Loyalty Health Badge
                            if ($rate >= 1.0) {
                                $badgeStyle = 'bg-emerald-50 text-emerald-700 border-emerald-100';
                                $badgeLabel = 'Lancar';
                            } elseif ($rate >= 0.75) {
                                $badgeStyle = 'bg-sky-50 text-sky-700 border-sky-100';
                                $badgeLabel = 'Baik';
                            } else {
                                $badgeStyle = 'bg-amber-50 text-amber-700 border-amber-100';
                                $badgeLabel = 'Butuh Perhatian';
                            }

                            // WhatsApp Prefilled Link
                            $cleaned = preg_replace('/[^0-9]/', '', $member->kontak);
                            if (str_starts_with($cleaned, '08')) {
                                $cleaned = '628' . substr($cleaned, 2);
                            }
                            $message = "Halo *{$member->nama}*, ini kami dari admin olahraga. Terima kasih sudah sering berpartisipasi dalam event kami!";
                            $waLink = "https://wa.me/{$cleaned}?text=" . urlencode($message);
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-semibold text-gray-800">{{ $member->nama }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $badgeStyle }} uppercase tracking-wider">
                                    {{ $badgeLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $member->kontak }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-700">
                                {{ $joined }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-emerald-600 font-bold">
                                {{ $paid }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-800">
                                Rp {{ number_format((float) $member->total_paid_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-400 font-medium">
                                {{ $member->last_joined_at ? \Carbon\Carbon::parse($member->last_joined_at)->diffForHumans() : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-xs font-semibold">
                                <a href="{{ $waLink }}" target="_blank" 
                                   class="inline-flex items-center gap-1 p-2 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 border border-emerald-100 hover:text-emerald-700 transition-colors" title="Hubungi via WA">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.73-1.45L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.625 1.451 5.403.002 9.803-4.394 9.806-9.799.002-2.618-1.016-5.078-2.868-6.931-1.85-1.85-4.311-2.867-6.93-2.869-5.408 0-9.813 4.402-9.816 9.808-.001 1.637.479 3.193 1.39 4.597l-.278 1.015-.75 2.738 2.808-.737.953-.284zm11.387-5.464c-.3-.149-1.786-.879-2.057-.978-.271-.099-.469-.149-.667.149-.198.298-.767.978-.94 1.177-.173.198-.347.223-.647.074-.3-.149-1.265-.466-2.41-1.487-.89-.794-1.49-1.775-1.665-2.074-.173-.299-.018-.46.131-.609.135-.133.3-.347.449-.52.149-.173.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.667-1.609-.913-2.203-.24-.577-.48-.497-.667-.507-.173-.008-.371-.01-.568-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.786-.73 2.034-1.437.248-.708.248-1.313.173-1.438-.074-.124-.272-.198-.572-.347z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-400">
                                @if(!empty($search))
                                    Tidak ada hasil pencarian untuk "{{ $search }}".
                                @else
                                    Belum ada data pendaftar.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($members instanceof \Illuminate\Contracts\Pagination\Paginator && $members->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $members->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
