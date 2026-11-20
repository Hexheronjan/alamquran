@extends('layouts.admin.app')

@section('title', 'Kehadiran Wali & Perizinan Santri')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <div class="flex items-center gap-2 text-cyan-600 font-semibold text-sm mb-1">
                <i class="ti ti-qrcode text-lg"></i> Manajemen Kehadiran & Perizinan
            </div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="ti ti-door-enter text-cyan-600"></i> Kehadiran Wali & Perizinan Santri
            </h1>
            <p class="text-gray-500 text-sm mt-1 flex items-center gap-1.5">
                <i class="ti ti-calendar-event"></i>
                {{ isset($today) ? \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') : now()->translatedFormat('l, d F Y') }}
            </p>
        </div>
        <div>
            <a href="{{ route('admin.perizinan.scanner') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-sm transition shadow-sm flex items-center gap-2 cursor-pointer" style="background-color: #16a34a !important; color: #ffffff !important;">
                <i class="ti ti-scan text-lg"></i> Buka Scanner QR Code
            </a>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3">
            <i class="ti ti-circle-check text-2xl text-emerald-600"></i>
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center gap-3">
            <i class="ti ti-alert-circle text-2xl text-rose-600"></i>
            <span class="font-medium text-sm">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Stat Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        {{-- Stat Card 1 --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                <i class="ti ti-user-check"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Hadir Hari Ini</p>
                <h3 class="text-2xl font-extrabold text-emerald-600 mt-0.5">{{ $counts['hadir_hari_ini'] ?? 0 }}</h3>
                <p class="text-[11px] text-gray-400">Kunjungan Ahad</p>
            </div>
        </div>

        {{-- Stat Card: Pengajuan Izin Menunggu --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border {{ ($counts['total_pengajuan'] ?? 0) > 0 ? 'border-amber-300 bg-amber-50/40' : 'border-gray-100' }} flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ ($counts['total_pengajuan'] ?? 0) > 0 ? 'bg-amber-100 text-amber-700 animate-pulse' : 'bg-gray-50 text-gray-400' }} flex items-center justify-center text-2xl font-bold flex-shrink-0">
                <i class="ti ti-bell-ringing"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">Pengajuan Izin</p>
                <h3 class="text-2xl font-extrabold text-amber-600 mt-0.5">{{ $counts['total_pengajuan'] ?? 0 }}</h3>
                <p class="text-[11px] text-amber-700/80 font-medium">Perlu persetujuan</p>
            </div>
        </div>

        {{-- Stat Card 2 --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                <i class="ti ti-door-exit"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Sedang Keluar</p>
                <h3 class="text-2xl font-extrabold text-rose-600 mt-0.5">{{ $counts['sedang_keluar'] ?? 0 }}</h3>
                <p class="text-[11px] text-gray-400">Belum kembali</p>
            </div>
        </div>

        {{-- Stat Card 3 --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                <i class="ti ti-clipboard-list"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Total Riwayat Izin</p>
                <h3 class="text-2xl font-extrabold text-cyan-600 mt-0.5">{{ $counts['total_izin'] ?? 0 }}</h3>
                <p class="text-[11px] text-gray-400">Semua catatan izin</p>
            </div>
        </div>

    </div>

    {{-- Section: Pengajuan Izin Menunggu Persetujuan --}}
    <div class="bg-white rounded-2xl shadow-sm border {{ count($pengajuanIzin ?? []) > 0 ? 'border-amber-300 ring-2 ring-amber-200/50' : 'border-gray-100' }} overflow-hidden space-y-4">
        <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2" style="{{ count($pengajuanIzin ?? []) > 0 ? 'background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);' : '' }}">
            <div>
                <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                    <i class="ti ti-shield-check text-xl text-amber-600"></i> Pengajuan Izin Santri (Menunggu Persetujuan)
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Daftar permohonan izin yang diajukan oleh wali santri dari aplikasi.</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold {{ count($pengajuanIzin ?? []) > 0 ? 'bg-amber-500 text-white shadow-xs' : 'bg-gray-100 text-gray-600' }}">
                {{ isset($pengajuanIzin) ? $pengajuanIzin->count() : 0 }} Pengajuan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 font-bold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Santri</th>
                        <th class="px-6 py-3">Wali Penjemput</th>
                        <th class="px-6 py-3">Jenis Izin</th>
                        <th class="px-6 py-3">Tujuan / Keperluan</th>
                        <th class="px-6 py-3">Rencana Waktu</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-center">Aksi Keputusan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pengajuanIzin ?? [] as $i => $p)
                        <tr class="hover:bg-amber-50/40 transition">
                            <td class="px-6 py-4 font-bold text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ $p->santri->nama_lengkap ?? $p->nama_santri ?? '-' }}</div>
                                <div class="text-xs text-gray-500 font-medium">Kelas {{ $p->santri->kelas ?? $p->kelas ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800">{{ $p->nama_penjemput ?? $p->user->name ?? '-' }}</div>
                                <div class="text-xs text-emerald-600 font-semibold flex items-center gap-1">
                                    <i class="ti ti-brand-whatsapp"></i> {{ $p->no_wa_penjemput ?? $p->user->no_wa ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200 capitalize">
                                    {{ str_replace('_', ' ', $p->jenis_izin ?? '-') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-800 font-medium max-w-xs">
                                {{ $p->tujuan ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <div><span class="text-gray-400">Keluar:</span> <span class="font-medium text-gray-700">{{ $p->waktu_keluar ? \Carbon\Carbon::parse($p->waktu_keluar)->format('d M H:i') : '-' }}</span></div>
                                <div><span class="text-gray-400">Batas:</span> <span class="font-medium text-gray-700">{{ $p->batas_jam_kembali ? \Carbon\Carbon::parse($p->batas_jam_kembali)->format('d M H:i') : '-' }}</span></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                    Menunggu Persetujuan
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Tombol Setujui --}}
                                    <form action="{{ route('admin.perizinan.setujui', $p->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENYETUJUI izin santri ini untuk keluar?')">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-xs flex items-center gap-1 cursor-pointer"
                                                style="background-color: #16a34a !important; color: #ffffff !important;">
                                            <i class="ti ti-check text-sm"></i> Setujui
                                        </button>
                                    </form>

                                    {{-- Tombol Tolak --}}
                                    <form action="{{ route('admin.perizinan.tolak', $p->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENOLAK izin santri ini?')">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-xs flex items-center gap-1 cursor-pointer"
                                                style="background-color: #dc2626 !important; color: #ffffff !important;">
                                            <i class="ti ti-x text-sm"></i> Tolak
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-400">
                                <i class="ti ti-circle-check text-3xl mb-1 block text-emerald-400"></i>
                                Tidak ada pengajuan izin santri yang menunggu persetujuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Section 1: Presensi Kunjungan Ahad Pagi --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden space-y-4">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                    <i class="ti ti-calendar-week text-emerald-600"></i> Presensi Kunjungan Ahad Pagi Hari Ini
                </h3>
                <p class="text-xs text-gray-500 mt-1">Daftar kehadiran wali santri yang terdeteksi via QR scanner.</p>
            </div>
            <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                {{ isset($presensiHariIni) ? $presensiHariIni->count() : 0 }} Hadir
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 font-bold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Wali Santri</th>
                        <th class="px-6 py-3">Nama Santri</th>
                        <th class="px-6 py-3">Kelas</th>
                        <th class="px-6 py-3">Waktu Hadir</th>
                        <th class="px-6 py-3">Kegiatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($presensiHariIni ?? [] as $i => $presensi)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-bold text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800">{{ $presensi->user->name ?? $presensi->nama_wali ?? '-' }}</div>
                                <div class="text-xs text-gray-400">{{ $presensi->user->no_wa ?? $presensi->no_hp_wali ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-800">{{ $presensi->santri->nama_lengkap ?? $presensi->nama_santri ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-cyan-50 text-cyan-700 border border-cyan-200">
                                    Kelas {{ $presensi->santri->kelas ?? $presensi->kelas ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono font-medium text-gray-700">
                                {{ isset($presensi->waktu_hadir) ? \Carbon\Carbon::parse($presensi->waktu_hadir)->format('H:i') : '-' }} WIB
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $presensi->kegiatan ?? 'Kunjungan Ahad Pagi' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <i class="ti ti-mood-empty text-4xl mb-2 block text-gray-300"></i>
                                Belum ada data presensi kunjungan hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Section 2: Santri Sedang Keluar --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden space-y-4">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                    <i class="ti ti-door-exit text-rose-600"></i> Santri Sedang Keluar Kompleks Pondok
                </h3>
                <p class="text-xs text-gray-500 mt-1">Daftar santri yang saat ini sedang berada di luar pondok.</p>
            </div>
            <span class="px-3 py-1 rounded-full bg-rose-100 text-rose-800 text-xs font-bold">
                {{ isset($sedangKeluar) ? $sedangKeluar->count() : 0 }} Santri
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 font-bold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Santri</th>
                        <th class="px-6 py-3">Wali Penjemput</th>
                        <th class="px-6 py-3">Jenis Izin</th>
                        <th class="px-6 py-3">Waktu Keluar</th>
                        <th class="px-6 py-3">Batas Kembali</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sedangKeluar ?? [] as $i => $izin)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-bold text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800">{{ $izin->santri->nama_lengkap ?? $izin->nama_santri ?? '-' }}</div>
                                <div class="text-xs text-gray-400">Kelas {{ $izin->santri->kelas ?? $izin->kelas ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-800 font-medium">
                                <div>{{ $izin->nama_penjemput ?? $izin->user->name ?? '-' }}</div>
                                <div class="text-xs text-gray-400">{{ $izin->no_wa_penjemput ?? $izin->user->no_wa ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 capitalize">
                                    {{ str_replace('_', ' ', $izin->jenis_izin ?? '-') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs">
                                {{ isset($izin->waktu_keluar) ? \Carbon\Carbon::parse($izin->waktu_keluar)->format('d M H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs">
                                @if(isset($izin->batas_jam_kembali))
                                    @php
                                        $batas = \Carbon\Carbon::parse($izin->batas_jam_kembali);
                                        $isOverdue = $batas->isPast();
                                    @endphp
                                    <span class="{{ $isOverdue ? 'text-rose-600 font-bold' : 'text-gray-700' }}">
                                        {{ $batas->format('d M H:i') }}
                                        @if($isOverdue)
                                            <span class="ml-1 text-[10px] px-1.5 py-0.5 bg-rose-100 text-rose-800 rounded font-bold">Terlewat</span>
                                        @endif
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-rose-600 text-white shadow-sm">
                                    KELUAR
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('admin.perizinan.kembali', $izin->id) }}" method="POST" onsubmit="return confirm('Konfirmasi: Santri sudah kembali ke pesantren?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-sm flex items-center gap-1.5 mx-auto cursor-pointer" style="background-color: #16a34a !important; color: #ffffff !important;">
                                        <i class="ti ti-door-enter"></i> Sudah Kembali
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                <i class="ti ti-circle-check text-4xl mb-2 block text-emerald-400"></i>
                                Tidak ada santri yang sedang keluar. Semua santri ada di dalam pondok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Section 3: Riwayat Perizinan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden space-y-4">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                    <i class="ti ti-history text-cyan-600"></i> Riwayat Lengkap Perizinan Santri
                </h3>
                <p class="text-xs text-gray-500 mt-1">Catatan histori izin santri terdahulu.</p>
            </div>
            <span class="px-3 py-1 rounded-full bg-cyan-100 text-cyan-800 text-xs font-bold">
                {{ isset($riwayatIzin) ? $riwayatIzin->count() : 0 }} Record
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 font-bold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Santri</th>
                        <th class="px-6 py-3">Wali</th>
                        <th class="px-6 py-3">Jenis Izin</th>
                        <th class="px-6 py-3">Waktu Keluar</th>
                        <th class="px-6 py-3">Waktu Kembali</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($riwayatIzin ?? [] as $i => $riwayat)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-bold text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ $riwayat->santri->nama_lengkap ?? $riwayat->nama_santri ?? '-' }}</div>
                                <div class="text-xs text-gray-500 font-medium">Kelas {{ $riwayat->santri->kelas ?? $riwayat->kelas ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-800 font-medium">
                                <div>{{ $riwayat->nama_penjemput ?? $riwayat->user->name ?? '-' }}</div>
                                @if($riwayat->no_wa_penjemput || ($riwayat->user && $riwayat->user->no_wa))
                                    <div class="text-xs text-gray-400">{{ $riwayat->no_wa_penjemput ?? $riwayat->user->no_wa }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-700 capitalize">
                                    {{ str_replace('_', ' ', $riwayat->jenis_izin ?? '-') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs">
                                {{ isset($riwayat->waktu_keluar) ? \Carbon\Carbon::parse($riwayat->waktu_keluar)->format('d M H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs">
                                {{ isset($riwayat->waktu_kembali) ? \Carbon\Carbon::parse($riwayat->waktu_kembali)->format('d M H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if(($riwayat->status ?? '') === 'kembali')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">KEMBALI</span>
                                @elseif(($riwayat->status ?? '') === 'terlambat')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">TERLAMBAT</span>
                                @elseif(($riwayat->status ?? '') === 'pengajuan')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">PENGAJUAN</span>
                                @elseif(($riwayat->status ?? '') === 'ditolak')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">DITOLAK</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">KELUAR</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-600">
                                <div>{{ $riwayat->tujuan ?? $riwayat->catatan ?? '-' }}</div>
                                @if(($riwayat->status ?? '') === 'pengajuan')
                                    <div class="mt-1 flex items-center gap-1.5">
                                        <form action="{{ route('admin.perizinan.setujui', $riwayat->id) }}" method="POST" onsubmit="return confirm('Setujui izin santri ini?')">
                                            @csrf
                                            <button type="submit" class="text-[11px] font-bold text-green-700 hover:underline cursor-pointer">
                                                [Setujui]
                                            </button>
                                        </form>
                                        <span class="text-gray-300">|</span>
                                        <form action="{{ route('admin.perizinan.tolak', $riwayat->id) }}" method="POST" onsubmit="return confirm('Tolak izin santri ini?')">
                                            @csrf
                                            <button type="submit" class="text-[11px] font-bold text-red-600 hover:underline cursor-pointer">
                                                [Tolak]
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                <i class="ti ti-clipboard-off text-4xl mb-2 block text-gray-300"></i>
                                Belum ada riwayat perizinan santri.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
