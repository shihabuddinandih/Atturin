@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    {{-- Breadcrumb + Header --}}
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
            <a href="{{ route('admin.events.index') }}" class="hover:text-brand-500 transition-colors">Dashboard</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-600">Detail Event</span>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Pertandingan</h1>
                <p class="text-sm text-gray-500 mt-1">Panel operasional realtime untuk monitor peserta dan iuran.</p>
            </div>
            <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Live Mode ON
            </span>
        </div>
    </div>

    {{-- Event Info Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Nama Event</p>
            <p class="text-base font-bold text-gray-900 mt-1">{{ $event->nama_event }}</p>
        </div>
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Jadwal</p>
            <p class="text-base font-bold text-gray-900 mt-1">{{ \Carbon\Carbon::parse($event->tanggal)->format('d M Y') }} — {{ $event->waktu }}</p>
        </div>
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Tempat</p>
            <p class="text-base font-bold text-gray-900 mt-1">{{ $event->tempat }}</p>
        </div>
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Skema & Pembayaran</p>
            <p class="text-base font-bold text-gray-900 mt-1">
                @if($event->skema_iuran === 'loyalitas')
                    Subsidi Silang (Loyalty Split)
                @elseif($event->skema_iuran === 'custom')
                    Custom (Per Role)
                @else
                    Bagi Rata (Flat Split)
                @endif
                <span class="text-xs font-normal text-gray-400 block mt-0.5">
                    Metode: {{ $event->metode_pembayaran === 'online_banking' ? 'Online Banking' : 'Tunai' }}
                </span>
            </p>
            @if($event->skema_iuran === 'loyalitas')
                <p class="text-xs text-amber-600 font-semibold mt-1">
                    Total Biaya Event: Rp {{ number_format((float) $event->biaya_total_event, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">
                    Iuran per pemain: Rp {{ number_format((float) $event->iuran_per_pemain, 0, ',', '.') }}
                    @if($event->metode_pembayaran === 'online_banking')
                        <span class="text-amber-500">(belum termasuk biaya admin)</span>
                    @endif
                </p>
            @elseif($event->skema_iuran === 'custom')
                @php
                    $roles = collect($event->roles ?? []);
                @endphp
                @if($roles->isNotEmpty())
                    <div class="mt-2 space-y-1">
                        @foreach($roles as $role)
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-600 font-medium">{{ $role['name'] }}
                                    <span class="text-gray-400 font-normal">({{ $role['slots'] }} slot)</span>
                                </span>
                                <span class="font-semibold text-gray-800">Rp {{ number_format((float)($role['price'] ?? 0), 0, ',', '.') }}
                                    @if($event->metode_pembayaran === 'online_banking')
                                        <span class="text-amber-500 font-normal">+admin</span>
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                    @if($event->metode_pembayaran === 'online_banking')
                        <p class="text-[10px] text-amber-500 mt-1.5">* Harga di atas belum termasuk biaya admin sistem</p>
                    @endif
                @endif
            @else
                <p class="text-xs text-gray-500 mt-1">
                    Iuran per pemain:
                    <span class="font-semibold text-gray-800">Rp {{ number_format((float) $event->iuran_per_pemain, 0, ',', '.') }}</span>
                    @if($event->metode_pembayaran === 'online_banking')
                        <span class="text-amber-500">(belum termasuk biaya admin)</span>
                    @endif
                </p>
            @endif
        </div>
    </div>

    @if(!empty($event->facilities) && is_array($event->facilities))
        <div class="pro-card p-5">
            <div class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Fasilitas Event</div>
            <div class="flex flex-wrap gap-2">
                @foreach($event->facilities as $facility)
                    <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 border border-brand-100">{{ $facility }}</span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Join Link --}}
    <div class="pro-card p-5 border-l-4 border-l-brand-500">
        <div class="text-[11px] font-bold uppercase tracking-wider text-brand-500 mb-2">Join Link</div>
        <div class="flex flex-col sm:flex-row gap-2">
            <input type="text" readonly value="{{ route('player.join.show', $event->slug) }}" class="flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm bg-gray-50 text-gray-700" id="share-link">
            <button type="button" onclick="copyLink()" class="inline-flex items-center justify-center gap-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold py-2.5 px-5 rounded-xl text-sm transition-colors whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                Copy Link
            </button>
        </div>
    </div>

    {{-- Visibility Settings --}}
    <div class="pro-card p-5">
        <div class="text-[11px] font-bold uppercase tracking-wider text-violet-600 mb-3">Pengaturan Visibilitas Publik</div>
        <form action="{{ route('admin.events.updateJoinVisibility', $event->id) }}" method="POST" class="space-y-3">
            @csrf
            <input type="hidden" name="show_joined_players_public" value="0">
            <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 hover:border-brand-200 cursor-pointer transition-all">
                <input type="checkbox" name="show_joined_players_public" id="detail_show_joined_players_public" value="1" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500" {{ $event->show_joined_players_public ? 'checked' : '' }}>
                <span>
                    <span class="block text-sm font-medium text-gray-800">Publik bisa melihat daftar peserta join</span>
                    <span class="block text-xs text-gray-400 mt-0.5">Aktifkan agar peserta baru bisa melihat member yang sudah join.</span>
                </span>
            </label>
            <input type="hidden" name="show_joined_player_contacts_public" value="0">
            <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 hover:border-brand-200 cursor-pointer transition-all">
                <input type="checkbox" name="show_joined_player_contacts_public" id="detail_show_joined_player_contacts_public" value="1" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500" {{ $event->show_joined_player_contacts_public ? 'checked' : '' }}>
                <span>
                    <span class="block text-sm font-medium text-gray-800">Publik bisa melihat kontak peserta</span>
                    <span class="block text-xs text-gray-400 mt-0.5">Jika nonaktif, kontak peserta akan disembunyikan.</span>
                </span>
            </label>
            <button type="submit" class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl transition-colors">
                Simpan Pengaturan
            </button>
        </form>
    </div>

    {{-- Metric Cards --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Pendaftar</p>
            <p id="metric-joined" class="text-2xl font-bold text-gray-900 mt-1">{{ $livePayload['metrics']['joined_count'] }}</p>
            <p class="text-xs text-gray-400">dari {{ $livePayload['metrics']['slot_max'] }} slot</p>
        </div>
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Lunas</p>
            <p id="metric-paid" class="text-2xl font-bold text-emerald-600 mt-1">{{ $livePayload['metrics']['paid_count'] }}</p>
            <p class="text-xs text-gray-400">peserta lunas</p>
        </div>
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Belum Dibayar</p>
            <p id="metric-pending" class="text-2xl font-bold text-amber-600 mt-1">{{ $livePayload['metrics']['pending_count'] }}</p>
            <p class="text-xs text-gray-400">menunggu pembayaran</p>
        </div>
        <div class="pro-card p-5">
            <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Total Pembayaran</p>
            <p id="metric-collected" class="text-xl font-bold text-gray-900 mt-1">Rp {{ number_format((float) $livePayload['metrics']['total_collected'], 0, ',', '.') }} <span class="text-gray-500 font-normal">/ {{ number_format((float) $event->biaya_total_event, 0, ',', '.') }}</span></p>
            <p class="text-xs text-gray-400">total pembayaran</p>
        </div>
    </div>    {{-- Participants Table --}}
    <div class="pro-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Daftar Peserta</h3>
                <p class="text-xs text-gray-400 mt-0.5">Update status join, kehadiran, dan pembayaran tanpa refresh.</p>
            </div>
            <div class="text-xs text-gray-400 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Sync: <span id="live-updated-at">-</span>
            </div>
        </div>

        <div id="ajax-feedback" class="hidden px-6 py-3 text-sm"></div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Peserta</th>
                        <th class="px-6 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Waktu Join</th>
                        <th class="px-6 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Status Join</th>
                        <th class="px-6 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Hadir</th>
                        <th class="px-6 py-3 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Pembayaran</th>
                    </tr>
                </thead>
                <tbody id="players-table-body" class="divide-y divide-gray-50">
                    @forelse($livePayload['players'] as $index => $player)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-brand-50 flex items-center justify-center text-brand-600 text-xs font-bold">{{ strtoupper(substr($player['nama'], 0, 1)) }}</div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">{{ $player['nama'] }}</p>
                                        <p class="text-xs text-gray-400">{{ $player['kontak'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-400 text-center">{{ $player['joined_at_human'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <form action="{{ route('admin.events.updateStatus', [$event->id, $player['id']]) }}" method="POST" data-ajax="true">
                                    @csrf
                                    <select name="status_join" class="text-xs rounded-lg border-gray-200 py-1.5 {{ $player['status_join'] === 'joined' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                        <option value="joined" {{ $player['status_join'] === 'joined' ? 'selected' : '' }}>Joined</option>
                                        <option value="batal" {{ $player['status_join'] === 'batal' ? 'selected' : '' }}>Batal</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <form action="{{ route('admin.events.updateAttendance', [$event->id, $player['id']]) }}" method="POST" data-ajax="true">
                                    @csrf
                                    <input type="checkbox" name="hadir" value="1" class="h-5 w-5 text-brand-500 rounded border-gray-300 focus:ring-brand-500 cursor-pointer" {{ $player['hadir'] ? 'checked' : '' }}>
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-xs text-gray-600">
                                <div class="mb-2">{{ $player['payment_method_label'] }} <span class="font-semibold">- Rp {{ number_format((float) $player['payment_amount'], 0, ',', '.') }}</span></div>
                                <form action="{{ route('admin.events.updatePayment', [$event->id, $player['id']]) }}" method="POST" data-ajax="true" class="mb-1">
                                    @csrf
                                    <select name="payment_status" class="text-xs rounded-lg border-gray-200 py-1.5 {{ $player['payment_status'] === 'paid' ? 'bg-emerald-50 text-emerald-700' : ($player['payment_status'] === 'failed' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                                        <option value="pending" {{ $player['payment_status'] === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="paid" {{ $player['payment_status'] === 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="failed" {{ $player['payment_status'] === 'failed' ? 'selected' : '' }}>Failed</option>
                                    </select>
                                </form>
                                @if($player['payment_reference'])
                                    <div class="text-[11px] text-gray-400">Ref: {{ $player['payment_reference'] }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">Belum ada pemain yang bergabung.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const liveUrl = @json(route('admin.events.live', $event->id));
    const statusUrlTemplate = @json(route('admin.events.updateStatus', [$event->id, '__PLAYER__']));
    const attendanceUrlTemplate = @json(route('admin.events.updateAttendance', [$event->id, '__PLAYER__']));
    const paymentUrlTemplate = @json(route('admin.events.updatePayment', [$event->id, '__PLAYER__']));
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const playersBody = document.getElementById('players-table-body');
    const feedback = document.getElementById('ajax-feedback');

    function rupiah(v) { return new Intl.NumberFormat('id-ID').format(Number(v||0)); }
    function ep(t,id) { return t.replace('__PLAYER__',id); }
    function esc(v) { return String(v||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }
    function pc(s) { return s==='paid'?'bg-emerald-50 text-emerald-700':s==='failed'?'bg-rose-50 text-rose-700':'bg-amber-50 text-amber-700'; }
    function jc(s) { return s==='joined'?'bg-emerald-50 text-emerald-700':'bg-rose-50 text-rose-700'; }

    function showFeedback(msg,type) {
        if(!msg) return;
        feedback.classList.remove('hidden','bg-emerald-50','text-emerald-700','border-emerald-200','bg-rose-50','text-rose-700','border-rose-200');
        feedback.classList.add('border');
        feedback.classList.add(type==='error'?'bg-rose-50':'bg-emerald-50');
        feedback.classList.add(type==='error'?'text-rose-700':'text-emerald-700');
        feedback.classList.add(type==='error'?'border-rose-200':'border-emerald-200');
        feedback.textContent=msg;
        clearTimeout(showFeedback.t);
        showFeedback.t=setTimeout(()=>feedback.classList.add('hidden'),2200);
    }

    function renderPlayers(players) {
        if(!players||!players.length){playersBody.innerHTML='<tr><td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">Belum ada peserta yang bergabung.</td></tr>';return;}
        playersBody.innerHTML=players.map((p,i)=>{
            const ref=p.payment_reference?'<div class="text-[11px] text-gray-400">Ref: '+esc(p.payment_reference)+'</div>':'';
            return '<tr class="hover:bg-gray-50/50 transition-colors">'
            +'<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">'+(i+1)+'</td>'
            +'<td class="px-6 py-4 whitespace-nowrap"><div class="flex items-center gap-3"><div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 text-xs font-bold">'+esc(p.nama).charAt(0).toUpperCase()+'</div><div><p class="text-sm font-semibold text-gray-800">'+esc(p.nama)+'</p><p class="text-xs text-gray-400">'+esc(p.kontak)+'</p></div></div></td>'
            +'<td class="px-6 py-4 whitespace-nowrap text-xs text-gray-400 text-center">'+esc(p.joined_at_human)+'</td>'
            +'<td class="px-6 py-4 whitespace-nowrap text-center"><form action="'+ep(statusUrlTemplate,p.id)+'" method="POST" data-ajax="true"><input type="hidden" name="_token" value="'+csrfToken+'"><select name="status_join" class="text-xs rounded-lg border-gray-200 py-1.5 '+jc(p.status_join)+'"><option value="joined" '+(p.status_join==='joined'?'selected':'')+'>Joined</option><option value="batal" '+(p.status_join==='batal'?'selected':'')+'>Batal</option></select></form></td>'
            +'<td class="px-6 py-4 whitespace-nowrap text-center"><form action="'+ep(attendanceUrlTemplate,p.id)+'" method="POST" data-ajax="true"><input type="hidden" name="_token" value="'+csrfToken+'"><input type="checkbox" name="hadir" value="1" class="h-5 w-5 text-blue-600 rounded border-gray-300 cursor-pointer" '+(p.hadir?'checked':'')+'></form></td>'
            +'<td class="px-6 py-4 whitespace-nowrap text-center text-xs text-gray-600"><div class="mb-2">'+esc(p.payment_method_label)+' <span class="font-semibold">- Rp '+rupiah(p.payment_amount)+'</span></div><form action="'+ep(paymentUrlTemplate,p.id)+'" method="POST" data-ajax="true" class="mb-1"><input type="hidden" name="_token" value="'+csrfToken+'"><select name="payment_status" class="text-xs rounded-lg border-gray-200 py-1.5 '+pc(p.payment_status)+'"><option value="pending" '+(p.payment_status==='pending'?'selected':'')+'>Pending</option><option value="paid" '+(p.payment_status==='paid'?'selected':'')+'>Paid</option><option value="failed" '+(p.payment_status==='failed'?'selected':'')+'>Failed</option></select></form>'+ref+'</td></tr>';
        }).join('');
    }

    function applyLivePayload(p) {
        if(!p||!p.metrics) return;
        document.getElementById('metric-joined').textContent=p.metrics.joined_count;
        document.getElementById('metric-paid').textContent=p.metrics.paid_count;
        document.getElementById('metric-pending').textContent=p.metrics.pending_count;
        document.getElementById('metric-collected').innerHTML='Rp '+rupiah(p.metrics.total_collected)+' <span class="text-gray-500 font-normal">/ '+rupiah({{ $event->biaya_total_event }})+'</span>';
        document.getElementById('live-updated-at').textContent=new Date().toLocaleTimeString('id-ID');
        renderPlayers(p.players||[]);
    }

    async function fetchLive(s) {
        try{const r=await fetch(liveUrl,{headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});if(!r.ok)throw new Error('Gagal');applyLivePayload(await r.json());}
        catch(e){if(!s)showFeedback('Sinkron realtime gagal.','error');}
    }

    async function submitAjaxForm(form) {
        try{const r=await fetch(form.action,{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken,'X-Requested-With':'XMLHttpRequest'},body:new FormData(form)});const p=await r.json();if(!r.ok)throw new Error(p.message||'Update gagal.');if(p.live)applyLivePayload(p.live);else fetchLive(true);showFeedback(p.message||'Data berhasil diupdate.','success');}
        catch(e){showFeedback(e.message||'Terjadi kesalahan.','error');}
    }

    playersBody.addEventListener('change',function(e){const f=e.target.closest('form[data-ajax="true"]');if(f)submitAjaxForm(f);});

    window.copyLink=function(){
        const i=document.getElementById('share-link');
        i.select();
        i.setSelectionRange(0,99999);
        try {
            document.execCommand('copy');
            showFeedback('Link join disalin.','success');
        } catch (err) {
            navigator.clipboard.writeText(i.value).then(() => {
                showFeedback('Link join disalin.','success');
            }).catch(() => {
                showFeedback('Gagal menyalin otomatis. Silakan salin manual.','error');
            });
        }
    };

    applyLivePayload(@json($livePayload));
    fetchLive(true);
    setInterval(()=>fetchLive(true),10000);

    const dpt=document.getElementById('detail_show_joined_players_public');
    const dct=document.getElementById('detail_show_joined_player_contacts_public');
    if(dpt&&dct){const sync=()=>{const e=!!dpt.checked;dct.disabled=!e;if(!e)dct.checked=false;};dpt.addEventListener('change',sync);sync();}
})();
</script>
@endpush

