@extends('layouts.admin.app')

@section('title', 'Tambah Admin Baru')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumb & Header --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <a href="{{ route('admin.admins.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 mb-2">
                    <i class="ti ti-arrow-left"></i> Kembali ke Daftar Admin
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Tambah Akun Admin Baru</h1>
                <p class="text-sm text-gray-500 mt-0.5">Buat akun pengelola baru dan tentukan hak akses tugasnya.</p>
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

        <form method="POST" action="{{ route('admin.admins.store') }}">
            @csrf

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
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   placeholder="Contoh: Ustadz Ahmad Fauzi"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        {{-- Username --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Username <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 font-mono">@</span>
                                <input type="text" name="username" value="{{ old('username') }}" required
                                       placeholder="ahmad_fauzi"
                                       class="w-full pl-8 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1">Dapat digunakan untuk login. Huruf, angka, tanda hubung & garis bawah.</p>
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Alamat Email <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   placeholder="ahmad@alamquran.com"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        {{-- Password --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Password <span class="text-rose-500">*</span>
                            </label>
                            <input type="password" name="password" required minlength="6"
                                   placeholder="Minimal 6 karakter"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                Konfirmasi Password <span class="text-rose-500">*</span>
                            </label>
                            <input type="password" name="password_confirmation" required minlength="6"
                                   placeholder="Ulangi password di atas"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                {{-- Card 2: Role & Status --}}
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-sm">
                    <h2 class="text-base font-bold text-gray-800 border-b border-gray-100 pb-3 mb-5 flex items-center gap-2">
                        <i class="ti ti-shield text-blue-600"></i> Role & Tanggung Jawab
                    </h2>

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
                                       {{ old('role', 'admin_ppdb') === $roleKey ? 'checked' : '' }}
                                       onchange="applyRoleDefaultPermissions('{{ $roleKey }}')"
                                       class="mt-1 text-blue-600 focus:ring-blue-500">
                                <div class="min-w-0">
                                    <p class="font-bold text-sm text-gray-800">{{ $roleLabel }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5 leading-snug">{{ $roleDescriptions[$roleKey] ?? '' }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    {{-- Status Aktif Toggle --}}
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200">
                        <div>
                            <p class="font-bold text-sm text-gray-800">Status Akun Aktif</p>
                            <p class="text-xs text-gray-500">Jika dinonaktifkan, akun ini tidak dapat login ke sistem admin.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    {{-- Assigned Toko (hanya muncul jika role = admin_kasir) --}}
                    <div id="boxAssignedToko" class="hidden mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                        <label class="block text-xs font-bold uppercase tracking-wider text-amber-800 mb-2">
                            <i class="ti ti-building-store mr-1"></i>
                            Kantin / Toko yang Dikelola <span class="text-rose-500">*</span>
                        </label>
                        <select name="assigned_toko" id="assignedTokoSelect"
                                class="w-full px-3 py-2.5 border border-amber-300 rounded-xl text-sm bg-white focus:ring-2 focus:ring-amber-400">
                            <option value="">-- Pilih Kantin / Toko --</option>
                            <option value="kantin_1" {{ old('assigned_toko') === 'kantin_1' ? 'selected' : '' }}>Kantin 1 (Makanan / Minuman Berat)</option>
                            <option value="kantin_2" {{ old('assigned_toko') === 'kantin_2' ? 'selected' : '' }}>Kantin 2 (Snack / Minuman Ringan)</option>
                            <option value="aqumart"  {{ old('assigned_toko') === 'aqumart'  ? 'selected' : '' }}>Minimarket Aqumart (Kebutuhan & Kitab)</option>
                        </select>
                        <p class="text-[11px] text-amber-700 mt-1.5">
                            <i class="ti ti-info-circle"></i>
                            Admin kasir ini hanya dapat mengelola produk, transaksi, dan laporan pada toko yang dipilih di atas.
                        </p>
                    </div>
                </div>

                {{-- Card 3: Rincian Hak Akses (Custom Permission Overrides) --}}
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                        <div>
                            <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                                <i class="ti ti-lock-access text-blue-600"></i> Rincian Hak Akses Modul
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">Izin ini otomatis terpilih sesuai role di atas, namun Anda dapat menyesuaikan hak akses tambahan.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="permissionsContainer">
                        @foreach($permissions as $permKey => $permLabel)
                            <label class="flex items-start gap-2.5 p-3 rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-gray-100/70 cursor-pointer transition">
                                <input type="checkbox" name="custom_permissions[{{ $permKey }}]" value="1"
                                       id="perm_{{ $permKey }}"
                                       class="mt-0.5 rounded text-blue-600 focus:ring-blue-500">
                                <div class="text-xs">
                                    <span class="font-bold text-gray-800 block">{{ $permLabel }}</span>
                                    <span class="text-gray-400 font-mono text-[10px]">{{ $permKey }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.admins.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-100 text-sm font-semibold transition">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-md transition cursor-pointer">
                        Simpan Akun Admin
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
        // Tampilkan/sembunyikan pilihan assigned_toko
        const boxToko = document.getElementById('boxAssignedToko');
        if (boxToko) {
            boxToko.classList.toggle('hidden', role !== 'admin_kasir');
        }
    }

    // Terapkan saat pertama kali render
    document.addEventListener('DOMContentLoaded', () => {
        const selectedRole = document.querySelector('input[name="role"]:checked');
        if (selectedRole) {
            applyRoleDefaultPermissions(selectedRole.value);
        }
    });
</script>
@endsection