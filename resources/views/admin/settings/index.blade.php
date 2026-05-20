@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola konfigurasi admin dan preferensi operasional.</p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            <div class="pro-card p-6">
                <h3 class="text-base font-semibold text-gray-900">Profil Organisasi</h3>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Nama Brand</label>
                        <input type="text" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm" placeholder="Atturin">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Email Admin</label>
                        <input type="email" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm" placeholder="admin@atturin.com">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Nomor Hotline</label>
                        <input type="text" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm" placeholder="+62 812 3456 7890">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Kota Operasional</label>
                        <input type="text" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm" placeholder="Jakarta">
                    </div>
                </div>
                <div class="mt-5 flex items-center justify-end gap-3">
                    <button class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600">Reset</button>
                    <button class="px-4 py-2 rounded-xl bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600">Simpan Perubahan</button>
                </div>
            </div>

            <div class="pro-card p-6">
                <h3 class="text-base font-semibold text-gray-900">Pembayaran & Iuran</h3>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Default Iuran</label>
                        <input type="text" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm" placeholder="50000">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Metode Pembayaran</label>
                        <select class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm">
                            <option>Transfer Bank</option>
                            <option>QRIS</option>
                            <option>Cash on Venue</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Catatan Pembayaran</label>
                        <textarea rows="3" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm" placeholder="Instruksi pembayaran terbaru"></textarea>
                    </div>
                </div>
                <div class="mt-5 flex items-center justify-end gap-3">
                    <button class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600">Batal</button>
                    <button class="px-4 py-2 rounded-xl bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600">Update Payment</button>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="pro-card p-6">
                <h3 class="text-base font-semibold text-gray-900">Notifikasi</h3>
                <div class="mt-4 space-y-3">
                    <label class="flex items-center justify-between text-sm text-gray-600">
                        Reminder pembayaran
                        <input type="checkbox" class="rounded border-gray-300 text-brand-500 focus:ring-brand-300" checked>
                    </label>
                    <label class="flex items-center justify-between text-sm text-gray-600">
                        Update event mingguan
                        <input type="checkbox" class="rounded border-gray-300 text-brand-500 focus:ring-brand-300" checked>
                    </label>
                    <label class="flex items-center justify-between text-sm text-gray-600">
                        Pengingat check-in
                        <input type="checkbox" class="rounded border-gray-300 text-brand-500 focus:ring-brand-300">
                    </label>
                </div>
            </div>

            <div class="pro-card p-6">
                <h3 class="text-base font-semibold text-gray-900">Keamanan</h3>
                <div class="mt-4 space-y-3">
                    <label class="flex items-center justify-between text-sm text-gray-600">
                        Aktifkan 2FA
                        <input type="checkbox" class="rounded border-gray-300 text-brand-500 focus:ring-brand-300">
                    </label>
                    <label class="flex items-center justify-between text-sm text-gray-600">
                        Login alert via email
                        <input type="checkbox" class="rounded border-gray-300 text-brand-500 focus:ring-brand-300" checked>
                    </label>
                    <label class="flex items-center justify-between text-sm text-gray-600">
                        Batasi akses IP
                        <input type="checkbox" class="rounded border-gray-300 text-brand-500 focus:ring-brand-300">
                    </label>
                </div>
                <button class="mt-4 w-full px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:border-brand-200">Kelola Security</button>
            </div>
        </div>
    </div>
</div>
@endsection
