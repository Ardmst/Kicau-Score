@extends('layouts.app', ['title' => 'Pendaftaran Akun Peserta'])

@section('content')
<div class="max-w-md mx-auto my-8">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        
        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-2xl bg-[#2E6B56] text-white flex items-center justify-center mx-auto mb-3 shadow-sm">
                <span class="material-symbols-outlined text-[28px]">person_add</span>
            </div>
            <h1 class="font-headline font-bold text-2xl text-slate-900 tracking-tight">Daftar Akun Peserta</h1>
            <p class="text-xs text-slate-500 mt-1">Lengkapi formulir untuk mulai mendaftarkan burung kicau Anda</p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">badge</span>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}" placeholder="Contoh: Ahmad Fauzi"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56] focus:border-transparent transition-all">
                </div>
            </div>

            <div>
                <label for="phone" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nomor WhatsApp / HP</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">phone</span>
                    <input type="text" name="phone" id="phone" required value="{{ old('phone') }}" placeholder="Contoh: 081234567890"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56] focus:border-transparent transition-all">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi (Min. 6 Karakter)</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">lock</span>
                    <input type="password" name="password" id="password" required placeholder="••••••••"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56] focus:border-transparent transition-all">
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Kata Sandi</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">check_circle</span>
                    <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="••••••••"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56] focus:border-transparent transition-all">
                </div>
            </div>

            <button type="submit" class="w-full h-11 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-sm transition-colors mt-2">
                <span class="material-symbols-outlined text-[18px]">how_to_reg</span> Daftarkan Akun Saya
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-100 text-center text-xs text-slate-500">
            Sudah memiliki akun? 
            <a href="{{ route('login') }}" class="font-semibold text-[#2E6B56] hover:underline">Masuk Sekarang</a>
        </div>
    </div>
</div>
@endsection
