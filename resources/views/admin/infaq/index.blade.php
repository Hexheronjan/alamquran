@extends('layouts.admin.app')

@section('title', 'Infaq & Sedekah Online')

@section('content')
<div class="p-6 space-y-8">

    {{-- ================================================================== --}}
    {{-- PAGE HEADER --}}
    {{-- ================================================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="ti ti-heart-handshake text-green-600 text-3xl"></i>
                Infaq & Sedekah Online
            </h1>
            <p class="text-sm text-gray-500 mt-1">Kelola program donasi dan konfirmasi transaksi</p>
        </div>
        <div class="flex items-center gap-3">
            <button
                onclick="document.getElementById('modalTambahProgram').classList.remove('hidden')"
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-4 py-2 rounded-lg shadow transition cursor-pointer"
                style="background-color: #16a34a !important; color: #ffffff !important;">
                <i class="ti ti-plus"></i>
                Tambah Program
            </button>
            <button
                onclick="document.getElementById('modalCatatDonasi').classList.remove('hidden')"
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-4 py-2 rounded-lg shadow transition cursor-pointer"
                style="background-color: #16a34a !important; color: #ffffff !important;">
                <i class="ti ti-clipboard-plus"></i>
                Tambah Donasi Manual
            </button>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- FLASH MESSAGES --}}
    {{-- ================================================================== --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3">
            <i class="ti ti-circle-check text-green-500 text-lg flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg px-4 py-3">
            <i class="ti ti-alert-circle text-red-500 text-lg flex-shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- ================================================================== --}}
    {{-- STAT CARDS --}}
    {{-- ================================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

        {{-- Total Terkumpul --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-green-100 flex items-center justify-center">
                <i class="ti ti-cash text-green-600 text-3xl"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Terkumpul</p>
                <p class="text-2xl font-bold text-gray-800 mt-0.5">
                    Rp {{ number_format($totalTerkumpul ?? 0, 0, ',', '.') }}
                </p>
                <p class="text-xs text-green-600 mt-1 flex items-center gap-1">
                    <i class="ti ti-trending-up"></i> Dari seluruh program aktif
                </p>
            </div>
        </div>

        {{-- Total Program --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center">
                <i class="ti ti-clipboard-list text-blue-600 text-3xl"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Program</p>
                <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ $programs->count() ?? 0 }}</p>
                <p class="text-xs text-blue-600 mt-1 flex items-center gap-1">
                    <i class="ti ti-checklist"></i> Program donasi terdaftar
                </p>
            </div>
        </div>

        {{-- Menunggu Konfirmasi --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-orange-100 flex items-center justify-center relative">
                <i class="ti ti-clock-hour-4 text-orange-500 text-3xl"></i>
                @if(($totalMenunggu ?? 0) > 0)
                    <span class="absolute -top-1.5 -right-1.5 bg-orange-500 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center">
                        {{ $totalMenunggu > 99 ? '99+' : $totalMenunggu }}
                    </span>
                @endif
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Menunggu Konfirmasi</p>
                <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ $totalMenunggu ?? 0 }}</p>
                <p class="text-xs text-orange-500 mt-1 flex items-center gap-1">
                    <i class="ti ti-alert-triangle"></i> Transaksi perlu ditindaklanjuti
                </p>
            </div>
        </div>

    </div>

    {{-- ================================================================== --}}
    {{-- DAFTAR PROGRAM DONASI --}}
    {{-- ================================================================== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                <i class="ti ti-heart text-emerald-500"></i>
                Daftar Program Donasi
            </h2>
            <span class="text-xs text-gray-400">{{ $programs->count() }} program</span>
        </div>

        <div class="p-6">
            @if($programs->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                        <i class="ti ti-heart-off text-gray-300 text-4xl"></i>
                    </div>
                    <p class="text-gray-500 font-medium">Belum ada program donasi</p>
                    <p class="text-gray-400 text-sm mt-1">Klik "Tambah Program" untuk membuat program baru.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($programs as $program)
                        @php
                            $progress = $program->target_dana > 0
                                ? min(100, round(($program->dana_terkumpul / $program->target_dana) * 100))
                                : 0;
                            $progressColor = $progress >= 100 ? 'bg-emerald-500' : ($progress >= 50 ? 'bg-blue-500' : 'bg-orange-400');
                        @endphp
                        <div class="border border-gray-200 rounded-xl overflow-hidden flex flex-col hover:shadow-md transition group">
                            {{-- Banner Image --}}
                            <div class="relative h-40 bg-gradient-to-br from-emerald-400 to-teal-600 overflow-hidden">
                                @if($program->foto_banner)
                                    <img
                                        src="{{ Storage::url($program->foto_banner) }}"
                                        alt="{{ $program->nama_program }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                        onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-emerald-600 text-white\'><i class=\'ti ti-heart-handshake text-5xl\'></i></div>';">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="ti ti-heart-handshake text-white/50 text-6xl"></i>
                                    </div>
                                @endif
                                {{-- Active Badge --}}
                                <span class="absolute top-2 right-2 text-xs font-semibold px-2.5 py-1 rounded-full shadow
                                    {{ $program->is_active ? 'bg-green-500 text-white' : 'bg-gray-500 text-white' }}">
                                    {{ $program->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>

                            {{-- Content --}}
                            <div class="p-4 flex-1 flex flex-col gap-3">
                                <div>
                                    <h3 class="font-semibold text-gray-800 text-sm leading-snug line-clamp-2">
                                        {{ $program->nama_program }}
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                                        {{ $program->deskripsi ?? '-' }}
                                    </p>
                                </div>

                                {{-- Progress --}}
                                <div>
                                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                                        <span>Terkumpul</span>
                                        <span class="font-medium text-gray-700">{{ $progress }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                        <div class="{{ $progressColor }} h-2.5 rounded-full transition-all duration-500"
                                             style="width: {{ $progress }}%"></div>
                                    </div>
                                    <div class="flex justify-between text-xs mt-1.5">
                                        <span class="text-emerald-600 font-semibold">
                                            Rp {{ number_format($program->dana_terkumpul ?? 0, 0, ',', '.') }}
                                        </span>
                                        <span class="text-gray-400">
                                            / Rp {{ number_format($program->target_dana ?? 0, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center gap-2 mt-auto pt-2 border-t border-gray-100">
                                    {{-- Toggle Active --}}
                                    <form action="{{ route('admin.infaq.toggleProgram', $program->id) }}" method="POST" class="flex-1">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="w-full inline-flex items-center justify-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border transition
                                            {{ $program->is_active
                                                ? 'border-gray-300 text-gray-600 hover:bg-gray-50'
                                                : 'border-green-300 text-green-700 hover:bg-green-50' }}">
                                            <i class="ti {{ $program->is_active ? 'ti-toggle-right' : 'ti-toggle-left' }}"></i>
                                            {{ $program->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    {{-- Edit --}}
                                    <button type="button"
                                            onclick="openEditProgramModal({{ json_encode([
                                                'id' => $program->id,
                                                'nama_program' => $program->nama_program,
                                                'deskripsi' => $program->deskripsi,
                                                'target_dana' => (float) $program->target_dana,
                                                'foto_banner' => $program->foto_banner ? Storage::url($program->foto_banner) : null,
                                            ]) }})"
                                            class="inline-flex items-center justify-center gap-1 text-xs font-semibold text-blue-600 border border-blue-200 hover:bg-blue-50 px-2.5 py-1.5 rounded-lg transition"
                                            title="Edit Program Infaq">
                                        <i class="ti ti-edit"></i> Edit
                                    </button>

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.infaq.destroyProgram', $program->id) }}" method="POST"
                                          onsubmit="return confirm('Hapus program \"{{ addslashes($program->nama_program) }}\"? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center justify-center gap-1 text-xs font-medium text-red-600 border border-red-200 hover:bg-red-50 px-2.5 py-1.5 rounded-lg transition"
                                            title="Hapus Program">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- RIWAYAT TRANSAKSI DONASI --}}
    {{-- ================================================================== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                <i class="ti ti-receipt text-blue-500"></i>
                Riwayat Transaksi Donasi
            </h2>
            @if(($totalMenunggu ?? 0) > 0)
                <span class="inline-flex items-center gap-1.5 bg-orange-100 text-orange-700 text-xs font-semibold px-3 py-1.5 rounded-full">
                    <i class="ti ti-clock-exclamation"></i>
                    {{ $totalMenunggu }} menunggu konfirmasi
                </span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                        <th class="px-5 py-3 text-left font-semibold w-10">No</th>
                        <th class="px-5 py-3 text-left font-semibold">Nama Donatur</th>
                        <th class="px-5 py-3 text-left font-semibold">Program</th>
                        <th class="px-5 py-3 text-right font-semibold">Nominal</th>
                        <th class="px-5 py-3 text-center font-semibold">Metode</th>
                        <th class="px-5 py-3 text-center font-semibold">Status</th>
                        <th class="px-5 py-3 text-left font-semibold">Tanggal</th>
                        <th class="px-5 py-3 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transaksiTerbaru as $index => $transaksi)
                        <tr class="hover:bg-gray-50 transition">
                            {{-- No --}}
                            <td class="px-5 py-3.5 text-gray-500 text-xs">
                                {{ $transaksiTerbaru->firstItem() + $index }}
                            </td>

                            {{-- Nama Donatur --}}
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                        <i class="ti ti-user text-emerald-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800 leading-tight">{{ $transaksi->nama_donatur }}</p>
                                        @if($transaksi->no_wa_donatur)
                                            <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1">
                                                <i class="ti ti-brand-whatsapp text-green-500"></i>
                                                {{ $transaksi->no_wa_donatur }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Program --}}
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-gray-700 text-xs max-w-[160px] line-clamp-2">
                                    {{ $transaksi->program->nama_program ?? '-' }}
                                </p>
                            </td>

                            {{-- Nominal --}}
                            <td class="px-5 py-3.5 text-right">
                                <span class="font-semibold text-gray-800">
                                    Rp {{ number_format($transaksi->nominal ?? 0, 0, ',', '.') }}
                                </span>
                            </td>

                            {{-- Metode --}}
                            <td class="px-5 py-3.5 text-center">
                                @if($transaksi->metode_pembayaran === 'potong_saldo')
                                    <span class="inline-flex items-center gap-1 bg-purple-100 text-purple-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                        <i class="ti ti-wallet"></i> Potong Saldo
                                    </span>
                                @elseif($transaksi->metode_pembayaran === 'transfer')
                                    <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                        <i class="ti ti-building-bank"></i> Transfer
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-3.5 text-center">
                                @if($transaksi->status === 'menunggu_konfirmasi')
                                    <span class="inline-flex items-center gap-1 bg-orange-100 text-orange-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                        <i class="ti ti-clock"></i> Menunggu
                                    </span>
                                @elseif($transaksi->status === 'berhasil')
                                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                        <i class="ti ti-circle-check"></i> Berhasil
                                    </span>
                                @elseif($transaksi->status === 'ditolak')
                                    <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                        <i class="ti ti-circle-x"></i> Ditolak
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">{{ $transaksi->status ?? '-' }}</span>
                                @endif
                            </td>

                            {{-- Tanggal --}}
                            <td class="px-5 py-3.5 text-gray-500 text-xs whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    <i class="ti ti-calendar text-gray-400"></i>
                                    {{ \Carbon\Carbon::parse($transaksi->created_at)->isoFormat('D MMM YYYY') }}
                                </div>
                                <div class="flex items-center gap-1 mt-0.5 text-gray-400">
                                    <i class="ti ti-clock text-gray-300"></i>
                                    {{ \Carbon\Carbon::parse($transaksi->created_at)->format('H:i') }}
                                </div>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-3.5 text-center">
                                @if($transaksi->status === 'menunggu_konfirmasi')
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- Lihat Bukti Transfer --}}
                                        @if($transaksi->bukti_transfer)
                                            <a href="{{ Storage::url($transaksi->bukti_transfer) }}"
                                               target="_blank"
                                               title="Lihat Bukti Transfer"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg shadow-sm transition text-white cursor-pointer"
                                               style="background-color: #2563eb !important; color: #ffffff !important;">
                                                <i class="ti ti-eye text-base font-bold" style="color: #ffffff !important;"></i>
                                            </a>
                                        @endif

                                        {{-- Konfirmasi (Centang putih background hijau) --}}
                                        <form action="{{ route('admin.infaq.konfirmasiTransaksi', $transaksi->id) }}" method="POST"
                                              onsubmit="return confirm('Konfirmasi donasi dari {{ addslashes($transaksi->nama_donatur) }}?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                title="Konfirmasi Donasi"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg shadow-sm transition cursor-pointer"
                                                style="background-color: #16a34a !important; color: #ffffff !important;">
                                                <i class="ti ti-check text-lg font-black" style="color: #ffffff !important;"></i>
                                            </button>
                                        </form>

                                        {{-- Tolak (Silang putih background merah) --}}
                                        <form action="{{ route('admin.infaq.tolakTransaksi', $transaksi->id) }}" method="POST"
                                              onsubmit="return confirm('Tolak donasi dari {{ addslashes($transaksi->nama_donatur) }}?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                title="Tolak Donasi"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg shadow-sm transition cursor-pointer"
                                                style="background-color: #dc2626 !important; color: #ffffff !important;">
                                                <i class="ti ti-x text-lg font-black" style="color: #ffffff !important;"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    {{-- Bukti transfer link if available --}}
                                    @if($transaksi->bukti_transfer)
                                        <a href="{{ Storage::url($transaksi->bukti_transfer) }}"
                                           target="_blank"
                                           title="Lihat Bukti Transfer"
                                           class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-xs font-semibold transition">
                                            <i class="ti ti-eye"></i>
                                            <span>Bukti</span>
                                        </a>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center">
                                        <i class="ti ti-receipt-off text-gray-300 text-3xl"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium">Belum ada transaksi donasi</p>
                                    <p class="text-gray-400 text-sm">Transaksi akan muncul di sini setelah donatur melakukan donasi.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($transaksiTerbaru->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $transaksiTerbaru->links() }}
            </div>
        @endif
    </div>

</div>

{{-- ================================================================== --}}
{{-- MODAL: TAMBAH PROGRAM INFAQ --}}
{{-- ================================================================== --}}
<div id="modalTambahProgram"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
     x-data
     @keydown.escape.window="document.getElementById('modalTambahProgram').classList.add('hidden')">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
         onclick="document.getElementById('modalTambahProgram').classList.add('hidden')"></div>

    {{-- Panel --}}
    <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <i class="ti ti-heart-plus text-emerald-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 text-sm">Tambah Program Infaq</h3>
                    <p class="text-xs text-gray-400">Buat program donasi baru</p>
                </div>
            </div>
            <button onclick="document.getElementById('modalTambahProgram').classList.add('hidden')"
                    class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                <i class="ti ti-x"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="overflow-y-auto flex-1">
            <form action="{{ route('admin.infaq.storeProgram') }}" method="POST" enctype="multipart/form-data"
                  id="formTambahProgram">
                @csrf

                <div class="px-6 py-5 space-y-4">

                    {{-- Nama Program --}}
                    <div>
                        <label for="nama_program" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Nama Program <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama_program" name="nama_program"
                               value="{{ old('nama_program') }}"
                               placeholder="Contoh: Beasiswa Santri Yatim 2026"
                               required
                               class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition @error('nama_program') border-red-400 @enderror">
                        @error('nama_program')
                            <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <i class="ti ti-alert-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label for="deskripsi" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Deskripsi Program
                        </label>
                        <textarea id="deskripsi" name="deskripsi" rows="4"
                                  placeholder="Tuliskan deskripsi dan tujuan program ini..."
                                  class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition resize-none @error('deskripsi') border-red-400 @enderror">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <i class="ti ti-alert-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Target Dana --}}
                    <div>
                        <label for="target_dana" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Target Dana (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-medium text-gray-500">Rp</span>
                            <input type="number" id="target_dana" name="target_dana"
                                   value="{{ old('target_dana') }}"
                                   min="1"
                                   placeholder="5000000"
                                   required
                                   class="w-full text-sm border border-gray-300 rounded-lg pl-10 pr-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition @error('target_dana') border-red-400 @enderror">
                        </div>
                        @error('target_dana')
                            <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <i class="ti ti-alert-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Foto Banner --}}
                    <div>
                        <label for="foto_banner" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Foto Banner Program
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-5 text-center hover:border-emerald-400 transition cursor-pointer relative"
                             id="bannerDropzone"
                             onclick="document.getElementById('foto_banner').click()">
                            <div id="bannerPreviewWrap" class="hidden mb-3">
                                <img id="bannerPreview" src="#" alt="Preview" class="mx-auto max-h-32 rounded-lg object-cover shadow">
                            </div>
                            <div id="bannerPlaceholder">
                                <i class="ti ti-photo-up text-gray-300 text-4xl"></i>
                                <p class="text-gray-400 text-xs mt-2">Klik untuk unggah gambar</p>
                                <p class="text-gray-300 text-xs">PNG, JPG, JPEG maks. 2MB</p>
                            </div>
                            <p id="bannerFileName" class="text-xs text-emerald-600 font-medium mt-2 hidden"></p>
                        </div>
                        <input type="file" id="foto_banner" name="foto_banner"
                               accept="image/png,image/jpeg,image/jpg"
                               class="hidden"
                               onchange="previewBanner(this)">
                        @error('foto_banner')
                            <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <i class="ti ti-alert-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 flex-shrink-0">
                    <button type="button"
                            onclick="document.getElementById('modalTambahProgram').classList.add('hidden')"
                            class="text-sm font-bold text-white px-4 py-2 rounded-lg transition"
                            style="background-color: #374151 !important; color: #ffffff !important;">
                        Batal
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-5 py-2 rounded-lg shadow transition cursor-pointer"
                            style="background-color: #16a34a !important; color: #ffffff !important;">
                        <i class="ti ti-device-floppy"></i>
                        Simpan Program
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ================================================================== --}}
{{-- MODAL: EDIT PROGRAM INFAQ --}}
{{-- ================================================================== --}}
<div id="modalEditProgram"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
         onclick="document.getElementById('modalEditProgram').classList.add('hidden')"></div>

    {{-- Panel --}}
    <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-base">
                    <i class="ti ti-edit text-blue-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 text-sm">Edit Program Infaq</h3>
                    <p class="text-xs text-gray-400">Perbarui rincian program donasi</p>
                </div>
            </div>
            <button onclick="document.getElementById('modalEditProgram').classList.add('hidden')"
                    class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                <i class="ti ti-x"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="overflow-y-auto flex-1">
            <form id="formEditProgram" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="px-6 py-5 space-y-4">

                    {{-- Nama Program --}}
                    <div>
                        <label for="edit_nama_program" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Nama Program <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="edit_nama_program" name="nama_program"
                               placeholder="Contoh: Beasiswa Santri Yatim 2026"
                               required
                               class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label for="edit_deskripsi" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Deskripsi Program
                        </label>
                        <textarea id="edit_deskripsi" name="deskripsi" rows="4"
                                  placeholder="Tuliskan deskripsi dan tujuan program ini..."
                                  class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none"></textarea>
                    </div>

                    {{-- Target Dana --}}
                    <div>
                        <label for="edit_target_dana" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Target Dana (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-medium text-gray-500">Rp</span>
                            <input type="number" id="edit_target_dana" name="target_dana"
                                   min="1"
                                   placeholder="5000000"
                                   required
                                   class="w-full text-sm border border-gray-300 rounded-lg pl-10 pr-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                    </div>

                    {{-- Foto Banner --}}
                    <div>
                        <label for="edit_foto_banner" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Ganti Foto Banner Program <span class="text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-blue-400 transition cursor-pointer relative"
                             onclick="document.getElementById('edit_foto_banner').click()">
                            <div id="editBannerPreviewWrap" class="mb-2">
                                <img id="editBannerPreview" src="#" alt="Preview" class="mx-auto max-h-32 rounded-lg object-cover shadow hidden">
                            </div>
                            <div id="editBannerPlaceholder">
                                <i class="ti ti-photo-up text-gray-300 text-3xl"></i>
                                <p class="text-gray-400 text-xs mt-1">Klik untuk memilih gambar baru</p>
                                <p class="text-gray-300 text-[11px]">PNG, JPG, JPEG maks. 2MB</p>
                            </div>
                            <p id="editBannerFileName" class="text-xs text-blue-600 font-medium mt-1.5 hidden"></p>
                        </div>
                        <input type="file" id="edit_foto_banner" name="foto_banner"
                               accept="image/png,image/jpeg,image/jpg"
                               class="hidden"
                               onchange="previewEditBanner(this)">
                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 flex-shrink-0">
                    <button type="button"
                            onclick="document.getElementById('modalEditProgram').classList.add('hidden')"
                            class="text-sm font-bold text-white px-4 py-2 rounded-lg transition"
                            style="background-color: #374151 !important; color: #ffffff !important;">
                        Batal
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 text-white text-sm font-bold px-5 py-2 rounded-lg shadow transition cursor-pointer"
                            style="background-color: #2563eb !important; color: #ffffff !important;">
                        <i class="ti ti-device-floppy"></i>
                        Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ================================================================== --}}
{{-- MODAL: CATAT DONASI MANUAL --}}
{{-- ================================================================== --}}
<div id="modalCatatDonasi"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
         onclick="document.getElementById('modalCatatDonasi').classList.add('hidden')"></div>

    {{-- Panel --}}
    <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="ti ti-clipboard-plus text-blue-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 text-sm">Catat Donasi Manual</h3>
                    <p class="text-xs text-gray-400">Input transaksi donasi secara manual</p>
                </div>
            </div>
            <button onclick="document.getElementById('modalCatatDonasi').classList.add('hidden')"
                    class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                <i class="ti ti-x"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="overflow-y-auto flex-1">
            <form action="{{ route('admin.infaq.storeTransaksi') }}" method="POST" enctype="multipart/form-data"
                  id="formCatatDonasi">
                @csrf

                <div class="px-6 py-5 space-y-4">

                    {{-- Pilih Program --}}
                    <div>
                        <label for="infaq_program_id" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Program Donasi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select id="infaq_program_id" name="infaq_program_id" required
                                    class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 pr-9 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition appearance-none bg-white @error('infaq_program_id') border-red-400 @enderror">
                                <option value="" disabled selected>-- Pilih Program --</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ old('infaq_program_id') == $program->id ? 'selected' : '' }}>
                                        {{ $program->nama_program }}
                                        @if(!$program->is_active) (Nonaktif) @endif
                                    </option>
                                @endforeach
                            </select>
                            <i class="ti ti-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                        @error('infaq_program_id')
                            <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <i class="ti ti-alert-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Nama Donatur --}}
                    <div>
                        <label for="nama_donatur" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Nama Donatur <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama_donatur" name="nama_donatur"
                               value="{{ old('nama_donatur') }}"
                               placeholder="Masukkan nama donatur..."
                               required
                               class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('nama_donatur') border-red-400 @enderror">
                        @error('nama_donatur')
                            <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <i class="ti ti-alert-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- No WhatsApp --}}
                    <div>
                        <label for="no_wa_donatur" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            No. WhatsApp Donatur
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="ti ti-brand-whatsapp text-green-500"></i>
                            </span>
                            <input type="tel" id="no_wa_donatur" name="no_wa_donatur"
                                   value="{{ old('no_wa_donatur') }}"
                                   placeholder="08xxxxxxxxxx"
                                   class="w-full text-sm border border-gray-300 rounded-lg pl-9 pr-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('no_wa_donatur') border-red-400 @enderror">
                        </div>
                        @error('no_wa_donatur')
                            <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <i class="ti ti-alert-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Nominal --}}
                    <div>
                        <label for="nominal" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Nominal Donasi (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-medium text-gray-500">Rp</span>
                            <input type="number" id="nominal" name="nominal"
                                   value="{{ old('nominal') }}"
                                   min="1"
                                   placeholder="50000"
                                   required
                                   class="w-full text-sm border border-gray-300 rounded-lg pl-10 pr-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error('nominal') border-red-400 @enderror">
                        </div>
                        @error('nominal')
                            <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <i class="ti ti-alert-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Metode Pembayaran --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-2">
                            Metode Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="metode_pembayaran" value="potong_saldo"
                                       class="peer sr-only"
                                       {{ old('metode_pembayaran') === 'potong_saldo' ? 'checked' : '' }}
                                       onchange="toggleBuktiTransfer(this.value)"
                                       required>
                                <div class="border-2 border-gray-200 peer-checked:border-purple-500 peer-checked:bg-purple-50 rounded-xl p-3 text-center transition">
                                    <i class="ti ti-wallet text-gray-400 peer-checked:text-purple-600 text-2xl block mb-1"></i>
                                    <span class="text-xs font-medium text-gray-600 peer-checked:text-purple-700">Potong Saldo</span>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="metode_pembayaran" value="transfer"
                                       class="peer sr-only"
                                       {{ old('metode_pembayaran') === 'transfer' ? 'checked' : '' }}
                                       onchange="toggleBuktiTransfer(this.value)">
                                <div class="border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 rounded-xl p-3 text-center transition">
                                    <i class="ti ti-building-bank text-gray-400 peer-checked:text-blue-600 text-2xl block mb-1"></i>
                                    <span class="text-xs font-medium text-gray-600 peer-checked:text-blue-700">Transfer Bank</span>
                                </div>
                            </label>
                        </div>
                        @error('metode_pembayaran')
                            <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <i class="ti ti-alert-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Bukti Transfer (conditional) --}}
                    <div id="buktiTransferSection" class="{{ old('metode_pembayaran') === 'transfer' ? '' : 'hidden' }}">
                        <label for="bukti_transfer" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Bukti Transfer
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-blue-400 transition cursor-pointer"
                             onclick="document.getElementById('bukti_transfer').click()">
                            <div id="buktiPreviewWrap" class="hidden mb-2">
                                <img id="buktiPreview" src="#" alt="Preview Bukti" class="mx-auto max-h-28 rounded-lg object-cover shadow">
                            </div>
                            <div id="buktiPlaceholder">
                                <i class="ti ti-receipt text-gray-300 text-3xl"></i>
                                <p class="text-gray-400 text-xs mt-1.5">Klik untuk unggah bukti</p>
                                <p class="text-gray-300 text-xs">PNG, JPG maks. 2MB</p>
                            </div>
                            <p id="buktiFileName" class="text-xs text-blue-600 font-medium mt-1.5 hidden"></p>
                        </div>
                        <input type="file" id="bukti_transfer" name="bukti_transfer"
                               accept="image/png,image/jpeg,image/jpg"
                               class="hidden"
                               onchange="previewBukti(this)">
                        @error('bukti_transfer')
                            <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <i class="ti ti-alert-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Doa / Harapan --}}
                    <div>
                        <label for="doa_harapan" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Doa / Harapan
                        </label>
                        <textarea id="doa_harapan" name="doa_harapan" rows="3"
                                  placeholder="Tuliskan doa atau harapan donatur (opsional)..."
                                  class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none @error('doa_harapan') border-red-400 @enderror">{{ old('doa_harapan') }}</textarea>
                        @error('doa_harapan')
                            <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <i class="ti ti-alert-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 flex-shrink-0">
                    <button type="button"
                            onclick="document.getElementById('modalCatatDonasi').classList.add('hidden')"
                            class="text-sm font-bold text-white px-4 py-2 rounded-lg transition"
                            style="background-color: #374151 !important; color: #ffffff !important;">
                        Batal
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-5 py-2 rounded-lg shadow transition cursor-pointer"
                            style="background-color: #16a34a !important; color: #ffffff !important;">
                        <i class="ti ti-device-floppy"></i>
                        Catat Donasi
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ================================================================== --}}
{{-- SCRIPTS --}}
{{-- ================================================================== --}}
@push('scripts')
<script>
    // ── Banner Preview ──────────────────────────────────────────────────────
    function previewBanner(input) {
        const file = input.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('bannerPreview').src = e.target.result;
            document.getElementById('bannerPreviewWrap').classList.remove('hidden');
            document.getElementById('bannerPlaceholder').classList.add('hidden');
        };
        reader.readAsDataURL(file);

        const nameEl = document.getElementById('bannerFileName');
        nameEl.textContent = file.name;
        nameEl.classList.remove('hidden');
    }

    // ── Bukti Transfer Preview ───────────────────────────────────────────────
    function previewBukti(input) {
        const file = input.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('buktiPreview').src = e.target.result;
            document.getElementById('buktiPreviewWrap').classList.remove('hidden');
            document.getElementById('buktiPlaceholder').classList.add('hidden');
        };
        reader.readAsDataURL(file);

        const nameEl = document.getElementById('buktiFileName');
        nameEl.textContent = file.name;
        nameEl.classList.remove('hidden');
    }

    // ── Toggle Bukti Transfer Section ────────────────────────────────────────
    function toggleBuktiTransfer(value) {
        const section = document.getElementById('buktiTransferSection');
        if (value === 'transfer') {
            section.classList.remove('hidden');
        } else {
            section.classList.add('hidden');
        }
    }

    // ── Auto-open modals if there are validation errors ──────────────────────
    @if($errors->any() && old('_form') === 'program')
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('modalTambahProgram').classList.remove('hidden');
        });
    @endif

    @if($errors->any() && old('_form') === 'transaksi')
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('modalCatatDonasi').classList.remove('hidden');
            // Re-check transfer metode to show bukti section
            const metode = '{{ old('metode_pembayaran') }}';
            if (metode === 'transfer') {
                toggleBuktiTransfer('transfer');
            }
        });
    @endif

    // ── Open Edit Program Modal ──────────────────────────────────────────────
    function openEditProgramModal(data) {
        const form = document.getElementById('formEditProgram');
        form.action = `/admin/infaq/program/${data.id}/update`;
        document.getElementById('edit_nama_program').value = data.nama_program || '';
        document.getElementById('edit_deskripsi').value = data.deskripsi || '';
        document.getElementById('edit_target_dana').value = data.target_dana || '';

        const preview = document.getElementById('editBannerPreview');
        const placeholder = document.getElementById('editBannerPlaceholder');
        const nameEl = document.getElementById('editBannerFileName');

        if (data.foto_banner) {
            preview.src = data.foto_banner;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        } else {
            preview.src = '#';
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }
        nameEl.textContent = '';
        nameEl.classList.add('hidden');

        document.getElementById('modalEditProgram').classList.remove('hidden');
    }

    function previewEditBanner(input) {
        const file = input.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('editBannerPreview');
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            document.getElementById('editBannerPlaceholder').classList.add('hidden');
        };
        reader.readAsDataURL(file);

        const nameEl = document.getElementById('editBannerFileName');
        nameEl.textContent = file.name;
        nameEl.classList.remove('hidden');
    }

    // ── Close modals on Escape key ───────────────────────────────────────────
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('modalTambahProgram').classList.add('hidden');
            document.getElementById('modalEditProgram').classList.add('hidden');
            document.getElementById('modalCatatDonasi').classList.add('hidden');
        }
    });
</script>
@endpush

@endsection
