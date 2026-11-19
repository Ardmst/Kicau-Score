@extends('layouts.app', ['title' => 'Tiket Gantangan Resmi'])

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('peserta.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-[#2E6B56] transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke Dashboard
        </a>

        <button onclick="window.print()" class="h-9 px-4 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-1.5 shadow-xs transition-colors">
            <span class="material-symbols-outlined text-[16px]">print</span> Cetak / Simpan PDF
        </button>
    </div>

    <!-- Official Ticket Card -->
    <div class="bg-white rounded-3xl border-2 border-slate-200 shadow-sm overflow-hidden relative" id="printable-ticket">
        
        <!-- Header Green Banner -->
        <div class="bg-gradient-to-r from-[#10533F] to-[#2E6B56] text-white p-6 sm:p-8 text-center relative overflow-hidden">
            <div class="relative z-10">
                <span class="inline-block px-3 py-1 rounded-full bg-white/20 text-white text-[10px] font-bold tracking-widest uppercase mb-2">
                    OFFICIAL GANTANGAN PASS
                </span>
                <h1 class="font-headline font-bold text-xl sm:text-2xl tracking-tight">
                    {{ $pendaftaran->kategoriLomba->event->nama_event }}
                </h1>
                <p class="text-xs text-emerald-100 mt-1">
                    {{ $pendaftaran->kategoriLomba->event->lokasi }} &bull; {{ $pendaftaran->kategoriLomba->event->tanggal->isoFormat('D MMMM Y') }} &bull; Waktu: {{ $pendaftaran->kategoriLomba->jam_mulai ?? $pendaftaran->kategoriLomba->event->jam_mulai ?? '08:30' }} WIB
                </p>
            </div>
        </div>

        <!-- Giant Gantangan Number Block -->
        <div class="py-8 px-6 text-center border-b border-dashed border-slate-200 bg-emerald-50/20">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-widest block mb-1">NOMOR GANTANGAN RESMI</span>
            <div class="inline-block px-8 py-3 rounded-2xl bg-[#10533F] text-white font-headline font-black text-4xl sm:text-5xl tracking-widest shadow-md">
                G-{{ str_pad($pendaftaran->nomor_gantangan, 2, '0', STR_PAD_LEFT) }}
            </div>
            <span class="text-[11px] text-emerald-800 font-semibold block mt-2">
                Terdaftar di Kategori: <strong>{{ $pendaftaran->kategoriLomba->nama_kategori }}</strong>
            </span>
        </div>

        <!-- Ticket Body Details -->
        <div class="p-6 sm:p-8 space-y-4">
            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 font-semibold uppercase block">Burung Gacoan</span>
                    <strong class="text-sm font-bold text-slate-900 block mt-0.5">{{ $pendaftaran->burung->nama_burung }}</strong>
                    <span class="text-slate-500">{{ $pendaftaran->burung->jenis_burung }} (Ring: {{ $pendaftaran->burung->no_ring ?? '-' }})</span>
                </div>

                <div>
                    <span class="text-slate-400 font-semibold uppercase block">Peserta / Pemilik</span>
                    <strong class="text-sm font-bold text-slate-900 block mt-0.5">{{ $pendaftaran->user->name }}</strong>
                    <span class="text-slate-500">{{ $pendaftaran->user->phone ?? '-' }}</span>
                </div>

                <div>
                    <span class="text-slate-400 font-semibold uppercase block">Status Pembayaran</span>
                    <span class="inline-block mt-0.5 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold uppercase text-[11px]">
                        {{ $pendaftaran->status_pembayaran }} ({{ $pendaftaran->metode_pembayaran }})
                    </span>
                </div>

                <div>
                    <span class="text-slate-400 font-semibold uppercase block">Kode Referensi Tiket</span>
                    <span class="font-mono font-bold text-slate-800 text-xs block mt-0.5">
                        KCS-{{ $pendaftaran->kategori_lomba_id }}-{{ str_pad($pendaftaran->id, 5, '0', STR_PAD_LEFT) }}
                    </span>
                </div>
            </div>

            <!-- Stylized Barcode / Verification Seal -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                <div>
                    <div class="font-mono text-xs tracking-widest text-slate-400">||| | |||| || ||||| ||| |||</div>
                    <span class="text-[10px] text-slate-400 block mt-1">Verifikasi Digital KicauScore</span>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 block">Diterbitkan oleh:</span>
                    <span class="text-xs font-bold text-[#2E6B56]">Panitia Pelaksana Kicau Nusantara</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
