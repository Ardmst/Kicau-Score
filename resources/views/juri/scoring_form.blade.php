@extends('layouts.juri', ['title' => 'Form Penilaian: Gantangan G-' . str_pad($pendaftaran->nomor_gantangan, 2, '0', STR_PAD_LEFT)])

@section('content')
<div class="space-y-4">
    
    <!-- Top Navigation between Gantangan -->
    <div class="flex items-center justify-between gap-2">
        @if($prevPendaftaran)
            <a href="{{ route('juri.penilaian.form', $prevPendaftaran->id) }}" class="h-9 px-3 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 flex items-center gap-1 shadow-xs">
                <span class="material-symbols-outlined text-[16px]">chevron_left</span> G-{{ str_pad($prevPendaftaran->nomor_gantangan, 2, '0', STR_PAD_LEFT) }}
            </a>
        @else
            <div></div>
        @endif

        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">
            {{ $pendaftaran->kategoriLomba->nama_kategori }}
        </span>

        @if($nextPendaftaran)
            <a href="{{ route('juri.penilaian.form', $nextPendaftaran->id) }}" class="h-9 px-3 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 flex items-center gap-1 shadow-xs">
                G-{{ str_pad($nextPendaftaran->nomor_gantangan, 2, '0', STR_PAD_LEFT) }} <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            </a>
        @else
            <div></div>
        @endif
    </div>

    <!-- Bird & Cage Info Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-14 h-14 rounded-2xl bg-[#10533F] text-white flex items-center justify-center font-headline font-bold text-xl shrink-0 shadow-sm">
                G-{{ str_pad($pendaftaran->nomor_gantangan, 2, '0', STR_PAD_LEFT) }}
            </div>
            <div>
                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                    {{ $pendaftaran->burung->jenis_burung }}
                </span>
                <h2 class="font-headline font-bold text-base text-slate-900 truncate mt-0.5">
                    {{ $pendaftaran->burung->nama_burung }}
                </h2>
                <span class="text-xs text-slate-500">Pemilik: {{ $pendaftaran->user->name }}</span>
            </div>
        </div>

        @if($penilaian && $penilaian->status_validasi === 'validated')
            <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-bold flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">lock</span> Terkunci
            </span>
        @endif
    </div>

    @if(!$canEdit)
        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center gap-2">
            <span class="material-symbols-outlined text-amber-600">lock</span>
            <span>Penilaian untuk kategori ini sudah divalidasi & dikunci oleh Admin (Mode Read-Only).</span>
        </div>
    @endif

    <!-- Scoring Form -->
    <form action="{{ route('juri.penilaian.store', $pendaftaran->id) }}" method="POST" class="space-y-4">
        @csrf

        <!-- 1. Kriteria Visual / Fisik & Gaya -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block">1. Kriteria Visual / Fisik</span>
                    <span class="text-[11px] text-slate-400">Gaya tarung, kebersihan bulu, keaktifan</span>
                </div>
                <div class="w-16 text-right">
                    <span id="visual-display" class="font-headline font-black text-2xl text-[#10533F] tabular-nums">
                        {{ old('skor_visual', $penilaian->skor_visual ?? '85.00') }}
                    </span>
                </div>
            </div>

            <!-- Stepper and Slider -->
            <div class="flex items-center gap-2">
                <button type="button" onclick="adjustScore('skor_visual', -1.0)" {{ !$canEdit ? 'disabled' : '' }} class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold flex items-center justify-center text-lg active:scale-95 transition-all">
                    -
                </button>
                <input type="range" name="skor_visual" id="skor_visual" min="1.00" max="100.00" step="0.50"
                       value="{{ old('skor_visual', $penilaian->skor_visual ?? '85.00') }}"
                       oninput="updateDisplay('visual', this.value)"
                       {{ !$canEdit ? 'disabled' : '' }}
                       class="flex-1 h-3 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#2E6B56]">
                <button type="button" onclick="adjustScore('skor_visual', 1.0)" {{ !$canEdit ? 'disabled' : '' }} class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold flex items-center justify-center text-lg active:scale-95 transition-all">
                    +
                </button>
            </div>
            <div class="flex justify-between text-[10px] text-slate-400 font-mono">
                <span>1.00 (Min)</span>
                <span>50.00</span>
                <span>100.00 (Max)</span>
            </div>
        </div>

        <!-- 2. Kriteria Audio / Irama Lagu -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block">2. Kriteria Audio / Kicauan</span>
                    <span class="text-[11px] text-slate-400">Variasi lagu, volume kristal, tembakan isian</span>
                </div>
                <div class="w-16 text-right">
                    <span id="audio-display" class="font-headline font-black text-2xl text-[#10533F] tabular-nums">
                        {{ old('skor_audio', $penilaian->skor_audio ?? '88.00') }}
                    </span>
                </div>
            </div>

            <!-- Stepper and Slider -->
            <div class="flex items-center gap-2">
                <button type="button" onclick="adjustScore('skor_audio', -1.0)" {{ !$canEdit ? 'disabled' : '' }} class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold flex items-center justify-center text-lg active:scale-95 transition-all">
                    -
                </button>
                <input type="range" name="skor_audio" id="skor_audio" min="1.00" max="100.00" step="0.50"
                       value="{{ old('skor_audio', $penilaian->skor_audio ?? '88.00') }}"
                       oninput="updateDisplay('audio', this.value)"
                       {{ !$canEdit ? 'disabled' : '' }}
                       class="flex-1 h-3 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#2E6B56]">
                <button type="button" onclick="adjustScore('skor_audio', 1.0)" {{ !$canEdit ? 'disabled' : '' }} class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold flex items-center justify-center text-lg active:scale-95 transition-all">
                    +
                </button>
            </div>
            <div class="flex justify-between text-[10px] text-slate-400 font-mono">
                <span>1.00 (Min)</span>
                <span>50.00</span>
                <span>100.00 (Max)</span>
            </div>
        </div>

        <!-- Calculated Grand Total Card -->
        <div class="bg-gradient-to-r from-[#10533F] to-[#2E6B56] text-white rounded-2xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-emerald-200 uppercase tracking-widest block">TOTAL SKOR JURI INI</span>
                <span class="text-xs text-emerald-100">Visual + Audio</span>
            </div>
            <div class="text-right">
                <span id="grand-total-display" class="font-headline font-black text-3xl tabular-nums">
                    173.00
                </span>
            </div>
        </div>

        <!-- Controlled Score Revision Reason (Audit Trail) -->
        @if($penilaian)
            <div class="bg-amber-50 border border-amber-200 p-3.5 rounded-xl space-y-1.5">
                <label for="alasan_revisi" class="block text-xs font-bold text-amber-900">
                    Alasan Perubahan Skor (Audit Trail Logging)
                </label>
                <input type="text" name="alasan_revisi" id="alasan_revisi" placeholder="Contoh: Koreksi durasi kerja babak akhir..."
                       class="w-full px-3 py-2 bg-white rounded-lg border border-amber-200 text-xs text-slate-800 focus:outline-none focus:ring-1 focus:ring-amber-500">
                <p class="text-[10px] text-amber-700">Setiap perubahan skor setelah simpan awal akan dicatat di log transparansi lomba.</p>
            </div>
        @endif

        <!-- Optional Notes -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-1.5">
            <label for="catatan" class="block text-xs font-bold text-slate-700">Catatan Khusus Juri (Opsional)</label>
            <input type="text" name="catatan" id="catatan" value="{{ old('catatan', $penilaian->catatan ?? '') }}" placeholder="Materi isian dominan, jeda bunyi..."
                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-1 focus:ring-[#2E6B56]">
        </div>

        @if($canEdit)
            <!-- Submit Action Buttons -->
            <div class="space-y-2 pt-2">
                <button type="submit" name="simpan_lanjut" value="1" class="w-full h-12 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white font-headline font-bold text-sm flex items-center justify-center gap-2 shadow-sm active:scale-98 transition-all">
                    <span class="material-symbols-outlined text-[18px]">save</span> Simpan & Lanjut ke Gantangan Berikutnya
                </button>
                <button type="submit" class="w-full h-11 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold text-xs flex items-center justify-center gap-1.5 hover:bg-slate-50 transition-colors">
                    Simpan Nilai Saja
                </button>
            </div>
        @endif
    </form>
</div>

@push('scripts')
<script>
    function updateDisplay(type, value) {
        const num = parseFloat(value).toFixed(2);
        document.getElementById(type + '-display').innerText = num;
        calculateTotal();
    }

    function adjustScore(inputId, delta) {
        const input = document.getElementById(inputId);
        let current = parseFloat(input.value) || 0;
        let next = Math.min(100.00, Math.max(1.00, current + delta));
        input.value = next.toFixed(2);
        
        const type = inputId === 'skor_visual' ? 'visual' : 'audio';
        document.getElementById(type + '-display').innerText = next.toFixed(2);
        calculateTotal();
    }

    function calculateTotal() {
        const vis = parseFloat(document.getElementById('skor_visual').value) || 0;
        const aud = parseFloat(document.getElementById('skor_audio').value) || 0;
        const total = (vis + aud).toFixed(2);
        document.getElementById('grand-total-display').innerText = total;
    }

    // Initial calculation on load
    document.addEventListener('DOMContentLoaded', calculateTotal);
</script>
@endpush
@endsection
