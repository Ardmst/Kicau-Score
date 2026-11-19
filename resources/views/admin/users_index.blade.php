@extends('layouts.app', ['title' => 'Manajemen Pengguna & Dewan Juri'])

@section('content')
<div class="space-y-6">
    
    <!-- Header Page -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-800 text-xs font-bold uppercase tracking-wider">Hak Akses & Pengguna</span>
            </div>
            <h1 class="font-headline font-bold text-2xl text-slate-900 tracking-tight mt-1">Manajemen Pengguna (CRUD)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola akun administrator, dewan juri lapangan bersertifikat, dan peserta lomba</p>
        </div>

        <button type="button" onclick="openTambahModal()" class="h-10 px-4 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition-colors shrink-0">
            <span class="material-symbols-outlined text-[18px]">person_add</span> Tambah Pengguna Baru
        </button>
    </div>

    <!-- User Roles Counters -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:border-slate-400 transition-colors block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Administrator</span>
                <span class="material-symbols-outlined text-slate-400 text-[20px]">admin_panel_settings</span>
            </div>
            <span class="font-headline font-bold text-2xl text-slate-900 block mt-1">{{ $totalAdmin }}</span>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'juri']) }}" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:border-emerald-300 transition-colors block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Dewan Juri Lapangan</span>
                <span class="material-symbols-outlined text-emerald-600 text-[20px]">gavel</span>
            </div>
            <span class="font-headline font-bold text-2xl text-emerald-700 block mt-1">{{ $totalJuri }}</span>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'peserta']) }}" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:border-blue-300 transition-colors block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Peserta Kicaumania</span>
                <span class="material-symbols-outlined text-blue-600 text-[20px]">groups</span>
            </div>
            <span class="font-headline font-bold text-2xl text-blue-700 block mt-1">{{ $totalPeserta }}</span>
        </a>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <!-- Filter Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0" style="scrollbar-width: thin;">
            <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ empty($role) ? 'bg-[#2E6B56] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua Role
            </a>
            <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $role === 'admin' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Admin
            </a>
            <a href="{{ route('admin.users.index', ['role' => 'juri']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $role === 'juri' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Juri
            </a>
            <a href="{{ route('admin.users.index', ['role' => 'peserta']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $role === 'peserta' ? 'bg-blue-700 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Peserta
            </a>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2">
            @if(!empty($role))
                <input type="hidden" name="role" value="{{ $role }}">
            @endif
            <div class="relative w-full md:w-64">
                <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama, no. HP, email..." 
                       class="w-full pl-9 pr-3.5 py-1.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                <span class="material-symbols-outlined absolute left-2.5 top-2 text-slate-400 text-[18px]">search</span>
            </div>
            <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                Cari
            </button>
            @if(!empty($q))
                <a href="{{ route('admin.users.index', ['role' => $role]) }}" class="px-2 py-1.5 text-xs text-rose-600 hover:underline">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-4">Nama Pengguna</th>
                        <th class="py-3 px-4">No. HP / WhatsApp</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4 text-center">Peran (Role)</th>
                        <th class="py-3 px-4 text-center">Terdaftar</th>
                        <th class="py-3 px-4 text-center">Aksi (CRUD)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4">
                                <strong class="font-bold text-slate-900 block">{{ $user->name }}</strong>
                                <span class="text-xs text-slate-400">ID: #{{ $user->id }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 text-xs font-medium">
                                {{ $user->phone ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 text-xs">
                                {{ $user->email ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $roleBadge = match($user->role) {
                                        'admin' => 'bg-slate-900 text-white font-bold',
                                        'juri' => 'bg-emerald-100 text-emerald-800 border-emerald-300 font-bold',
                                        'peserta' => 'bg-blue-50 text-blue-800 border-blue-200 font-medium',
                                    };
                                @endphp
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs border {{ $roleBadge }} uppercase">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center text-xs text-slate-400">
                                {{ $user->created_at ? $user->created_at->isoFormat('D MMM Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" 
                                            onclick='openEditModal(@json($user))'
                                            class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors"
                                            title="Edit Data User">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                    </button>

                                    @if(auth()->id() !== $user->id)
                                        <button type="button" 
                                                onclick="openDeleteModal('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->role }}')"
                                                class="p-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition-colors"
                                                title="Hapus User">
                                            <span class="material-symbols-outlined text-[16px]">delete</span>
                                        </button>
                                    @else
                                        <span class="p-1.5 text-slate-300 text-[11px] italic" title="Akun Anda Saat Ini">(Anda)</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <span class="material-symbols-outlined text-[40px] text-slate-300 block mb-1">person_search</span>
                                Tidak ada data pengguna yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Pengguna Baru (Create) -->
<div id="modal-tambah-user" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 relative">
        <button type="button" onclick="closeTambahModal()" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined">close</span>
        </button>

        <div class="mb-4">
            <h3 class="font-headline font-bold text-lg text-slate-900">Tambah Akun Pengguna Baru</h3>
            <p class="text-xs text-slate-500 mt-0.5">Daftarkan akun Admin, Dewan Juri, atau Peserta lomba</p>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="create_role" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Peran (Role) Akun</label>
                <select name="role" id="create_role" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                    <option value="peserta">Peserta (Kicaumania)</option>
                    <option value="juri">Dewan Juri Lapangan</option>
                    <option value="admin">Administrator Sistem</option>
                </select>
            </div>

            <div>
                <label for="create_name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Lengkap</label>
                <input type="text" name="name" id="create_name" required placeholder="Contoh: Rian Hidayat"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div>
                <label for="create_phone" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">No. WhatsApp / HP</label>
                <input type="text" name="phone" id="create_phone" required placeholder="081234567890"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div>
                <label for="create_email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Alamat Email (Opsional)</label>
                <input type="email" name="email" id="create_email" placeholder="email@contoh.com"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div>
                <label for="create_password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Kata Sandi (Min. 6 Karakter)</label>
                <input type="password" name="password" id="create_password" required placeholder="••••••••"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeTambahModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold shadow-sm">
                    Simpan Akun
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pengguna (Update) -->
<div id="modal-edit-user" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 relative">
        <button type="button" onclick="closeEditModal()" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined">close</span>
        </button>

        <div class="mb-4">
            <h3 class="font-headline font-bold text-lg text-slate-900">Perbarui Data Pengguna</h3>
            <p class="text-xs text-slate-500 mt-0.5">Ubah identitas, nomor WhatsApp, peran, atau reset kata sandi</p>
        </div>

        <form id="form-edit-user" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="edit_role" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Peran (Role) Akun</label>
                <select name="role" id="edit_role" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
                    <option value="peserta">Peserta (Kicaumania)</option>
                    <option value="juri">Dewan Juri Lapangan</option>
                    <option value="admin">Administrator Sistem</option>
                </select>
            </div>

            <div>
                <label for="edit_name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Lengkap</label>
                <input type="text" name="name" id="edit_name" required
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div>
                <label for="edit_phone" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">No. WhatsApp / HP</label>
                <input type="text" name="phone" id="edit_phone" required
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div>
                <label for="edit_email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Alamat Email (Opsional)</label>
                <input type="email" name="email" id="edit_email"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div>
                <label for="edit_password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Reset Kata Sandi (Kosongkan jika tidak diubah)</label>
                <input type="password" name="password" id="edit_password" placeholder="Biarkan kosong jika tetap"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E6B56]">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#2E6B56] hover:bg-[#245443] text-white text-xs font-semibold shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus (Delete) -->
<div id="modal-delete-user" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-sm w-full p-6 relative">
        <div class="text-center space-y-3">
            <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                <span class="material-symbols-outlined text-[28px]">warning</span>
            </div>
            <h3 class="font-headline font-bold text-lg text-slate-900">Hapus Akun Pengguna?</h3>
            <p class="text-xs text-slate-500">
                Apakah Anda yakin ingin menghapus akun <strong id="delete-user-name" class="text-slate-800"></strong>? Tindakan ini bersifat permanen.
            </p>
        </div>

        <form id="form-delete-user" method="POST" class="mt-5 flex items-center justify-center gap-2">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteModal()" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                Batal
            </button>
            <button type="submit" class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm">
                Ya, Hapus
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openTambahModal() {
        document.getElementById('modal-tambah-user').classList.remove('hidden');
    }
    function closeTambahModal() {
        document.getElementById('modal-tambah-user').classList.add('hidden');
    }

    function openEditModal(user) {
        const form = document.getElementById('form-edit-user');
        form.action = `/admin/users/${user.id}`;
        document.getElementById('edit_name').value = user.name || '';
        document.getElementById('edit_phone').value = user.phone || '';
        document.getElementById('edit_email').value = user.email || '';
        document.getElementById('edit_role').value = user.role || 'peserta';
        document.getElementById('edit_password').value = '';
        document.getElementById('modal-edit-user').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('modal-edit-user').classList.add('hidden');
    }

    function openDeleteModal(userId, userName, userRole) {
        const form = document.getElementById('form-delete-user');
        form.action = `/admin/users/${userId}`;
        document.getElementById('delete-user-name').textContent = `${userName} (${userRole})`;
        document.getElementById('modal-delete-user').classList.remove('hidden');
    }
    function closeDeleteModal() {
        document.getElementById('modal-delete-user').classList.add('hidden');
    }
</script>
@endpush
@endsection
