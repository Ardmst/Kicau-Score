@extends('layouts.app', ['title' => 'Masuk ke Sistem'])

@section('content')
<div class="max-w-md mx-auto my-8">
    
    <!-- White Rounded Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        
        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-2xl bg-[#2E6B56] text-white flex items-center justify-center mx-auto mb-3 shadow-sm">
                <span class="material-symbols-outlined text-[28px]">lock</span>
            </div>
            <h1 class="font-headline font-bold text-2xl text-slate-900 tracking-tight">Masuk KicauScore</h1>
            <p class="text-xs text-slate-500 mt-1">Platform Manajemen & Penjurian Burung Berkicau</p>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="login" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nomor WhatsApp / HP
                </label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">phone</span>
                    <input type="text" name="login" id="login" required value="{{ old('login') }}" placeholder="Contoh: 081234567890"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56] focus:border-transparent transition-all">
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Gunakan nomor HP terdaftar (atau email untuk Admin/Juri).</p>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">key</span>
                    <input type="password" name="password" id="password" required placeholder="••••••••"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56] focus:border-transparent transition-all">
                </div>
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-[#2E6B56] focus:ring-[#2E6B56]">
                    <span>Ingat saya di perangkat ini</span>
                </label>
            </div>

            <button type="submit" class="w-full h-11 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-sm transition-colors">
                <span class="material-symbols-outlined text-[18px]">login</span> Masuk ke Akun
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-100 text-center text-xs text-slate-500">
            Belum memiliki akun peserta? 
            <a href="{{ route('register') }}" class="font-semibold text-[#2E6B56] hover:underline">Daftar Akun Baru</a>
        </div>
    </div>
</div>
@endsection
