@extends('layouts.app', ['title' => 'Master Event & Kategori'])

@section('content')
<div class="space-y-6">
    
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-800 text-xs font-bold uppercase tracking-wider">Master Data</span>
            </div>
            <h1 class="font-headline font-bold text-2xl text-slate-900 tracking-tight mt-1">Master Event & Turnamen</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola event, tanggal & jam pelaksanaan, lokasi gantangan, dan kategori lomba</p>
        </div>

        <button type="button" onclick="document.getElementById('modal-tambah-event').classList.remove('hidden')" class="h-10 px-4 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition-colors">
            <span class="material-symbols-outlined text-[18px]">add</span> Buat Event Baru
        </button>
    </div>

    <!-- Events List -->
    <div class="space-y-6">
        @forelse($events as $event)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-headline font-bold text-xl text-slate-900">{{ $event->nama_event }}</h2>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase {{ $event->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ $event->status }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-3">
                            <span>
                                <span class="material-symbols-outlined text-[16px] text-[#2E6B56] align-middle">location_on</span> {{ $event->lokasi }}
                            </span>
                            <span>&bull;</span>
                            <span>
                                <span class="material-symbols-outlined text-[16px] text-slate-400 align-middle">calendar_month</span> {{ $event->tanggal->isoFormat('D MMMM Y') }}
                            </span>
                            <span>&bull;</span>
                            <span class="font-semibold text-slate-700">
                                <span class="material-symbols-outlined text-[16px] text-emerald-600 align-middle">schedule</span> {{ $event->jam_mulai ?? '08:00' }} - {{ $event->jam_selesai ?? '17:00' }} WIB
                            </span>
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="openTambahKategoriModal('{{ $event->id }}', '{{ addslashes($event->nama_event) }}')" class="h-9 px-3 rounded-xl border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-semibold flex items-center gap-1 transition-colors">
                            <span class="material-symbols-outlined text-[16px]">add_circle</span> Tambah Kategori
                        </button>
                        <span class="text-xs text-slate-400 px-2">
                            {{ $event->kategoriLombas->count() }} Kategori
                        </span>
                    </div>
                </div>

                <!-- Categories in this Event -->
                <div>
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kategori Lomba di Event Ini:</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        @forelse($event->kategoriLombas as $kat)
                            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-headline font-bold text-sm text-slate-900 truncate">{{ $kat->nama_kategori }}</span>
                                    <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-white border border-slate-200 text-slate-700">
                                        {{ $kat->status_lomba }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-600 flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px] text-emerald-600">cruelty_free</span>
                                        <strong>{{ $kat->jenis_burung ?? 'Murai Batu' }}</strong>
                                    </span>
                                    @if($kat->jam_mulai)
                                        <span class="text-[10px] bg-slate-200/80 px-1.5 py-0.5 rounded text-slate-700 font-medium">
                                            {{ $kat->jam_mulai }} WIB
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500 flex justify-between pt-1 border-t border-slate-200/60">
                                    <span>Biaya: <strong>Rp {{ number_format($kat->biaya, 0, ',', '.') }}</strong></span>
                                    <span>Gantangan: <strong>{{ $kat->verified_count }} / {{ $kat->kuota }}</strong></span>
                                </div>
                                <div class="text-[11px] text-slate-500 pt-1.5 border-t border-slate-200/60">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-medium text-slate-600">Dewan Juri:</span>
                                        <span class="font-bold text-emerald-800 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200 text-[10px]">
                                            {{ $kat->juris->count() }} Juri
                                        </span>
                                    </div>
                                    @if($kat->juris->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($kat->juris as $assignedJuri)
                                                <span class="inline-block px-1.5 py-0.5 rounded bg-slate-200/70 text-[10px] font-mono font-semibold text-slate-700" title="{{ $assignedJuri->name }}">
                                                    {{ $assignedJuri->kode_id }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">Semua juri terdaftar</span>
                                    @endif
                                </div>
                                <button type="button" 
                                        onclick='openAturJuriModal({{ $kat->id }}, "{{ addslashes($kat->nama_kategori) }}", @json($kat->juris->pluck("id")))'
                                        class="w-full mt-2 py-1 px-2 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-[11px] font-semibold border border-emerald-200 flex items-center justify-center gap-1 transition-colors">
                                    <span class="material-symbols-outlined text-[15px]">gavel</span> Atur Dewan Juri
                                </button>
                            </div>
                        @empty
                            <div class="col-span-full py-4 text-center text-xs text-slate-400 bg-slate-50 rounded-xl border border-dashed border-slate-200">
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

<!-- Modal Buat Event Baru -->
<div id="modal-tambah-event" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 relative">
        <button type="button" onclick="document.getElementById('modal-tambah-event').classList.add('hidden')" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined">close</span>
        </button>

        <div class="mb-4">
            <h3 class="font-headline font-bold text-lg text-slate-900">Buat Master Event Baru</h3>
            <p class="text-xs text-slate-500 mt-0.5">Konfigurasi nama event, jadwal tanggal & jam, dan lokasi arena gantangan</p>
        </div>

        <form action="{{ route('admin.events.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="nama_event" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Event</label>
                <input type="text" name="nama_event" id="nama_event" required placeholder="Contoh: Piala Presiden Kicau 2026"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div>
                <label for="tanggal" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Tanggal Pelaksanaan</label>
                <input type="date" name="tanggal" id="tanggal" required
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="jam_mulai" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Jam Mulai</label>
                    <input type="time" name="jam_mulai" id="jam_mulai" value="08:30"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>
                <div>
                    <label for="jam_selesai" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Jam Selesai</label>
                    <input type="time" name="jam_selesai" id="jam_selesai" value="17:00"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>
            </div>

            <div>
                <label for="lokasi" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Lokasi Gantangan</label>
                <input type="text" name="lokasi" id="lokasi" required placeholder="Contoh: Arena Gantangan Alun-Alun, Solo"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div>
                <label for="deskripsi" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Deskripsi Singkat (Opsional)</label>
                <textarea name="deskripsi" id="deskripsi" rows="2" placeholder="Informasi sponsor, dewan juri, total hadiah..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]"></textarea>
            </div>

            <div>
                <label for="status" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Status Event</label>
                <select name="status" id="status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                    <option value="active">Active (Publik & Dibuka)</option>
                    <option value="draft">Draft (Belum Dibuka)</option>
                    <option value="finished">Finished (Selesai)</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-tambah-event').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold shadow-sm">
                    Simpan Event
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Kategori ke Event -->
<div id="modal-tambah-kategori" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 relative">
        <button type="button" onclick="document.getElementById('modal-tambah-kategori').classList.add('hidden')" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined">close</span>
        </button>

        <div class="mb-4">
            <h3 class="font-headline font-bold text-lg text-slate-900">Tambah Kategori Lomba</h3>
            <p class="text-xs text-slate-500 mt-0.5">Event: <strong id="kategori-event-name" class="text-slate-800"></strong></p>
        </div>

        <form id="form-tambah-kategori" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="nama_kategori" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Kategori / Kelas</label>
                <input type="text" name="nama_kategori" id="nama_kategori" required placeholder="Contoh: Murai Batu VIP 36G"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div>
                <label for="jenis_burung" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Jenis Burung yang Dilombakan</label>
                <select name="jenis_burung" id="jenis_burung" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                    @foreach(\App\Models\KategoriLomba::JENIS_BURUNG_LIST as $jb)
                        <option value="{{ $jb }}">{{ $jb }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="biaya" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Biaya Tiket (Rp)</label>
                    <input type="number" name="biaya" id="biaya" required min="0" value="150000"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>
                <div>
                    <label for="kuota" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Kuota Gantangan</label>
                    <input type="number" name="kuota" id="kuota" required min="1" value="36"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="kat_jam_mulai" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Jadwal Sesi Mulai</label>
                    <input type="time" name="jam_mulai" id="kat_jam_mulai" value="09:00"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>
                <div>
                    <label for="kat_jam_selesai" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Jadwal Sesi Selesai</label>
                    <input type="time" name="jam_selesai" id="kat_jam_selesai" value="10:00"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                </div>
            </div>

            <div>
                <label for="status_lomba" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Status Sesi</label>
                <select name="status_lomba" id="status_lomba" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                    <option value="upcoming">Upcoming (Persiapan)</option>
                    <option value="ongoing">Ongoing (Sedang Berlangsung)</option>
                    <option value="validasi">Validasi Rekap</option>
                    <option value="finished">Finished (Selesai)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Tugaskan Dewan Juri (Opsional)</label>
                <p class="text-[11px] text-slate-500 mb-2">Pilih dewan juri yang ditugaskan menilai burung di kelas/kategori ini:</p>
                <div class="space-y-1.5 max-h-36 overflow-y-auto p-2.5 rounded-xl border border-slate-200 bg-slate-50/50">
                    @forelse($allJuris as $juri)
                        <label class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-white transition cursor-pointer text-xs">
                            <input type="checkbox" name="juri_ids[]" value="{{ $juri->id }}" class="rounded text-[#2E6B56] focus:ring-[#2E6B56]">
                            <span class="font-medium text-slate-800">{{ $juri->name }}</span>
                            <span class="font-mono text-[10px] text-emerald-700 bg-emerald-100/70 px-1.5 py-0.2 rounded">{{ $juri->kode_id }}</span>
                        </label>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada akun juri yang terdaftar.</p>
                    @endforelse
                </div>
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

<!-- Modal Atur Dewan Juri Kategori -->
<div id="modal-atur-juri" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 relative">
        <button type="button" onclick="document.getElementById('modal-atur-juri').classList.add('hidden')" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined">close</span>
        </button>

        <div class="mb-4">
            <h3 class="font-headline font-bold text-lg text-slate-900">Penugasan Dewan Juri</h3>
            <p class="text-xs text-slate-500 mt-0.5">Kategori: <strong id="atur-juri-kategori-name" class="text-slate-800"></strong></p>
        </div>

        <form id="form-atur-juri" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Daftar Dewan Juri Penilai</label>
                <p class="text-[11px] text-slate-500 mb-2">Centang juri yang berhak menilai burung pada kategori/kelas ini:</p>
                <div class="space-y-1.5 max-h-52 overflow-y-auto p-3 rounded-xl border border-slate-200 bg-slate-50/50">
                    @forelse($allJuris as $juri)
                        <label class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-white transition cursor-pointer text-xs border border-transparent hover:border-slate-200">
                            <input type="checkbox" name="juri_ids[]" value="{{ $juri->id }}" class="juri-checkbox rounded text-[#2E6B56] focus:ring-[#2E6B56]">
                            <div class="flex-1">
                                <span class="font-semibold text-slate-800 block">{{ $juri->name }}</span>
                                <span class="text-[11px] text-slate-500">{{ $juri->phone }}</span>
                            </div>
                            <span class="font-mono text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded border border-emerald-200">{{ $juri->kode_id }}</span>
                        </label>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada akun juri yang terdaftar di sistem.</p>
                    @endforelse
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-atur-juri').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold shadow-sm">
                    Simpan Penugasan Juri
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openTambahKategoriModal(eventId, eventName) {
        document.getElementById('form-tambah-kategori').action = `/admin/events/${eventId}/kategori`;
        document.getElementById('kategori-event-name').textContent = eventName;
        document.getElementById('modal-tambah-kategori').classList.remove('hidden');
    }

    function openAturJuriModal(kategoriId, kategoriName, assignedJuriIds) {
        document.getElementById('form-atur-juri').action = `/admin/kategori/${kategoriId}/juris`;
        document.getElementById('atur-juri-kategori-name').textContent = kategoriName;

        document.querySelectorAll('.juri-checkbox').forEach(cb => {
            cb.checked = assignedJuriIds.includes(parseInt(cb.value));
        });

        document.getElementById('modal-atur-juri').classList.remove('hidden');
    }
</script>
@endpush
@endsection
