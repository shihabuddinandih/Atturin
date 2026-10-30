@extends('layouts.admin')

@section('content')
@php
    $hasTeams = $event->teams->isNotEmpty();
    $hasMatches = $event->matches->isNotEmpty();
    $isPublished = $event->team_draw_published_at !== null;
    $publicUrl = route('player.teams.show', $event->slug);
    $defaultTim = old('jumlah_tim', $config['jumlah_tim'] ?? 2);
    $defaultPerTim = old('pemain_per_tim', $config['pemain_per_tim'] ?? 5);
    $defaultHadir = old('hanya_hadir', $config['hanya_hadir'] ?? false);
    $defaultLaga = old('jumlah_pertandingan', $config['jumlah_pertandingan'] ?? ($fairCounts[0]['jumlah'] ?? 1));
    $defaultDurasi = old('durasi_laga', $config['durasi_laga'] ?? null);
@endphp
<div class="max-w-6xl mx-auto space-y-6">
    {{-- Breadcrumb + Header --}}
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
            <a href="{{ route('admin.events.index') }}" class="hover:text-brand-500 transition-colors">Dashboard</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('admin.events.show', $event) }}" class="hover:text-brand-500 transition-colors">Detail Event</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-600">Acak Tim & Jadwal</span>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Acak Tim & Jadwal</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $event->nama_event }} — bagi peserta ke tim secara acak dan seimbang, lalu susun jadwal pertandingan.</p>
            </div>
            @if($hasTeams)
                <form action="{{ route('admin.events.teamDraw.reset', $event) }}" method="POST" onsubmit="return confirm('Hapus semua hasil acak tim dan jadwal?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition-colors">
                        Hapus Hasil
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Ringkasan peserta --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Peserta Joined</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $joinedCount }}</p>
            <p class="text-xs text-gray-400">pemain</p>
        </div>
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Sudah Hadir</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $hadirCount }}</p>
            <p class="text-xs text-gray-400">pemain</p>
        </div>
        <div class="pro-card p-5 col-span-2">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Sebaran Peran</p>
            <div class="flex flex-wrap gap-2 mt-2">
                @forelse($roleSummary as $role => $count)
                    <span class="px-2.5 py-1 rounded-lg bg-gray-100 text-xs font-medium text-gray-700">{{ $role }} <span class="font-bold text-gray-900">{{ $count }}</span></span>
                @empty
                    <span class="text-xs text-gray-400">Belum ada peserta.</span>
                @endforelse
            </div>
            <p class="text-[11px] text-gray-400 mt-2">Peran yang jumlahnya sedikit (mis. Kiper) disebar lebih dulu ke tiap tim.</p>
        </div>
    </div>

    {{-- Step 1: Acak tim --}}
    <div class="pro-card p-5">
        <div class="flex items-center gap-2 mb-4">
            <span class="w-6 h-6 rounded-full bg-brand-500 text-white text-xs font-bold flex items-center justify-center">1</span>
            <h3 class="text-base font-semibold text-gray-900">Acak Pemain ke Tim</h3>
        </div>
        <form action="{{ route('admin.events.teamDraw.draw', $event) }}" method="POST" id="draw-form" class="space-y-4"
              @if($hasTeams) onsubmit="return confirm('Hasil acak dan jadwal sebelumnya akan diganti. Lanjutkan?')" @endif>
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <label class="block">
                    <span class="block text-xs font-semibold text-gray-600 mb-1">Jumlah Tim</span>
                    <input type="number" name="jumlah_tim" id="jumlah_tim" min="2" max="{{ \App\Services\TeamDrawService::MAX_TEAMS }}" value="{{ $defaultTim }}" required
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500">
                </label>
                <label class="block">
                    <span class="block text-xs font-semibold text-gray-600 mb-1">Pemain per Tim</span>
                    <input type="number" name="pemain_per_tim" id="pemain_per_tim" min="1" max="100" value="{{ $defaultPerTim }}" required
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500">
                </label>
                <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-brand-200 cursor-pointer transition-all sm:mt-5">
                    <input type="hidden" name="hanya_hadir" value="0">
                    <input type="checkbox" name="hanya_hadir" id="hanya_hadir" value="1" {{ $defaultHadir ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                    <span class="text-sm font-medium text-gray-800">Hanya pemain yang hadir</span>
                </label>
            </div>
            <div id="draw-preview" class="rounded-xl bg-surface px-4 py-3 text-sm text-gray-700"></div>
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600 shadow-lg shadow-brand-500/20 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                {{ $hasTeams ? 'Acak Ulang Tim' : 'Acak Tim' }}
            </button>
        </form>
    </div>

    {{-- Hasil tim --}}
    @if($hasTeams)
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($event->teams as $team)
                <div class="pro-card overflow-hidden">
                    <div class="px-5 py-3 flex items-center justify-between" style="border-top: 4px solid {{ $team->warna }}">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full" style="background: {{ $team->warna }}"></span>
                            <h4 class="text-sm font-bold text-gray-900">{{ $team->nama_tim }}</h4>
                        </div>
                        <span class="text-xs text-gray-400">{{ $team->members->count() }} pemain · {{ $gamesPerTeam[$team->id] ?? 0 }} laga</span>
                    </div>
                    <ul class="divide-y divide-gray-50">
                        @foreach($team->members as $member)
                            <li class="px-5 py-2 flex items-center justify-between gap-2 text-sm">
                                <span class="text-gray-800 truncate">{{ $member->player?->nama ?? 'Pemain dihapus' }}</span>
                                <span class="flex items-center gap-1.5 flex-shrink-0">
                                    @if(($joinStatuses[$member->player_id] ?? null) !== 'joined')
                                        <span class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-700 text-[10px] font-bold uppercase">Batal</span>
                                    @endif
                                    @if($member->role_name)
                                        <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-600 text-[11px] font-medium">{{ $member->role_name }}</span>
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        @if($cadangan->isNotEmpty())
            <div class="pro-card p-5">
                <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold mb-2">Cadangan ({{ $cadangan->count() }})</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($cadangan as $member)
                        <span class="px-2.5 py-1 rounded-lg bg-gray-100 text-xs font-medium text-gray-700">
                            {{ $member->player?->nama ?? 'Pemain dihapus' }}@if($member->role_name) <span class="text-gray-400">· {{ $member->role_name }}</span>@endif
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Step 2: Jadwal --}}
        <div class="pro-card p-5">
            <div class="flex items-center gap-2 mb-4">
                <span class="w-6 h-6 rounded-full bg-brand-500 text-white text-xs font-bold flex items-center justify-center">2</span>
                <h3 class="text-base font-semibold text-gray-900">Buat Jadwal Pertandingan</h3>
            </div>
            <form action="{{ route('admin.events.teamDraw.schedule', $event) }}" method="POST" class="space-y-4"
                  @if($hasMatches) onsubmit="return confirm('Jadwal sebelumnya akan diganti. Lanjutkan?')" @endif>
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="block">
                        <span class="block text-xs font-semibold text-gray-600 mb-1">Jumlah Pertandingan</span>
                        <input type="number" name="jumlah_pertandingan" id="jumlah_pertandingan" min="1" max="{{ \App\Services\EventMatchScheduler::MAX_MATCHES }}" value="{{ $defaultLaga }}" required
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500">
                    </label>
                    <label class="block">
                        <span class="block text-xs font-semibold text-gray-600 mb-1">Durasi per Laga (menit, opsional)</span>
                        <input type="number" name="durasi_laga" min="1" max="600" value="{{ $defaultDurasi }}" placeholder="mis. 15"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500">
                        <span class="block text-[11px] text-gray-400 mt-1">Jika diisi, perkiraan jam mulai dihitung dari jam event ({{ $event->waktu }}).</span>
                    </label>
                </div>
                @if(!empty($fairCounts))
                    <div>
                        <p class="text-xs font-semibold text-gray-600 mb-1.5">Saran jumlah laga (semua tim main sama banyak):</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach(array_slice($fairCounts, 0, 10) as $option)
                                <button type="button" data-laga="{{ $option['jumlah'] }}"
                                        class="laga-chip px-3 py-1.5 rounded-lg border text-xs font-medium transition-colors {{ $option['round_robin_penuh'] ? 'border-lime-400 bg-lime-50 text-gray-800' : 'border-gray-200 bg-white text-gray-600' }} hover:border-brand-500">
                                    {{ $option['jumlah'] }} laga <span class="text-gray-400">· {{ $option['main_per_tim'] }}× main/tim</span>
                                    @if($option['round_robin_penuh'])<span class="font-semibold text-lime-700">· semua saling bertemu</span>@endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
                <p class="text-[11px] text-gray-400">
                    Jadwal disusun dengan prinsip round-robin: semua tim saling bertemu dulu sebelum ada pertemuan ulang (kandang/tandang dibalik), jumlah main tiap tim selisih maksimal 1, dan sebisa mungkin tidak ada tim yang main dua kali berturut-turut.
                </p>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600 shadow-lg shadow-brand-500/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ $hasMatches ? 'Buat Ulang Jadwal' : 'Buat Jadwal' }}
                </button>
            </form>
        </div>

        @if($hasMatches)
            <div class="pro-card overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-900">Jadwal Pertandingan ({{ $event->matches->count() }} laga)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-[11px] uppercase tracking-wider text-gray-400">
                                <th class="px-4 sm:px-6 py-3 whitespace-nowrap font-semibold">#</th>
                                <th class="px-4 sm:px-6 py-3 whitespace-nowrap font-semibold">Perkiraan</th>
                                <th class="px-4 sm:px-6 py-3 whitespace-nowrap font-semibold">Pertandingan</th>
                                <th class="px-4 sm:px-6 py-3 whitespace-nowrap font-semibold">Pertemuan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($event->matches as $match)
                                <tr>
                                    <td class="px-4 sm:px-6 py-3 whitespace-nowrap font-semibold text-gray-500">{{ $match->urutan }}</td>
                                    <td class="px-4 sm:px-6 py-3 whitespace-nowrap text-gray-600">{{ $match->perkiraan_mulai ? substr($match->perkiraan_mulai, 0, 5) : '—' }}</td>
                                    <td class="px-4 sm:px-6 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-2 font-semibold text-gray-900">
                                            <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $match->homeTeam->warna }}"></span>
                                            {{ $match->homeTeam->nama_tim }}
                                            <span class="text-gray-400 font-normal">vs</span>
                                            <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $match->awayTeam->warna }}"></span>
                                            {{ $match->awayTeam->nama_tim }}
                                        </div>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3 whitespace-nowrap text-gray-500">ke-{{ $match->putaran }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Step 3: Publikasi --}}
        <div class="pro-card p-5 border-l-4 {{ $isPublished ? 'border-l-emerald-500' : 'border-l-gray-300' }}">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-brand-500 text-white text-xs font-bold flex items-center justify-center">3</span>
                    <h3 class="text-base font-semibold text-gray-900">Link Publik</h3>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $isPublished ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $isPublished ? 'Tampil di publik' : 'Belum dipublikasi' }}
                </span>
            </div>
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="text" readonly value="{{ $publicUrl }}" id="team-share-link" class="flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm bg-gray-50 text-gray-700">
                <button type="button" id="copy-team-link" class="inline-flex items-center justify-center gap-2 border border-gray-200 bg-white text-gray-700 hover:text-brand-600 font-semibold py-2.5 px-5 rounded-xl text-sm transition-colors whitespace-nowrap">
                    Copy Link
                </button>
                <form action="{{ route('admin.events.teamDraw.publish', $event) }}" method="POST">
                    @csrf
                    <input type="hidden" name="publish" value="{{ $isPublished ? 0 : 1 }}">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 font-semibold py-2.5 px-5 rounded-xl text-sm transition-colors whitespace-nowrap {{ $isPublished ? 'bg-gray-800 hover:bg-gray-900 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                        {{ $isPublished ? 'Sembunyikan' : 'Publikasikan' }}
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const joined = {{ $joinedCount }};
        const hadir = {{ $hadirCount }};
        const timInput = document.getElementById('jumlah_tim');
        const perTimInput = document.getElementById('pemain_per_tim');
        const hadirInput = document.getElementById('hanya_hadir');
        const preview = document.getElementById('draw-preview');

        function renderPreview() {
            const pool = hadirInput.checked ? hadir : joined;
            const tim = parseInt(timInput.value, 10) || 0;
            const perTim = parseInt(perTimInput.value, 10) || 0;

            if (tim < 2 || perTim < 1) {
                preview.innerHTML = '<span class="text-rose-600">Minimal 2 tim dan 1 pemain per tim.</span>';
                return;
            }
            if (pool < tim) {
                preview.innerHTML = `<span class="text-rose-600">Pemain tersedia (${pool}) kurang dari jumlah tim (${tim}). Setiap tim minimal harus memiliki 1 pemain.</span>`;
                return;
            }

            const capacity = tim * perTim;
            const playing = Math.min(pool, capacity);
            const base = Math.floor(playing / tim);
            const extra = playing % tim;
            const sizes = extra === 0 ? `${base} pemain` : `${base}–${base + 1} pemain`;
            const cadangan = Math.max(0, pool - capacity);

            preview.innerHTML = `<b>${pool}</b> pemain tersedia → <b>${tim}</b> tim berisi <b>${sizes}</b>`
                + (cadangan > 0 ? `, <b>${cadangan}</b> pemain menjadi cadangan.` : '.');
        }

        [timInput, perTimInput, hadirInput].forEach((el) => el.addEventListener('input', renderPreview));
        hadirInput.addEventListener('change', renderPreview);
        renderPreview();

        document.querySelectorAll('.laga-chip').forEach((chip) => {
            chip.addEventListener('click', () => {
                document.getElementById('jumlah_pertandingan').value = chip.dataset.laga;
            });
        });

        const copyBtn = document.getElementById('copy-team-link');
        if (copyBtn) {
            copyBtn.addEventListener('click', () => {
                const input = document.getElementById('team-share-link');
                navigator.clipboard.writeText(input.value).then(() => {
                    copyBtn.textContent = 'Tersalin!';
                    setTimeout(() => (copyBtn.textContent = 'Copy Link'), 1500);
                });
            });
        }
    })();
</script>
@endpush
