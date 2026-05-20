<x-guest-layout>
    <div>
        <h2 class="text-2xl font-bold text-white">Masuk</h2>
        <p class="text-sm text-gray-400 mt-1">Masukkan kredensial Anda untuk lanjut.</p>
    </div>

    <form action="{{ route('login') }}" method="POST" class="mt-8 space-y-5">
        @csrf

        @if($errors->any())
            <div class="flex items-center gap-2 bg-rose-950/40 border border-rose-800/60 text-rose-300 px-4 py-3 rounded-xl text-sm font-medium backdrop-blur-md">
                <svg class="w-4 h-4 flex-shrink-0 text-rose-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                {{ $errors->first() }}
            </div>
        @endif

        <div>
            <label for="email" class="text-sm font-semibold text-gray-300">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                   class="auth-input mt-2" placeholder="nama@email.com">
            @error('email')
                <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="text-sm font-semibold text-gray-300">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required
                   class="auth-input mt-2" placeholder="Masukkan password">
            @error('password')
                <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-gray-300 cursor-pointer">
                <input type="checkbox" name="remember" class="rounded border-white/10 bg-white/5 text-lime-400 focus:ring-lime-400/30">
                Ingat saya
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-lime-400 hover:text-lime-300 font-medium transition-colors">Lupa password?</a>
            @endif
        </div>

        <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-500 hover:to-brand-400 text-white font-semibold text-sm shadow-lg shadow-brand-600/30 transition-all duration-200 hover:shadow-xl hover:shadow-brand-500/40 hover:-translate-y-0.5 active:translate-y-0">
            Masuk
        </button>
    </form>

    <div class="mt-6 text-center text-sm text-gray-400">
        Belum punya akun?
        @if (Route::has('register'))
            <a href="{{ route('register') }}" class="text-lime-400 hover:text-lime-300 font-semibold transition-colors">Daftar di sini</a>
        @endif
    </div>
</x-guest-layout>
