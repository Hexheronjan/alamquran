@extends('layouts.admin.app')

@section('title', 'Arsip Surat Keluar')

@section('content')
<div class="container-xl">

    {{-- Page Header --}}
    <div class="page-header d-print-none mb-4">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="ti ti-send text-blue-600 text-3xl"></i>
                    Arsip Surat Keluar
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Kelola semua surat keluar pondok pesantren. Nomor surat berikutnya:
                    <span class="font-semibold text-blue-600">{{ $nomorBerikut }}</span>
                </p>
            </div>
            <div class="mt-3 sm:mt-0">
                <a href="{{ route('admin.surat.keluar.create') }}"
                   class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-5 py-2.5 rounded-lg shadow transition"
                   style="background-color: #16a34a !important; color: #ffffff !important;">
                    <i class="ti ti-plus text-lg"></i>
                    Buat Surat Baru
                </a>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-4 shadow-sm">
            <i class="ti ti-circle-check text-green-500 text-xl flex-shrink-0"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 mb-4 shadow-sm">
            <i class="ti ti-alert-circle text-red-500 text-xl flex-shrink-0"></i>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Search & Filter Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
        <form action="{{ route('admin.surat.keluar.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg pointer-events-none"></i>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nomor surat, tujuan, perihal..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-800 text-white text-sm font-bold px-4 py-2.5 rounded-lg transition"
                        style="background-color: #374151 !important; color: #ffffff !important;">
                    <i class="ti ti-search text-base"></i>
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.surat.keluar.index') }}"
                       class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2.5 rounded-lg transition">
                        <i class="ti ti-x text-base"></i>
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-8">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Surat</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tujuan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Perihal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Penandatangan</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Lampiran</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Dibuat Oleh</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($suratKeluar as $index => $surat)
                        <tr class="hover:bg-blue-50/40 transition-colors">
                            {{-- Row Number --}}
                            <td class="px-4 py-3 text-sm text-gray-500 text-center">
                                {{ $suratKeluar->firstItem() + $index }}
                            </td>

                            {{-- Nomor Surat --}}
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 text-sm font-mono font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded">
                                    <i class="ti ti-file-text text-blue-500 text-xs"></i>
                                    {{ $surat->nomor_surat }}
                                </span>
                            </td>

                            {{-- Tanggal --}}
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <i class="ti ti-calendar text-gray-400 text-base"></i>
                                    {{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d M Y') }}
                                </div>
                            </td>

                            {{-- Tujuan --}}
                            <td class="px-4 py-3 text-sm text-gray-700 max-w-[180px]">
                                <div class="flex items-start gap-1.5">
                                    <i class="ti ti-building text-gray-400 text-base mt-0.5 flex-shrink-0"></i>
                                    <span class="truncate" title="{{ $surat->tujuan_surat }}">{{ $surat->tujuan_surat }}</span>
                                </div>
                            </td>

                            {{-- Perihal --}}
                            <td class="px-4 py-3 text-sm text-gray-700 max-w-[200px]">
                                <span class="line-clamp-2" title="{{ $surat->perihal }}">{{ $surat->perihal }}</span>
                            </td>

                            {{-- Penandatangan --}}
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <i class="ti ti-user-check text-gray-400 text-base"></i>
                                    {{ $surat->penandatangan }}
                                </div>
                            </td>

                            {{-- Lampiran --}}
                            <td class="px-4 py-3 text-center">
                                @if ($surat->file_lampiran)
                                    <a href="{{ Storage::url($surat->file_lampiran) }}"
                                       target="_blank"
                                       title="Unduh Lampiran"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 hover:bg-emerald-200 text-emerald-700 transition">
                                        <i class="ti ti-download text-base"></i>
                                    </a>
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Admin Pembuat --}}
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                        <i class="ti ti-user text-indigo-600 text-xs"></i>
                                    </div>
                                    <span>{{ $surat->creator->name ?? '-' }}</span>
                                </div>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- Lihat --}}
                                    <a href="{{ route('admin.surat.keluar.show', $surat->id) }}"
                                       title="Lihat & Cetak"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 hover:bg-blue-200 text-blue-700 transition">
                                        <i class="ti ti-eye text-base"></i>
                                    </a>

                                    {{-- Hapus --}}
                                    <form action="{{ route('admin.surat.keluar.destroy', $surat->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus surat ini? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                title="Hapus Surat"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 hover:bg-red-200 text-red-700 transition">
                                            <i class="ti ti-trash text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                    <i class="ti ti-inbox-off text-5xl"></i>
                                    <p class="text-base font-medium">Belum ada surat keluar</p>
                                    @if(request('search'))
                                        <p class="text-sm">Tidak ada hasil untuk pencarian "<span class="font-semibold text-gray-600">{{ request('search') }}</span>"</p>
                                        <a href="{{ route('admin.surat.keluar.index') }}" class="text-sm text-blue-600 hover:underline">Tampilkan semua surat</a>
                                    @else
                                        <a href="{{ route('admin.surat.keluar.create') }}"
                                           class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-4 py-2 rounded-lg transition mt-1"
                                           style="background-color: #16a34a !important; color: #ffffff !important;">
                                            <i class="ti ti-plus"></i> Buat Surat Pertama
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination & Summary --}}
        @if ($suratKeluar->hasPages())
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-3 bg-gray-50 border-t border-gray-200">
                <p class="text-xs text-gray-500">
                    Menampilkan <span class="font-semibold text-gray-700">{{ $suratKeluar->firstItem() }}</span>
                    – <span class="font-semibold text-gray-700">{{ $suratKeluar->lastItem() }}</span>
                    dari <span class="font-semibold text-gray-700">{{ $suratKeluar->total() }}</span> surat
                </p>
                <div class="text-sm">
                    {{ $suratKeluar->appends(request()->query())->links() }}
                </div>
            </div>
        @else
            <div class="px-5 py-3 bg-gray-50 border-t border-gray-200">
                <p class="text-xs text-gray-500">
                    Total <span class="font-semibold text-gray-700">{{ $suratKeluar->total() }}</span> surat keluar
                </p>
            </div>
        @endif
    </div>

</div>
@endsection
