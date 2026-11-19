@extends('layouts.juri', ['title' => 'Dashboard Penjurian'])

@section('content')
<div class="space-y-4">
    
    <!-- Category & Session Context Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <div class="flex items-center justify-between gap-2 mb-2">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Sesi Penjurian Aktif</span>
                <h2 class="font-headline font-bold text-base text-slate-900 truncate">
                    {{ $activeKategori->nama_kategori ?? 'Tidak Ada Kategori Aktif' }}
                </h2>
            </div>
            <div class="text-right">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#10533F] text-white text-xs font-bold shadow-xs">
                    <span class="material-symbols-outlined text-[14px]">timer</span>
                    <span>10:00</span>
                </span>
            </div>
        </div>

        <div class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100">
            <span>Event: <strong>{{ $activeKategori->event->nama_event ?? '-' }}</strong></span>
            <span class="px-2 py-0.5 rounded-full font-bold text-[10px] uppercase {{ $activeKategori && $activeKategori->status_lomba === 'ongoing' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                {{ $activeKategori->status_lomba ?? '-' }}
            </span>
        </div>
    </div>

    <!-- Category Switcher Dropdown (If multiple categories exist) -->
    @if($kategoriList->count() > 1)
        <div class="bg-white rounded-2xl border border-slate-200 p-3 shadow-xs flex items-center justify-between gap-2">
            <span class="text-xs font-semibold text-slate-500">Ganti Kategori:</span>
            <select onchange="window.location.href = '{{ route('juri.dashboard') }}?kategori_id=' + this.value" class="text-xs font-semibold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#2E6B56]">
                @foreach($kategoriList as $k)
                    <option value="{{ $k->id }}" {{ $activeKategori && $activeKategori->id === $k->id ? 'selected' : '' }}>
                        {{ $k->nama_kategori }} ({{ strtoupper($k->status_lomba) }})
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    <!-- Gantangan Strip Slider -->
    @if($pendaftarans->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-slate-700 uppercase tracking-wider">Pilih Nomor Gantangan:</span>
                <span class="text-[#2E6B56] font-bold">
                    {{ $penilaianByPendaftaran->count() }} / {{ $pendaftarans->count() }} Dinilai
                </span>
            </div>

            <!-- Horizontal Scroll Strip -->
            <div class="flex items-center gap-2 overflow-x-auto py-1 scroll-smooth" style="scrollbar-width: none;">
                @foreach($pendaftarans as $p)
                    @php
                        $isScored = $penilaianByPendaftaran->has($p->id);
                    @endphp
                    <a href="{{ route('juri.penilaian.form', $p->id) }}"
                       class="shrink-0 h-10 px-3.5 rounded-full text-xs font-headline font-bold flex items-center gap-1 transition-transform active:scale-95 shadow-xs {{ $isScored ? 'bg-[#2E6B56] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        @if($isScored)
                            <span class="material-symbols-outlined text-[14px]">check</span>
                        @endif
                        G-{{ str_pad($p->nomor_gantangan, 2, '0', STR_PAD_LEFT) }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Bird Cards List for Scoring -->
        <div class="space-y-2.5">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider px-1 block">Daftar Burung di Arena Gantangan:</span>
            
            @foreach($pendaftarans as $p)
                @php
                    $pen = $penilaianByPendaftaran->get($p->id);
                    $isScored = !empty($pen);
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex items-center justify-between gap-3 hover:border-slate-300 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl {{ $isScored ? 'bg-[#10533F] text-white' : 'bg-slate-100 text-slate-700' }} flex items-center justify-center font-headline font-bold text-sm shrink-0 shadow-xs">
                            G-{{ str_pad($p->nomor_gantangan, 2, '0', STR_PAD_LEFT) }}
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-headline font-bold text-sm text-slate-900 truncate">
                                {{ $p->burung->nama_burung }}
                            </h3>
                            <span class="text-xs text-slate-500 block truncate">
                                {{ $p->burung->jenis_burung }} &bull; {{ $p->user->name }}
                            </span>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        @if($isScored)
                            <div class="text-right">
                                <span class="font-headline font-bold text-base text-[#10533F] block tabular-nums">
                                    {{ number_format($pen->total_skor_juri, 2) }}
                                </span>
                                <span class="text-[10px] text-emerald-700 font-semibold block">Tersimpan</span>
                            </div>
                        @endif
                        <a href="{{ route('juri.penilaian.form', $p->id) }}" class="mt-1 inline-flex items-center gap-1 px-3 py-1.5 rounded-xl {{ $isScored ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-[#2E6B56] text-white hover:bg-[#245443]' }} text-xs font-semibold shadow-xs transition-colors">
                            <span class="material-symbols-outlined text-[14px]">{{ $isScored ? 'edit' : 'add' }}</span>
                            {{ $isScored ? 'Edit' : 'Nilai' }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
            <span class="material-symbols-outlined text-[48px] text-slate-300 block mb-2">event_seat</span>
            <h3 class="font-headline font-bold text-sm text-slate-700">Belum Ada Burung Terverifikasi</h3>
            <p class="text-xs text-slate-400 mt-1">Belum ada peserta dengan nomor gantangan terverifikasi pada kategori ini.</p>
        </div>
    @endif
</div>
@endsection
