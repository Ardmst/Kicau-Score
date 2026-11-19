@extends('layouts.app', ['title' => 'Profil Burung: ' . $burung->nama_burung])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- Bird Header Showcase Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 relative overflow-hidden">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-[#10533F] to-[#2E6B56] text-white flex items-center justify-center font-headline font-bold text-2xl sm:text-3xl shadow-sm shrink-0">
                    {{ strtoupper(substr($burung->nama_burung, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold flex items-center gap-1">
                            <x-icon-bird class="w-3.5 h-3.5" />
                            {{ $burung->jenis_burung ?? 'Murai Batu' }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[11px] font-medium">
                            Gacoan Resmi
                        </span>
                    </div>
                    <h1 class="font-headline font-bold text-2xl sm:text-3xl text-slate-900 tracking-tight mt-1">
                        {{ $burung->nama_burung }}
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Pemilik: <strong class="text-slate-800">{{ $burung->user->name ?? 'Anonim' }}</strong> &bull; No. Ring: <span class="font-mono font-semibold">{{ $burung->no_ring ?? '-' }}</span>
                    </p>
                </div>
            </div>

            <div class="shrink-0 flex items-center gap-2">
                <a href="{{ route('scoreboard') }}" class="h-9 px-3.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <span class="material-symbols-outlined text-[16px]">leaderboard</span> Live Klasemen
                </a>
                <a href="{{ route('home') }}" class="h-9 px-3.5 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-[16px]">home</span> Beranda
                </a>
            </div>
        </div>

        @if($burung->catatan)
            <div class="mt-6 pt-5 border-t border-slate-100 text-xs text-slate-600 bg-slate-50/50 p-3.5 rounded-xl">
                <strong class="text-slate-800 block mb-1">Catatan Karakter & Materi Lagu:</strong>
                {{ $burung->catatan }}
            </div>
        @endif
    </div>

    <!-- Competition History Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="font-headline font-bold text-lg text-slate-900">Riwayat Prestasi & Perlombaan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Track record nomor gantangan, akumulasi skor juri, dan posisi peringkat</p>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">
                {{ $burung->pendaftarans->count() }} Lomba Diikuti
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-4">Event & Kategori</th>
                        <th class="py-3 px-4 text-center">Gantangan</th>
                        <th class="py-3 px-4 text-right">Rata Visual</th>
                        <th class="py-3 px-4 text-right">Rata Audio</th>
                        <th class="py-3 px-4 text-right font-bold text-slate-900">Total Skor</th>
                        <th class="py-3 px-4 text-center">Peringkat</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($burung->pendaftarans as $p)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-headline font-bold text-slate-900 block text-sm">
                                    {{ $p->kategoriLomba->nama_kategori ?? '-' }}
                                </span>
                                <span class="text-xs text-slate-500">
                                    {{ $p->kategoriLomba->event->nama_event ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($p->nomor_gantangan)
                                    <span class="inline-block px-2.5 py-0.5 rounded-full bg-[#10533F] text-white font-headline font-bold text-xs">
                                        G-{{ str_pad($p->nomor_gantangan, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right tabular-nums text-slate-700 font-medium">
                                {{ $p->rata_visual ? number_format($p->rata_visual, 2) : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right tabular-nums text-emerald-800 font-semibold">
                                {{ $p->rata_audio ? number_format($p->rata_audio, 2) : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right tabular-nums font-headline font-bold text-slate-900">
                                {{ $p->total_skor_akhir ? number_format($p->total_skor_akhir, 2) : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($p->peringkat)
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold {{ $p->peringkat <= 3 ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-slate-100 text-slate-700' }}">
                                        #{{ $p->peringkat }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">TBA</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $p->kategoriLomba && $p->kategoriLomba->status_lomba === 'finished' ? 'bg-slate-100 text-slate-700' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $p->kategoriLomba->status_lomba ?? '-' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                Belum ada riwayat lomba resmi yang tercatat untuk burung ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
