@extends('layouts.app', ['title' => 'Dashboard Peserta'])

@section('content')
<div class="space-y-6">
    
    <!-- Welcome Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold uppercase tracking-wider">Dashboard Peserta</span>
            </div>
            <h1 class="font-headline font-bold text-2xl text-slate-900 tracking-tight mt-1">
                Halo, {{ $user->name }}!
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola burung gacoan Anda, pantau nomor gantangan, dan cetak tiket perlombaan</p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('peserta.burung.index') }}" class="h-10 px-4 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-2 transition-colors">
                <x-icon-bird class="w-4 h-4 text-[#2E6B56]" /> Burung Saya
            </a>
            <a href="{{ route('peserta.daftar') }}" class="h-10 px-4 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition-colors">
                <span class="material-symbols-outlined text-[18px]">add_circle</span> Daftar Lomba
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-[#2E6B56] flex items-center justify-center shrink-0">
                <x-icon-bird class="w-6 h-6 text-[#2E6B56]" />
            </div>
            <div>
                <span class="text-xs text-slate-500 font-medium">Koleksi Burung</span>
                <span class="font-headline font-bold text-2xl text-slate-900 block">{{ $burungs->count() }}</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[26px]">confirmation_number</span>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-medium">Gantangan Terverifikasi</span>
                <span class="font-headline font-bold text-2xl text-slate-900 block">
                    {{ $pendaftarans->where('status_pembayaran', 'verified')->count() }}
                </span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[26px]">pending_actions</span>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-medium">Menunggu Verifikasi</span>
                <span class="font-headline font-bold text-2xl text-slate-900 block">
                    {{ $pendaftarans->where('status_pembayaran', 'pending')->count() }}
                </span>
            </div>
        </div>
    </div>

    <!-- Pendaftaran & Gantangan Tiket Saya -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="font-headline font-bold text-lg text-slate-900">Tiket Gantangan & Status Pendaftaran</h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar kategori yang diikuti beserta nomor gantangan resmi</p>
            </div>
            <a href="{{ route('peserta.daftar') }}" class="text-xs font-semibold text-[#2E6B56] hover:underline flex items-center gap-1">
                + Tambah Pendaftaran
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-4">Nomor Gantangan</th>
                        <th class="py-3 px-4">Burung Gacoan</th>
                        <th class="py-3 px-4">Kategori Lomba</th>
                        <th class="py-3 px-4">Metode Bayar</th>
                        <th class="py-3 px-4 text-center">Status Pembayaran</th>
                        <th class="py-3 px-4 text-right">Hasil Skor</th>
                        <th class="py-3 px-4 text-center">Aksi / Tiket</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pendaftarans as $p)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4">
                                @if($p->nomor_gantangan)
                                    <span class="inline-block px-3 py-1 rounded-full bg-[#10533F] text-white font-headline font-bold text-xs tracking-wider shadow-xs">
                                        G-{{ str_pad($p->nomor_gantangan, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 text-xs font-medium">
                                        Menunggu ACC
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('burung.profile', $p->burung->slug) }}" class="font-headline font-bold text-slate-900 hover:text-[#2E6B56] hover:underline">
                                    {{ $p->burung->nama_burung }}
                                </a>
                                <span class="text-xs text-slate-500 block">{{ $p->burung->jenis_burung }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-800 block text-xs sm:text-sm">
                                    {{ $p->kategoriLomba->nama_kategori }}
                                </span>
                                <span class="text-xs text-slate-500">
                                    {{ $p->kategoriLomba->event->nama_event }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-xs uppercase font-medium text-slate-600">
                                {{ $p->metode_pembayaran }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $statusBadge = match($p->status_pembayaran) {
                                        'verified' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
                                        'rejected' => 'bg-rose-100 text-rose-800 border-rose-300',
                                    };
                                @endphp
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $statusBadge }}">
                                    {{ strtoupper($p->status_pembayaran) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right tabular-nums">
                                @if($p->total_skor_akhir)
                                    <span class="font-headline font-bold text-slate-900 block">
                                        {{ number_format($p->total_skor_akhir, 2) }}
                                    </span>
                                    <span class="text-[11px] text-slate-400">Rank #{{ $p->peringkat ?? '-' }}</span>
                                @else
                                    <span class="text-xs text-slate-400">Belum ada skor</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($p->status_pembayaran === 'verified')
                                    <a href="{{ route('peserta.tiket', $p->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-[#2E6B56] hover:text-white text-xs font-semibold text-slate-700 transition-colors shadow-xs">
                                        <span class="material-symbols-outlined text-[16px]">print</span> Cetak Tiket
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">Tiket Belum Terbit</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                Anda belum memiliki pendaftaran lomba aktif. Klik <a href="{{ route('peserta.daftar') }}" class="text-[#2E6B56] font-bold underline">Daftar Lomba</a> untuk mendaftarkan burung Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
