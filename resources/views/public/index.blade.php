@extends('layouts.app', ['title' => 'Beranda & Live Scoreboard'])

@section('content')
<div class="space-y-8">
    
    @if($event)
        <!-- Hero Showcase Card -->
        <div class="w-full bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:p-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-80 h-full bg-gradient-to-l from-emerald-50 to-transparent pointer-events-none"></div>
            
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div class="max-w-3xl space-y-3">
                    
                    <!-- Live Indicator & Status -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <div class="inline-flex items-center gap-1.5 bg-emerald-100 text-emerald-900 px-3 py-1 rounded-full text-xs font-bold tracking-wide">
                            <span class="w-2 h-2 rounded-full bg-emerald-600 animate-ping"></span>
                            <span>OFFICIAL COMPETITION</span>
                        </div>
                        <span class="bg-slate-100 text-slate-700 text-xs px-2.5 py-1 rounded-full font-medium">
                            Status: {{ strtoupper($event->status) }}
                        </span>
                        <span class="bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-full flex items-center gap-1 font-medium">
                            <span class="material-symbols-outlined text-[14px]">stadium</span> Arena Utama
                        </span>
                    </div>

                    <!-- Title & Subtitle -->
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#2E6B56] text-[32px]" style="font-variation-settings: 'FILL' 1;">military_tech</span>
                            <h1 class="font-headline font-bold text-2xl sm:text-3xl lg:text-4xl text-slate-900 tracking-tight">
                                {{ $event->nama_event }}
                            </h1>
                        </div>
                        <p class="text-sm sm:text-base text-slate-600 mt-1">
                            {{ $event->deskripsi ?? 'Kejuaraan Nasional Burung Berkicau • Penilaian Algoritmik Multi-Juri Berstandar PBI & BNR' }}
                        </p>
                    </div>

                    <!-- Metadata Strip (Tanggal, Jam, Lokasi, Kategori) -->
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 pt-2">
                        <div class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl text-xs text-slate-700 font-medium">
                            <span class="material-symbols-outlined text-[#2E6B56] text-[18px]">location_on</span>
                            <span>{{ $event->lokasi }}</span>
                        </div>
                        <div class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl text-xs text-slate-700 font-medium">
                            <span class="material-symbols-outlined text-slate-500 text-[18px]">calendar_today</span>
                            <span>{{ $event->tanggal->isoFormat('dddd, D MMMM Y') }}</span>
                        </div>
                        <div class="inline-flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-xl text-xs text-emerald-800 font-semibold">
                            <span class="material-symbols-outlined text-emerald-600 text-[18px]">schedule</span>
                            <span>{{ $event->jam_mulai ?? '08:30' }} - {{ $event->jam_selesai ?? '17:00' }} WIB</span>
                        </div>
                        <div class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl text-xs text-slate-700 font-medium">
                            <span class="material-symbols-outlined text-slate-500 text-[18px]">category</span>
                            <span>{{ $event->kategoriLombas->count() }} Kategori Lomba</span>
                        </div>
                    </div>
                </div>

                <!-- Registration CTA -->
                <div class="flex flex-col sm:flex-row lg:flex-col items-stretch lg:items-end gap-3 shrink-0">
                    <a href="{{ route('peserta.daftar') }}" class="h-12 px-6 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-sm transition-colors">
                        <span class="material-symbols-outlined text-[20px]">how_to_vote</span> Daftar Gantangan Sekarang
                    </a>
                    <a href="{{ route('scoreboard') }}" class="h-11 px-5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs flex items-center justify-center gap-2 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">leaderboard</span> Lihat Full Klasemen
                    </a>
                </div>
            </div>
        </div>

        <!-- Master Jenis Burung Showcase Bar -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#2E6B56] text-[20px]">cruelty_free</span>
                    <h3 class="font-headline font-bold text-sm text-slate-900">Jenis Burung yang Dilombakan</h3>
                </div>
                <span class="text-xs text-slate-400">Standar Penjurian Nasional PBI & BNR</span>
            </div>
            <div class="flex items-center gap-2 overflow-x-auto pb-1" style="scrollbar-width: thin;">
                @php
                    $masterJenis = \App\Models\KategoriLomba::JENIS_BURUNG_LIST;
                @endphp
                @foreach($masterJenis as $jb)
                    @php
                        $countKat = $event->kategoriLombas->filter(fn($k) => str_contains(strtolower($k->nama_kategori), strtolower($jb)) || strtolower($k->jenis_burung ?? '') === strtolower($jb))->count();
                    @endphp
                    <div class="shrink-0 px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 text-xs flex items-center gap-2 text-slate-700">
                        <span class="font-semibold">{{ $jb }}</span>
                        @if($countKat > 0)
                            <span class="px-1.5 py-0.2 rounded-full bg-[#2E6B56] text-white text-[10px] font-bold">{{ $countKat }} Kelas</span>
                        @else
                            <span class="text-[10px] text-slate-400">Tersedia</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Category Schedule & Selector Strip -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-headline font-bold text-lg text-slate-900">Rundown & Kategori Lomba</h2>
                    <p class="text-xs text-slate-500">Pilih kategori untuk melihat perolehan skor langsung (Live Scoreboard)</p>
                </div>
            </div>

            <!-- Categories Horizontal Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach($event->kategoriLombas as $kat)
                    @php
                        $isActive = $activeKategori && $activeKategori->id === $kat->id;
                        $statusBadge = match($kat->status_lomba) {
                            'ongoing' => ['bg' => 'bg-emerald-100 text-emerald-800 border-emerald-300', 'label' => 'Sedang Dinilai', 'dot' => 'bg-emerald-600 animate-ping'],
                            'upcoming' => ['bg' => 'bg-amber-50 text-amber-800 border-amber-200', 'label' => 'Persiapan', 'dot' => 'bg-amber-400'],
                            'validasi' => ['bg' => 'bg-blue-50 text-blue-800 border-blue-200', 'label' => 'Validasi Rekap', 'dot' => 'bg-blue-500'],
                            'finished' => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => 'Selesai', 'dot' => 'bg-slate-400'],
                        };
                    @endphp
                    <a href="{{ route('home', ['kategori_id' => $kat->id]) }}"
                       class="p-4 rounded-xl border text-left transition-all relative overflow-hidden {{ $isActive ? 'border-[#2E6B56] ring-2 ring-[#2E6B56]/20 bg-emerald-50/40' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge['bg'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusBadge['dot'] }}"></span>
                                {{ $statusBadge['label'] }}
                            </span>
                            <span class="text-xs font-bold text-slate-700">Rp {{ number_format($kat->biaya, 0, ',', '.') }}</span>
                        </div>
                        <h3 class="font-headline font-bold text-sm text-slate-900 truncate mb-1">
                            {{ $kat->nama_kategori }}
                        </h3>
                        <div class="text-[11px] text-slate-500 mb-2 flex items-center justify-between">
                            <span>{{ $kat->jenis_burung ?? 'Murai Batu' }}</span>
                            @if($kat->jam_mulai)
                                <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">
                                    {{ $kat->jam_mulai }} WIB
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-500 pt-1.5 border-t border-slate-100">
                            <span>Gantangan: <strong>{{ $kat->total_terdaftar }} / {{ $kat->kuota }}</strong></span>
                            @if($isActive)
                                <span class="text-[#2E6B56] font-bold text-[11px] flex items-center">
                                    Aktif <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Live Scoreboard Section (With Asynchronous Auto-Refresh) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" id="live-scoreboard-section">
            <div class="p-4 sm:p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span id="sync-pulse" class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <h2 class="font-headline font-bold text-lg text-slate-900" id="scoreboard-title">
                            Live Scoreboard: {{ $activeKategori->nama_kategori ?? 'Semua Kategori' }}
                        </h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Urutan peringkat dihitung transparan menggunakan <strong>Average Score Engine</strong> & <strong>4-Tier Tie-Breaker</strong>.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <span id="sync-status" class="text-xs text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full font-medium flex items-center gap-1 border border-emerald-200">
                        <span class="material-symbols-outlined text-[14px]">sync</span>
                        <span>Async Polling Aktif</span>
                    </span>
                    <button id="btn-async-refresh" onclick="fetchLiveScoreboard()" class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 transition-colors" title="Muat Ulang Skor Secara Asinkron">
                        <span id="refresh-icon" class="material-symbols-outlined text-[18px]">refresh</span>
                    </button>
                </div>
            </div>

            <!-- Leaderboard Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" id="table-scoreboard">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                            <th class="py-3.5 px-4 w-16 text-center">Juara</th>
                            <th class="py-3.5 px-4 w-28 text-center">Gantangan</th>
                            <th class="py-3.5 px-4">Identitas Burung</th>
                            <th class="py-3.5 px-4">Pemilik (Peserta)</th>
                            <th class="py-3.5 px-4 text-right">Rata Visual</th>
                            <th class="py-3.5 px-4 text-right">Rata Audio</th>
                            <th class="py-3.5 px-4 text-right font-bold text-slate-900">Skor Akhir</th>
                            <th class="py-3.5 px-4 text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="scoreboard-tbody">
                        @forelse($leaderboard as $index => $item)
                            @php
                                $rank = $item->peringkat ?? ($index + 1);
                                $isTop3 = $rank <= 3;
                                $rankBadge = match($rank) {
                                    1 => 'bg-amber-100 text-amber-900 border-amber-300 font-extrabold',
                                    2 => 'bg-slate-200 text-slate-800 border-slate-300 font-bold',
                                    3 => 'bg-orange-100 text-orange-900 border-orange-300 font-bold',
                                    default => 'bg-slate-50 text-slate-600 border-slate-200 font-semibold',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors {{ $isTop3 ? 'bg-amber-50/20' : '' }}">
                                <!-- Peringkat -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full border text-xs {{ $rankBadge }} shadow-xs">
                                        {{ $rank }}
                                    </span>
                                </td>

                                <!-- Nomor Gantangan -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-block px-3 py-1 rounded-full bg-[#10533F] text-white font-headline font-bold text-xs tracking-wider shadow-xs">
                                        G-{{ str_pad($item->nomor_gantangan, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>

                                <!-- Burung -->
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('burung.profile', $item->burung->slug) }}" class="font-headline font-bold text-slate-900 hover:text-[#2E6B56] hover:underline flex items-center gap-1.5">
                                        {{ $item->burung->nama_burung }}
                                        <span class="material-symbols-outlined text-[16px] text-slate-400">open_in_new</span>
                                    </a>
                                    <span class="text-xs text-slate-500">
                                        {{ $item->burung->jenis_burung ?? 'Murai Batu' }} &bull; Ring: {{ $item->burung->no_ring ?? '-' }}
                                    </span>
                                </td>

                                <!-- Pemilik -->
                                <td class="py-3.5 px-4">
                                    <span class="font-semibold text-slate-800 block text-xs sm:text-sm">{{ $item->user->name }}</span>
                                    <span class="text-xs text-slate-400">ID: #{{ $item->user->id }}</span>
                                </td>

                                <!-- Rata-rata Visual -->
                                <td class="py-3.5 px-4 text-right tabular-nums text-slate-700 font-medium">
                                    {{ number_format($item->rata_visual, 2) }}
                                </td>

                                <!-- Rata-rata Audio (Tie-Breaker 1) -->
                                <td class="py-3.5 px-4 text-right tabular-nums text-emerald-800 font-semibold bg-emerald-50/30">
                                    {{ number_format($item->rata_audio, 2) }}
                                </td>

                                <!-- Total Skor Akhir -->
                                <td class="py-3.5 px-4 text-right tabular-nums">
                                    <span class="font-headline font-bold text-base text-slate-900">
                                        {{ number_format($item->total_skor_akhir, 2) }}
                                    </span>
                                </td>

                                <!-- Action / Profil -->
                                <td class="py-3.5 px-4 text-center">
                                    <a href="{{ route('burung.profile', $item->burung->slug) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                                        Profil
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-[48px] block mx-auto mb-2 text-slate-300">hourglass_empty</span>
                                    <span class="font-semibold text-sm">Belum ada skor yang divalidasi untuk kategori ini.</span>
                                    <p class="text-xs mt-1">Sesi penjurian di lapangan mungkin sedang berlangsung atau juri belum menginput skor.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Tie-Breaker Information Note -->
            <div class="p-4 bg-slate-50 border-t border-slate-200 text-xs text-slate-500 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#2E6B56] text-[18px]">verified</span>
                    <span>Hierarki Pemecah Seri: <strong>1. Skor Total (DESC)</strong> &rarr; <strong>2. Rata Audio (DESC)</strong> &rarr; <strong>3. Rata Visual (DESC)</strong> &rarr; <strong>4. Nomor Gantangan Terkecil (ASC)</strong></span>
                </div>
                <div>
                    Terakhir diperbarui: <strong id="timestamp-text">{{ now()->format('H:i:s') }} WIB</strong>
                </div>
            </div>
        </div>

    @else
        <!-- Empty State If No Events -->
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
            <span class="material-symbols-outlined text-[56px] text-slate-300 mb-3 block">event_busy</span>
            <h2 class="font-headline font-bold text-lg text-slate-800">Belum Ada Event Aktif</h2>
            <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">Saat ini belum ada jadwal perlombaan burung yang dipublikasikan oleh Panitia.</p>
        </div>
    @endif

</div>

@push('scripts')
<script>
    const activeKategoriId = {{ $activeKategori ? $activeKategori->id : 'null' }};

    async function fetchLiveScoreboard() {
        if (!activeKategoriId) return;

        const refreshIcon = document.getElementById('refresh-icon');
        if (refreshIcon) refreshIcon.classList.add('animate-spin');

        try {
            const res = await fetch(`/api/scoreboard?kategori_id=${activeKategoriId}`);
            if (!res.ok) throw new Error('Network error');
            const json = await res.parse ? await res.parse() : await res.json();

            if (json.status === 'success' && json.leaderboard) {
                renderScoreboardRows(json.leaderboard);
                const ts = document.getElementById('timestamp-text');
                if (ts) ts.textContent = json.updated_at;
            }
        } catch (err) {
            console.error('Async fetch error:', err);
        } finally {
            if (refreshIcon) {
                setTimeout(() => refreshIcon.classList.remove('animate-spin'), 400);
            }
        }
    }

    function renderScoreboardRows(items) {
        const tbody = document.getElementById('scoreboard-tbody');
        if (!tbody) return;

        if (items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="py-12 text-center text-slate-400">
                        <span class="material-symbols-outlined text-[48px] block mx-auto mb-2 text-slate-300">hourglass_empty</span>
                        <span class="font-semibold text-sm">Belum ada skor yang divalidasi untuk kategori ini.</span>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        items.forEach((item) => {
            const rank = item.peringkat;
            let rankBadge = 'bg-slate-50 text-slate-600 border-slate-200 font-semibold';
            if (rank === 1) rankBadge = 'bg-amber-100 text-amber-900 border-amber-300 font-extrabold';
            else if (rank === 2) rankBadge = 'bg-slate-200 text-slate-800 border-slate-300 font-bold';
            else if (rank === 3) rankBadge = 'bg-orange-100 text-orange-900 border-orange-300 font-bold';

            const rowBg = item.is_top3 ? 'bg-amber-50/20' : '';

            html += `
                <tr class="hover:bg-slate-50/80 transition-colors ${rowBg}">
                    <td class="py-3.5 px-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full border text-xs ${rankBadge} shadow-xs">
                            ${rank}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-center">
                        <span class="inline-block px-3 py-1 rounded-full bg-[#10533F] text-white font-headline font-bold text-xs tracking-wider shadow-xs">
                            ${item.nomor_gantangan}
                        </span>
                    </td>
                    <td class="py-3.5 px-4">
                        <a href="${item.profile_url}" class="font-headline font-bold text-slate-900 hover:text-[#2E6B56] hover:underline flex items-center gap-1.5">
                            ${item.nama_burung}
                            <span class="material-symbols-outlined text-[16px] text-slate-400">open_in_new</span>
                        </a>
                        <span class="text-xs text-slate-500">
                            ${item.jenis_burung} &bull; Ring: ${item.no_ring}
                        </span>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="font-semibold text-slate-800 block text-xs sm:text-sm">${item.pemilik}</span>
                        <span class="text-xs text-slate-400">ID: #${item.pemilik_id}</span>
                    </td>
                    <td class="py-3.5 px-4 text-right tabular-nums text-slate-700 font-medium">
                        ${item.rata_visual}
                    </td>
                    <td class="py-3.5 px-4 text-right tabular-nums text-emerald-800 font-semibold bg-emerald-50/30">
                        ${item.rata_audio}
                    </td>
                    <td class="py-3.5 px-4 text-right tabular-nums">
                        <span class="font-headline font-bold text-base text-slate-900">
                            ${item.total_skor_akhir}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-center">
                        <a href="${item.profile_url}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                            Profil
                        </a>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    // Auto-polling secara asinkron setiap 10 detik jika kategori sedang ongoing atau validasi
    if (activeKategoriId) {
        setInterval(fetchLiveScoreboard, 10000);
    }
</script>
@endpush
@endsection
