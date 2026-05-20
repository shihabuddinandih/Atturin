<x-guest-layout>
    <div>
        <h2 class="text-2xl font-bold text-white">Buat Akun Baru</h2>
        <p class="text-sm text-gray-400 mt-1">Daftar untuk menggunakan {{ config('app.name', 'Atturin') }}.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf

        {{-- Name --}}
        <div>
            <label for="name" class="text-sm font-semibold text-gray-300">Nama Lengkap</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="auth-input mt-2" placeholder="Masukkan nama lengkap">
            @error('name')
                <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="text-sm font-semibold text-gray-300">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                   class="auth-input mt-2" placeholder="nama@email.com">
            @error('email')
                <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
            @enderror
        </div>

        {{-- Role Selection --}}
        <div>
            <label class="text-sm font-semibold text-gray-300 mb-2 block">Pilih Peran</label>
            <div class="space-y-3">
                <label class="flex items-start p-4 border-2 border-white/10 bg-white/5 rounded-xl cursor-pointer transition-all duration-200 hover:border-lime-400/50 hover:bg-white/10 @if(old('role') === 'admin') border-lime-400 bg-lime-400/10 @endif">
                    <input type="radio" name="role" value="admin" {{ old('role') === 'admin' ? 'checked' : '' }} class="mt-0.5 w-5 h-5 text-lime-400 bg-white/5 border-white/20 focus:ring-offset-0 focus:ring-lime-400/30" required>
                    <div class="ms-3 flex-1">
                        <p class="font-semibold text-white">Admin/Pengelola</p>
                        <p class="text-xs text-gray-400 mt-0.5">Buat dan kelola event</p>
                    </div>
                </label>
                <label class="flex items-start p-4 border-2 border-white/10 bg-white/5 rounded-xl cursor-pointer transition-all duration-200 hover:border-lime-400/50 hover:bg-white/10 @if(old('role') === 'player') border-lime-400 bg-lime-400/10 @endif">
                    <input type="radio" name="role" value="player" {{ old('role') === 'player' ? 'checked' : '' }} class="mt-0.5 w-5 h-5 text-lime-400 bg-white/5 border-white/20 focus:ring-offset-0 focus:ring-lime-400/30" required>
                    <div class="ms-3 flex-1">
                        <p class="font-semibold text-white">Player</p>
                        <p class="text-xs text-gray-400 mt-0.5">Bergabung dan ikuti event</p>
                    </div>
                </label>
            </div>
            @error('role')
                <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="text-sm font-semibold text-gray-300">Password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="auth-input mt-2" placeholder="Minimal 8 karakter">
            @error('password')
                <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div>
            <label for="password_confirmation" class="text-sm font-semibold text-gray-300">Konfirmasi Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="auth-input mt-2" placeholder="Ulangi password">
            @error('password_confirmation')
                <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit Button --}}
        <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-500 hover:to-brand-400 text-white font-semibold text-sm shadow-lg shadow-brand-600/30 transition-all duration-200 hover:shadow-xl hover:shadow-brand-500/40 hover:-translate-y-0.5 active:translate-y-0 mt-6">
            Daftar
        </button>

        <div class="text-center text-sm text-gray-400">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-lime-400 hover:text-lime-300 font-semibold transition-colors">Masuk di sini</a>
        </div>
    </form>
</x-guest-layout>
