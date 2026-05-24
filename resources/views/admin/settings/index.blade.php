@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola profil admin dan informasi penarikan dana.</p>
    </div>

    @if ($errors->any())
        <div class="pro-card p-5 border border-rose-200 bg-rose-50 text-rose-700">
            <ul class="space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        @csrf
        @method('PATCH')

        <div class="xl:col-span-2 space-y-6">
            <div class="pro-card p-6">
                <h3 class="text-base font-semibold text-gray-900">Profil Admin</h3>
                <p class="text-sm text-gray-500 mt-1">Isikan data admin dan komunitas yang akan tampil untuk member.</p>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Nama Admin</label>
                        <input type="text" name="name" value="{{ old('name', $admin->name) }}" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm" placeholder="Nama Admin" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Gmail</label>
                        <input type="email" name="email" value="{{ old('email', $admin->email) }}" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm" placeholder="admin@gmail.com" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Nomor</label>
                        <input type="text" name="phone" value="{{ old('phone', $admin->phone) }}" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm" placeholder="0812 3456 7890">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Nama Komunitas</label>
                        <input type="text" name="community_name" value="{{ old('community_name', $admin->community_name) }}" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm" placeholder="Nama Komunitas">
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <button type="reset" class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600">Reset</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-brand-500 text-white text-sm font-semibold hover:bg-brand-600">Simpan Profil</button>
                </div>
            </div>

            <div class="pro-card p-6">
                <h3 class="text-base font-semibold text-gray-900">Rekening & E-Wallet</h3>
                <p class="text-sm text-gray-500 mt-1">Pilih metode penarikan dana dan masukkan nomor rekening atau ID e-wallet.</p>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-1">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Metode Penarikan</label>
                        <select name="payment_method" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm">
                            <option value="">Pilih Metode</option>
                            @foreach($paymentMethods as $method => $label)
                                <option value="{{ $method }}" {{ old('payment_method', $admin->payment_method) === $method ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-1">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Rekening / ID E-Wallet</label>
                        <input type="text" name="payment_account" value="{{ old('payment_account', $admin->payment_account) }}" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm" placeholder="Nomor rekening atau ID e-wallet">
                    </div>
                </div>

                <div class="mt-5 text-sm text-gray-500">
                    <p>Contoh: nomor rekening BCA / OVO / DANA / GoPay / ShopeePay.</p>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="pro-card p-6">
                <h3 class="text-base font-semibold text-gray-900">Ringkasan Profil</h3>
                <div class="mt-5 space-y-4 text-sm text-gray-600">
                    <div class="rounded-2xl border border-gray-200 bg-white p-4">
                        <p class="font-semibold text-gray-900">Admin</p>
                        <p>{{ $admin->name }}</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-4">
                        <p class="font-semibold text-gray-900">Email</p>
                        <p>{{ $admin->email }}</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-4">
                        <p class="font-semibold text-gray-900">Komunitas</p>
                        <p>{{ $admin->community_name ?? '-' }}</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-4">
                        <p class="font-semibold text-gray-900">Rekening / Wallet</p>
                        <p>{{ $admin->payment_method ? ($paymentMethods[$admin->payment_method] ?? $admin->payment_method) : '-' }}</p>
                        <p class="text-xs text-gray-500">{{ $admin->payment_account ?? 'Belum diatur' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
