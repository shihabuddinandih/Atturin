@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Langganan</h1>
            <p class="text-sm text-gray-500 mt-1">Pilih paket yang paling cocok untuk kebutuhan event dan manajemen Anda.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="#plans" class="px-4 py-2 rounded-xl bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600 shadow-lg shadow-brand-500/20">Lihat Paket</a>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-4 lg:grid-cols-2">
        <div class="pro-card p-6 border border-gray-200">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-widest bg-gray-100 text-gray-600">Gratis</span>
            <h2 class="mt-5 text-xl font-semibold text-gray-900">Free</h2>
            <p class="mt-2 text-3xl font-bold text-gray-900">Rp 0 / bulan</p>
            <p class="mt-3 text-sm text-gray-500">Akses dasar untuk mencoba platform dan mengelola event pertama Anda.</p>
            <div class="mt-6 space-y-3 text-sm text-gray-600">
                <p>• Buat sampai 2 event</p>
                <p>• Laporan dasar</p>
                <p>• Notifikasi standar</p>
                <p>• Support komunitas</p>
            </div>
            <div class="mt-6">
                <button disabled class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 text-sm font-semibold text-gray-500">Sedang Aktif</button>
            </div>
        </div>

        <div class="pro-card p-6 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-widest bg-brand-50 text-brand-700">Basic</span>
            <h2 class="mt-5 text-xl font-semibold text-gray-900">Basic</h2>
            <p class="mt-2 text-3xl font-bold text-gray-900">Rp 199.000 / bulan</p>
            <p class="mt-3 text-sm text-gray-500">Cocok untuk penyelenggara event kecil yang butuh lebih banyak kontrol.</p>
            <div class="mt-6 space-y-3 text-sm text-gray-600">
                <p>• Buat sampai 10 event</p>
                <p>• Statistik event</p>
                <p>• Tanpa iklan</p>
                <p>• Prioritas notifikasi</p>
            </div>
            <div class="mt-6">
                <button class="w-full rounded-xl bg-brand-500 py-3 text-sm font-semibold text-white hover:bg-brand-600">Pilih Basic</button>
            </div>
        </div>

        <div class="pro-card relative p-6 border border-brand-500 shadow-xl shadow-brand-500/10 bg-brand-50 hover:shadow-2xl transition-shadow duration-200">
            <div class="absolute -top-3 right-3 inline-flex items-center rounded-full bg-brand-600 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white shadow-sm">Best value</div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-widest bg-lime-100 text-lime-700">Pro</span>
            <h2 class="mt-5 text-xl font-semibold text-gray-900">Pro</h2>
            <p class="mt-2 text-3xl font-bold text-gray-900">Rp 499.000 / bulan</p>
            <p class="mt-3 text-sm text-gray-500">Ideal untuk penyelenggara aktif yang ingin insight dan fleksibilitas lebih.</p>
            <div class="mt-6 space-y-3 text-sm text-gray-600">
                <p>• Event tanpa batas</p>
                <p>• Dashboard analytics lanjutan</p>
                <p>• Export data laporan</p>
                <p>• Custom branding ringan</p>
            </div>
            <div class="mt-6">
                <button class="w-full rounded-xl bg-brand-500 py-3 text-sm font-semibold text-white hover:bg-brand-600">Pilih Pro</button>
            </div>
        </div>

        <div class="pro-card p-6 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-widest bg-amber-100 text-amber-800">Premium</span>
            <h2 class="mt-5 text-xl font-semibold text-gray-900">Premium</h2>
            <p class="mt-2 text-3xl font-bold text-gray-900">Rp 999.000 / bulan</p>
            <p class="mt-3 text-sm text-gray-500">Untuk tim besar dan partner yang butuh dukungan khusus.</p>
            <div class="mt-6 space-y-3 text-sm text-gray-600">
                <p>• Dedicated support</p>
                <p>• Integrasi API dan pembayaran</p>
                <p>• Kuota tinggi & tim multi-user</p>
                <p>• Tools automasi</p>
            </div>
            <div class="mt-6">
                <button class="w-full rounded-xl bg-brand-500 py-3 text-sm font-semibold text-white hover:bg-brand-600">Hubungi Sales</button>
            </div>
        </div>
    </div>

    <div id="plans" class="pro-card p-6 border border-gray-200">
        <div class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-3xl bg-brand-600 p-6 text-white">
                <p class="text-xs uppercase tracking-[0.24em] text-brand-200 font-semibold">Kenapa Upgrade?</p>
                <h3 class="mt-4 text-2xl font-semibold">Tingkatkan hasil event Anda</h3>
                <p class="mt-3 text-sm text-brand-100">Paket berbayar membantu Anda mengelola lebih banyak event, mendapatkan data terbaik, dan memberi pengalaman peserta lebih profesional.</p>
            </div>
            <div class="rounded-3xl bg-white p-6 shadow-sm border border-gray-100">
                <h4 class="text-base font-semibold text-gray-900">Saran Penggunaan</h4>
                <ul class="mt-4 space-y-3 text-sm text-gray-600">
                    <li>• Basic untuk penyelenggara partai kecil.</li>
                    <li>• Pro untuk event dengan volume dan pelaporan intensif.</li>
                    <li>• Premium untuk partner dengan kebutuhan dukungan dan integrasi.</li>
                </ul>
            </div>
            <div class="rounded-3xl bg-white p-6 shadow-sm border border-gray-100">
                <h4 class="text-base font-semibold text-gray-900">Catatan</h4>
                <p class="mt-4 text-sm text-gray-600">Halaman ini masih untuk tampilan. Integrasi pembayaran dan logika langganan akan dibangun setelah desain paket disetujui.</p>
            </div>
        </div>
    </div>
</div>
@endsection
