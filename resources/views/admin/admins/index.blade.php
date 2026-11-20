@extends('layouts.admin.app')

@section('title', 'Manajemen Admin')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="w-full py-6 px-4 lg:px-8">

        {{-- Header & Navigation --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2.5">
                    <i class="ti ti-users-group text-blue-600"></i> Manajemen Admin
                </h1>
                <p class="text-sm text-gray-500 mt-1">Kelola akun administrator, tentukan role, hak akses, dan pantau status aktif/nonaktif.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.admins.logs') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50 hover:text-blue-600 transition shadow-sm">
                    <i class="ti ti-history text-lg"></i> Riwayat Aktivitas
                </a>
                <a href="{{ route('admin.admins.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition shadow-md">
                    <i class="ti ti-plus text-lg"></i> Tambah Admin
                </a>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm font-medium text-emerald-800 shadow-sm">
                <i class="ti ti-circle-check text-xl text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 flex items-center gap-3 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm font-medium text-red-800 shadow-sm">
                <i class="ti ti-alert-circle text-xl text-red-600"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Statistik Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl flex-shrink-0">
                    <i class="ti ti-users"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Admin</p>
                    <p class="text-2xl font-extrabold text-gray-800">{{ $totalAdmins }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl flex-shrink-0">
                    <i class="ti ti-user-check"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Admin Aktif</p>
                    <p class="text-2xl font-extrabold text-emerald-600">{{ $activeAdmins }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl flex-shrink-0">
                    <i class="ti ti-user-x"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Admin Nonaktif</p>
                    <p class="text-2xl font-extrabold text-rose-600">{{ $inactiveAdmins }}</p>
                </div>
            </div>
        </div>

        {{-- Filter & Pencarian --}}
        <div class="mb-6 rounded-2xl bg-white p-4 shadow-sm border border-gray-200/80">
            <form method="GET" action="{{ route('admin.admins.index') }}" class="flex flex-col md:flex-row gap-3">
                <div class="flex-1 relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nama, username, atau email admin..."
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    <i class="ti ti-search absolute left-3.5 top-3 text-gray-400 text-lg"></i>
                </div>

                <div class="w-full md:w-56">
                    <select name="role" onchange="this.form.submit()" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Role</option>
                        @foreach($roles as $roleKey => $roleName)
                            <option value="{{ $roleKey }}" {{ request('role') == $roleKey ? 'selected' : '' }}>{{ $roleName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full md:w-44">
                    <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition flex items-center gap-1.5 cursor-pointer shadow-sm">
                        <i class="ti ti-search"></i> Cari
                    </button>
                    <a href="{{ route('admin.admins.index') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition text-sm font-bold flex items-center gap-1">
                        <i class="ti ti-refresh"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Tabel Daftar Admin --}}
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-200/80">
            <div class="border-b border-gray-100 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <h2 class="font-bold text-gray-800 text-base">Daftar Akun Administrator ({{ $admins->total() }})</h2>
                <span class="text-xs text-gray-400">Kelola izin & keamanan akun secara terpusat</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50/80 text-left text-xs font-bold uppercase text-gray-600">
                        <tr>
                            <th class="px-6 py-3.5">Admin</th>
                            <th class="px-6 py-3.5">Role</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5">Terakhir Login</th>
                            <th class="px-6 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($admins as $admin)
                            @php
                                $roleBadges = [
                                    'super_admin'    => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'admin_ppdb'     => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'admin_keuangan' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'admin_tahfidz'  => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'operator'       => 'bg-teal-50 text-teal-700 border-teal-200',
                                ];
                                $badgeClass = $roleBadges[$admin->role] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                            @endphp
                            <tr class="hover:bg-gray-50/80 transition duration-150">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-600 font-bold text-base flex-shrink-0">
                                            {{ strtoupper(substr($admin->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-gray-800 truncate flex items-center gap-1.5">
                                                {{ $admin->name }}
                                                @if($admin->id === 1)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">
                                                        Utama
                                                    </span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500">{{ $admin->email }}</p>
                                            @if($admin->username)
                                                <p class="text-xs text-blue-600 font-mono mt-0.5">@<span>{{ $admin->username }}</span></p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold border {{ $badgeClass }}">
                                        {{ $admin->role_label }}
                                    </span>
                                    @if($admin->assigned_toko)
                                        @php
                                            $tokoColors = [
                                                'kantin_1' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'kantin_2' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                'aqumart'  => 'bg-amber-50 text-amber-700 border-amber-200',
                                            ];
                                        @endphp
                                        <span class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold border {{ $tokoColors[$admin->assigned_toko] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                            <i class="ti ti-building-store"></i>
                                            {{ $admin->assigned_toko_label }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    @if($admin->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-xs text-gray-500">
                                    @if($admin->last_login_at)
                                        <p class="font-medium text-gray-700">{{ $admin->last_login_at->translatedFormat('d M Y, H:i') }}</p>
                                        <p class="text-gray-400 font-mono text-[11px]">IP: {{ $admin->last_login_ip ?? '-' }}</p>
                                    @else
                                        <span class="text-gray-400 italic">Belum pernah login</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- Tombol Edit --}}
                                        <a href="{{ route('admin.admins.edit', $admin->id) }}"
                                           title="Edit Admin"
                                           class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition">
                                            <i class="ti ti-edit text-base"></i>
                                        </a>

                                        {{-- Tombol Reset Password --}}
                                        <button type="button"
                                                title="Reset Password"
                                                onclick="openResetModal({{ $admin->id }}, '{{ addslashes($admin->name) }}')"
                                                class="p-2 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition cursor-pointer">
                                            <i class="ti ti-key text-base"></i>
                                        </button>

                                        {{-- Toggle Status (Hanya jika bukan Admin Utama / diri sendiri) --}}
                                        @if($admin->id !== 1 && $admin->id !== auth('admin')->id())
                                            <form method="POST" action="{{ route('admin.admins.toggle', $admin->id) }}" class="inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin {{ $admin->is_active ? 'menonaktifkan' : 'mengaktifkan' }} akun {{ $admin->name }}?');">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit"
                                                        title="{{ $admin->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}"
                                                        class="p-2 rounded-lg {{ $admin->is_active ? 'bg-rose-50 text-rose-600 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }} transition cursor-pointer">
                                                    <i class="ti {{ $admin->is_active ? 'ti-user-off' : 'ti-user-check' }} text-base"></i>
                                                </button>
                                            </form>

                                            {{-- Tombol Hapus (Bukan Super Admin) --}}
                                            @if(!$admin->isSuperAdmin())
                                                <form method="POST" action="{{ route('admin.admins.destroy', $admin->id) }}" class="inline"
                                                      onsubmit="return confirm('Hapus permanen akun admin {{ $admin->name }}? Tindakan ini tidak dapat dibatalkan.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            title="Hapus Admin"
                                                            class="p-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-red-50 hover:text-red-600 transition cursor-pointer">
                                                        <i class="ti ti-trash text-base"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                    <i class="ti ti-mood-empty text-4xl block mb-2"></i>
                                    Tidak ada data admin yang cocok dengan kriteria pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($admins->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $admins->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

{{-- Modal Reset Password --}}
<div id="resetModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
            <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                <i class="ti ti-key text-amber-500"></i> Reset Password Admin
            </h3>
            <button type="button" onclick="closeResetModal()" class="text-gray-400 hover:text-gray-600">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>

        <p class="text-sm text-gray-600 mb-4">
            Masukkan password baru untuk akun <strong id="modalAdminName" class="text-gray-900"></strong>:
        </p>

        <form id="resetPasswordForm" method="POST" action="">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Password Baru (min. 6 karakter)</label>
                <input type="password" name="password" required minlength="6"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="mb-6">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required minlength="6"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                <button type="button" onclick="closeResetModal()" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-xl transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition shadow-md">
                    Simpan Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openResetModal(adminId, adminName) {
        document.getElementById('modalAdminName').textContent = adminName;
        document.getElementById('resetPasswordForm').action = "{{ url('admin/admins') }}/" + adminId + "/reset-password";
        document.getElementById('resetModal').classList.remove('hidden');
    }

    function closeResetModal() {
        document.getElementById('resetModal').classList.add('hidden');
    }
</script>
@endsection