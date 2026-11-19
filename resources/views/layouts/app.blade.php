<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'KicauScore' }} - Platform Penjurian Burung Berkicau</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Material Symbols Outlined -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- Tailwind CSS CDN for instant, standalone high-fidelity UI rendering -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-dark': '#10533F',
                        'brand': '#2E6B56',
                        'brand-hover': '#245443',
                        'brand-light': '#ECFDF5',
                        'brand-border': '#A7F3D0',
                        'canvas': '#F8FAFC',
                        'obsidian': '#0F172A',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        headline: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    borderRadius: {
                        'card': '18px',
                    }
                }
            }
        }
    </script>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; }
        body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }
        .tabular-nums { font-variant-numeric: tabular-nums; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
            display: inline-block;
            line-height: 1;
        }
        /* Cross-Browser Scrollbar (Chrome, Opera, Firefox) */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 9999px; }
        .custom-scrollbar { scrollbar-width: thin; scrollbar-color: #CBD5E1 transparent; }
        
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        button::-moz-focus-inner, [type="button"]::-moz-focus-inner, [type="submit"]::-moz-focus-inner {
            border-style: none;
            padding: 0;
        }
    </style>
</head>
<body class="bg-[#F8FAFC] font-sans text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
            
            <!-- Logo & Brand -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0">
                <div class="w-10 h-10 rounded-xl bg-[#2E6B56] text-white flex items-center justify-center shadow-sm">
                    <span class="material-symbols-outlined text-[24px]" style="font-variation-settings: 'FILL' 1;">rewarded_ads</span>
                </div>
                <div>
                    <span class="font-headline font-bold text-xl text-[#0F172A] tracking-tight block">
                        KicauScore
                    </span>
                    <p class="text-[11px] text-slate-500 hidden sm:block">Digital Avian Contest Scoring Platform</p>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden lg:flex items-center gap-1 xl:gap-1.5 p-1.5 bg-slate-100/90 rounded-2xl border border-slate-200/90 shadow-2xs shrink-0">
                <a href="{{ route('home') }}" class="h-9 px-3 xl:px-3.5 inline-flex items-center justify-center text-center whitespace-nowrap rounded-xl text-xs xl:text-sm font-semibold transition-all duration-150 {{ request()->routeIs('home') ? 'bg-[#2E6B56] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/80' }}">
                    Beranda & Rundown
                </a>
                <a href="{{ route('scoreboard') }}" class="h-9 px-3 xl:px-3.5 inline-flex items-center justify-center text-center whitespace-nowrap rounded-xl text-xs xl:text-sm font-semibold transition-all duration-150 {{ request()->routeIs('scoreboard*') ? 'bg-[#2E6B56] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/80' }}">
                    Live Klasemen
                </a>
                @auth
                    @if(auth()->user()->isPeserta())
                        <a href="{{ route('peserta.burung.index') }}" class="h-9 px-3 xl:px-3.5 inline-flex items-center justify-center text-center whitespace-nowrap rounded-xl text-xs xl:text-sm font-semibold transition-all duration-150 {{ request()->routeIs('peserta.burung*') ? 'bg-[#2E6B56] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/80' }}">
                            Burung Saya
                        </a>
                        <a href="{{ route('peserta.daftar') }}" class="h-9 px-3 xl:px-3.5 inline-flex items-center justify-center text-center whitespace-nowrap rounded-xl text-xs xl:text-sm font-semibold transition-all duration-150 {{ request()->routeIs('peserta.daftar*') ? 'bg-[#2E6B56] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/80' }}">
                            Daftar Lomba
                        </a>
                    @elseif(auth()->user()->isJuri())
                        <a href="{{ route('juri.dashboard') }}" class="h-9 px-3 xl:px-3.5 inline-flex items-center justify-center text-center whitespace-nowrap rounded-xl text-xs xl:text-sm font-semibold transition-all duration-150 gap-1.5 {{ request()->routeIs('juri.*') ? 'bg-[#2E6B56] text-white shadow-xs' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200' }}">
                            <span class="material-symbols-outlined text-[18px]">edit_note</span> Penjurian Lapangan
                        </a>
                    @elseif(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="h-9 px-3 xl:px-3.5 inline-flex items-center justify-center text-center whitespace-nowrap rounded-xl text-xs xl:text-sm font-semibold transition-all duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-[#2E6B56] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/80' }}">
                            Panel Admin
                        </a>
                        <a href="{{ route('admin.events.index') }}" class="h-9 px-3 xl:px-3.5 inline-flex items-center justify-center text-center whitespace-nowrap rounded-xl text-xs xl:text-sm font-semibold transition-all duration-150 {{ request()->routeIs('admin.events*') ? 'bg-[#2E6B56] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/80' }}">
                            Event & Kategori
                        </a>
                        <a href="{{ route('admin.verifikasi.index') }}" class="h-9 px-3 xl:px-3.5 inline-flex items-center justify-center text-center whitespace-nowrap rounded-xl text-xs xl:text-sm font-semibold transition-all duration-150 {{ request()->routeIs('admin.verifikasi*') ? 'bg-[#2E6B56] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/80' }}">
                            Verifikasi
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="h-9 px-3 xl:px-3.5 inline-flex items-center justify-center text-center whitespace-nowrap rounded-xl text-xs xl:text-sm font-semibold transition-all duration-150 {{ request()->routeIs('admin.users*') ? 'bg-[#2E6B56] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/80' }}">
                            Kelola User
                        </a>
                    @endif
                @endauth
            </nav>

            <!-- User Auth Actions & Mobile Hamburger -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                @auth
                    <div class="flex items-center gap-2">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition-colors shadow-2xs">
                            <div class="w-8 h-8 rounded-full bg-emerald-50 text-[#2E6B56] border border-emerald-200 flex items-center justify-center font-bold text-xs">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                            <div class="text-left hidden sm:block">
                                <span class="text-xs font-semibold text-slate-800 block leading-tight">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] uppercase font-bold text-emerald-700 bg-emerald-50 px-1 py-0.2 rounded border border-emerald-200">{{ auth()->user()->kode_id }} &bull; {{ auth()->user()->role }}</span>
                            </div>
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="p-2 rounded-xl border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-600 text-slate-500 transition-colors" title="Keluar">
                                <span class="material-symbols-outlined text-[20px]">logout</span>
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-xs sm:text-sm font-semibold text-slate-700 hover:text-[#2E6B56] px-2 sm:px-3 py-2 transition-colors">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="h-9 sm:h-10 px-3 sm:px-4 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white font-semibold text-xs sm:text-sm inline-flex items-center gap-1.5 shadow-sm transition-colors">
                        <span class="material-symbols-outlined text-[16px] sm:text-[18px]">person_add</span> Daftar
                    </a>
                @endauth

                <!-- Hamburger Button (Mobile / Tablet) -->
                <button type="button" onclick="document.getElementById('mobile-nav').classList.toggle('hidden')" class="lg:hidden p-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                    <span class="material-symbols-outlined text-[22px]">menu</span>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div id="mobile-nav" class="hidden lg:hidden px-4 py-3 space-y-1.5 border-t border-slate-100 bg-white">
            <a href="{{ route('home') }}" class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('home') ? 'bg-[#2E6B56] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-50' }}">
                Beranda & Rundown
            </a>
            <a href="{{ route('scoreboard') }}" class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('scoreboard*') ? 'bg-[#2E6B56] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-50' }}">
                Live Klasemen
            </a>
            @auth
                @if(auth()->user()->isPeserta())
                    <a href="{{ route('peserta.burung.index') }}" class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('peserta.burung*') ? 'bg-[#2E6B56] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-50' }}">
                        Burung Saya
                    </a>
                    <a href="{{ route('peserta.daftar') }}" class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('peserta.daftar*') ? 'bg-[#2E6B56] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-50' }}">
                        Daftar Lomba
                    </a>
                @elseif(auth()->user()->isJuri())
                    <a href="{{ route('juri.dashboard') }}" class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('juri.*') ? 'bg-[#2E6B56] text-white shadow-xs' : 'text-emerald-800 bg-emerald-50' }}">
                        Penjurian Lapangan
                    </a>
                @elseif(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-[#2E6B56] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-50' }}">
                        Panel Admin
                    </a>
                    <a href="{{ route('admin.events.index') }}" class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.events*') ? 'bg-[#2E6B56] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-50' }}">
                        Event & Kategori
                    </a>
                    <a href="{{ route('admin.verifikasi.index') }}" class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.verifikasi*') ? 'bg-[#2E6B56] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-50' }}">
                        Verifikasi
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="block px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.users*') ? 'bg-[#2E6B56] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-50' }}">
                        Kelola User (CRUD)
                    </a>
                @endif
            @endauth
        </div>
    </header>

    <!-- Flash Notifications -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-2 shadow-xs">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl flex items-center gap-2 shadow-xs">
                <span class="material-symbols-outlined text-rose-600">error</span>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl shadow-xs">
                <div class="flex items-center gap-2 font-semibold text-sm mb-1">
                    <span class="material-symbols-outlined text-rose-600 text-[18px]">warning</span> Terdapat beberapa kesalahan input:
                </div>
                <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content Body -->
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span class="font-bold text-[#0F172A]">KicauScore</span>
                <span>&bull;</span>
                <span>Platform Penjurian Burung Berkicau Multi-Juri Terstandarisasi</span>
            </div>
            <div>
                &copy; {{ date('Y') }} KicauScore System. All rights reserved.
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
