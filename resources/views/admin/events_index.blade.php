@extends('layouts.app', ['title' => 'Master Event & Kategori'])

@section('content')
<div class="space-y-6">
    
    <!-- Top Action Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-800 text-xs font-bold uppercase tracking-wider">Master Data</span>
                <span class="text-xs text-slate-400">&bull;</span>
                <span class="text-xs text-emerald-700 font-medium">Manajemen Terpadu Event, Kategori & Juri</span>
            </div>
            <h1 class="font-headline font-bold text-2xl text-slate-900 tracking-tight mt-1">Master Event & Turnamen</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola event, tanggal & jam pelaksanaan, status sesi, kunci nilai, serta penugasan dewan juri dalam satu panel terpusat.</p>
        </div>

        <button type="button" onclick="document.getElementById('modal-tambah-event').classList.remove('hidden')" class="h-10 px-4 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition-colors whitespace-nowrap">
            <span class="material-symbols-outlined text-[18px]">add</span> Buat Event Baru
        </button>
    </div>

    <!-- Events List -->
    <div class="space-y-6">
        @forelse($events as $event)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                
                <!-- Event Header & Actions -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h2 class="font-headline font-bold text-xl text-slate-900">{{ $event->nama_event }}</h2>
                            @if($event->status === 'active')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    ACTIVE
                                </span>
                            @elseif($event->status === 'finished')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-slate-100 text-slate-700 border border-slate-300">
                                    FINISHED
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-amber-100 text-amber-800 border border-amber-200">
                                    DRAFT
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-slate-500 flex flex-wrap items-center gap-3">
                            <span class="inline-flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px] text-[#2E6B56]">location_on</span> {{ $event->lokasi }}
                            </span>
                            <span>&bull;</span>
                            <span class="inline-flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px] text-slate-400">calendar_month</span> {{ $event->tanggal->isoFormat('D MMMM Y') }}
                            </span>
                            <span>&bull;</span>
                            <span class="inline-flex items-center gap-1 font-semibold text-slate-700">
                                <span class="material-symbols-outlined text-[16px] text-emerald-600">schedule</span> {{ $event->jam_mulai ?? '08:00' }} - {{ $event->jam_selesai ?? '17:00' }} WIB
                            </span>
                        </p>

                        @if($event->deskripsi)
                            <p class="text-[11px] text-slate-500 pt-0.5 line-clamp-1 italic">"{{ $event->deskripsi }}"</p>
                        @endif
                    </div>

                    <!-- Event Control Buttons -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <!-- Tambah Kategori -->
                        <button type="button" 
                                onclick="openTambahKategoriModal('{{ $event->id }}', '{{ addslashes($event->nama_event) }}')" 
                                class="h-9 px-3 rounded-xl border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-semibold flex items-center gap-1 transition-colors">
                            <span class="material-symbols-outlined text-[16px]">add_circle</span> Tambah Kategori
                        </button>

                        <!-- Edit Event -->
                        <button type="button" 
                                onclick='openEditEventModal(@json($event))'
                                class="h-9 px-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-1 transition-colors">
                            <span class="material-symbols-outlined text-[16px] text-slate-500">edit</span> Edit Event
                        </button>

                        <!-- Hapus Event -->
                        <form action="{{ route('admin.events.destroy', $event) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Event {{ addslashes($event->nama_event) }} beserta seluruh kategorinya? Data yang dihapus tidak dapat dipulihkan!')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Hapus Event" class="h-9 w-9 rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 flex items-center justify-center transition-colors">
                                <span class="material-symbols-outlined text-[16px]">delete</span>
                            </button>
                        </form>

                        <span class="text-xs text-slate-400 font-medium px-2 py-1 bg-slate-50 rounded-lg border border-slate-100">
                            {{ $event->kategoriLombas->count() }} Kategori
                        </span>
                    </div>
                </div>

                <!-- Categories List -->
                <div>
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2.5">Kategori Sesi Lomba di Event Ini:</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                        @forelse($event->kategoriLombas as $kat)
                            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 hover:bg-white hover:shadow-sm transition-all space-y-2.5 flex flex-col justify-between">
                                <div class="space-y-1.5">
                                    <!-- Header Kategori -->
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="font-headline font-bold text-sm text-slate-900 leading-tight">{{ $kat->nama_kategori }}</span>
                                        
                                        @if($kat->status_lomba === 'ongoing')
                                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1 shrink-0 animate-pulse">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Ongoing
                                            </span>
                                        @elseif($kat->status_lomba === 'validasi')
                                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200 inline-flex items-center gap-1 shrink-0">
                                                <span class="material-symbols-outlined text-[12px]">lock</span> Terkunci
                                            </span>
                                        @elseif($kat->status_lomba === 'finished')
                                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 border border-slate-300 shrink-0">
                                                Finished
                                            </span>
                                        @else
                                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-sky-50 text-sky-700 border border-sky-200 shrink-0">
                                                Upcoming
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Meta Info -->
                                    <div class="text-[11px] text-slate-600 flex items-center justify-between">
                                        <span class="inline-flex items-center gap-1 font-medium">
                                            <span class="material-symbols-outlined text-[14px] text-emerald-600">cruelty_free</span>
                                            {{ $kat->jenis_burung ?? 'Murai Batu' }}
                                        </span>
                                        <span class="text-[10px] bg-white border border-slate-200 px-1.5 py-0.5 rounded text-slate-600 font-medium">
                                            {{ $kat->jam_mulai ?? '08:00' }} - {{ $kat->jam_selesai ?? '17:00' }} WIB
                                        </span>
                                    </div>

                                    <div class="text-xs text-slate-500 flex justify-between pt-1 border-t border-slate-200/70">
                                        <span>Biaya: <strong>Rp {{ number_format($kat->biaya, 0, ',', '.') }}</strong></span>
                                        <span>Gantangan: <strong>{{ $kat->verified_count }} / {{ $kat->kuota }}</strong></span>
                                    </div>

                                    <!-- Dewan Juri Penilai -->
                                    <div class="text-[11px] text-slate-500 pt-1.5 border-t border-slate-200/70">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="font-medium text-slate-600">Dewan Juri:</span>
                                            <span class="font-bold text-emerald-800 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200 text-[10px]">
                                                {{ $kat->juris->count() }} Juri
                                            </span>
                                        </div>
                                        @if($kat->juris->isNotEmpty())
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($kat->juris as $assignedJuri)
                                                    <span class="inline-block px-1.5 py-0.5 rounded bg-slate-200/80 text-[10px] font-mono font-semibold text-slate-700" title="{{ $assignedJuri->name }}">
                                                        {{ $assignedJuri->kode_id }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-[10px] text-slate-400 italic">Semua juri dapat menilai</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Action Buttons: Quick Status + All-in-One Edit Modal -->
                                <div class="pt-2 border-t border-slate-200/70 space-y-1.5">
                                    
                                    <!-- Quick Action Button (1-Click Status & Kunci) -->
                                    @if($kat->status_lomba === 'upcoming')
                                        <form action="{{ route('admin.kategori.status', $kat) }}" method="POST" class="w-full">
                                            @csrf
                                            <input type="hidden" name="status_lomba" value="ongoing">
                                            <button type="submit" class="w-full py-1.5 px-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-semibold flex items-center justify-center gap-1 shadow-xs transition-colors">
                                                <span class="material-symbols-outlined text-[15px]">play_arrow</span> Mulai Sesi (Ongoing)
                                            </button>
                                        </form>
                                    @elseif($kat->status_lomba === 'ongoing')
                                        <form action="{{ route('admin.kategori.status', $kat) }}" method="POST" onsubmit="return confirm('Kunci nilai juri dan rekap skor akhir untuk kategori ini?')" class="w-full">
                                            @csrf
                                            <input type="hidden" name="status_lomba" value="validasi">
                                            <button type="submit" class="w-full py-1.5 px-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-bold flex items-center justify-center gap-1 shadow-xs transition-colors">
                                                <span class="material-symbols-outlined text-[15px]">lock</span> Kunci & Validasi Nilai
                                            </button>
                                        </form>
                                    @elseif($kat->status_lomba === 'validasi')
                                        <form action="{{ route('admin.kategori.status', $kat) }}" method="POST" onsubmit="return confirm('Tandai kategori ini Selesai (Finished)?')" class="w-full">
                                            @csrf
                                            <input type="hidden" name="status_lomba" value="finished">
                                            <button type="submit" class="w-full py-1.5 px-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[11px] font-bold flex items-center justify-center gap-1 shadow-xs transition-colors">
                                                <span class="material-symbols-outlined text-[15px]">verified</span> Selesaikan Kategori
                                            </button>
                                        </form>
                                    @else
                                        <div class="py-1 px-2 rounded-lg bg-slate-100 text-slate-500 text-[10px] font-semibold text-center flex items-center justify-center gap-1">
                                            <span class="material-symbols-outlined text-[14px] text-amber-500">workspace_premium</span> Kategori Selesai
                                        </div>
                                    @endif

                                    <!-- All-in-One Edit / Kelola Kategori Button -->
                                    <button type="button" 
                                            onclick='openEditKategoriModal(@json($kat), @json($kat->juris->pluck("id")))'
                                            class="w-full py-1.5 px-2 rounded-lg bg-white hover:bg-slate-100 text-slate-700 text-[11px] font-semibold border border-slate-300 flex items-center justify-center gap-1 transition-colors">
                                        <span class="material-symbols-outlined text-[15px] text-slate-500">settings</span> Kelola / Edit Lengkap
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full py-6 text-center text-xs text-slate-400 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                Belum ada kategori kelas lomba. Klik "+ Tambah Kategori" di atas untuk menambahkan.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400">
                Belum ada master event yang dibuat.
            </div>
        @endforelse
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 1: BUAT EVENT BARU                    -->
<!-- ========================================== -->
<div id="modal-tambah-event" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 relative">
        <button type="button" onclick="document.getElementById('modal-tambah-event').classList.add('hidden')" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined">close</span>
        </button>

        <div class="mb-4">
            <h3 class="font-headline font-bold text-lg text-slate-900">Buat Master Event Baru</h3>
            <p class="text-xs text-slate-500 mt-0.5">Konfigurasi nama event, jadwal tanggal & jam, dan lokasi arena gantangan</p>
        </div>

        <form action="{{ route('admin.events.store') }}" method="POST" class="space-y-3.5">
            @csrf

            <div>
                <label for="nama_event" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Event</label>
                <input type="text" name="nama_event" id="nama_event" required placeholder="Contoh: Piala Presiden Kicau 2026"
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div>
                <label for="tanggal" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Tanggal Pelaksanaan</label>
                <input type="date" name="tanggal" id="tanggal" required
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="jam_mulai" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Jam Mulai</label>
                    <input type="time" name="jam_mulai" id="jam_mulai" value="08:30"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>
                <div>
                    <label for="jam_selesai" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Jam Selesai</label>
                    <input type="time" name="jam_selesai" id="jam_selesai" value="17:00"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>
            </div>

            <div>
                <label for="lokasi" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Lokasi Gantangan</label>
                <input type="text" name="lokasi" id="lokasi" required placeholder="Contoh: Arena Gantangan Alun-Alun, Solo"
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div>
                <label for="deskripsi" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Deskripsi Singkat (Opsional)</label>
                <textarea name="deskripsi" id="deskripsi" rows="2" placeholder="Informasi sponsor, dewan juri, total hadiah..."
                          class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]"></textarea>
            </div>

            <div>
                <label for="status" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Status Event</label>
                <select name="status" id="status" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                    <option value="active">Active (Publik & Dibuka)</option>
                    <option value="draft">Draft (Belum Dibuka)</option>
                    <option value="finished">Finished (Selesai)</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-tambah-event').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold shadow-sm">
                    Simpan Event
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: EDIT EVENT (LENGKAP)               -->
<!-- ========================================== -->
<div id="modal-edit-event" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 relative">
        <button type="button" onclick="document.getElementById('modal-edit-event').classList.add('hidden')" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined">close</span>
        </button>

        <div class="mb-4">
            <h3 class="font-headline font-bold text-lg text-slate-900">Edit Master Event</h3>
            <p class="text-xs text-slate-500 mt-0.5">Perbarui informasi event, tanggal, jam pelaksanaan, dan status</p>
        </div>

        <form id="form-edit-event" method="POST" class="space-y-3.5">
            @csrf
            @method('PUT')

            <div>
                <label for="edit_nama_event" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Event</label>
                <input type="text" name="nama_event" id="edit_nama_event" required
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div>
                <label for="edit_tanggal" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Tanggal Pelaksanaan</label>
                <input type="date" name="tanggal" id="edit_tanggal" required
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="edit_jam_mulai" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Jam Mulai</label>
                    <input type="time" name="jam_mulai" id="edit_jam_mulai"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>
                <div>
                    <label for="edit_jam_selesai" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Jam Selesai</label>
                    <input type="time" name="jam_selesai" id="edit_jam_selesai"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>
            </div>

            <div>
                <label for="edit_lokasi" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Lokasi Gantangan</label>
                <input type="text" name="lokasi" id="edit_lokasi" required
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div>
                <label for="edit_deskripsi" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Deskripsi Singkat</label>
                <textarea name="deskripsi" id="edit_deskripsi" rows="2"
                          class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]"></textarea>
            </div>

            <div>
                <label for="edit_status" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Status Event</label>
                <select name="status" id="edit_status" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                    <option value="active">Active (Publik & Dibuka)</option>
                    <option value="draft">Draft (Belum Dibuka)</option>
                    <option value="finished">Finished (Selesai)</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-edit-event').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold shadow-sm">
                    Perbarui Event
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 3: TAMBAH KATEGORI BARU               -->
<!-- ========================================== -->
<div id="modal-tambah-kategori" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-lg w-full p-6 relative max-h-[92vh] overflow-y-auto">
        <button type="button" onclick="document.getElementById('modal-tambah-kategori').classList.add('hidden')" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined">close</span>
        </button>

        <div class="mb-4">
            <h3 class="font-headline font-bold text-lg text-slate-900">Tambah Kategori Lomba Baru</h3>
            <p class="text-xs text-slate-500 mt-0.5">Event: <strong id="kategori-event-name" class="text-slate-800"></strong></p>
        </div>

        <form id="form-tambah-kategori" method="POST" class="space-y-3.5">
            @csrf

            <div>
                <label for="nama_kategori" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Kategori / Kelas</label>
                <input type="text" name="nama_kategori" id="nama_kategori" required placeholder="Contoh: Murai Batu VIP 36G"
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div>
                <label for="jenis_burung" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Jenis Burung yang Dilombakan</label>
                <select name="jenis_burung" id="jenis_burung" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                    @foreach(\App\Models\KategoriLomba::JENIS_BURUNG_LIST as $jb)
                        <option value="{{ $jb }}">{{ $jb }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="biaya" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Biaya Tiket (Rp)</label>
                    <input type="number" name="biaya" id="biaya" required min="0" value="150000"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>
                <div>
                    <label for="kuota" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Kuota Gantangan</label>
                    <input type="number" name="kuota" id="kuota" required min="1" value="36"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="kat_jam_mulai" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Jadwal Sesi Mulai</label>
                    <input type="time" name="jam_mulai" id="kat_jam_mulai" value="09:00"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>
                <div>
                    <label for="kat_jam_selesai" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Jadwal Sesi Selesai</label>
                    <input type="time" name="jam_selesai" id="kat_jam_selesai" value="10:30"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>
            </div>

            <div>
                <label for="status_lomba" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Status Sesi</label>
                <select name="status_lomba" id="status_lomba" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                    <option value="upcoming">Upcoming (Mendatang / Pendaftaran Buka)</option>
                    <option value="ongoing">Ongoing (Sedang Berlangsung)</option>
                    <option value="validasi">Validasi (🔒 Kunci Skor Nilai Juri)</option>
                    <option value="finished">Finished (Selesai & Diumumkan)</option>
                </select>
            </div>

            <!-- Tugaskan Juri -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Tugaskan Dewan Juri Penilai</label>
                <p class="text-[11px] text-slate-500 mb-2">Centang juri yang berhak menilai di kategori ini:</p>
                <div class="space-y-1.5 max-h-36 overflow-y-auto p-2.5 rounded-xl border border-slate-200 bg-slate-50/50">
                    @forelse($allJuris as $juri)
                        <label class="flex items-center gap-2.5 p-1.5 rounded-lg hover:bg-white transition cursor-pointer text-xs">
                            <input type="checkbox" name="juri_ids[]" value="{{ $juri->id }}" class="rounded text-[#2E6B56] focus:ring-[#2E6B56]">
                            <span class="font-medium text-slate-800 flex-1">{{ $juri->name }}</span>
                            <span class="font-mono text-[10px] text-emerald-700 bg-emerald-100/70 px-1.5 py-0.2 rounded font-bold">{{ $juri->kode_id }}</span>
                        </label>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada akun juri yang terdaftar.</p>
                    @endforelse
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-tambah-kategori').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold shadow-sm">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 4: ALL-IN-ONE EDIT KATEGORI LOMBA     -->
<!-- (Update Info, Jam, Kunci Status & Juri)     -->
<!-- ========================================== -->
<div id="modal-edit-kategori" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-lg w-full p-6 relative max-h-[92vh] overflow-y-auto">
        <button type="button" onclick="document.getElementById('modal-edit-kategori').classList.add('hidden')" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined">close</span>
        </button>

        <div class="mb-4">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-bold uppercase tracking-wider">Kelola Terpadu</span>
            </div>
            <h3 class="font-headline font-bold text-lg text-slate-900 mt-1">Edit Kategori & Pengaturan Sesi</h3>
            <p class="text-xs text-slate-500 mt-0.5">Ubah info, jadwal jam sesi, status kunci nilai, serta dewan juri dalam satu formulir.</p>
        </div>

        <form id="form-edit-kategori" method="POST" class="space-y-3.5">
            @csrf
            @method('PUT')

            <div>
                <label for="edit_nama_kategori" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Kategori / Kelas</label>
                <input type="text" name="nama_kategori" id="edit_nama_kategori" required
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div>
                <label for="edit_jenis_burung" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Jenis Burung</label>
                <select name="jenis_burung" id="edit_jenis_burung" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                    @foreach(\App\Models\KategoriLomba::JENIS_BURUNG_LIST as $jb)
                        <option value="{{ $jb }}">{{ $jb }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="edit_biaya" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Biaya Tiket (Rp)</label>
                    <input type="number" name="biaya" id="edit_biaya" required min="0"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>
                <div>
                    <label for="edit_kuota" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Kuota Gantangan</label>
                    <input type="number" name="kuota" id="edit_kuota" required min="1"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="edit_kat_jam_mulai" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Jadwal Sesi Mulai</label>
                    <input type="time" name="jam_mulai" id="edit_kat_jam_mulai"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>
                <div>
                    <label for="edit_kat_jam_selesai" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Jadwal Sesi Selesai</label>
                    <input type="time" name="jam_selesai" id="edit_kat_jam_selesai"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>
            </div>

            <!-- Status Lomba / Penguncian Nilai -->
            <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-200 space-y-1">
                <label for="edit_kat_status_lomba" class="block text-xs font-bold text-emerald-950 uppercase tracking-wider">
                    Status Sesi & Penguncian Nilai
                </label>
                <select name="status_lomba" id="edit_kat_status_lomba" required class="w-full px-3 py-2 rounded-lg border border-emerald-300 bg-white text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                    <option value="upcoming">Upcoming (Persiapan / Pendaftaran Buka)</option>
                    <option value="ongoing">Ongoing (Sedang Berlangsung - Penjurian Terbuka)</option>
                    <option value="validasi">🔒 Validasi (Kunci Nilai Juri & Rekap Skor)</option>
                    <option value="finished">Finished (Selesai & Juara Diumumkan)</option>
                </select>
                <p class="text-[10px] text-emerald-800">
                    *Pilih <strong>Validasi</strong> atau <strong>Finished</strong> untuk mengunci form input skor juri dan menghitung podium juara.
                </p>
            </div>

            <!-- Tugaskan Dewan Juri -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Penugasan Dewan Juri</label>
                <p class="text-[11px] text-slate-500 mb-1.5">Centang juri yang ditugaskan menilai kelas ini:</p>
                <div class="space-y-1.5 max-h-40 overflow-y-auto p-2.5 rounded-xl border border-slate-200 bg-slate-50/50">
                    @forelse($allJuris as $juri)
                        <label class="flex items-center gap-2.5 p-1.5 rounded-lg hover:bg-white transition cursor-pointer text-xs border border-transparent hover:border-slate-200">
                            <input type="checkbox" name="juri_ids[]" value="{{ $juri->id }}" class="edit-juri-checkbox rounded text-[#2E6B56] focus:ring-[#2E6B56]">
                            <span class="font-medium text-slate-800 flex-1">{{ $juri->name }}</span>
                            <span class="font-mono text-[10px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.2 rounded">{{ $juri->kode_id }}</span>
                        </label>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada akun juri yang terdaftar.</p>
                    @endforelse
                </div>
            </div>

            <!-- Actions: Delete & Save -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-200">
                <button type="button" onclick="hapusKategoriAktif()" class="px-3 py-2 rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold flex items-center gap-1 transition-colors">
                    <span class="material-symbols-outlined text-[15px]">delete</span> Hapus
                </button>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="document.getElementById('modal-edit-kategori').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>

        <!-- Hidden Delete Form for Kategori -->
        <form id="form-delete-kategori" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

@push('scripts')
<script>
    // 1. Tambah Kategori
    function openTambahKategoriModal(eventId, eventName) {
        document.getElementById('form-tambah-kategori').action = `/admin/events/${eventId}/kategori`;
        document.getElementById('kategori-event-name').textContent = eventName;
        document.getElementById('modal-tambah-kategori').classList.remove('hidden');
    }

    // 2. Edit Event
    function openEditEventModal(event) {
        document.getElementById('form-edit-event').action = `/admin/events/${event.id}`;
        document.getElementById('edit_nama_event').value = event.nama_event;
        
        // Format tanggal Y-m-d
        let tgl = event.tanggal;
        if (tgl && tgl.includes('T')) {
            tgl = tgl.split('T')[0];
        }
        document.getElementById('edit_tanggal').value = tgl;
        document.getElementById('edit_jam_mulai').value = event.jam_mulai || '08:00';
        document.getElementById('edit_jam_selesai').value = event.jam_selesai || '17:00';
        document.getElementById('edit_lokasi').value = event.lokasi;
        document.getElementById('edit_deskripsi').value = event.deskripsi || '';
        document.getElementById('edit_status').value = event.status;

        document.getElementById('modal-edit-event').classList.remove('hidden');
    }

    // 3. Edit Kategori (All-in-One)
    let currentEditKategoriId = null;
    function openEditKategoriModal(kategori, assignedJuriIds) {
        currentEditKategoriId = kategori.id;
        document.getElementById('form-edit-kategori').action = `/admin/kategori/${kategori.id}`;
        document.getElementById('edit_nama_kategori').value = kategori.nama_kategori;
        document.getElementById('edit_jenis_burung').value = kategori.jenis_burung || 'Murai Batu';
        document.getElementById('edit_biaya').value = Math.round(kategori.biaya);
        document.getElementById('edit_kuota').value = kategori.kuota;
        document.getElementById('edit_kat_jam_mulai').value = kategori.jam_mulai || '09:00';
        document.getElementById('edit_kat_jam_selesai').value = kategori.jam_selesai || '10:30';
        document.getElementById('edit_kat_status_lomba').value = kategori.status_lomba;

        // Check assigned juries
        document.querySelectorAll('.edit-juri-checkbox').forEach(cb => {
            cb.checked = assignedJuriIds.includes(parseInt(cb.value));
        });

        document.getElementById('modal-edit-kategori').classList.remove('hidden');
    }

    function hapusKategoriAktif() {
        if (!currentEditKategoriId) return;
        if (confirm('Apakah Anda yakin ingin menghapus kategori ini? Data nilai dan pendaftaran pada kategori ini akan ikut terhapus.')) {
            const formDel = document.getElementById('form-delete-kategori');
            formDel.action = `/admin/kategori/${currentEditKategoriId}`;
            formDel.submit();
        }
    }
</script>
@endpush
@endsection
