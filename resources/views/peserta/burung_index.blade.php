@extends('layouts.app', ['title' => 'Burung Saya'])

@section('content')
<div class="space-y-6">
    
    <!-- Top Action Header -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-headline font-bold text-2xl text-slate-900 tracking-tight">Koleksi Burung Saya</h1>
            <p class="text-xs text-slate-500 mt-0.5">Daftar burung kicau yang telah didaftarkan untuk kompetisi</p>
        </div>

        <button type="button" onclick="document.getElementById('modal-tambah-burung').classList.remove('hidden')" class="h-10 px-4 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition-colors">
            <span class="material-symbols-outlined text-[18px]">add</span> Tambah Burung Baru
        </button>
    </div>

    <!-- Bird Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($burungs as $burung)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col justify-between hover:border-slate-300 transition-all">
                <div>
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#10533F] to-[#2E6B56] text-white flex items-center justify-center font-headline font-bold text-lg shadow-xs">
                            {{ strtoupper(substr($burung->nama_burung, 0, 2)) }}
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">
                            {{ $burung->jenis_burung }}
                        </span>
                    </div>

                    <h2 class="font-headline font-bold text-lg text-slate-900 truncate">
                        {{ $burung->nama_burung }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        No. Ring: <span class="font-mono font-semibold text-slate-700">{{ $burung->no_ring ?? '-' }}</span>
                    </p>

                    @if($burung->catatan)
                        <p class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl mt-3 line-clamp-2">
                            {{ $burung->catatan }}
                        </p>
                    @endif
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-400">
                        {{ $burung->pendaftarans_count }} Lomba Diikuti
                    </span>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('burung.profile', $burung->slug) }}" class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs" title="Lihat Profil">
                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                        </a>
                        <form action="{{ route('peserta.burung.destroy', $burung->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus burung ini?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-lg border border-slate-200 text-rose-500 hover:bg-rose-50 text-xs" title="Hapus">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-12 text-center">
                <x-icon-bird class="w-12 h-12 text-slate-300 mx-auto mb-2" />
                <h3 class="font-headline font-bold text-base text-slate-800">Belum Ada Burung Terdaftar</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-4">Tambahkan identitas burung kicau Anda untuk bisa didaftarkan ke lomba.</p>
                <button type="button" onclick="document.getElementById('modal-tambah-burung').classList.remove('hidden')" class="h-10 px-4 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-[18px]">add</span> Tambah Burung Pertama
                </button>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal Tambah Burung Baru -->
<div id="modal-tambah-burung" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 relative">
        <button type="button" onclick="document.getElementById('modal-tambah-burung').classList.add('hidden')" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined">close</span>
        </button>

        <div class="mb-4">
            <h3 class="font-headline font-bold text-lg text-slate-900">Tambah Burung Gacoan</h3>
            <p class="text-xs text-slate-500 mt-0.5">Identitas burung akan disimpan dan dapat didaftarkan ke berbagai lomba</p>
        </div>

        <form action="{{ route('peserta.burung.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="nama_burung" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Burung</label>
                <input type="text" name="nama_burung" id="nama_burung" required placeholder="Contoh: Samurai X"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div>
                <label for="jenis_burung" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Jenis Burung</label>
                <select name="jenis_burung" id="jenis_burung" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                    <option value="Murai Batu">Murai Batu</option>
                    <option value="Lovebird">Lovebird</option>
                    <option value="Cucak Ijo">Cucak Ijo</option>
                    <option value="Kacer">Kacer</option>
                    <option value="Kenari">Kenari</option>
                    <option value="Pleci">Pleci</option>
                    <option value="Anis Merah">Anis Merah</option>
                </select>
            </div>

            <div>
                <label for="no_ring" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nomor Ring (Opsional)</label>
                <input type="text" name="no_ring" id="no_ring" placeholder="Contoh: BNR-SOLO-0921"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div>
                <label for="catatan" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Karakter & Materi Lagu (Opsional)</label>
                <textarea name="catatan" id="catatan" rows="3" placeholder="Gaya tarung, variasi tembakan isian..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-tambah-burung').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold shadow-sm">
                    Simpan Burung
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
