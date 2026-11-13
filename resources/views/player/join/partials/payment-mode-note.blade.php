{{-- Penjelasan alur pembayaran event untuk calon peserta --}}
@php $compact = $compact ?? false; @endphp
@if($event->requiresPaymentFirst())
    <div class="{{ $compact ? 'mt-2' : 'mb-4' }} flex items-start gap-2 rounded-xl border border-brand-500/20 bg-brand-500/5 px-3 py-2.5 text-xs text-gray-700">
        <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        <span><strong>Bayar dulu untuk mengamankan slot.</strong> Setelah klik daftar, slot ditahan {{ \App\Models\Event::HOLD_MINUTES }} menit selama Anda membayar via QRIS. Jika tidak dibayar, pendaftaran batal otomatis.</span>
    </div>
@else
    <div class="{{ $compact ? 'mt-2' : 'mb-4' }} flex items-start gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-xs text-emerald-900">
        <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
        <span><strong>Daftar sekarang, bayar menyusul.</strong> Link pelunasan QRIS dikirim ke WhatsApp Anda. Pastikan nomor aktif dan benar; pendaftaran dengan nomor tidak valid akan digugurkan.</span>
    </div>
@endif
