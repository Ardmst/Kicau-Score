@extends('layouts.app', ['title' => 'Antrean Verifikasi Pembayaran'])

@section('content')
<div class="space-y-6">
    
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-xs font-bold uppercase tracking-wider">Antrean Pembayaran</span>
            </div>
            <h1 class="font-headline font-bold text-2xl text-slate-900 tracking-tight mt-1">Verifikasi & Alokasi Gantangan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Memproses bukti transfer bank & menerbitkan nomor gantangan otomatis tanpa race-condition</p>
        </div>

        <!-- Filter Status Tabs -->
        <div class="flex items-center gap-2 bg-slate-50 p-1 rounded-xl border border-slate-200 text-xs font-semibold">
            <a href="{{ route('admin.verifikasi.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ $status === 'pending' ? 'bg-white shadow-xs text-amber-800' : 'text-slate-600 hover:text-slate-900' }}">
                Pending ({{ $countPending }})
            </a>
            <a href="{{ route('admin.verifikasi.index', ['status' => 'verified']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ $status === 'verified' ? 'bg-white shadow-xs text-emerald-800' : 'text-slate-600 hover:text-slate-900' }}">
                Verified ({{ $countVerified }})
            </a>
            <a href="{{ route('admin.verifikasi.index', ['status' => 'rejected']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ $status === 'rejected' ? 'bg-white shadow-xs text-rose-800' : 'text-slate-600 hover:text-slate-900' }}">
                Rejected ({{ $countRejected }})
            </a>
        </div>
    </div>

    <!-- Verification Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-4">Gantangan</th>
                        <th class="py-3 px-4">Burung & Peserta</th>
                        <th class="py-3 px-4">Kategori & Biaya</th>
                        <th class="py-3 px-4">Metode & Bukti</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Tindakan Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pendaftarans as $p)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4">
                                @if($p->nomor_gantangan)
                                    <span class="inline-block px-3 py-1 rounded-full bg-[#10533F] text-white font-headline font-bold text-xs shadow-xs">
                                        G-{{ str_pad($p->nomor_gantangan, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum Dialokasikan</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <strong class="font-headline font-bold text-slate-900 block text-sm">{{ $p->burung->nama_burung }}</strong>
                                <span class="text-xs text-slate-500">{{ $p->burung->jenis_burung }} &bull; {{ $p->user->name }} ({{ $p->user->phone ?? '-' }})</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-800 block text-xs">{{ $p->kategoriLomba->nama_kategori }}</span>
                                <span class="text-xs font-bold text-[#2E6B56]">Rp {{ number_format($p->kategoriLomba->biaya, 0, ',', '.') }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-xs font-semibold uppercase text-slate-700 block">{{ $p->metode_pembayaran }}</span>
                                @if($p->bukti_pembayaran)
                                    <span class="text-[11px] text-[#2E6B56] font-medium flex items-center gap-1 mt-0.5">
                                        <span class="material-symbols-outlined text-[14px]">receipt_long</span> Bukti Terlampir
                                    </span>
                                @else
                                    <span class="text-[11px] text-slate-400">Tanpa Bukti Upload</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $badgeStyle = match($p->status_pembayaran) {
                                        'verified' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
                                        'rejected' => 'bg-rose-100 text-rose-800 border-rose-300',
                                    };
                                @endphp
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $badgeStyle }}">
                                    {{ strtoupper($p->status_pembayaran) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    @if($p->status_pembayaran !== 'verified')
                                        <form action="{{ route('admin.verifikasi.acc', $p->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold shadow-xs transition-colors flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[14px]">check</span> ACC & Kunci Gantangan
                                            </button>
                                        </form>
                                    @endif

                                    @if($p->status_pembayaran !== 'rejected')
                                        <form action="{{ route('admin.verifikasi.reject', $p->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold transition-colors">
                                                Tolak
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                Tidak ada data pendaftaran dengan filter status ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pendaftarans->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $pendaftarans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
