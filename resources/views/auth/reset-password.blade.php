<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-xl font-bold text-white">Reset Password</h2>
        <p class="text-sm text-gray-400 mt-1">Masukkan password baru untuk akun Anda.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        {{-- Password Reset Token --}}
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-300 mb-1.5">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                   class="auth-input" placeholder="admin@atturin.com">
            @error('email')
                <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-semibold text-gray-300 mb-1.5">Password Baru</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="auth-input" placeholder="Minimal 8 karakter">
            @error('password')
                <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-300 mb-1.5">Konfirmasi Password Baru</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="auth-input" placeholder="Ulangi password baru">
            @error('password_confirmation')
                <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="pt-2">
            <button type="submit"
                    class="w-full py-3 rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-500 hover:to-brand-400 text-white font-semibold text-sm shadow-lg shadow-brand-600/30 transition-all duration-200 hover:shadow-xl hover:shadow-brand-500/40 hover:-translate-y-0.5 active:translate-y-0">
                Reset Password
            </button>
        </div>
    </form>
</x-guest-layout>
