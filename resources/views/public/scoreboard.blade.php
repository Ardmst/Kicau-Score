@extends('layouts.app', ['title' => 'Live Scoreboard & Klasemen'])

@section('content')
<div class="space-y-6">
    
    <!-- Header Page -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span id="sync-pulse-sb" class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></span>
                <h1 class="font-headline font-bold text-2xl text-slate-900 tracking-tight">Live Klasemen Resmi</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">Transparansi rekapitulasi nilai juri lapangan berstandar PBI & BNR dengan pembaruan otomatis (Async)</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" class="h-10 px-4 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-[18px]">calendar_month</span> Rundown Event
            </a>
            <button id="btn-refresh-sb" onclick="fetchScoreboardAsync()" class="h-10 px-4 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-sm">
                <span id="icon-refresh-sb" class="material-symbols-outlined text-[18px]">refresh</span> Refresh Skor
            </button>
        </div>
    </div>

    <!-- Category Tabs with Bird Type Badges -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2" style="scrollbar-width: thin;">
        @foreach($categories as $kat)
            @php
                $isSelected = $selectedKategori && $selectedKategori->id === $kat->id;
            @endphp
            <a href="{{ route('scoreboard', $kat->slug) }}"
               class="shrink-0 px-4 py-2.5 rounded-xl border text-xs font-semibold flex items-center gap-2 transition-all {{ $isSelected ? 'bg-[#2E6B56] text-white border-[#2E6B56] shadow-sm' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                <span>{{ $kat->nama_kategori }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $isSelected ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ strtoupper($kat->status_lomba) }}
                </span>
            </a>
        @endforeach
    </div>

    <!-- Leaderboard Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="font-headline font-bold text-lg text-slate-900" id="sb-title">
                        {{ $selectedKategori->nama_kategori ?? 'Pilih Kategori' }}
                    </h2>
                    @if($selectedKategori && $selectedKategori->jenis_burung)
                        <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 text-xs font-bold border border-emerald-200">
                            {{ $selectedKategori->jenis_burung }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Event: {{ $selectedKategori->event->nama_event ?? '-' }} &bull; Kuota: {{ $selectedKategori->kuota ?? 36 }} Gantangan
                    @if($selectedKategori && $selectedKategori->jam_mulai)
                        &bull; Waktu: <strong class="text-slate-700">{{ $selectedKategori->jam_mulai }} WIB</strong>
                    @endif
                </p>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $selectedKategori && $selectedKategori->status_lomba === 'ongoing' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                    Status: {{ strtoupper($selectedKategori->status_lomba ?? '-') }}
                </span>
                <span class="text-xs text-slate-400 hidden sm:inline" id="sb-timestamp">
                    {{ now()->format('H:i:s') }} WIB
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm" id="table-main-sb">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <th class="py-3.5 px-4 w-16 text-center">Juara</th>
                        <th class="py-3.5 px-4 w-28 text-center">Gantangan</th>
                        <th class="py-3.5 px-4">Identitas Burung</th>
                        <th class="py-3.5 px-4">Pemilik (Peserta)</th>
                        <th class="py-3.5 px-4 text-right">Rata Visual</th>
                        <th class="py-3.5 px-4 text-right">Rata Audio</th>
                        <th class="py-3.5 px-4 text-right font-bold text-slate-900">Skor Akhir</th>
                        <th class="py-3.5 px-4 text-center">Profil</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="sb-tbody">
                    @forelse($leaderboard as $index => $item)
                        @php
                            $rank = $item->peringkat ?? ($index + 1);
                            $rankStyle = match($rank) {
                                1 => 'bg-amber-100 text-amber-900 border-amber-300 font-extrabold',
                                2 => 'bg-slate-200 text-slate-800 border-slate-300 font-bold',
                                3 => 'bg-orange-100 text-orange-900 border-orange-300 font-bold',
                                default => 'bg-slate-50 text-slate-600 border-slate-200 font-semibold',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors {{ $rank <= 3 ? 'bg-amber-50/20' : '' }}">
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full border text-xs {{ $rankStyle }} shadow-xs">
                                    {{ $rank }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-block px-3 py-1 rounded-full bg-[#10533F] text-white font-headline font-bold text-xs tracking-wider shadow-xs">
                                    G-{{ str_pad($item->nomor_gantangan, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('burung.profile', $item->burung->slug) }}" class="font-headline font-bold text-slate-900 hover:text-[#2E6B56] hover:underline flex items-center gap-1.5">
                                    {{ $item->burung->nama_burung }}
                                    <span class="material-symbols-outlined text-[16px] text-slate-400">open_in_new</span>
                                </a>
                                <span class="text-xs text-slate-500">
                                    {{ $item->burung->jenis_burung ?? 'Murai Batu' }} &bull; Ring: {{ $item->burung->no_ring ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-800 block text-xs sm:text-sm">{{ $item->user->name }}</span>
                                <span class="text-xs text-slate-400">ID: #{{ $item->user->id }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-right tabular-nums text-slate-700 font-medium">
                                {{ number_format($item->rata_visual, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right tabular-nums text-emerald-800 font-semibold bg-emerald-50/30">
                                {{ number_format($item->rata_audio, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right tabular-nums">
                                <span class="font-headline font-bold text-base text-slate-900">
                                    {{ number_format($item->total_skor_akhir, 2) }}
                                </span>
                            </td>
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
                                <span class="font-semibold text-sm">Belum ada skor untuk kategori ini.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const currentKategoriId = {{ $selectedKategori ? $selectedKategori->id : 'null' }};

    async function fetchScoreboardAsync() {
        if (!currentKategoriId) return;

        const icon = document.getElementById('icon-refresh-sb');
        if (icon) icon.classList.add('animate-spin');

        try {
            const res = await fetch(`/api/scoreboard?kategori_id=${currentKategoriId}`);
            if (!res.ok) throw new Error('Failed to fetch scoreboard');
            const data = await res.json();

            if (data.status === 'success' && data.leaderboard) {
                renderLeaderboard(data.leaderboard);
                const ts = document.getElementById('sb-timestamp');
                if (ts) ts.textContent = data.updated_at;
            }
        } catch (e) {
            console.error('Async scoreboard error:', e);
        } finally {
            if (icon) {
                setTimeout(() => icon.classList.remove('animate-spin'), 400);
            }
        }
    }

    function renderLeaderboard(items) {
        const tbody = document.getElementById('sb-tbody');
        if (!tbody) return;

        if (items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="py-12 text-center text-slate-400">
                        <span class="material-symbols-outlined text-[48px] block mx-auto mb-2 text-slate-300">hourglass_empty</span>
                        <span class="font-semibold text-sm">Belum ada skor untuk kategori ini.</span>
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

    if (currentKategoriId) {
        setInterval(fetchScoreboardAsync, 10000);
    }
</script>
@endpush
@endsection
