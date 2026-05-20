@extends('layouts.admin')

@section('content')
@php
    $notifications = [
        ['title' => 'Pembayaran diterima', 'desc' => 'Dimas melunasi iuran Liga Jumat Malam.', 'time' => '5 menit lalu', 'type' => 'success'],
        ['title' => 'Slot hampir penuh', 'desc' => 'Mini Cup U23 tinggal 2 slot tersisa.', 'time' => '30 menit lalu', 'type' => 'warning'],
        ['title' => 'Event baru dipublish', 'desc' => 'Friendly Event Sunday sudah tayang.', 'time' => '2 jam lalu', 'type' => 'info'],
        ['title' => 'Pembayaran gagal', 'desc' => 'Transaksi untuk Budi gagal, cek kembali.', 'time' => 'Kemarin', 'type' => 'danger'],
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
            <p class="text-sm text-gray-500 mt-1">Ringkasan update penting untuk admin.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:border-brand-200">Tandai semua</button>
            <button class="px-4 py-2 rounded-xl bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600">Buat Broadcast</button>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-4">
            @foreach ($notifications as $item)
                @php
                    $dot = $item['type'] === 'danger' ? 'bg-rose-500' : ($item['type'] === 'warning' ? 'bg-amber-500' : ($item['type'] === 'success' ? 'bg-emerald-500' : 'bg-brand-500'));
                @endphp
                <div class="pro-card p-5 flex items-start gap-4">
                    <span class="w-2.5 h-2.5 rounded-full mt-2 {{ $dot }}"></span>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">{{ $item['title'] }}</h3>
                            <span class="text-xs text-gray-400">{{ $item['time'] }}</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">{{ $item['desc'] }}</p>
                    </div>
                    <button class="text-xs font-semibold text-brand-500 hover:text-brand-600">Detail</button>
                </div>
            @endforeach
        </div>

        <div class="space-y-4">
            <div class="pro-card p-5">
                <h3 class="text-base font-semibold text-gray-900">Filter</h3>
                <div class="mt-4 space-y-2">
                    <button class="w-full text-left px-3 py-2 rounded-xl bg-brand-50 text-brand-600 text-sm font-semibold">Semua</button>
                    <button class="w-full text-left px-3 py-2 rounded-xl border border-gray-200 text-sm text-gray-600">Pembayaran</button>
                    <button class="w-full text-left px-3 py-2 rounded-xl border border-gray-200 text-sm text-gray-600">Event</button>
                    <button class="w-full text-left px-3 py-2 rounded-xl border border-gray-200 text-sm text-gray-600">Broadcast</button>
                </div>
            </div>
            <div class="pro-card p-5">
                <h3 class="text-base font-semibold text-gray-900">Statistik</h3>
                <div class="mt-4 space-y-3 text-sm text-gray-600">
                    <div class="flex items-center justify-between">
                        <span>Hari ini</span>
                        <span class="font-semibold text-gray-900">12</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Minggu ini</span>
                        <span class="font-semibold text-gray-900">64</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Broadcast terkirim</span>
                        <span class="font-semibold text-gray-900">8</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

