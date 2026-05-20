<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-xl font-bold text-white">Lupa Password?</h2>
        <p class="text-sm text-gray-400 mt-1">Masukkan email Anda dan kami akan mengirimkan link untuk reset password.</p>
    </div>

    {{-- Session Status --}}
    @if (session('status'))
        <div class="mb-4 flex items-center gap-2 bg-emerald-950/40 border border-emerald-800/60 text-emerald-300 px-4 py-3 rounded-xl text-sm font-medium backdrop-blur-md">
            <svg class="w-4 h-4 flex-shrink-0 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-300 mb-1.5">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   class="auth-input" placeholder="admin@atturin.com">
            @error('email')
                <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="pt-2 space-y-4">
            <button type="submit"
                    class="w-full py-3 rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-500 hover:to-brand-400 text-white font-semibold text-sm shadow-lg shadow-brand-600/30 transition-all duration-200 hover:shadow-xl hover:shadow-brand-500/40 hover:-translate-y-0.5 active:translate-y-0">
                Kirim Link Reset Password
            </button>
            <p class="text-center text-sm text-gray-400">
                Ingat password Anda?
                <a href="{{ route('login') }}" class="text-lime-400 hover:text-lime-300 font-semibold transition-colors">Kembali ke Login</a>
            </p>
        </div>
    </form>
</x-guest-layout>
