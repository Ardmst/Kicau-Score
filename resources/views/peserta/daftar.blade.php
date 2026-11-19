@extends('layouts.app', ['title' => 'Pendaftaran Lomba & Pembayaran'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <div class="mb-6">
            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold uppercase tracking-wider">Checkout Pendaftaran</span>
            <h1 class="font-headline font-bold text-2xl text-slate-900 tracking-tight mt-1">Daftar Gantangan Lomba</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pilih kategori perlombaan, gacoan burung yang sesuai, dan metode pembayaran otomatis</p>
        </div>

        <form id="form-pendaftaran" action="{{ route('peserta.daftar.store') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="auto_verify" value="1">

            <!-- STEP 1: Pilih Kategori Lomba -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">1. Pilih Kategori Perlombaan</label>
                <div class="space-y-3">
                    @forelse($activeEvents as $evt)
                        <div class="text-xs font-bold text-slate-500 bg-slate-50 px-3 py-1.5 rounded-lg flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-[#2E6B56]">event</span> {{ $evt->nama_event }} ({{ $evt->lokasi }})
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            @foreach($evt->kategoriLombas as $kat)
                                @php
                                    $isFull = $kat->total_terdaftar >= $kat->kuota;
                                @endphp
                                <label class="p-3.5 rounded-xl border cursor-pointer transition-all flex items-start gap-3 relative {{ $isFull ? 'opacity-50 bg-slate-50 cursor-not-allowed border-slate-200' : 'bg-white border-slate-200 hover:border-[#2E6B56] hover:bg-emerald-50/20' }}">
                                    <input type="radio" name="kategori_lomba_id" value="{{ $kat->id }}" 
                                           data-biaya="{{ $kat->biaya }}" 
                                           data-biaya-fmt="Rp {{ number_format($kat->biaya, 0, ',', '.') }}" 
                                           data-nama="{{ $kat->nama_kategori }}"
                                           data-jenis="{{ strtolower($kat->expected_jenis_burung ?? '') }}"
                                           data-expected="{{ $kat->expected_jenis_burung ?? 'Umum' }}"
                                           {{ ($selectedKategoriId == $kat->id || ($loop->first && !$selectedKategoriId)) ? 'checked' : '' }} 
                                           {{ $isFull ? 'disabled' : '' }} required 
                                           onchange="onKategoriChanged()"
                                           class="mt-1 text-[#2E6B56] focus:ring-[#2E6B56]">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-1">
                                            <span class="font-headline font-bold text-sm text-slate-900 truncate">{{ $kat->nama_kategori }}</span>
                                            <span class="text-xs font-bold text-[#2E6B56] shrink-0">Rp {{ number_format($kat->biaya, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex items-center justify-between text-[11px] text-slate-500 mt-1">
                                            <span>Sisa Kuota: <strong>{{ max(0, $kat->kuota - $kat->total_terdaftar) }}</strong> / {{ $kat->kuota }}</span>
                                            <span class="uppercase font-semibold {{ $isFull ? 'text-rose-600' : 'text-emerald-700' }}">
                                                {{ $isFull ? 'Penuh' : $kat->status_lomba }}
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Tidak ada kategori lomba yang sedang dibuka.</p>
                    @endforelse
                </div>
            </div>

            <!-- STEP 2: Pilih Burung Gacoan (Strict Species Matching & Single-Category Binding) -->
            <div class="pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">2. Pilih Burung Gacoan Anda (Syarat Spesies & Single-Category)</label>
                    <a href="{{ route('peserta.burung.index') }}" class="text-xs font-semibold text-[#2E6B56] hover:underline">+ Tambah Burung Baru</a>
                </div>

                <div class="bg-amber-50 border border-amber-200 text-amber-900 text-xs p-3 rounded-xl mb-3 space-y-1">
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-[18px] text-amber-700 shrink-0">verified_user</span>
                        <span><strong>Syarat Ketat Perlombaan:</strong> Burung harus sesuai dengan jenis/spesies kategori yang dipilih (misal: Lomba Murai Batu hanya untuk burung Murai Batu). 1 burung hanya dapat didaftarkan pada 1 kategori.</span>
                    </div>
                </div>

                @if($burungs->isEmpty())
                    <div class="p-6 rounded-xl border border-dashed border-slate-300 text-center">
                        <x-icon-bird class="w-10 h-10 text-slate-300 mx-auto mb-2" />
                        <p class="text-xs text-slate-500 mb-2">Anda belum memiliki burung terdaftar di akun Anda.</p>
                        <a href="{{ route('peserta.burung.index') }}" class="inline-flex items-center gap-1 px-4 py-2 rounded-xl bg-[#2E6B56] text-white text-xs font-semibold">
                            Tambah Burung Dulu
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5" id="bird-list-container">
                        @foreach($burungs as $bg)
                            <label id="bird-label-{{ $bg->id }}" class="p-3.5 rounded-xl border border-slate-200 bg-white hover:border-[#2E6B56] hover:bg-emerald-50/20 cursor-pointer transition-all flex items-start gap-3">
                                <input type="radio" name="burung_id" value="{{ $bg->id }}" 
                                       data-nama="{{ $bg->nama_burung }}" 
                                       data-jenis="{{ $bg->jenis_burung }}"
                                       id="bird-radio-{{ $bg->id }}"
                                       {{ $loop->first ? 'checked' : '' }} required 
                                       onchange="updateTotalTagihan()"
                                       class="mt-1 text-[#2E6B56] focus:ring-[#2E6B56]">
                                <div class="min-w-0 flex-1">
                                    <span class="font-headline font-bold text-sm text-slate-900 block truncate">{{ $bg->nama_burung }}</span>
                                    <span class="text-xs text-slate-500 block">{{ $bg->jenis_burung }} &bull; Ring: {{ $bg->no_ring ?? '-' }}</span>
                                    <span id="bird-badge-{{ $bg->id }}" class="text-[11px] font-semibold mt-1 block"></span>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <!-- Alert Saat Tidak Ada Burung yang Sesuai Spesies -->
                    <div id="no-matching-bird-alert" class="hidden p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-2.5 mt-3">
                        <span class="material-symbols-outlined text-[20px] text-amber-700 shrink-0">warning</span>
                        <div>
                            <strong class="block font-semibold">Tidak Ada Burung yang Memenuhi Syarat Kategori</strong>
                            <span>Anda tidak memiliki burung berjenis <strong id="alert-expected-species" class="text-amber-800">Murai Batu</strong>. Pilih kategori lain atau daftarkan burung baru di koleksi Anda.</span>
                            <div class="mt-2">
                                <a href="{{ route('peserta.burung.index') }}" class="inline-flex items-center gap-1 font-semibold text-[#2E6B56] hover:underline">
                                    + Tambah Burung Baru di Koleksi &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- STEP 3: Metode Pembayaran (QRIS & Transfer Bank) -->
            <div class="pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">3. Metode Pembayaran</label>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Auto-Verification Active
                    </span>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    <!-- QRIS -->
                    <label class="p-4 rounded-xl border-2 border-[#2E6B56] bg-emerald-50/30 cursor-pointer transition-all flex items-start gap-3 relative" id="payment-label-qris">
                        <input type="radio" name="metode_pembayaran" value="qris" checked onchange="selectPaymentMethod('qris')" class="mt-1 text-[#2E6B56] focus:ring-[#2E6B56]">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="font-headline font-bold text-sm text-slate-900">QRIS (Otomatis & Instan)</span>
                                <span class="text-[10px] font-black tracking-wider uppercase px-2 py-0.5 bg-emerald-600 text-white rounded">INSTANT</span>
                            </div>
                            <span class="text-xs text-slate-500 block mt-1">BCA, Mandiri, BRI, GoPay, OVO, Dana, ShopeePay</span>
                            <span class="text-[11px] text-emerald-700 font-semibold block mt-1.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[15px]">flash_on</span> Auto Settlement 3 Detik
                            </span>
                        </div>
                    </label>

                    <!-- Bank Transfer -->
                    <label class="p-4 rounded-xl border border-slate-200 bg-white hover:border-[#2E6B56] cursor-pointer transition-all flex items-start gap-3 relative" id="payment-label-transfer">
                        <input type="radio" name="metode_pembayaran" value="transfer" onchange="selectPaymentMethod('transfer')" class="mt-1 text-[#2E6B56] focus:ring-[#2E6B56]">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="font-headline font-bold text-sm text-slate-900">Transfer Bank</span>
                                <span class="text-[10px] font-bold tracking-wider uppercase px-2 py-0.5 bg-slate-100 text-slate-700 rounded">VIRTUAL</span>
                            </div>
                            <span class="text-xs text-slate-500 block mt-1">BCA &bull; Mandiri &bull; BNI Virtual Account</span>
                            <span class="text-[11px] text-emerald-700 font-semibold block mt-1.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[15px]">sync_alt</span> Auto Settlement 3 Detik
                            </span>
                        </div>
                    </label>
                </div>

                <!-- Box Preview Detail Pembayaran -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    
                    <!-- View QRIS -->
                    <div id="view-qris" class="flex flex-col sm:flex-row items-center gap-5">
                        <div class="w-36 h-36 bg-white p-2.5 rounded-xl border border-slate-200 shadow-sm flex flex-col items-center justify-center shrink-0">
                            <!-- Simulated SVG QR Code -->
                            <svg class="w-full h-full text-slate-900" viewBox="0 0 100 100" fill="currentColor">
                                <path d="M0,0 h30 v30 h-30 z M6,6 h18 v18 h-18 z M10,10 h10 v10 h-10 z" />
                                <path d="M70,0 h30 v30 h-30 z M76,6 h18 v18 h-18 z M80,10 h10 v10 h-10 z" />
                                <path d="M0,70 h30 v30 h-30 z M6,76 h18 v18 h-18 z M10,80 h10 v10 h-10 z" />
                                <rect x="36" y="6" width="6" height="18" />
                                <rect x="46" y="10" width="8" height="6" />
                                <rect x="58" y="6" width="6" height="12" />
                                <rect x="10" y="36" width="16" height="6" />
                                <rect x="6" y="46" width="8" height="14" />
                                <rect x="36" y="36" width="28" height="28" />
                                <rect x="42" y="42" width="16" height="16" fill="white" />
                                <rect x="46" y="46" width="8" height="8" />
                                <rect x="70" y="36" width="24" height="6" />
                                <rect x="76" y="46" width="6" height="18" />
                                <rect x="88" y="48" width="6" height="16" />
                                <rect x="36" y="70" width="6" height="24" />
                                <rect x="46" y="76" width="18" height="6" />
                                <rect x="52" y="88" width="12" height="6" />
                                <rect x="70" y="70" width="14" height="14" />
                                <rect x="88" y="76" width="6" height="18" />
                            </svg>
                            <span class="text-[9px] font-black text-slate-400 mt-1 uppercase tracking-widest">QRIS INDONESIA</span>
                        </div>
                        <div class="space-y-1.5 text-center sm:text-left flex-1">
                            <span class="text-xs uppercase font-bold text-slate-500 tracking-wider">Merchant Resmi</span>
                            <h4 class="font-headline font-bold text-slate-900 text-base">KicauScore Indonesia</h4>
                            <p class="text-xs text-slate-500">NMID: ID202688921102 &bull; Biaya Admin Rp 0</p>
                            <div class="pt-2 flex items-center justify-center sm:justify-start gap-2">
                                <span class="text-xs text-slate-500">Total Pembayaran:</span>
                                <span id="display-nominal-qris" class="text-base font-headline font-extrabold text-[#2E6B56]">Rp 350.000</span>
                            </div>
                        </div>
                    </div>

                    <!-- View Transfer -->
                    <div id="view-transfer" class="hidden space-y-3">
                        <div class="p-3.5 rounded-xl bg-white border border-slate-200 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Bank Central Asia (BCA)</span>
                                <span class="font-mono text-base font-bold text-slate-900">892-0192-381</span>
                                <span class="text-xs text-slate-500 block">a/n Panitia KicauScore Indonesia</span>
                            </div>
                            <button type="button" onclick="navigator.clipboard.writeText('8920192381'); alert('Nomor rekening BCA disalin!');" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                                Salin Rekening
                            </button>
                        </div>
                        <div class="flex items-center justify-between text-xs px-1">
                            <span class="text-slate-500">Total Nominal Transfer:</span>
                            <span id="display-nominal-transfer" class="text-sm font-headline font-extrabold text-[#2E6B56]">Rp 350.000</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('peserta.dashboard') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                    Batal
                </a>
                <button type="button" onclick="mulaiSimulasiPembayaran()" id="btn-submit" class="h-11 px-6 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white font-semibold text-sm flex items-center gap-2 shadow-sm transition-all hover:scale-[1.01] active:scale-[0.99]">
                    <span class="material-symbols-outlined text-[18px]">lock</span> Bayar & Terbitkan Gantangan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL SIMULASI PEMBAYARAN & PAYMENT SUCCESS ================= -->
<div id="payment-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full p-6 sm:p-8 transform transition-transform duration-300 scale-95" id="modal-box">
        
        <!-- STATE 1: COUNTDOWN PEMBAYARAN -->
        <div id="modal-state-loading" class="text-center space-y-4">
            <div class="relative w-20 h-20 mx-auto flex items-center justify-center">
                <!-- Circular pulse animation -->
                <div class="absolute inset-0 rounded-full bg-emerald-100 animate-ping opacity-75"></div>
                <div class="relative w-16 h-16 rounded-2xl bg-[#2E6B56] text-white flex items-center justify-center shadow-lg">
                    <span class="material-symbols-outlined text-[32px] animate-spin">sync</span>
                </div>
            </div>

            <div>
                <h3 class="font-headline font-bold text-xl text-slate-900">Menunggu Pembayaran...</h3>
                <p class="text-xs text-slate-500 mt-1">Simulasi auto-detect payment gateway sedang berjalan</p>
            </div>

            <!-- Progress Bar -->
            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                <div id="progress-bar" class="bg-[#2E6B56] h-full transition-all duration-300" style="width: 10%;"></div>
            </div>

            <!-- Timer Countdown Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800">
                <span class="material-symbols-outlined text-[18px] text-emerald-600">timer</span>
                <span>Auto-verifikasi dalam: <strong id="timer-count" class="text-sm font-bold text-emerald-700">3</strong> detik</span>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-left text-xs space-y-1.5">
                <div class="flex justify-between text-slate-600">
                    <span>Burung Gacoan:</span>
                    <strong id="modal-bird-name" class="text-slate-900">-</strong>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Kategori Lomba:</span>
                    <strong id="modal-cat-name" class="text-slate-900">-</strong>
                </div>
                <div class="flex justify-between text-slate-600 pt-1 border-t border-slate-200">
                    <span>Total Tagihan:</span>
                    <strong id="modal-amount" class="text-[#2E6B56] font-bold">-</strong>
                </div>
            </div>
        </div>

        <!-- STATE 2: PAYMENT SUCCESS (AUTO TRANSITION) -->
        <div id="modal-state-success" class="hidden text-center space-y-4">
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto shadow-sm animate-bounce">
                <span class="material-symbols-outlined text-[36px]">check_circle</span>
            </div>

            <div>
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-black uppercase tracking-wider">Lunas & Terverifikasi</span>
                <h3 class="font-headline font-bold text-2xl text-slate-900 mt-1.5">Pembayaran Berhasil! 🎉</h3>
                <p class="text-xs text-slate-500 mt-0.5">Sistem telah mengalokasikan nomor gantangan resmi Anda.</p>
            </div>

            <!-- Gantangan Highlight Card -->
            <div class="p-5 rounded-2xl bg-gradient-to-b from-emerald-50 to-white border border-emerald-200 text-center">
                <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider block">Nomor Gantangan Resmi</span>
                <div id="success-gantangan-badge" class="font-headline font-black text-4xl text-emerald-700 my-1 tracking-tight">
                    G-01
                </div>
                <span id="success-cat-label" class="text-xs text-slate-500 font-medium block">Murai Batu VIP 36G</span>
            </div>

            <!-- Detail Pendaftaran -->
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-left text-xs space-y-1">
                <div class="flex justify-between text-slate-600">
                    <span>Nama Burung:</span>
                    <strong id="success-bird-label" class="text-slate-900">-</strong>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Metode:</span>
                    <strong id="success-method-label" class="text-slate-900 uppercase">QRIS INSTANT</strong>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Status Tiket:</span>
                    <strong class="text-emerald-700 font-semibold">Aktif & Terdaftar</strong>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-2 flex flex-col sm:flex-row gap-2">
                <a id="btn-lihat-tiket" href="#" class="flex-1 h-11 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white font-semibold text-xs flex items-center justify-center gap-1.5 shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-[18px]">confirmation_number</span> Buka E-Tiket Gantangan
                </a>
                <a href="{{ route('peserta.dashboard') }}" class="px-4 h-11 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs flex items-center justify-center transition-colors">
                    Dashboard
                </a>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    function selectPaymentMethod(method) {
        const qrisLabel = document.getElementById('payment-label-qris');
        const transferLabel = document.getElementById('payment-label-transfer');
        const viewQris = document.getElementById('view-qris');
        const viewTransfer = document.getElementById('view-transfer');

        if (method === 'qris') {
            qrisLabel.className = 'p-4 rounded-xl border-2 border-[#2E6B56] bg-emerald-50/30 cursor-pointer transition-all flex items-start gap-3 relative';
            transferLabel.className = 'p-4 rounded-xl border border-slate-200 bg-white hover:border-[#2E6B56] cursor-pointer transition-all flex items-start gap-3 relative';
            viewQris.classList.remove('hidden');
            viewTransfer.classList.add('hidden');
        } else {
            transferLabel.className = 'p-4 rounded-xl border-2 border-[#2E6B56] bg-emerald-50/30 cursor-pointer transition-all flex items-start gap-3 relative';
            qrisLabel.className = 'p-4 rounded-xl border border-slate-200 bg-white hover:border-[#2E6B56] cursor-pointer transition-all flex items-start gap-3 relative';
            viewTransfer.classList.remove('hidden');
            viewQris.classList.add('hidden');
        }
    }

    function updateTotalTagihan() {
        const selectedKat = document.querySelector('input[name="kategori_lomba_id"]:checked');
        if (selectedKat) {
            const biayaFmt = selectedKat.getAttribute('data-biaya-fmt') || 'Rp 0';
            const displayQris = document.getElementById('display-nominal-qris');
            const displayTransfer = document.getElementById('display-nominal-transfer');
            if (displayQris) displayQris.innerText = biayaFmt;
            if (displayTransfer) displayTransfer.innerText = biayaFmt;
        }
    }

    function onKategoriChanged() {
        updateTotalTagihan();
        filterBurungByKategori();
    }

    function filterBurungByKategori() {
        const selectedKat = document.querySelector('input[name="kategori_lomba_id"]:checked');
        if (!selectedKat) return;

        const expectedJenis = (selectedKat.getAttribute('data-jenis') || '').toLowerCase().trim();
        const expectedNama = selectedKat.getAttribute('data-expected') || 'Umum';

        const birdRadios = document.querySelectorAll('input[name="burung_id"]');
        const alertBox = document.getElementById('no-matching-bird-alert');
        const alertSpecies = document.getElementById('alert-expected-species');

        let matchedCount = 0;
        let firstMatchingRadio = null;
        let currentlyCheckedIsMatched = false;

        birdRadios.forEach(radio => {
            const birdId = radio.value;
            const birdJenis = (radio.getAttribute('data-jenis') || '').toLowerCase().trim();
            const label = document.getElementById('bird-label-' + birdId);
            const badge = document.getElementById('bird-badge-' + birdId);

            const isMatch = !expectedJenis || 
                            birdJenis.includes(expectedJenis) || 
                            expectedJenis.includes(birdJenis);

            if (isMatch) {
                matchedCount++;
                radio.disabled = false;
                if (label) {
                    label.classList.remove('opacity-40', 'cursor-not-allowed', 'bg-slate-50');
                    label.classList.add('bg-white', 'hover:border-[#2E6B56]', 'hover:bg-emerald-50/20');
                }
                if (badge) {
                    badge.innerHTML = `<span class="text-emerald-700 flex items-center gap-1">` +
                                      `<span class="material-symbols-outlined text-[14px]">check_circle</span> Sesuai Spesies (${expectedNama})</span>`;
                }
                if (radio.checked) {
                    currentlyCheckedIsMatched = true;
                }
                if (!firstMatchingRadio) {
                    firstMatchingRadio = radio;
                }
            } else {
                radio.disabled = true;
                if (radio.checked) {
                    radio.checked = false;
                }
                if (label) {
                    label.classList.add('opacity-40', 'cursor-not-allowed', 'bg-slate-50');
                    label.classList.remove('hover:border-[#2E6B56]', 'hover:bg-emerald-50/20');
                }
                if (badge) {
                    badge.innerHTML = `<span class="text-rose-600 flex items-center gap-1">` +
                                      `<span class="material-symbols-outlined text-[14px]">cancel</span> Tidak sesuai (Khusus ${expectedNama})</span>`;
                }
            }
        });

        // Auto-select first matching bird if nothing or an invalid bird was selected
        if (!currentlyCheckedIsMatched && firstMatchingRadio) {
            firstMatchingRadio.checked = true;
        }

        // Show or hide the no matching bird alert
        if (alertBox) {
            if (matchedCount === 0 && birdRadios.length > 0) {
                alertBox.classList.remove('hidden');
                if (alertSpecies) alertSpecies.innerText = expectedNama;
            } else {
                alertBox.classList.add('hidden');
            }
        }
    }

    // Inisialisasi saat pertama kali render
    document.addEventListener('DOMContentLoaded', () => {
        onKategoriChanged();
    });

    // Simulasi Pembayaran Otomatis dengan Timer
    function mulaiSimulasiPembayaran() {
        const form = document.getElementById('form-pendaftaran');
        const selectedKat = document.querySelector('input[name="kategori_lomba_id"]:checked');
        const selectedBird = document.querySelector('input[name="burung_id"]:checked');
        const selectedMethod = document.querySelector('input[name="metode_pembayaran"]:checked');

        if (!selectedKat) {
            alert('Silakan pilih salah satu kategori perlombaan terlebih dahulu.');
            return;
        }

        if (!selectedBird || selectedBird.disabled) {
            const expectedNama = selectedKat.getAttribute('data-expected') || 'kategori ini';
            alert(`Silakan pilih burung gacoan yang sesuai dengan persyaratan kategori lomba (Khusus ${expectedNama}).`);
            return;
        }

        // Buka Modal & Set Informasi
        const modal = document.getElementById('payment-modal');
        const modalBox = document.getElementById('modal-box');
        const stateLoading = document.getElementById('modal-state-loading');
        const stateSuccess = document.getElementById('modal-state-success');
        const timerCount = document.getElementById('timer-count');
        const progressBar = document.getElementById('progress-bar');

        document.getElementById('modal-bird-name').innerText = selectedBird.getAttribute('data-nama') || '-';
        document.getElementById('modal-cat-name').innerText = selectedKat.getAttribute('data-nama') || '-';
        document.getElementById('modal-amount').innerText = selectedKat.getAttribute('data-biaya-fmt') || '-';

        stateLoading.classList.remove('hidden');
        stateSuccess.classList.add('hidden');

        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalBox.classList.remove('scale-95');
        }, 10);

        // Jalankan Timer 3 Detik
        let secondsLeft = 3;
        timerCount.innerText = secondsLeft;
        progressBar.style.width = '20%';

        const interval = setInterval(() => {
            secondsLeft--;
            timerCount.innerText = secondsLeft;
            progressBar.style.width = ((3 - secondsLeft) / 3 * 100) + '%';

            if (secondsLeft <= 0) {
                clearInterval(interval);
                
                // Kirim pendaftaran via AJAX ke server
                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Transisi UI ke Payment Success
                        stateLoading.classList.add('hidden');
                        stateSuccess.classList.remove('hidden');

                        document.getElementById('success-gantangan-badge').innerText = data.nomor_gantangan_badge || ('G-' + data.nomor_gantangan);
                        document.getElementById('success-bird-label').innerText = data.nama_burung || '-';
                        document.getElementById('success-cat-label').innerText = data.kategori || '-';
                        document.getElementById('success-method-label').innerText = (selectedMethod.value === 'qris' ? 'QRIS INSTANT' : 'TRANSFER BANK');
                        document.getElementById('btn-lihat-tiket').href = data.tiket_url;
                    } else {
                        alert(data.message || 'Terjadi kesalahan saat memproses pendaftaran.');
                        modal.classList.add('opacity-0');
                        setTimeout(() => modal.classList.add('hidden'), 300);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Gagal menghubungi server. Formulir akan dikirim secara konvensional.');
                    form.submit();
                });
            }
        }, 1000);
    }
</script>
@endpush
@endsection
