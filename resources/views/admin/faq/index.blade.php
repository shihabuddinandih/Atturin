@extends('layouts.admin')

@section('content')
@php
    $faqs = [
        ['q' => 'Bagaimana cara membuat event baru?', 'a' => 'Klik tombol Buat Event di sidebar, lengkapi detail, lalu simpan draft atau publish.'],
        ['q' => 'Bagaimana mengubah status pembayaran pemain?', 'a' => 'Masuk ke detail event, pilih peserta, lalu update status pembayaran.'],
        ['q' => 'Kenapa slot tidak bertambah?', 'a' => 'Periksa pengaturan slot maksimum pada event dan pastikan status aktif.'],
        ['q' => 'Bagaimana mengirim broadcast?', 'a' => 'Buka Notifications, klik Buat Broadcast, lalu pilih segment penerima.'],
    ];
@endphp

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">FAQ</h1>
        <p class="text-sm text-gray-500 mt-1">Pertanyaan yang sering ditanyakan tentang admin console.</p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-3">
            @foreach ($faqs as $item)
                <details class="pro-card p-5 group">
                    <summary class="flex items-center justify-between cursor-pointer list-none">
                        <span class="text-sm font-semibold text-gray-900">{{ $item['q'] }}</span>
                        <span class="text-brand-500 text-xl group-open:rotate-45 transition-transform">+</span>
                    </summary>
                    <p class="text-sm text-gray-500 mt-3">{{ $item['a'] }}</p>
                </details>
            @endforeach
        </div>
        <div class="space-y-4">
            <div class="pro-card p-5">
                <h3 class="text-base font-semibold text-gray-900">Butuh bantuan?</h3>
                <p class="text-sm text-gray-500 mt-2">Kontak support untuk pertanyaan khusus atau laporan bug.</p>
                <button class="mt-4 w-full px-4 py-2 rounded-xl bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600">Hubungi Support</button>
            </div>
            <div class="pro-card p-5">
                <h3 class="text-base font-semibold text-gray-900">Tips Admin</h3>
                <ul class="mt-3 space-y-2 text-sm text-gray-600">
                    <li>Gunakan filter event untuk pantau slot cepat.</li>
                    <li>Aktifkan reminder pembayaran otomatis.</li>
                    <li>Periksa laporan keuangan mingguan.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
