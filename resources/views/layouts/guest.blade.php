<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Atturin') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo/Logo (Lettermark)/Primary Dark.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#E6EEFF',
                            100: '#CCDDFF',
                            200: '#99BBFF',
                            300: '#6699FF',
                            400: '#3377FF',
                            500: '#0052FF',
                            600: '#0042CC',
                            700: '#003199',
                            800: '#002166',
                            900: '#0A1628',
                        },
                        lime: {
                            400: '#ABD600',
                            500: '#9BC200',
                        },
                        surface: '#EEF1F6',
                    },
                    fontFamily: {
                        sans: ['Lexend', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Lexend', sans-serif; }

        /* Premium auth input styling (Dark Glass) */
        .auth-input {
            width: 100%;
            border-radius: 12px;
            border: 1.5px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.03);
            padding: 12px 16px;
            font-size: 14px;
            color: #FFFFFF;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .auth-input::placeholder { color: #6B7280; }
        .auth-input:focus {
            outline: none;
            border-color: #ABD600;
            background: rgba(255, 255, 255, 0.07);
            box-shadow: 0 0 0 4px rgba(171, 214, 0, 0.15), 0 1px 3px rgba(171, 214, 0, 0.1);
        }
        .auth-input:hover:not(:focus) {
            border-color: rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.06);
        }

        /* Floating mesh orbs animation */
        @keyframes floatOrb {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(30px, -20px) scale(1.05); }
            50% { transform: translate(-10px, 20px) scale(0.95); }
            75% { transform: translate(-30px, -10px) scale(1.02); }
        }
        .orb { animation: floatOrb 15s ease-in-out infinite; }
        .orb-delay-1 { animation-delay: -3s; }
        .orb-delay-2 { animation-delay: -7s; }
        .orb-delay-3 { animation-delay: -11s; }

        /* Card entrance animation */
        @keyframes cardEntrance {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card-animate { animation: cardEntrance 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards; }

        /* Logo pulse */
        @keyframes logoPulse {
            0%, 100% { filter: drop-shadow(0 0 0 transparent); }
            50% { filter: drop-shadow(0 4px 20px rgba(171, 214, 0, 0.15)); }
        }
        .logo-animate { animation: logoPulse 3s ease-in-out infinite; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-8 bg-[#070913] text-white">
    {{-- Decorative Background Mesh --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
        {{-- Base gradient --}}
        <div class="absolute inset-0 bg-gradient-to-b from-[#070913] via-[#0A0D1E] to-[#070913]"></div>

        {{-- Animated gradient orbs --}}
        <div class="orb absolute -top-20 -right-20 w-[400px] h-[400px] rounded-full bg-gradient-to-br from-brand-500/10 to-brand-700/5 blur-[80px]"></div>
        <div class="orb orb-delay-1 absolute bottom-[-5%] left-[-5%] w-[350px] h-[350px] rounded-full bg-gradient-to-tr from-brand-600/10 to-purple-600/5 blur-[70px]"></div>
        <div class="orb orb-delay-2 absolute top-1/3 right-1/4 w-[250px] h-[250px] rounded-full bg-gradient-to-br from-lime-500/10 to-transparent blur-[60px]"></div>
        <div class="orb orb-delay-3 absolute top-[60%] left-[40%] w-[180px] h-[180px] rounded-full bg-gradient-to-br from-indigo-500/10 to-transparent blur-[50px]"></div>

        {{-- Subtle grid pattern --}}
        <div class="absolute inset-0 opacity-[0.02]" style="background-image: radial-gradient(circle, #ABD600 1px, transparent 1px); background-size: 40px 40px;"></div>
    </div>

    <div class="relative w-full max-w-md card-animate">
        {{-- Logo / Branding --}}
        <div class="text-center mb-8">
            <a href="/" class="inline-block logo-animate">
                <img src="{{ asset('images/Logo/Vertical/Secondary.png') }}" class="h-20 object-contain mx-auto" alt="{{ config('app.name', 'Atturin') }}">
            </a>
        </div>

        {{-- Card Container --}}
        <div class="bg-[#0B132B]/60 backdrop-blur-xl border border-white/10 rounded-3xl shadow-[0_24px_80px_rgba(0,0,0,0.4)] p-8 relative overflow-hidden">
            {{-- Subtle top accent line --}}
            <div class="absolute top-0 left-8 right-8 h-[2px] bg-gradient-to-r from-transparent via-lime-400/40 to-transparent rounded-full"></div>

            {{ $slot }}
        </div>

        {{-- Footer --}}
        <p class="text-center text-xs text-gray-500 mt-6">
            &copy; {{ date('Y') }} {{ config('app.name', 'Atturin') }}. All rights reserved.
        </p>
    </div>
</body>
</html>
