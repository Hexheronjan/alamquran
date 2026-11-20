@extends('layouts.admin.app')

@section('title', 'Manajemen Santri Aktif')

@section('content')
<div class="bg-gray-100 min-h-screen" x-data="{ showImportModal: false }">
    <div class="w-full py-6 px-4 lg:px-8">
        
        <!-- Header & Top Action Buttons -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Santri Aktif</h1>
                <p class="text-gray-600 text-sm">Kelola data seluruh santri aktif (Kelas 1, 2, 3), import Excel massal, dan export data</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.santri.create') }}"
                   style="background-color: #0d9488; color: #ffffff;"
                   class="px-4 py-2.5 rounded-lg text-sm font-semibold hover:opacity-90 transition flex items-center gap-2 border-0 cursor-pointer shadow">
                    <i class="fas fa-user-plus"></i> Tambah Santri
                </a>
                <a href="{{ route('admin.santri.exportExcel', request()->query()) }}"
                   style="background-color: #0284c7; color: #ffffff;"
                   class="px-4 py-2.5 rounded-lg text-sm font-semibold hover:opacity-90 transition flex items-center gap-2 border-0 cursor-pointer shadow">
                    <i class="fas fa-download"></i> Export Excel (XLSX)
                </a>
            </div>
        </div>

        <!-- Alert Error Report dari Import Excel (jika ada) -->
        @if(session('import_errors'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-xl shadow">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                        <div>
                            <h3 class="text-sm font-bold text-red-800">Laporan Hasil Import Excel</h3>
                            <p class="text-xs text-red-700 mt-0.5">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full text-xs text-left text-red-900 border border-red-200 bg-white rounded-lg">
                        <thead class="bg-red-100 font-bold uppercase text-red-800">
                            <tr>
                                <th class="px-3 py-2 border-b">Baris Excel</th>
                                <th class="px-3 py-2 border-b">Nama Santri</th>
                                <th class="px-3 py-2 border-b">Detail Error / Alasan Gagal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-red-100">
                            @foreach(session('import_errors') as $err)
                                <tr>
                                    <td class="px-3 py-2 font-mono font-semibold text-red-700">Baris {{ $err['row'] ?? '-' }}</td>
                                    <td class="px-3 py-2 font-medium">{{ $err['nama'] ?? '-' }}</td>
                                    <td class="px-3 py-2 text-red-600 font-mono">{{ $err['error'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-xl shadow flex items-center gap-3">
                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                <p class="text-sm font-semibold text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Search and Multi-Filter -->
        <div class="bg-white rounded-xl shadow p-4 mb-6">
            <form method="GET" action="{{ route('admin.santri.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div class="md:col-span-2 relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nama, NIS, NIK, NISN, atau nama ayah..."
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 text-sm">
                    <i class="fas fa-search absolute left-3.5 top-3 text-gray-400"></i>
                </div>
                <div>
                    <select name="status_santri" onchange="this.form.submit()" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 font-semibold">
                        <option value="all" {{ ($selectedStatusSantri ?? '') == 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="aktif" {{ ($selectedStatusSantri ?? '') == 'aktif' ? 'selected' : '' }}>Santri Aktif</option>
                        <option value="mutasi" {{ ($selectedStatusSantri ?? '') == 'mutasi' ? 'selected' : '' }}>Santri Mutasi (Keluar)</option>
                    </select>
                </div>
                <div>
                    <select name="kelas" onchange="this.form.submit()" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                        <option value="">Semua Kelas</option>
                        <option value="1" {{ request('kelas') == '1' ? 'selected' : '' }}>Kelas 1</option>
                        <option value="2" {{ request('kelas') == '2' ? 'selected' : '' }}>Kelas 2</option>
                        <option value="3" {{ request('kelas') == '3' ? 'selected' : '' }}>Kelas 3</option>
                    </select>
                </div>
                <div>
                    <select name="tahun_masuk" onchange="this.form.submit()" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                        <option value="">Semua Tahun Masuk</option>
                        @foreach($tahunList as $tahun)
                            <option value="{{ $tahun }}" {{ request('tahun_masuk') == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" style="background-color: #0d9488; color: #ffffff;"
                            class="flex-1 py-2.5 rounded-lg text-sm font-semibold hover:opacity-90 transition flex items-center justify-center gap-2 border-0 cursor-pointer">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('admin.santri.index') }}" class="px-3 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-semibold flex items-center justify-center gap-1">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- JIKA SEDANG FILTER --}}
        @if(request('search') || request('status') || request('status_santri') || request('kelas') || request('tahun_masuk') || request('angkatan_id'))
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-base font-bold text-gray-800">
                        Hasil Pencarian / Filter ({{ $santri->total() }} Data Santri)
                    </h2>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.santri.cetakKartuMassal', request()->query()) }}" target="_blank"
                           style="background-color: #16a34a; color: #ffffff;"
                           class="px-3 py-1.5 rounded-lg text-xs font-bold shadow hover:opacity-90 inline-flex items-center gap-1.5">
                            <i class="fas fa-id-card"></i> Cetak Kartu Hasil Filter
                        </a>
                        <a href="{{ route('admin.santri.index') }}" class="text-xs text-teal-600 font-bold hover:underline">
                            Reset Filter
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <p class="sm:hidden px-4 py-1.5 text-[11px] text-gray-500 bg-gray-50 border-b border-gray-200 flex items-center gap-1.5">
                        <i class="fas fa-arrows-alt-h text-teal-600"></i> Geser ke kanan untuk melihat kolom lengkap & tombol aksi
                    </p>
                    <table class="admin-data-table divide-y divide-gray-200 text-sm">
                        <colgroup><col style="width:4%"><col style="width:16%"><col style="width:8%"><col style="width:12%"><col style="width:14%"><col style="width:14%"><col style="width:12%"><col style="width:10%"><col style="width:10%"></colgroup>
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3.5 text-left font-bold text-gray-700 uppercase">No</th>
                                <th class="px-4 py-3.5 text-left font-bold text-gray-700 uppercase">Nama Santri</th>
                                <th class="px-4 py-3.5 text-center font-bold text-gray-700 uppercase">Kelas</th>
                                <th class="px-4 py-3.5 text-left font-bold text-gray-700 uppercase">Tahun Masuk</th>
                                <th class="px-4 py-3.5 text-left font-bold text-gray-700 uppercase">NIS / NISN</th>
                                <th class="px-4 py-3.5 text-left font-bold text-gray-700 uppercase">Nama Ayah</th>
                                <th class="px-4 py-3.5 text-left font-bold text-gray-700 uppercase">No WA Ortu</th>
                                <th class="px-4 py-3.5 text-center font-bold text-gray-700 uppercase">Status</th>
                                <th class="px-4 py-3.5 text-center font-bold text-gray-700 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($santri as $idx => $s)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3.5 text-gray-500 font-medium">{{ $santri->firstItem() + $idx }}</td>
                                <td class="px-4 py-3.5 font-bold text-gray-900">{{ $s->nama_lengkap }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-md bg-teal-100 text-teal-800">
                                        {{ $s->kelas_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-xs font-mono font-semibold text-teal-800">{{ $s->tahun_masuk ?? '-' }}</td>
                                <td class="px-4 py-3.5 text-xs text-gray-600 font-mono">
                                    <div>NIS: {{ $s->nis ?? '-' }}</div>
                                    <div>NISN: {{ $s->nisn ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3.5 text-gray-700 font-medium">{{ $s->orangTua->nama_ayah ?? '-' }}</td>
                                <td class="px-4 py-3.5 text-xs text-gray-600 font-mono">
                                    <div>{{ $s->wali_account_status['no_wa_normalized'] ?? '-' }}</div>
                                    @if(!$s->wali_account_status['has_wa'])
                                        <a href="{{ route('admin.santri.edit', $s->id) }}" class="mt-1 inline-flex text-amber-700 font-bold hover:underline">⚠ Belum ada No. WA · Edit</a>
                                    @elseif($s->wali_account_status['has_account'])
                                        <span class="mt-1 inline-flex text-emerald-700 font-bold">✓ Akun Aktif</span>
                                    @else
                                        <button type="button" data-url="{{ route('admin.santri.wali.generate', $s->id) }}" class="mt-1 text-teal-700 font-bold hover:underline" onclick="generateWaliAccount(this)">Generate Akun</button>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($s->status_santri === 'mutasi')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-orange-100 text-orange-800 border border-orange-300 inline-flex items-center gap-1" title="{{ $s->keterangan_mutasi }}">
                                            <i class="fas fa-sign-out-alt"></i> MUTASI
                                        </span>
                                        @if($s->keterangan_mutasi)
                                            <div class="text-[10px] text-gray-500 mt-1 max-w-[140px] mx-auto truncate" title="{{ $s->keterangan_mutasi }}">{{ $s->keterangan_mutasi }}</div>
                                        @endif
                                    @else
                                        @switch($s->status)
                                            @case('menunggu_verifikasi')
                                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>@break
                                            @case('diverifikasi')
                                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">Diverifikasi</span>@break
                                            @case('diterima')
                                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700">Diterima</span>@break
                                            @case('ditolak')
                                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-red-100 text-red-700">Ditolak</span>@break
                                        @endswitch
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1 flex-wrap">
                                        <a href="{{ route('admin.santri.show', $s->id) }}"
                                           style="background-color: #0d9488; color: #ffffff;"
                                           class="px-2.5 py-1.5 rounded-lg text-xs font-bold shadow hover:opacity-90 inline-flex items-center gap-1">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        @if($s->status === 'diterima' || $s->status_santri === 'mutasi')
                                        <button type="button"
                                                onclick="openMutasiModal({{ $s->id }}, '{{ addslashes($s->nama_lengkap) }}', '{{ $s->status_santri ?? 'aktif' }}', '{{ addslashes($s->keterangan_mutasi ?? '') }}', '{{ $s->tanggal_mutasi ? $s->tanggal_mutasi->format('Y-m-d') : date('Y-m-d') }}')"
                                                style="{{ $s->status_santri === 'mutasi' ? 'background-color: #ea580c !important; color: #ffffff !important;' : 'background-color: #fff7ed !important; color: #c2410c !important; border: 1px solid #fed7aa !important;' }}"
                                                class="px-2.5 py-1.5 rounded-lg text-xs font-bold inline-flex items-center gap-1 cursor-pointer transition shadow-sm hover:opacity-90"
                                                title="Kelola Status Mutasi / Keluar">
                                            <i class="fas {{ $s->status_santri === 'mutasi' ? 'fa-edit' : 'fa-exchange-alt' }}"></i>
                                            <span>{{ $s->status_santri === 'mutasi' ? 'Edit Mutasi' : 'Mutasi' }}</span>
                                        </button>
                                        @endif
                                        <a href="{{ route('admin.santri.cetakKartu', $s->id) }}" target="_blank"
                                           class="px-2 py-1.5 rounded-lg text-xs font-bold bg-green-50 text-green-700 hover:bg-green-100 border border-green-200 inline-flex items-center gap-1"
                                           title="Cetak Kartu Santri">
                                            <i class="fas fa-id-card"></i> Kartu
                                        </a>
                                        <a href="{{ route('admin.santri.edit', $s->id) }}" class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 inline-flex items-center gap-1"><i class="fas fa-pen"></i> Edit</a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-6 py-10 text-center text-gray-500">Data santri tidak ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($santri->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t">
                    {{ $santri->appends(request()->query())->links() }}
                </div>
                @endif
            </div>

        {{-- TAMPILAN UTAMA: KOTAK PER TAHUN AJARAN --}}
        @else
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-folder-open text-teal-600"></i> Data Santri Aktif per Tahun Masuk
                    </h2>
                </div>

                @forelse($grouped as $tahun => $items)
                @php
                    $ag = $items->first()->angkatan;
                @endphp
                <div x-data="{ showData: {{ $loop->first ? 'true' : 'false' }} }" class="bg-white rounded-2xl shadow border border-gray-200 overflow-hidden transition">
                    {{-- Header Kotak Tahun Masuk --}}
                    <div style="background: linear-gradient(135deg, #0f766e 0%, #0891b2 100%); color: #ffffff;" class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div style="background-color: rgba(255, 255, 255, 0.2);" class="w-12 h-12 rounded-xl flex items-center justify-center text-xl font-bold text-white">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div>
                                <h3 style="color: #ffffff;" class="text-xl font-extrabold tracking-wide">
                                    Tahun Masuk {{ $tahun }}
                                </h3>
                                @if($ag)
                                <p style="color: #e0f2fe;" class="text-xs mt-0.5 font-medium">
                                    <i class="fas fa-bookmark mr-1"></i> {{ $ag->nama_angkatan }}
                                </p>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                            <span style="background-color: rgba(255, 255, 255, 0.2); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.3);" class="px-3 py-1.5 rounded-lg text-xs font-bold">
                                Total: {{ $items->count() }} Santri
                            </span>

                            <a href="{{ route('admin.santri.cetakKartuMassal', ['tahun_masuk' => $tahun === 'Belum Ditentukan' ? '__belum_ditentukan__' : $tahun]) }}" target="_blank"
                               style="background-color: #16a34a; color: #ffffff;"
                               class="px-3 py-1.5 rounded-lg text-xs font-extrabold shadow hover:opacity-90 transition inline-flex items-center gap-1.5"
                               title="Cetak Kartu Santri Massal untuk Tahun Masuk {{ $tahun }}">
                                <i class="fas fa-id-card"></i> Cetak Kartu ({{ $items->count() }})
                            </a>

                            <a href="{{ route('admin.santri.exportExcel', ['tahun_masuk' => $tahun === 'Belum Ditentukan' ? '__belum_ditentukan__' : $tahun]) }}"
                               style="background-color: #0284c7; color: #ffffff;"
                               class="px-3 py-1.5 rounded-lg text-xs font-extrabold shadow hover:opacity-90 transition inline-flex items-center gap-1.5">
                                <i class="fas fa-file-excel"></i> Export XLSX
                            </a>

                            <a href="{{ route('admin.santri.exportPDF', ['tahun_masuk' => $tahun === 'Belum Ditentukan' ? '__belum_ditentukan__' : $tahun]) }}"
                               style="background-color: #dc2626; color: #ffffff;"
                               class="px-3 py-1.5 rounded-lg text-xs font-extrabold shadow hover:opacity-90 transition inline-flex items-center gap-1.5">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </a>

                            <button @click="showData = !showData" type="button"
                                     style="background-color: #ffffff; color: #0f766e;"
                                     class="px-3 py-1.5 rounded-lg text-xs font-extrabold shadow hover:bg-gray-100 transition border-0 cursor-pointer flex items-center gap-1.5">
                                <span x-text="showData ? 'Sembunyikan' : 'Lihat Data'">Lihat Data</span>
                                <i class="fas" :class="showData ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Tabel Santri --}}
                    <div x-show="showData" x-transition class="overflow-x-auto border-t border-gray-200">
                        <p class="sm:hidden px-4 py-1.5 text-[11px] text-gray-500 bg-gray-50 border-b border-gray-200 flex items-center gap-1.5">
                            <i class="fas fa-arrows-alt-h text-teal-600"></i> Geser ke kanan untuk melihat kolom lengkap & tombol aksi
                        </p>
                        <table class="admin-data-table divide-y divide-gray-200 text-sm">
                            <colgroup><col style="width:4%"><col style="width:18%"><col style="width:8%"><col style="width:14%"><col style="width:16%"><col style="width:12%"><col style="width:14%"><col style="width:14%"></colgroup>
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3.5 text-left font-bold text-gray-700 uppercase">No</th>
                                    <th class="px-6 py-3.5 text-left font-bold text-gray-700 uppercase">Nama Santri</th>
                                    <th class="px-6 py-3.5 text-center font-bold text-gray-700 uppercase">Kelas</th>
                                    <th class="px-6 py-3.5 text-left font-bold text-gray-700 uppercase">NIS / NISN</th>
                                    <th class="px-6 py-3.5 text-left font-bold text-gray-700 uppercase">Sekolah Asal</th>
                                    <th class="px-6 py-3.5 text-left font-bold text-gray-700 uppercase">Orang Tua (Ayah)</th>
                                    <th class="px-6 py-3.5 text-center font-bold text-gray-700 uppercase">Status</th>
                                    <th class="px-6 py-3.5 text-center font-bold text-gray-700 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($items as $idx => $s)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-gray-500 font-medium">{{ $idx + 1 }}</td>
                                    <td class="px-6 py-4 font-bold text-gray-900">{{ $s->nama_lengkap }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-md bg-teal-100 text-teal-800">
                                            {{ $s->kelas_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600 font-mono">
                                        <div><span class="text-gray-400">NIS:</span> {{ $s->nis ?? '-' }}</div>
                                        <div><span class="text-gray-400">NISN:</span> {{ $s->nisn ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600">{{ $s->sekolah_asal }}</td>
                                    <td class="px-6 py-4 text-gray-700">
                                        <div class="font-semibold">{{ $s->orangTua->nama_ayah ?? '-' }}</div>
                                        <div class="text-xs text-teal-600"><i class="fab fa-whatsapp mr-1"></i>{{ $s->wali_account_status['no_wa_normalized'] ?? '-' }}</div>
                                        @if(!$s->wali_account_status['has_wa'])
                                            <a href="{{ route('admin.santri.edit', $s->id) }}" class="mt-1 inline-flex text-xs text-amber-700 font-bold hover:underline">⚠ Belum ada No. WA · Edit</a>
                                        @elseif($s->wali_account_status['has_account'])
                                            <span class="mt-1 inline-flex text-xs text-emerald-700 font-bold">✓ Akun Aktif</span>
                                        @else
                                            <button type="button" data-url="{{ route('admin.santri.wali.generate', $s->id) }}" class="mt-1 text-xs text-teal-700 font-bold hover:underline" onclick="generateWaliAccount(this)">Generate Akun</button>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($s->status_santri === 'mutasi')
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-orange-100 text-orange-800 border border-orange-200 block mb-1">
                                                <i class="fas fa-sign-out-alt mr-0.5"></i> MUTASI
                                            </span>
                                            @if($s->keterangan_mutasi)
                                                <span class="text-[10px] text-gray-500 block max-w-[130px] mx-auto truncate" title="{{ $s->keterangan_mutasi }}">{{ $s->keterangan_mutasi }}</span>
                                            @endif
                                        @else
                                            @switch($s->status)
                                                @case('menunggu_verifikasi')
                                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>@break
                                                @case('diverifikasi')
                                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">Diverifikasi</span>@break
                                                @case('diterima')
                                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700">Diterima</span>@break
                                                @case('ditolak')
                                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-700">Ditolak</span>@break
                                            @endswitch
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-1 flex-wrap">
                                            <a href="{{ route('admin.santri.show', $s->id) }}"
                                               style="background-color: #0d9488; color: #ffffff;"
                                               class="px-2.5 py-1.5 rounded-lg text-xs font-bold shadow hover:opacity-90 inline-flex items-center gap-1">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            @if($s->status === 'diterima' || $s->status_santri === 'mutasi')
                                            <button type="button"
                                                    onclick="openMutasiModal({{ $s->id }}, '{{ addslashes($s->nama_lengkap) }}', '{{ $s->status_santri ?? 'aktif' }}', '{{ addslashes($s->keterangan_mutasi ?? '') }}', '{{ $s->tanggal_mutasi ? $s->tanggal_mutasi->format('Y-m-d') : date('Y-m-d') }}')"
                                                    style="{{ $s->status_santri === 'mutasi' ? 'background-color: #ea580c !important; color: #ffffff !important;' : 'background-color: #fff7ed !important; color: #c2410c !important; border: 1px solid #fed7aa !important;' }}"
                                                    class="px-2.5 py-1.5 rounded-lg text-xs font-bold inline-flex items-center gap-1 cursor-pointer transition shadow-sm hover:opacity-90"
                                                    title="Kelola Status Mutasi / Keluar">
                                                <i class="fas {{ $s->status_santri === 'mutasi' ? 'fa-edit' : 'fa-exchange-alt' }}"></i>
                                                <span>{{ $s->status_santri === 'mutasi' ? 'Edit Mutasi' : 'Mutasi' }}</span>
                                            </button>
                                            @endif
                                            <a href="{{ route('admin.santri.cetakKartu', $s->id) }}" target="_blank"
                                               class="px-2 py-1.5 rounded-lg text-xs font-bold bg-green-50 text-green-700 hover:bg-green-100 border border-green-200 inline-flex items-center gap-1"
                                               title="Cetak Kartu Santri">
                                                <i class="fas fa-id-card"></i> Kartu
                                            </a>
                                            <a href="{{ route('admin.santri.edit', $s->id) }}" class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 inline-flex items-center gap-1"><i class="fas fa-pen"></i> Edit</a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @empty
                <div class="text-center py-16 bg-white rounded-2xl shadow border border-gray-200 text-gray-500">
                    <i class="fas fa-users-slash text-5xl text-gray-300 mb-3 block"></i>
                    <p class="font-bold text-base text-gray-800">Belum Ada Data Santri.</p>
                </div>
                @endforelse
            </div>
        @endif

    </div>

    <!-- MODAL IMPORT EXCEL SANTRI -->
    <div x-show="showImportModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
        <div @click.away="showImportModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all">
            <div class="bg-gradient-to-r from-emerald-700 to-teal-600 px-6 py-4 text-white flex items-center justify-between">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <i class="fas fa-file-excel"></i> Import Data Santri dari Excel
                </h3>
                <button @click="showImportModal = false" class="text-white hover:text-gray-200 text-xl font-bold bg-transparent border-0 cursor-pointer">&times;</button>
            </div>
            
            <form action="{{ route('admin.santri.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                @csrf
                
                <!-- Download Template Banner -->
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center justify-between gap-3">
                    <div>
                        <h4 class="text-xs font-bold text-emerald-900 uppercase">Petunjuk Format</h4>
                        <p class="text-xs text-emerald-700 mt-0.5">Gunakan template resmi untuk mencegah error kolom saat import.</p>
                    </div>
                    <a href="{{ route('admin.santri.downloadTemplate') }}"
                       style="background-color: #059669; color: #ffffff;"
                       class="px-3 py-2 rounded-lg text-xs font-bold shadow hover:opacity-90 whitespace-nowrap inline-flex items-center gap-1">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                </div>

                <!-- Input File -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Pilih File Excel (.xlsx / .csv)</label>
                    <input type="file" name="file_excel" accept=".xlsx,.xls,.csv" required
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 border border-gray-300 rounded-lg p-1">
                    <p class="text-xs text-gray-500 mt-1">Sistem otomatis mendeteksi NIK/NISN duplikat & membuat akun wali santri.</p>
                </div>

                <!-- Footer Buttons -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t">
                    <button type="button" @click="showImportModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit" style="background-color: #059669; color: #ffffff;" class="px-5 py-2 rounded-lg text-sm font-bold hover:opacity-90 shadow">
                        <i class="fas fa-upload mr-1"></i> Mulai Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="waliAccountModal" class="hidden fixed inset-0 z-[70] items-center justify-center bg-slate-950/60 p-4">
        <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="bg-emerald-700 px-6 py-4 text-white">
                <h3 class="font-bold flex items-center gap-2">
                    <i class="fab fa-whatsapp text-xl"></i> Akun Wali Berhasil Dibuat
                </h3>
                <p class="mt-1 text-xs text-emerald-100">Notifikasi petunjuk login via OTP langsung dikirim ke WhatsApp wali.</p>
            </div>
            <div class="space-y-3 p-6 text-sm">
                <p><span class="text-gray-500">Nama Wali:</span> <strong id="waliName"></strong></p>
                <p><span class="text-gray-500">No. WhatsApp / Username:</span> <strong id="waliUsername" class="font-mono text-emerald-700"></strong> <button type="button" class="ml-2 text-xs font-bold text-teal-700" onclick="copyWaliValue('waliUsername')">Salin</button></p>
                <p class="text-xs text-gray-600 bg-emerald-50 p-3 rounded-lg border border-emerald-100">
                    <i class="fas fa-info-circle text-emerald-600 mr-1"></i> Wali santri dapat langsung login melalui tautan aplikasi menggunakan <strong>Nomor WhatsApp</strong> dan kode <strong>OTP</strong>.
                </p>
                <p id="waliSendNotice" class="hidden rounded-lg bg-gray-50 p-3 text-xs text-gray-600"></p>
            </div>
            <div class="flex justify-end gap-2 border-t p-4">
                <button type="button" id="sendWaliCredentials" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800 transition flex items-center gap-1.5">
                    <i class="fab fa-whatsapp"></i> Kirim Ulang WA
                </button>
                <button type="button" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-200 transition" onclick="closeWaliModal()">Selesai</button>
            </div>
        </div>
    </div>
</div>

<script>
    const waliCsrfToken = '{{ csrf_token() }}';
    let waliCredential = null;

    async function generateWaliAccount(button) {
        button.disabled = true;
        const previous = button.textContent;
        button.textContent = 'Memproses...';
        try {
            const response = await fetch(button.dataset.url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': waliCsrfToken },
            });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Akun wali gagal dibuat.');

            if (data.already_exists) {
                button.outerHTML = '<span class="mt-1 inline-flex text-xs text-emerald-700 font-bold">✓ Akun Aktif</span>';
                return;
            }

            waliCredential = data;
            document.getElementById('waliName').textContent = data.nama_wali;
            document.getElementById('waliUsername').textContent = data.username;
            const notice = document.getElementById('waliSendNotice');
            if (data.whatsapp_sent === true) {
                notice.textContent = data.whatsapp_message || 'Petunjuk login telah dikirim otomatis ke WhatsApp wali.';
                notice.className = 'rounded-lg bg-emerald-50 p-3 text-xs font-semibold text-emerald-800';
            } else if (data.whatsapp_sent === false) {
                notice.textContent = 'Akun berhasil dibuat, namun pengiriman WhatsApp belum terkirim: ' + (data.whatsapp_message || 'Gateway offline/tidak merespons.');
                notice.className = 'rounded-lg bg-red-50 p-3 text-xs font-semibold text-red-800';
            } else {
                notice.classList.add('hidden');
            }
            document.getElementById('waliAccountModal').classList.remove('hidden');
            document.getElementById('waliAccountModal').classList.add('flex');
            button.outerHTML = '<span class="mt-1 inline-flex text-xs text-emerald-700 font-bold">✓ Akun Aktif</span>';
        } catch (error) {
            alert(error.message);
            button.disabled = false;
            button.textContent = previous;
        }
    }

    async function copyWaliValue(id) {
        await navigator.clipboard.writeText(document.getElementById(id).textContent);
    }

    function closeWaliModal() {
        const modal = document.getElementById('waliAccountModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        waliCredential = null;
    }

    document.getElementById('sendWaliCredentials').addEventListener('click', async function () {
        if (!waliCredential) return;
        this.disabled = true;
        try {
            const response = await fetch('{{ route('admin.wali.send_credentials') }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': waliCsrfToken },
                body: JSON.stringify({ user_id: waliCredential.user_id, santri_id: waliCredential.santri_id, plain_password: waliCredential.plain_password }),
            });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Pengiriman WhatsApp gagal.');
            const notice = document.getElementById('waliSendNotice');
            notice.textContent = data.message;
            notice.classList.remove('hidden');
        } catch (error) {
            alert(error.message);
        } finally {
            this.disabled = false;
        }
    });

    // Mutasi Modal Functions
    function openMutasiModal(id, nama, status, keterangan, tanggal) {
        document.getElementById('formMutasiSantri').action = '/admin/santri/' + id + '/mutasi';
        document.getElementById('mutasiSantriNama').textContent = nama;
        document.getElementById('mutasiStatusSelect').value = status || 'aktif';
        document.getElementById('mutasiKeteranganInput').value = keterangan || '';
        document.getElementById('mutasiTanggalInput').value = tanggal || '{{ date('Y-m-d') }}';
        
        toggleMutasiFields();
        
        const modal = document.getElementById('modalMutasiSantri');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeMutasiModal() {
        const modal = document.getElementById('modalMutasiSantri');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function toggleMutasiFields() {
        const status = document.getElementById('mutasiStatusSelect').value;
        const box = document.getElementById('mutasiDetailBox');
        const ket = document.getElementById('mutasiKeteranganInput');
        if (status === 'mutasi') {
            box.classList.remove('hidden');
            ket.setAttribute('required', 'required');
        } else {
            box.classList.add('hidden');
            ket.removeAttribute('required');
        }
    }
</script>

{{-- MODAL KELOLA STATUS MUTASI SANTRI --}}
<div id="modalMutasiSantri" class="fixed inset-0 z-50 bg-black/60 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl relative border border-gray-100 animate-in fade-in zoom-in duration-150">
        <button type="button" onclick="closeMutasiModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-lg w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100">
            <i class="fas fa-times"></i>
        </button>

        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-lg">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900">Kelola Status Santri</h3>
                <p class="text-xs text-gray-500 font-medium" id="mutasiSantriNama">-</p>
            </div>
        </div>

        <form id="formMutasiSantri" method="POST" action="">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Status Keaktifan Santri</label>
                    <select name="status_santri" id="mutasiStatusSelect" onchange="toggleMutasiFields()" class="w-full text-sm border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500 p-2.5 bg-gray-50 font-medium">
                        <option value="aktif">🟢 Aktif (Masih Belajar di Pondok)</option>
                        <option value="mutasi">🟠 Mutasi / Keluar / Pindah Pondok</option>
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1">Santri yang diatur menjadi 'Mutasi' tidak akan ditagih SPP bulanan berikutnya dan tidak muncul di daftar santri aktif utama.</p>
                </div>

                <div id="mutasiDetailBox" class="space-y-3 bg-orange-50/60 p-3.5 rounded-xl border border-orange-200">
                    <div>
                        <label class="block text-xs font-bold text-orange-950 mb-1">Tanggal Keluar / Mutasi <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_mutasi" id="mutasiTanggalInput" class="w-full text-sm border-orange-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 p-2 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-orange-950 mb-1">Keterangan / Alasan Keluar <span class="text-red-500">*</span></label>
                        <textarea name="keterangan_mutasi" id="mutasiKeteranganInput" rows="3" placeholder="Contoh: Pindah sekolah dikarenakan tidak betah / ikut orang tua ke luar kota" class="w-full text-sm border-orange-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 p-2 bg-white"></textarea>
                        <p class="text-[11px] text-orange-700 mt-1">Catatan ini akan tersimpan rapi sebagai riwayat tanpa menghapus data santri.</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t">
                    <button type="button" onclick="closeMutasiModal()" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow flex items-center gap-1.5">
                        <i class="fas fa-save"></i> Simpan Status
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
