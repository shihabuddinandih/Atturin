@extends('layouts.admin')

@section('content')
<div class="w-full max-w-5xl mx-auto space-y-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
            <a href="{{ route('admin.tournaments.index') }}" class="hover:text-brand-500 transition-colors">Turnamen</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-600">{{ $tournament->nama_turnamen }}</span>
        </div>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $tournament->nama_turnamen }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $tournament->lokasi }} &middot; {{ $tournament->tanggal_mulai->translatedFormat('d M Y') }} &ndash; {{ $tournament->tanggal_selesai->translatedFormat('d M Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.tournaments.edit', $tournament) }}" class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:border-brand-200 hover:text-brand-600 transition-colors">Edit</a>
                <a href="{{ route('admin.tournaments.schedule.index', $tournament) }}" class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:border-brand-200 hover:text-brand-600 transition-colors">Jadwal</a>
                <a href="{{ route('admin.tournaments.bracket.show', $tournament) }}" class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:border-brand-200 hover:text-brand-600 transition-colors">Kelola Bracket</a>
                <a href="{{ route('admin.tournaments.teams.index', $tournament) }}" class="px-4 py-2 rounded-xl bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600 shadow-lg shadow-brand-500/20">Kelola Tim</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Format</p>
            <p class="text-lg font-bold text-brand-500 mt-1">{{ \App\Enums\TournamentFormat::from($tournament->format)->label() }}</p>
        </div>
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Tim Terdaftar</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $tournament->teams_count }} / {{ $tournament->jumlah_tim }}</p>
        </div>
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Pemain / Official per Tim</p>
            <p class="text-lg font-bold text-gray-900 mt-1">{{ $tournament->jumlah_pemain_per_tim }} / {{ $tournament->jumlah_official_per_tim }}</p>
        </div>
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Status</p>
            @php $tStatus = \App\Enums\TournamentStatus::from($tournament->status); @endphp
            <span class="inline-flex mt-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-{{ $tStatus->color() }}-100 text-{{ $tStatus->color() }}-800 uppercase tracking-wider">
                {{ $tStatus->label() }}
            </span>
        </div>
    </div>

    <div class="pro-card p-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-2">Langkah Selanjutnya</h3>
        <p class="text-sm text-gray-500">Bagan pertandingan sudah otomatis dibuat berdasarkan format &amp; jumlah tim. Tambahkan tim dan roster pemain/official, lalu tempatkan tim ke bagan lewat halaman Kelola Bracket.</p>
    </div>

    <div class="pro-card p-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-2">Halaman Publik</h3>
        <p class="text-sm text-gray-500 mb-3">Bagikan link ini ke penonton untuk melihat livescore &amp; bracket secara real-time.</p>
        <a href="{{ route('tournament.show', $tournament) }}" target="_blank" class="text-sm font-semibold text-brand-600 hover:text-brand-700 break-all">{{ route('tournament.show', $tournament) }}</a>
    </div>
</div>
@endsection
