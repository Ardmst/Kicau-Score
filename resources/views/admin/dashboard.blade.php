@extends('layouts.app', ['title' => 'Panel Admin & Rundown Manager'])

@section('content')
<div class="space-y-6">
    
    <!-- Admin Header -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-slate-900 text-white text-xs font-bold uppercase tracking-wider">PANEL ADMINISTRATOR</span>
            </div>
            <h1 class="font-headline font-bold text-2xl text-slate-900 tracking-tight mt-1">
                Kontrol Turnamen & Jadwal Perlombaan
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Manajemen rundown event, validasi penguncian skor juri, dan verifikasi gantangan</p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.events.index') }}" class="h-10 px-4 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-[18px]">calendar_add_on</span> Master Event
            </a>
            <a href="{{ route('admin.verifikasi.index') }}" class="h-10 px-4 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition-colors">
                <span class="material-symbols-outlined text-[18px]">verified</span> Antrean Verifikasi ({{ $totalPending }})
            </a>
        </div>
    </div>

    <!-- Quick Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 block">Peserta Gantangan Lomba</span>
            <span class="font-headline font-bold text-2xl text-slate-900 mt-1 block">{{ $totalPeserta }}</span>
            <span class="text-[11px] text-emerald-700 font-medium">Terverifikasi di arena</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 block">Antrean Pembayaran Pending</span>
            <span class="font-headline font-bold text-2xl text-amber-600 mt-1 block">{{ $totalPending }}</span>
            <span class="text-[11px] text-slate-400">Perlu diverifikasi admin</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 block">Total Kategori Lomba</span>
            <span class="font-headline font-bold text-2xl text-slate-900 mt-1 block">{{ $totalKategori }}</span>
            <span class="text-[11px] text-slate-400">Di semua event</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 block">Dewan Juri Bersertifikat</span>
            <span class="font-headline font-bold text-2xl text-slate-900 mt-1 block">{{ $totalJuri }}</span>
            <a href="{{ route('admin.users.index') }}" class="text-[11px] text-[#2E6B56] font-semibold hover:underline block">Kelola Juri &rarr;</a>
        </div>
    </div>

    <!-- Active Event Rundown & Category Control -->
    @if($activeEvent)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">EVENT AKTIF</span>
                    <h2 class="font-headline font-bold text-lg text-slate-900">
                        {{ $activeEvent->nama_event }} ({{ $activeEvent->lokasi }})
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Ubah status lomba bertahap atau kunci sesi penilaian juri</p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="document.getElementById('modal-tambah-kategori').classList.remove('hidden')" class="h-9 px-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700 flex items-center gap-1.5 transition-colors">
                        <span class="material-symbols-outlined text-[16px]">add</span> Tambah Kategori
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                            <th class="py-3 px-4">Kategori Lomba</th>
                            <th class="py-3 px-4">Biaya</th>
                            <th class="py-3 px-4 text-center">Gantangan Terisi</th>
                            <th class="py-3 px-4 text-center">Status Sesi</th>
                            <th class="py-3 px-4">Ubah Status Sesi</th>
                            <th class="py-3 px-4 text-center">Aksi Penguncian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($activeEvent->kategoriLombas as $kat)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4">
                                    <strong class="font-headline font-bold text-slate-900 block text-sm">{{ $kat->nama_kategori }}</strong>
                                    <span class="text-xs text-slate-400 font-mono">slug: {{ $kat->slug }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-xs font-bold text-slate-700">
                                    Rp {{ number_format($kat->biaya, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-800 text-xs font-bold">
                                        {{ $kat->verified_count }} / {{ $kat->kuota }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @php
                                        $badgeColor = match($kat->status_lomba) {
                                            'ongoing' => 'bg-emerald-100 text-emerald-800 border-emerald-300 font-bold',
                                            'upcoming' => 'bg-amber-100 text-amber-800 border-amber-300 font-semibold',
                                            'validasi' => 'bg-blue-100 text-blue-800 border-blue-300 font-bold',
                                            'finished' => 'bg-slate-100 text-slate-700 border-slate-200 font-semibold',
                                        };
                                    @endphp
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs border {{ $badgeColor }}">
                                        {{ strtoupper($kat->status_lomba) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <form action="{{ route('admin.kategori.status', $kat->id) }}" method="POST" class="flex items-center gap-2">
                                        @csrf
                                        <select name="status_lomba" class="text-xs bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 focus:outline-none focus:ring-1 focus:ring-[#2E6B56]">
                                            <option value="upcoming" {{ $kat->status_lomba === 'upcoming' ? 'selected' : '' }}>upcoming</option>
                                            <option value="ongoing" {{ $kat->status_lomba === 'ongoing' ? 'selected' : '' }}>ongoing</option>
                                            <option value="validasi" {{ $kat->status_lomba === 'validasi' ? 'selected' : '' }}>validasi (kunci)</option>
                                            <option value="finished" {{ $kat->status_lomba === 'finished' ? 'selected' : '' }}>finished</option>
                                        </select>
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-slate-800 text-white text-xs hover:bg-slate-700 transition-colors">
                                            Update
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($kat->status_lomba === 'ongoing')
                                        <form action="{{ route('admin.kategori.status', $kat->id) }}" method="POST" onsubmit="return confirm('Kunci semua penilaian juri dan hitung tie-breaker untuk kategori ini?');" class="inline">
                                            @csrf
                                            <input type="hidden" name="status_lomba" value="validasi">
                                            <button type="submit" class="px-3 py-1 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold flex items-center gap-1 mx-auto shadow-xs">
                                                <span class="material-symbols-outlined text-[14px]">lock</span> Validasi & Kunci
                                            </button>
                                        </form>
                                    @elseif($kat->status_lomba === 'validasi')
                                        <form action="{{ route('admin.kategori.status', $kat->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status_lomba" value="finished">
                                            <button type="submit" class="px-3 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold flex items-center gap-1 mx-auto shadow-xs">
                                                <span class="material-symbols-outlined text-[14px]">check_circle</span> Selesaikan Lomba
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400 font-mono">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Recent Pending Payment Verifications -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="font-headline font-bold text-lg text-slate-900">Antrean Verifikasi Pembayaran Terbaru</h2>
                <p class="text-xs text-slate-500 mt-0.5">Klik ACC untuk otomatis mengunci dan menerbitkan nomor gantangan</p>
            </div>
            <a href="{{ route('admin.verifikasi.index') }}" class="text-xs font-semibold text-[#2E6B56] hover:underline flex items-center gap-1">
                Lihat Semua Antrean ({{ $totalPending }}) &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-4">Peserta & Burung</th>
                        <th class="py-3 px-4">Kategori Lomba</th>
                        <th class="py-3 px-4">Metode</th>
                        <th class="py-3 px-4">Waktu Daftar</th>
                        <th class="py-3 px-4 text-center">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentPending as $rp)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4">
                                <strong class="font-bold text-slate-900 block">{{ $rp->burung->nama_burung }}</strong>
                                <span class="text-xs text-slate-500">Pemilik: {{ $rp->user->name }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-xs font-semibold text-slate-800 block">{{ $rp->kategoriLomba->nama_kategori }}</span>
                                <span class="text-[11px] text-slate-500">Rp {{ number_format($rp->kategoriLomba->biaya, 0, ',', '.') }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-xs font-medium uppercase text-slate-600">
                                {{ $rp->metode_pembayaran }}
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-400">
                                {{ $rp->created_at->diffForHumans() }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <form action="{{ route('admin.verifikasi.acc', $rp->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold shadow-xs transition-colors">
                                            ACC & Terbitkan Gantangan
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.verifikasi.reject', $rp->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold transition-colors">
                                            Tolak
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                Tidak ada antrean pendaftaran pending saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Kategori -->
@if($activeEvent)
    <div id="modal-tambah-kategori" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 relative">
            <button type="button" onclick="document.getElementById('modal-tambah-kategori').classList.add('hidden')" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>

            <div class="mb-4">
                <h3 class="font-headline font-bold text-lg text-slate-900">Tambah Kategori Lomba</h3>
                <p class="text-xs text-slate-500 mt-0.5">Event: {{ $activeEvent->nama_event }}</p>
            </div>

            <form action="{{ route('admin.kategori.store', $activeEvent->id) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="nama_kategori" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Kategori</label>
                    <input type="text" name="nama_kategori" id="nama_kategori" required placeholder="Contoh: Kenari Bebas VIP"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>

                <div>
                    <label for="biaya" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Biaya Pendaftaran (Rp)</label>
                    <input type="number" name="biaya" id="biaya" required min="0" step="5000" placeholder="150000"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>

                <div>
                    <label for="kuota" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Kuota Gantangan</label>
                    <input type="number" name="kuota" id="kuota" required min="1" max="100" value="36"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>

                <div>
                    <label for="status_lomba" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Status Awal</label>
                    <select name="status_lomba" id="status_lomba" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                        <option value="upcoming">upcoming (Persiapan)</option>
                        <option value="ongoing">ongoing (Sedang Dinilai)</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('modal-tambah-kategori').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold shadow-sm">
                        Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
