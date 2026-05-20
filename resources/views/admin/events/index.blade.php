@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Daftar Event</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola jadwal, kuota slot, dan membagikan link pendaftaran.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.events.create') }}" class="px-4 py-2 rounded-xl bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600 shadow-lg shadow-brand-500/20">Buat Event Baru</a>
        </div>
    </div>

    {{-- 1. Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Event Mendatang</p>
            <p class="text-2xl font-bold text-brand-500 mt-1">{{ $summary['upcoming_events'] }}</p>
            <p class="text-xs text-gray-400 mt-1">jadwal aktif</p>
        </div>
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Okupansi Slot</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $summary['occupancy_rate'] }}%</p>
            <p class="text-xs text-gray-400 mt-1">rata-rata kuota terisi</p>
        </div>
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Event Hari Ini</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $summary['today_events'] }}</p>
            <p class="text-xs text-gray-400 mt-1">butuh dipantau</p>
        </div>
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Total Semua Event</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $summary['total_events'] }}</p>
            <p class="text-xs text-gray-400 mt-1">riwayat dibuat</p>
        </div>
    </div>

    {{-- 2. Filter Navigation & Search --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-1.5 p-1 bg-gray-100 rounded-xl">
            <a href="{{ route('admin.events.index', ['status' => 'all']) }}" 
               class="px-4 py-2 rounded-lg text-xs font-semibold transition-all {{ $status === 'all' ? 'bg-white text-brand-600 shadow-sm' : 'text-gray-500 hover:text-gray-800' }}">
                Semua
            </a>
            <a href="{{ route('admin.events.index', ['status' => 'upcoming']) }}" 
               class="px-4 py-2 rounded-lg text-xs font-semibold transition-all {{ $status === 'upcoming' ? 'bg-white text-brand-600 shadow-sm' : 'text-gray-500 hover:text-gray-800' }}">
                Mendatang
            </a>
            <a href="{{ route('admin.events.index', ['status' => 'past']) }}" 
               class="px-4 py-2 rounded-lg text-xs font-semibold transition-all {{ $status === 'past' ? 'bg-white text-brand-600 shadow-sm' : 'text-gray-500 hover:text-gray-800' }}">
                Selesai
            </a>
            <a href="{{ route('admin.events.index', ['status' => 'full']) }}" 
               class="px-4 py-2 rounded-lg text-xs font-semibold transition-all {{ $status === 'full' ? 'bg-white text-brand-600 shadow-sm' : 'text-gray-500 hover:text-gray-800' }}">
                Penuh
            </a>
        </div>
    </div>

    {{-- 3. Table list --}}
    <div class="pro-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/20">
                        <th class="px-6 py-4 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Nama Event</th>
                        <th class="px-6 py-4 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Metode</th>
                        <th class="px-6 py-4 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Jadwal & Tempat</th>
                        <th class="px-6 py-4 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Keterisian Slot</th>
                        <th class="px-6 py-4 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Status Waktu</th>
                        <th class="px-6 py-4 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Pembayaran</th>
                        <th class="px-6 py-4 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($events as $event)
                        @php
                            $eventDateStr = \Carbon\Carbon::parse($event->tanggal)->format('Y-m-d');
                            $eventDateTime = \Carbon\Carbon::parse($eventDateStr . ' ' . $event->waktu);
                            $slotsLeft = $event->slot_max - $event->joined_count;
                            $percent = $event->slot_max > 0 ? round(($event->joined_count / $event->slot_max) * 100) : 0;
                            
                            // Determine visual state of status time
                            $isPast = $eventDateTime->isPast();
                            $isToday = $eventDateTime->isToday();
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-semibold text-gray-800">{{ $event->nama_event }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1 items-start">
                                    <span class="inline-flex px-2 py-0.5 rounded-md text-[9px] font-bold tracking-wide uppercase
                                        {{ $event->metode_pembayaran === 'online_banking' ? 'bg-indigo-50 text-indigo-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ $event->metode_pembayaran === 'online_banking' ? 'Online' : 'Tunai' }}
                                    </span>
                                    <span class="inline-flex px-2 py-0.5 rounded-md text-[9px] font-bold tracking-wide uppercase
                                        {{ $event->skema_iuran === 'loyalitas' ? 'bg-purple-50 text-purple-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $event->skema_iuran === 'loyalitas' ? 'Loyalty' : 'Flat' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm text-gray-800">{{ $eventDateTime->translatedFormat('d M Y') }} • {{ \Carbon\Carbon::parse($event->waktu)->format('H:i') }} WIB</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $event->tempat }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap w-48">
                                <div class="flex items-center justify-between text-xs text-gray-600 font-semibold mb-1">
                                    <span>{{ $event->joined_count }} / {{ $event->slot_max }} Slot</span>
                                    <span>{{ $percent }}%</span>
                                </div>
                                <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500
                                        {{ $slotsLeft <= 0 ? 'bg-rose-500' : ($slotsLeft <= 3 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                        style="width: {{ $percent }}%">
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($isPast)
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500 uppercase tracking-wider">
                                        Selesai
                                    </span>
                                @elseif($isToday)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Hari Ini
                                    </span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider">
                                        Mendatang
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <p class="text-sm font-semibold text-gray-800">Rp {{ number_format((float) $event->total_pembayaran, 0, ',', '.') }} / {{ number_format((float) $event->biaya_total_event, 0, ',', '.') }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-xs font-semibold">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Detail --}}
                                    <a href="{{ route('admin.events.show', $event->id) }}" 
                                       class="p-2 rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-600 border border-gray-100 hover:text-gray-800 transition-colors" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    {{-- Share --}}
                                    <input type="text" readonly value="{{ route('player.join.show', $event->slug) }}" class="opacity-0 absolute -z-10" id="share-link-{{ $event->id }}">
                                    <button type="button" onclick="copyEventLink({{ $event->id }})"
                                            class="p-2 rounded-lg bg-brand-50 hover:bg-brand-100 text-brand-600 border border-brand-100 transition-colors" title="Salin Link Join">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                        </svg>
                                    </button>
                                    {{-- Delete --}}
                                    <form action="{{ route('admin.events.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus event ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-100 transition-colors" title="Hapus Event">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <p class="text-sm text-gray-400">Tidak ada event yang ditemukan.</p>
                                    <a href="{{ route('admin.events.create') }}" class="text-xs text-brand-500 font-semibold hover:text-brand-600 bg-brand-50 px-3 py-2 rounded-lg border border-brand-100">Buat Event Baru</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($events instanceof \Illuminate\Contracts\Pagination\Paginator && $events->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $events->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.copyEventLink = function(id) {
        const i = document.getElementById('share-link-' + id);
        if (i) {
            i.select();
            i.setSelectionRange(0, 99999);
            let success = false;
            try {
                success = document.execCommand('copy');
            } catch (err) {}

            const changeButtonText = () => {
                const btn = i.nextElementSibling;
                const origSvg = btn.innerHTML;
                btn.innerHTML = '<span class="text-[9px] font-bold text-brand-600">Disalin!</span>';
                setTimeout(() => {
                    btn.innerHTML = origSvg;
                }, 1800);
            };

            if (success) {
                changeButtonText();
            } else {
                navigator.clipboard.writeText(i.value).then(changeButtonText).catch(() => {
                    alert('Gagal menyalin otomatis. Silakan salin manual.');
                });
            }
        }
    };
</script>
@endpush
