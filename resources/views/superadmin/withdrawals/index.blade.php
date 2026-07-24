<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Super Admin Dashboard — {{ config('app.name', 'Atturin') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo/Logo (Lettermark)/Primary Dark.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#F0F5FF',
                            100: '#E1E9FF',
                            500: '#0052FF',
                            600: '#0042CC',
                            700: '#003199',
                            900: '#0A1628',
                        },
                        violet: {
                            50: '#F5F3FF',
                            100: '#EDE9FE',
                            500: '#8B5CF6',
                            600: '#7C3AED',
                            700: '#6D28D9',
                        },
                        surface: '#F8FAFC',
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .sidebar { width: 280px; }
        .main-content { margin-left: 280px; }
        .sidebar-link {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 18px; border-radius: 12px;
            font-size: 15px; font-weight: 500; color: #94A3B8;
            transition: all 0.2s ease;
            position: relative;
        }
        .sidebar-link:hover { background: rgba(255, 255, 255, 0.05); color: #fff; }
        .sidebar-link.active {
            background: rgba(255, 255, 255, 0.1); color: #fff; font-weight: 600;
        }
        .sidebar-link.active::before {
            content: '';
            position: absolute; left: -12px; top: 8px; bottom: 8px;
            width: 4px; border-radius: 0 4px 4px 0;
            background: #8B5CF6;
        }
        .pro-card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .animate-fade-in {
            animation: fadeIn 0.4s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-surface text-slate-800 antialiased min-h-screen">

    {{-- Sidebar --}}
    <aside class="sidebar fixed top-0 left-0 bottom-0 bg-[#0A1628] border-r border-slate-800 flex flex-col z-40">
        <div class="px-7 pt-7 pb-5">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/Logo/Logo (Lettermark)/Secondary.png') }}" class="h-8 object-contain" alt="Logo">
                <div>
                    <h1 class="text-white text-base font-bold tracking-wide">Atturin HQ</h1>
                    <span class="text-violet-400 text-[10px] uppercase font-bold tracking-widest">Super Admin</span>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-4 py-4 space-y-1">
            <a href="{{ route('superadmin.withdrawals.index') }}" class="sidebar-link {{ request()->routeIs('superadmin.withdrawals.index') ? 'active' : '' }}">
                <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Penarikan Dana (Withdraw)
            </a>
            <a href="{{ route('superadmin.waitlist-reminders.index') }}" class="sidebar-link {{ request()->routeIs('superadmin.waitlist-reminders.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h.01M12 10h.01M16 10h.01M9 16h6M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
                Reminder Waiting List
            </a>
            <a href="{{ route('superadmin.payment-reminders.index') }}" class="sidebar-link {{ request()->routeIs('superadmin.payment-reminders.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Reminder Pembayaran
            </a>
        </nav>

        <div class="px-5 pb-7 pt-4 border-t border-slate-800">
            <div class="flex items-center gap-3 mb-4 px-2">
                <div class="w-10 h-10 rounded-full bg-violet-600 flex items-center justify-center text-white font-bold text-sm">
                    SA
                </div>
                <div class="overflow-hidden">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl border border-slate-700 text-sm font-semibold text-slate-300 hover:border-rose-500 hover:text-rose-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 01-3-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="main-content min-h-screen p-8 lg:p-10 flex flex-col gap-6 animate-fade-in">
        
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Kelola Penarikan Dana</h2>
                <p class="text-sm text-slate-500 mt-1">Konfirmasi bukti transfer manual dan proses dana para admin/komunitas.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-semibold text-slate-600 tracking-wide uppercase">Sistem Siap Operasional</span>
            </div>
        </div>

        {{-- Toast Notifications --}}
        @if(session('success'))
            <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3.5 rounded-xl text-sm shadow-sm" role="alert">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3.5 rounded-xl text-sm shadow-sm" role="alert">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Metrics Summary Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="pro-card p-5 bg-gradient-to-br from-white to-slate-50/50">
                <div class="flex justify-between items-start">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Menunggu Diproses</span>
                    <span class="p-1.5 rounded-lg bg-amber-50 text-amber-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="text-2xl font-bold text-slate-900 mt-2">{{ $stats['total_pending_count'] }} Request</p>
                <p class="text-xs font-medium text-amber-600 mt-1">Pending approval</p>
            </div>
            
            <div class="pro-card p-5 bg-gradient-to-br from-white to-slate-50/50">
                <div class="flex justify-between items-start">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nominal Pending</span>
                    <span class="p-1.5 rounded-lg bg-orange-50 text-orange-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </span>
                </div>
                <p class="text-2xl font-bold text-slate-900 mt-2">Rp {{ number_format($stats['total_pending_amount'], 0, ',', '.') }}</p>
                <p class="text-xs font-medium text-slate-500 mt-1">Estimasi total transfer</p>
            </div>

            <div class="pro-card p-5 bg-gradient-to-br from-white to-slate-50/50">
                <div class="flex justify-between items-start">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Selesai Ditransfer</span>
                    <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="text-2xl font-bold text-slate-900 mt-2">{{ $stats['total_completed_count'] }} Transaksi</p>
                <p class="text-xs font-medium text-emerald-600 mt-1">Berhasil dibayarkan</p>
            </div>

            <div class="pro-card p-5 bg-gradient-to-br from-white to-slate-50/50">
                <div class="flex justify-between items-start">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Dana Ditarik</span>
                    <span class="p-1.5 rounded-lg bg-violet-50 text-violet-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="text-2xl font-bold text-slate-900 mt-2">Rp {{ number_format($stats['total_completed_amount'], 0, ',', '.') }}</p>
                <p class="text-xs font-medium text-slate-500 mt-1">Dana keluar dari sistem</p>
            </div>
        </div>

        {{-- Filters & Content Table Card --}}
        <div class="pro-card overflow-hidden">
            
            {{-- Tabs --}}
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/40">
                <div class="flex flex-wrap gap-1">
                    <a href="{{ route('superadmin.withdrawals.index', ['status' => 'all']) }}" 
                       class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $status === 'all' ? 'bg-[#0A1628] text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                        Semua Request
                    </a>
                    <a href="{{ route('superadmin.withdrawals.index', ['status' => 'pending']) }}" 
                       class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $status === 'pending' ? 'bg-amber-500 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                        Pending ({{ $stats['total_pending_count'] }})
                    </a>
                    <a href="{{ route('superadmin.withdrawals.index', ['status' => 'completed']) }}" 
                       class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $status === 'completed' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                        Selesai ({{ $stats['total_completed_count'] }})
                    </a>
                    <a href="{{ route('superadmin.withdrawals.index', ['status' => 'failed']) }}" 
                       class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $status === 'failed' ? 'bg-rose-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                        Ditolak / Gagal
                    </a>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr class="bg-slate-50/20 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                            <th class="px-6 py-3 text-left">Nama Admin / Komunitas</th>
                            <th class="px-6 py-3 text-left">Jumlah Penarikan</th>
                            <th class="px-6 py-3 text-left">Tujuan Transfer</th>
                            <th class="px-6 py-3 text-center">Waktu Request</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-left">Catatan Internal</th>
                            <th class="px-6 py-3 text-center">Aksi Operasional</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($withdrawals as $w)
                            <tr class="hover:bg-slate-50/30 transition-colors text-sm">
                                
                                {{-- User Info --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-violet-50 text-violet-600 flex items-center justify-center font-bold text-sm">
                                            {{ strtoupper(substr($w->admin->name ?? 'A', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800">{{ $w->admin->name ?? 'Admin Terhapus' }}</p>
                                            <p class="text-xs text-slate-400">{{ $w->admin->email ?? '-' }}</p>
                                            <p class="text-[10px] text-slate-400 font-medium">Komunitas: {{ $w->admin->community_name ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Amount --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-extrabold text-slate-900 text-base">Rp {{ number_format($w->amount, 0, ',', '.') }}</span>
                                </td>

                                {{-- Destination --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="inline-flex flex-col">
                                        <span class="px-2 py-0.5 rounded bg-violet-100 text-violet-800 text-[10px] font-bold uppercase tracking-wider self-start mb-1">
                                            {{ $w->payment_method ?: 'Bank Transfer' }}
                                        </span>
                                        <span class="font-semibold text-slate-700 select-all">{{ $w->payment_account ?: '-' }}</span>
                                    </div>
                                </td>

                                {{-- Requested At --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center text-slate-500">
                                    <p class="text-xs font-semibold">{{ $w->requested_at ? $w->requested_at->format('d M Y') : '-' }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $w->requested_at ? $w->requested_at->format('H:i') : '-' }} WIB</p>
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($w->status === 'pending')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 border border-amber-200 text-amber-600">Pending</span>
                                    @elseif($w->status === 'completed')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 border border-emerald-200 text-emerald-700">Sukses</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 border border-rose-200 text-rose-700">Ditolak</span>
                                    @endif
                                </td>

                                {{-- Notes --}}
                                <td class="px-6 py-4">
                                    <p class="text-xs text-slate-500 max-w-[200px] break-words">{{ $w->note ?: '-' }}</p>
                                    @if($w->status !== 'pending' && $w->processed_at)
                                        <p class="text-[9px] text-slate-400 mt-1 font-medium">Diproses: {{ $w->processed_at->format('d M, H:i') }} WIB</p>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($w->status === 'pending')
                                        <div class="flex items-center justify-center gap-2">
                                            {{-- Approve Button triggering Modal --}}
                                            <button onclick="openActionModal('approve', {{ $w->id }}, '{{ number_format($w->amount, 0, ',', '.') }}', '{{ $w->payment_method }}', '{{ $w->payment_account }}')"
                                                    class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-3.5 py-2 rounded-xl transition-all shadow-md shadow-emerald-500/10">
                                                Setujui
                                            </button>
                                            
                                            {{-- Reject Button triggering Modal --}}
                                            <button onclick="openActionModal('reject', {{ $w->id }}, '{{ number_format($w->amount, 0, ',', '.') }}', '{{ $w->payment_method }}', '{{ $w->payment_account }}')"
                                                    class="inline-flex items-center gap-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs px-3.5 py-2 rounded-xl transition-all">
                                                Tolak
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 font-medium">Selesai Diproses</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-medium">
                                    Tidak ada permintaan penarikan dana ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($withdrawals->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/20">
                    {{ $withdrawals->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Universal Action Modal --}}
    <div id="action-modal" class="fixed inset-0 bg-[#0A1628]/60 backdrop-blur-sm z-50 items-center justify-center hidden">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl p-7 max-w-md w-full mx-4 relative transform transition-all duration-300 scale-95 opacity-0" id="modal-container">
            <h3 class="text-lg font-bold text-slate-900" id="modal-title">Konfirmasi Aksi</h3>
            <p class="text-xs text-slate-400 mt-1 mb-4">Pastikan Anda telah melakukan transfer secara manual di perbankan Anda sebelum menyetujui.</p>
            
            {{-- Info Box --}}
            <div class="rounded-2xl bg-slate-50 border border-slate-200/60 p-4 space-y-2.5 text-xs mb-5">
                <div class="flex justify-between">
                    <span class="text-slate-500">Jumlah Transfer:</span>
                    <span class="font-bold text-slate-900" id="modal-amount">Rp 0</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Metode Tujuan:</span>
                    <span class="font-semibold text-slate-800" id="modal-dest-method">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Nomor Rekening/Wallet:</span>
                    <span class="font-bold text-violet-600 select-all" id="modal-dest-account">-</span>
                </div>
            </div>

            <form method="POST" action="" id="modal-form" class="space-y-4">
                @csrf
                <div>
                    <label for="modal-note" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2" id="note-label">Keterangan Transfer / Catatan Internal</label>
                    <textarea name="note" id="modal-note" rows="3" required
                              class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 placeholder-slate-400 outline-none transition-all resize-none"
                              placeholder="Masukkan detail transfer atau alasan penolakan..."></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="closeActionModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-500 hover:text-slate-700 transition-colors">
                        Batal
                    </button>
                    <button type="submit" id="modal-submit-btn" class="px-6 py-2.5 rounded-xl text-white font-bold text-sm shadow-lg transition-all">
                        Tandai Selesai
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openActionModal(type, withdrawalId, amount, destMethod, destAccount) {
            const modal = document.getElementById('action-modal');
            const container = document.getElementById('modal-container');
            const title = document.getElementById('modal-title');
            const noteLabel = document.getElementById('note-label');
            const textarea = document.getElementById('modal-note');
            const submitBtn = document.getElementById('modal-submit-btn');
            const form = document.getElementById('modal-form');

            // Set info details
            document.getElementById('modal-amount').innerText = 'Rp ' + amount;
            document.getElementById('modal-dest-method').innerText = destMethod;
            document.getElementById('modal-dest-account').innerText = destAccount;

            if (type === 'approve') {
                title.innerText = 'Setujui & Selesaikan Penarikan';
                noteLabel.innerText = 'Keterangan Transfer (Opsional)';
                textarea.placeholder = 'Cth: Berhasil ditransfer via Bank BCA Ref: 8292837';
                textarea.required = false;
                
                submitBtn.innerText = 'Ya, Setujui';
                submitBtn.className = 'px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-lg shadow-emerald-500/10 transition-all';
                form.action = `/superadmin/withdrawals/${withdrawalId}/approve`;
            } else {
                title.innerText = 'Tolak Permintaan Penarikan';
                noteLabel.innerText = 'Alasan Penolakan (Wajib)';
                textarea.placeholder = 'Cth: Rekening tidak valid atau tidak ditemukan...';
                textarea.required = true;

                submitBtn.innerText = 'Ya, Tolak';
                submitBtn.className = 'px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm shadow-lg shadow-rose-500/10 transition-all';
                form.action = `/superadmin/withdrawals/${withdrawalId}/reject`;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                container.classList.remove('scale-95', 'opacity-0');
                container.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeActionModal() {
            const modal = document.getElementById('action-modal');
            const container = document.getElementById('modal-container');
            
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.getElementById('modal-form').reset();
            }, 150);
        }
    </script>
</body>
</html>
