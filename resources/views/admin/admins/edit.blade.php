@extends('layouts.admin.app')

@section('title', 'Edit Admin: ' . $admin->name)

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumb & Header --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <a href="{{ route('admin.admins.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 mb-2">
                    <i class="ti ti-arrow-left"></i> Kembali ke Daftar Admin
                </a>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    Edit Akun: {{ $admin->name }}
                    @if($admin->id === 1)
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 font-bold">Admin Utama</span>
                    @endif
                </h1>
                <p class="text-sm text-gray-500 mt-0.5">Ubah rincian akun, role, atau sesuaikan hak akses modul.</p>
            </div>
        </div>

        @if(isset($errors) && $errors->any())
            <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 shadow-sm">
                <p class="font-bold flex items-center gap-2 mb-1">
                    <i class="ti ti-alert-circle text-lg text-rose-600"></i> Mohon periksa kesalahan input berikut:
                </p>
                <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.admins.update', $admin->id) }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">

                {{-- Card 1: Informasi Akun --}}
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-sm">
                    <h2 class="text-base font-bold text-gray-800 border-b border-gray-100 pb-3 mb-5 flex items-center gap-2">
                        <i class="ti ti-user text-blue-600"></i> Informasi Pengguna
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        {{-- Nama Lengkap --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $admin->name) }}" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        {{-- Username --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Username <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 font-mono">@</span>
                                <input type="text" name="username" value="{{ old('username', $admin->username) }}" required
                                       class="w-full pl-8 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Alamat Email <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email', $admin->email) }}" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        {{-- Password (Opsional) --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Password Baru (Kosongkan jika tidak diubah)
                            </label>
                            <input type="password" name="password" minlength="6"
                                   placeholder="Biarkan kosong jika tidak diubah"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Konfirmasi Password Baru
                            </label>
                            <input type="password" name="password_confirmation" minlength="6"
                                   placeholder="Ulangi jika mengubah password"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                {{-- Card 2: Role & Status --}}
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-sm">
                    <h2 class="text-base font-bold text-gray-800 border-b border-gray-100 pb-3 mb-5 flex items-center gap-2">
                        <i class="ti ti-shield text-blue-600"></i> Role & Tanggung Jawab
                    </h2>

                    @if($admin->id === 1)
                        <div class="mb-4 p-4 rounded-xl bg-purple-50 border border-purple-200 flex items-center gap-3">
                            <i class="ti ti-lock text-xl text-purple-700"></i>
                            <div class="text-xs text-purple-900">
                                <p class="font-bold">Akun Admin Utama (Protected)</p>
                                <p>Role untuk akun ini dikunci secara permanen sebagai Super Admin dan selalu aktif untuk menjamin kelangsungan akses sistem.</p>
                            </div>
                        </div>
                        <input type="hidden" name="role" value="super_admin">
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
                            @php
                                $roleDescriptions = [
                                    'super_admin'    => 'Akses penuh ke seluruh sistem, manajemen akun admin, dan log aktivitas.',
                                    'admin_ppdb'     => 'Fokus di PSB (Penerimaan Santri Baru) & Data Santri Baru.',
                                    'operator'       => 'Mengelola Tahfizh, Data Santri, UKS (Kesehatan), dan Uang Saku Santri.',
                                    'admin_keuangan' => 'Bendahara: Biaya Masuk & Daftar Ulang Santri, SPP Bulanan, dan Infaq.',
                                    'admin_tahfidz'  => 'Kelola data hafalan & mutaba\'ah santri (akses data santri read-only).',
                                    'admin_kasir'    => 'Kasir POS Kantin / Minimarket Aqumart.',
                                    'admin_spp'       => 'Admin SPP & Administrasi Bulanan.',
                                    'ustadz_kelas'    => 'Ustadz Pembina Kelas.',
                                    'admin_uang_saku' => 'Petugas khusus pencatatan titipan & belanja uang saku santri.',
                                ];
                            @endphp

                            @foreach($roles as $roleKey => $roleLabel)
                                <label class="relative flex items-start gap-3 p-4 rounded-xl border border-gray-200 hover:border-blue-300 hover:bg-blue-50/30 cursor-pointer transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50 has-[:checked]:ring-1 has-[:checked]:ring-blue-500">
                                    <input type="radio" name="role" value="{{ $roleKey }}"
                                           {{ old('role', $admin->role) === $roleKey ? 'checked' : '' }}
                                           onchange="applyRoleDefaultPermissions('{{ $roleKey }}')"
                                           class="mt-1 text-blue-600 focus:ring-blue-500">
                                    <div class="min-w-0">
                                        <p class="font-bold text-sm text-gray-800">{{ $roleLabel }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5 leading-snug">{{ $roleDescriptions[$roleKey] ?? '' }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    {{-- Status Aktif Toggle --}}
                    @if($admin->id !== 1 && $admin->id !== auth('admin')->id())
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200">
                            <div>
                                <p class="font-bold text-sm text-gray-800">Status Akun Aktif</p>
                                <p class="text-xs text-gray-500">Jika dinonaktifkan, akun ini tidak dapat login ke sistem admin.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $admin->is_active) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    @else
                        <input type="hidden" name="is_active" value="1">
                    @endif

                    {{-- Assigned Toko (hanya jika role = admin_kasir) --}}
                    @if($admin->id !== 1)
                    <div id="boxAssignedToko" class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl {{ old('role', $admin->role) !== 'admin_kasir' ? 'hidden' : '' }}">
                        <label class="block text-xs font-bold uppercase tracking-wider text-amber-800 mb-2">
                            <i class="ti ti-building-store mr-1"></i>
                            Kantin / Toko yang Dikelola <span class="text-rose-500">*</span>
                        </label>
                        <select name="assigned_toko" id="assignedTokoSelect"
                                class="w-full px-3 py-2.5 border border-amber-300 rounded-xl text-sm bg-white focus:ring-2 focus:ring-amber-400">
                            <option value="">-- Pilih Kantin / Toko --</option>
                            <option value="kantin_1" {{ old('assigned_toko', $admin->assigned_toko) === 'kantin_1' ? 'selected' : '' }}>Kantin 1 (Makanan / Minuman Berat)</option>
                            <option value="kantin_2" {{ old('assigned_toko', $admin->assigned_toko) === 'kantin_2' ? 'selected' : '' }}>Kantin 2 (Snack / Minuman Ringan)</option>
                            <option value="aqumart"  {{ old('assigned_toko', $admin->assigned_toko) === 'aqumart'  ? 'selected' : '' }}>Minimarket Aqumart (Kebutuhan & Kitab)</option>
                        </select>
                        <p class="text-[11px] text-amber-700 mt-1.5">
                            <i class="ti ti-info-circle"></i>
                            Admin kasir ini hanya dapat mengelola produk, transaksi, dan laporan pada toko yang dipilih di atas.
                        </p>
                    </div>
                    @endif
                </div>

                {{-- Card 3: Rincian Hak Akses (Custom Permission Overrides) --}}
                @if($admin->id !== 1)
                    <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-sm">
                        <div class="border-b border-gray-100 pb-3 mb-4">
                            <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                                <i class="ti ti-lock-access text-blue-600"></i> Rincian Hak Akses Modul
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">Centang modul yang diizinkan untuk akun ini.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="permissionsContainer">
                            @foreach($permissions as $permKey => $permLabel)
                                @php
                                    $isChecked = $admin->canAccess($permKey);
                                @endphp
                                <label class="flex items-start gap-2.5 p-3 rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-gray-100/70 cursor-pointer transition">
                                    <input type="checkbox" name="custom_permissions[{{ $permKey }}]" value="1"
                                           id="perm_{{ $permKey }}"
                                           {{ $isChecked ? 'checked' : '' }}
                                           class="mt-0.5 rounded text-blue-600 focus:ring-blue-500">
                                    <div class="text-xs">
                                        <span class="font-bold text-gray-800 block">{{ $permLabel }}</span>
                                        <span class="text-gray-400 font-mono text-[10px]">{{ $permKey }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.admins.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-100 text-sm font-semibold transition">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-md transition cursor-pointer">
                        Perbarui Akun Admin
                    </button>
                </div>

            </div>
        </form>

    </div>
</div>

<script>
    const roleDefaults = {
        'super_admin':    ['admin_management', 'master_data', 'ppdb', 'kesehatan', 'uang_saku', 'tahfidz', 'daftar_ulang', 'spp', 'infaq', 'kantin', 'pengguna', 'otp_monitoring', 'absensi', 'pelanggaran', 'prestasi', 'kalender', 'saran', 'monitoring_wali'],
        'admin_ppdb':     ['ppdb', 'master_data', 'kalender'],
        'operator':       ['tahfidz', 'ppdb_read', 'kesehatan', 'uang_saku', 'otp_monitoring', 'pengguna', 'pelanggaran', 'prestasi', 'kalender', 'saran', 'monitoring_wali'],
        'admin_keuangan': ['daftar_ulang', 'spp', 'infaq'],
        'admin_tahfidz':  ['tahfidz', 'ppdb_read', 'prestasi', 'kalender'],
        'admin_kasir':    ['kantin'],
        'admin_spp':      ['spp'],
        'ustadz_kelas':   ['absensi', 'prestasi', 'uang_saku', 'pelanggaran', 'kalender'],
        'admin_uang_saku':['uang_saku', 'ppdb_read']
    };

    function applyRoleDefaultPermissions(role) {
        const allowed = roleDefaults[role] || [];
        document.querySelectorAll('#permissionsContainer input[type="checkbox"]').forEach(cb => {
            const key = cb.id.replace('perm_', '');
            cb.checked = allowed.includes(key);
        });
        // Tampilkan/sembunyikan pilihan kantin
        const boxToko = document.getElementById('boxAssignedToko');
        if (boxToko) {
            boxToko.classList.toggle('hidden', role !== 'admin_kasir');
        }
    }
</script>
@endsection