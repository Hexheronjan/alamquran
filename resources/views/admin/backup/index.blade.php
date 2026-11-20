@extends('layouts.admin.app')

@section('title', 'Backup Sistem & Database')

@section('content')
<div class="bg-gray-100 min-h-screen">
    <div class="w-full py-6 px-4 lg:px-8 space-y-6">

        {{-- Flash Alerts --}}
        @if(session('success'))
            <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-2xl shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-start gap-3">
                    <i class="ti ti-circle-check text-2xl text-green-600 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <h4 class="text-sm font-bold text-green-800">Berhasil!</h4>
                        <p class="text-sm text-green-700 mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
                @if(session('download_file'))
                    <a href="{{ route('admin.backup.download', session('download_file')) }}"
                       class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3 py-2 rounded-xl shadow-sm transition whitespace-nowrap self-start sm:self-center"
                       style="background-color: #059669 !important; color: #ffffff !important;">
                        <i class="ti ti-download text-base"></i> Unduh File Sekarang
                    </a>
                @endif
            </div>

        @endif

        @if(session('error'))
            <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-r-2xl shadow-sm flex items-start gap-3">
                <i class="ti ti-alert-circle text-2xl text-red-600 flex-shrink-0 mt-0.5"></i>
                <div>
                    <h4 class="text-sm font-bold text-red-800">Perhatian!</h4>
                    <p class="text-sm text-red-700 mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{-- Header & Restore Action --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="ti ti-shield-check text-indigo-600"></i> Backup Sistem & Database
                </h1>
                <p class="text-gray-600 text-sm mt-1">
                    Cadangkan seluruh database, rekap Excel, dan script sistem untuk mengantisipasi kehilangan data atau error server.
                </p>
            </div>
            <div>
                <button type="button"
                        onclick="openModalRestore()"
                        class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold px-4 py-2.5 rounded-xl shadow-sm transition cursor-pointer"
                        style="background-color: #1e293b !important; color: #ffffff !important;">
                    <i class="ti ti-upload text-lg"></i>
                    Restore Database (.sql)
                </button>
            </div>
        </div>

        {{-- Statistic Overview Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-2xl flex-shrink-0">
                    <i class="ti ti-files"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">File Tersimpan</span>
                    <div class="text-2xl font-black text-gray-900 mt-0.5">{{ $totalFiles }} <span class="text-xs font-normal text-gray-400">file</span></div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 text-2xl flex-shrink-0">
                    <i class="ti ti-server"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Ruang Backup</span>
                    <div class="text-2xl font-black text-gray-900 mt-0.5">{{ $totalSizeHuman }}</div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600 text-2xl flex-shrink-0">
                    <i class="ti ti-clock-history"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Cadangan Terakhir</span>
                    <div class="text-sm font-bold text-gray-800 mt-1">{{ $lastBackup }}</div>
                </div>
            </div>
        </div>

        {{-- 4 Primary Action Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- 1. Database Backup --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-emerald-200 hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl mb-3">
                        <i class="ti ti-database"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 text-base">Backup Database (.sql)</h3>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                        Ekspor seluruh tabel & data MySQL (santri, SPP, UKS, tahfidz, akun wali) ke format SQL standar.
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.backup.database') }}" class="mt-4" onsubmit="showLoading(this, 'Membuat file SQL...')">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-sm transition cursor-pointer"
                            style="background-color: #059669 !important; color: #ffffff !important;">
                        <i class="ti ti-download"></i> Backup Database
                    </button>
                </form>
            </div>

            {{-- 2. Excel Backup --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-blue-200 hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-xl mb-3">
                        <i class="ti ti-file-spreadsheet"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 text-base">Backup Data Excel (.xlsx)</h3>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                        Ekspor seluruh data santri, pembayaran, tabungan, tahfidz, presensi & infaq dalam 1 file Excel multi-sheet.
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.backup.excel') }}" class="mt-4" onsubmit="showLoading(this, 'Mengompilasi Excel...')">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-sm transition cursor-pointer"
                            style="background-color: #2563eb !important; color: #ffffff !important;">
                        <i class="ti ti-download"></i> Backup ke Excel
                    </button>
                </form>
            </div>

            {{-- 3. Script Backup --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-amber-200 hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl mb-3">
                        <i class="ti ti-code"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 text-base">Backup Script (.zip)</h3>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                        Cadangkan seluruh kodingan sistem (controllers, models, views, routes, config) ke file ZIP ringan.
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.backup.script') }}" class="mt-4" onsubmit="showLoading(this, 'Mengompresi script...')">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-sm transition cursor-pointer"
                            style="background-color: #d97706 !important; color: #ffffff !important;">
                        <i class="ti ti-download"></i> Backup Script
                    </button>
                </form>
            </div>

            {{-- 4. Full Backup --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-purple-200 hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl mb-3">
                        <i class="ti ti-archive"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 text-base">Backup Lengkap (.zip)</h3>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                        Paket komplit: Database SQL + Rekap Excel + Script kodingan dalam satu file ZIP utuh siap simpan.
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.backup.full') }}" class="mt-4" onsubmit="showLoading(this, 'Membuat Full Backup...')">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-sm transition cursor-pointer"
                            style="background-color: #9333ea !important; color: #ffffff !important;">
                        <i class="ti ti-download"></i> Backup Lengkap
                    </button>
                </form>
            </div>

        </div>

        {{-- Table Riwayat Backup --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                    <i class="ti ti-history text-lg text-gray-500"></i> Riwayat File Backup di Server
                </h2>
                <span class="text-xs bg-gray-100 text-gray-700 px-3 py-1 rounded-full font-bold">
                    {{ count($backups) }} File
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-gray-600 font-semibold text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5 w-12 text-center">No</th>
                            <th class="px-5 py-3.5">Nama File Backup</th>
                            <th class="px-5 py-3.5 text-center">Jenis Cadangan</th>
                            <th class="px-5 py-3.5 text-center">Ukuran File</th>
                            <th class="px-5 py-3.5 text-center">Tanggal Dibuat</th>
                            <th class="px-5 py-3.5 text-center w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($backups as $idx => $b)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-4 text-center text-gray-400 text-xs">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-mono text-xs font-bold text-gray-900 flex items-center gap-2">
                                        <i class="ti {{ $b['icon'] }} text-base text-gray-500"></i>
                                        <span>{{ $b['filename'] }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold border {{ $b['badge_color'] }}">
                                        {{ $b['type_label'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap text-xs font-mono font-semibold text-gray-700">
                                    {{ $b['size_human'] }}
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap text-xs text-gray-500">
                                    {{ $b['date_human'] }}
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- Tombol Download --}}
                                        <a href="{{ route('admin.backup.download', ['filename' => $b['filename']]) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-lg transition"
                                           title="Download ke Komputer">
                                            <i class="ti ti-download text-sm"></i> Download
                                        </a>

                                        {{-- Tombol Hapus --}}
                                        <form method="POST" action="{{ route('admin.backup.destroy', ['filename' => $b['filename']]) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus file backup {{ $b['filename'] }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer"
                                                    title="Hapus File Backup">
                                                <i class="ti ti-trash text-base"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-gray-400">
                                    <i class="ti ti-folder-off text-5xl text-gray-300 block mb-2"></i>
                                    <p class="font-medium text-gray-600">Belum ada file backup di server</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Klik salah satu tombol backup di atas untuk mulai membuat cadangan data.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

{{-- MODAL RESTORE DATABASE --}}
<div id="modalRestore" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden transition-all">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-amber-50">
            <h3 class="text-base font-bold text-amber-900 flex items-center gap-2">
                <i class="ti ti-alert-triangle text-amber-600 text-xl"></i> Restore Database (.sql)
            </h3>
            <button type="button" onclick="closeModalRestore()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold cursor-pointer">
                &times;
            </button>
        </div>

        <form method="POST" action="{{ route('admin.backup.restore') }}" enctype="multipart/form-data" class="p-6 space-y-4" onsubmit="return confirmRestore(this)">
            @csrf

            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-800 leading-relaxed">
                <strong class="font-bold block mb-1">⚠️ PERINGATAN PENTING:</strong>
                Memulihkan database akan menimpa data yang ada di server saat ini dengan data dari file backup SQL yang Anda unggah. Pastikan Anda sudah membackup database saat ini terlebih dahulu!
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                    Pilih File Backup SQL <span class="text-red-500">*</span>
                </label>
                <input type="file"
                       name="sql_file"
                       required
                       accept=".sql"
                       class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-xl p-2 cursor-pointer">
                <span class="text-[11px] text-gray-400 mt-1 block">Format file wajib <strong>.sql</strong> (Maksimal 100 MB).</span>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button"
                        onclick="closeModalRestore()"
                        class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-100 text-xs font-semibold transition cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-md transition cursor-pointer flex items-center gap-1.5"
                        style="background-color: #dc2626 !important; color: #ffffff !important;">
                    <i class="ti ti-refresh"></i> Mulai Restore Database
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openModalRestore() {
        document.getElementById('modalRestore').classList.remove('hidden');
    }

    function closeModalRestore() {
        document.getElementById('modalRestore').classList.add('hidden');
    }

    function confirmRestore(form) {
        if (!confirm("APAKAH ANDA YAKIN?\n\nDatabase saat ini akan ditimpa dengan data dari file SQL yang Anda pilih. Tindakan ini tidak dapat dibatalkan!")) {
            return false;
        }
        showLoading(form, 'Sedang memulihkan database...');
        return true;
    }

    function showLoading(form, text) {
        const btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> ' + text;
        }
    }
</script>
@endpush

@endsection
