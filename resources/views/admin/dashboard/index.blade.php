@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Ringkasan utama aktivitas dan performa event Anda.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.events.create') }}" class="px-4 py-2 rounded-xl bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600 shadow-lg shadow-brand-500/20">Buat Event Baru</a>
        </div>
    </div>

    {{-- 1. Quick Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ($quickStats as $stat)
            @php
                $tone = $stat['tone'] === 'emerald' ? 'bg-emerald-50 text-emerald-600' : 
                        ($stat['tone'] === 'rose' ? 'bg-rose-50 text-rose-600' : 
                        ($stat['tone'] === 'amber' ? 'bg-amber-50 text-amber-700' : 'bg-brand-50 text-brand-600'));
            @endphp
            <div class="pro-card p-5 flex flex-col justify-between">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">{{ $stat['label'] }}</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ $stat['value'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $stat['note'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl {{ $tone }} flex items-center justify-center">
                        <span class="text-sm font-bold">PS</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            {{-- 2. Grafik Tren Pendaftaran --}}
            <div class="pro-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900">Grafik Pendaftaran (7 Hari Terakhir)</h3>
                </div>
                <div class="relative h-[250px] w-full">
                    <canvas id="registrationChart"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- 4. Urgent Outstanding --}}
                <div class="pro-card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></div>
                        <h3 class="text-base font-semibold text-gray-900">Tunggakan Mendesak</h3>
                    </div>
                    <div class="space-y-4">
                        @forelse($urgentOutstanding as $urgent)
                            <div class="flex items-start justify-between p-3 rounded-xl border border-rose-100 bg-rose-50/30">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $urgent['nama'] }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $urgent['event'] }}</p>
                                    <p class="text-xs font-bold text-rose-600 mt-1">Rp {{ number_format($urgent['amount'], 0, ',', '.') }}</p>
                                </div>
                                <a href="{{ $urgent['wa_link'] }}" target="_blank" class="p-2 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200 transition-colors" title="Tagih via WA">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.73-1.45L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.625 1.451 5.403.002 9.803-4.394 9.806-9.799.002-2.618-1.016-5.078-2.868-6.931-1.85-1.85-4.311-2.867-6.93-2.869-5.408 0-9.813 4.402-9.816 9.808-.001 1.637.479 3.193 1.39 4.597l-.278 1.015-.75 2.738 2.808-.737.953-.284zm11.387-5.464c-.3-.149-1.786-.879-2.057-.978-.271-.099-.469-.149-.667.149-.198.298-.767.978-.94 1.177-.173.198-.347.223-.647.074-.3-.149-1.265-.466-2.41-1.487-.89-.794-1.49-1.775-1.665-2.074-.173-.299-.018-.46.131-.609.135-.133.3-.347.449-.52.149-.173.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.667-1.609-.913-2.203-.24-.577-.48-.497-.667-.507-.173-.008-.371-.01-.568-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.786-.73 2.034-1.437.248-.708.248-1.313.173-1.438-.074-.124-.272-.198-.572-.347z"/></svg>
                                </a>
                            </div>
                        @empty
                            <div class="text-center py-6">
                                <p class="text-sm text-gray-400">Semua lunas! 🎉</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- 5. Top Loyal Members --}}
                <div class="pro-card p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Pendaftar Teraktif</h3>
                    <div class="space-y-4">
                        @forelse($topMembers as $index => $member)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-brand-50 flex items-center justify-center text-brand-600 text-xs font-bold">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $member['nama'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $member['total_join'] }}x Join Event</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-4">Belum ada data pendaftar.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            {{-- 3. Upcoming Event Highlight --}}
            @if($upcomingEvent)
                @php
                    $slotsLeft = $upcomingEvent->slot_max - $upcomingEvent->joined_count;
                    $percent = $upcomingEvent->slot_max > 0 ? round(($upcomingEvent->joined_count / $upcomingEvent->slot_max) * 100) : 0;
                @endphp
                <div class="pro-card overflow-hidden bg-gradient-to-br from-brand-600 to-brand-800 text-white relative">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>
                    </div>
                    <div class="p-6 relative z-10">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/20 text-white text-[10px] font-bold uppercase tracking-wider mb-4 border border-white/10 backdrop-blur-md">
                            <span class="w-1.5 h-1.5 rounded-full bg-lime-400 animate-pulse"></span>
                            Event Terdekat
                        </div>
                        <h3 class="text-xl font-bold leading-tight">{{ $upcomingEvent->nama_event }}</h3>
                        <p class="text-brand-100 text-sm mt-1">{{ \Carbon\Carbon::parse($upcomingEvent->tanggal)->translatedFormat('d M Y') }} • {{ $upcomingEvent->waktu }}</p>
                        
                        <div class="mt-6 space-y-2">
                            <div class="flex justify-between text-xs font-medium text-brand-100">
                                <span>{{ $upcomingEvent->joined_count }} Pendaftar</span>
                                <span>Sisa {{ max(0, $slotsLeft) }} Slot</span>
                            </div>
                            <div class="h-2 w-full bg-black/20 rounded-full overflow-hidden">
                                <div class="h-full bg-lime-400 rounded-full transition-all duration-1000" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <input type="text" readonly value="{{ route('player.join.show', $upcomingEvent->slug) }}" class="opacity-0 absolute -z-10" id="highlight-link">
                            <button type="button" onclick="copyHighlightLink()" class="w-full py-2.5 bg-white text-brand-700 hover:bg-brand-50 rounded-xl text-sm font-bold shadow-lg shadow-black/10 transition-colors">
                                Salin Link Join
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <div class="pro-card p-6">
                <h3 class="text-base font-semibold text-gray-900">Aktivitas Terbaru</h3>
                <div class="mt-4 space-y-4">
                    @foreach ($activity as $item)
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-gray-50 border border-gray-100 text-gray-500 flex items-center justify-center text-xs font-bold">ACT</div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $item['title'] }}</p>
                                <p class="text-sm text-gray-500">{{ $item['desc'] }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $item['time'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="pro-card p-6">
                <h3 class="text-base font-semibold text-gray-900">Status Event</h3>
                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Terbuka</span>
                        <span class="font-semibold text-emerald-600">{{ $statusSummary['open'] }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Konsep</span>
                        <span class="font-semibold text-amber-600">{{ $statusSummary['draft'] }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Penuh</span>
                        <span class="font-semibold text-rose-600">{{ $statusSummary['full'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function copyHighlightLink() {
        const i = document.getElementById('highlight-link');
        if (i) {
            i.select();
            i.setSelectionRange(0, 99999);
            let success = false;
            try {
                success = document.execCommand('copy');
            } catch (err) {}

            const changeText = () => {
                const btn = i.nextElementSibling;
                const orig = btn.innerText;
                btn.innerText = 'Disalin!';
                setTimeout(() => btn.innerText = orig, 2000);
            };

            if (success) {
                changeText();
            } else {
                navigator.clipboard.writeText(i.value).then(changeText).catch(() => {
                    alert('Gagal menyalin otomatis. Silakan salin manual.');
                });
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('registrationChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [{
                        label: 'Pendaftar Baru',
                        data: @json($chartData['data']),
                        borderColor: '#0052FF',
                        backgroundColor: 'rgba(0, 82, 255, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#0052FF',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0A1628',
                            padding: 12,
                            titleFont: { family: 'Lexend', size: 13 },
                            bodyFont: { family: 'Lexend', size: 13 },
                            cornerRadius: 8,
                            displayColors: false
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Lexend', size: 11 }, color: '#9CA3AF' }
                        },
                        y: {
                            grid: { borderDash: [4, 4], color: '#F3F4F6' },
                            ticks: { font: { family: 'Lexend', size: 11 }, color: '#9CA3AF', stepSize: 1, beginAtZero: true }
                        }
                    },
                    interaction: { mode: 'index', intersect: false }
                }
            });
        }
    });
</script>
@endpush
