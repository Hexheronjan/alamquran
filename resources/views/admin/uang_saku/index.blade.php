@extends('layouts.admin.app')

@section('title', 'Uang Saku Santri')

@section('content')
<div class="bg-gray-100 min-h-screen">
    <div class="w-full py-6 px-4 lg:px-8">

        <!-- Header -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pengelolaan Uang Saku Santri</h1>
                <p class="text-gray-600 text-sm">Kelola titipan uang saku dan perincian belanja santri</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.uang_saku.createManual') }}"
                    style="background-color: #0d9488; color: #ffffff;"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-bold hover:opacity-90 transition shadow">
                    <i class="fas fa-plus-circle"></i> Input Transaksi Manual
                </a>
                <a href="{{ route('admin.uang_saku.settings') }}"
                    style="background-color: #6366f1; color: #ffffff;"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-bold hover:opacity-90 transition shadow">
                    <i class="fab fa-whatsapp"></i> Pengaturan Notifikasi WA
                </a>
            </div>
        </div>


        <!-- Summary Card Utama & Per Kelas -->
        <div style="background-color: #0d9488; color: #ffffff;" class="rounded-2xl shadow-lg p-6 mb-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div style="background-color: rgba(255, 255, 255, 0.2);" class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl font-bold text-white">
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <p style="color: #ffffff;" class="text-sm font-semibold opacity-90">
                        @if(!empty($selectedKelas))
                            Total Titipan Uang Saku (Kelas {{ $selectedKelas }})
                        @else
                            Total Titipan Uang Saku (Seluruh Kelas)
                        @endif
                    </p>
                    <h2 style="color: #ffffff;" class="text-3xl font-extrabold mt-0.5">
                        Rp {{ number_format($totalKas, 0, ',', '.') }}
                    </h2>
                </div>
            </div>
            <div style="background-color: rgba(255, 255, 255, 0.15); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.3);" class="text-xs font-semibold px-4 py-2 rounded-lg">
                <i class="fas fa-info-circle mr-1"></i>
                @if(!empty($selectedKelas))
                    Menampilkan akumulasi saldo santri khusus <strong>Kelas {{ $selectedKelas }}</strong>
                @else
                    Data dihitung dari saldo akhir aktif setiap santri
                @endif
            </div>
        </div>

        <!-- Ringkasan Total Titipan Sesuai Kelas (Klik untuk Filter Cepat) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
            <a href="{{ route('admin.uang_saku.index', array_merge(request()->except('page'), ['kelas' => ''])) }}"
               class="rounded-xl p-4 border transition shadow-xs flex flex-col justify-between {{ empty($selectedKelas) ? 'bg-teal-50 border-teal-500 ring-2 ring-teal-500/20' : 'bg-white border-gray-200 hover:border-teal-300' }}">
                <div class="flex items-center justify-between text-xs font-bold {{ empty($selectedKelas) ? 'text-teal-800' : 'text-gray-500' }}">
                    <span>SEMUA KELAS</span>
                    <i class="fas fa-layer-group {{ empty($selectedKelas) ? 'text-teal-600' : 'text-gray-400' }}"></i>
                </div>
                <div class="text-lg font-extrabold font-mono text-gray-900 mt-1">
                    Rp {{ number_format($totalSemuaKelas ?? $totalKas, 0, ',', '.') }}
                </div>
                <span class="text-[11px] text-gray-400 mt-0.5">Total gabungan kelas 1, 2 & 3</span>
            </a>

            @foreach(['1', '2', '3'] as $kls)
                <a href="{{ route('admin.uang_saku.index', array_merge(request()->except('page'), ['kelas' => $kls])) }}"
                   class="rounded-xl p-4 border transition shadow-xs flex flex-col justify-between {{ (string)($selectedKelas ?? '') === $kls ? 'bg-teal-50 border-teal-500 ring-2 ring-teal-500/20' : 'bg-white border-gray-200 hover:border-teal-300' }}">
                    <div class="flex items-center justify-between text-xs font-bold {{ (string)($selectedKelas ?? '') === $kls ? 'text-teal-800' : 'text-gray-500' }}">
                        <span>TOTAL TITIPAN KELAS {{ $kls }}</span>
                        <i class="fas fa-graduation-cap {{ (string)($selectedKelas ?? '') === $kls ? 'text-teal-600' : 'text-gray-400' }}"></i>
                    </div>
                    <div class="text-lg font-extrabold font-mono {{ (string)($selectedKelas ?? '') === $kls ? 'text-teal-700' : 'text-gray-900' }} mt-1">
                        Rp {{ number_format($totalPerKelas[$kls] ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] {{ (string)($selectedKelas ?? '') === $kls ? 'text-teal-600 font-semibold' : 'text-gray-400' }} mt-0.5">
                        {{ (string)($selectedKelas ?? '') === $kls ? '✓ Sedang Difilter' : 'Klik untuk lihat Kelas ' . $kls }}
                    </span>
                </a>
            @endforeach
        </div>

        <!-- Search Bar & Filters -->
        <div class="bg-white rounded-xl shadow p-4 mb-6">
            <form method="GET" action="{{ route('admin.uang_saku.index') }}" class="flex flex-col md:flex-row gap-3">
                <div class="flex-1 relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama santri, NIK, NISN, atau nama ayah..."
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm">
                    <i class="fas fa-search absolute left-3.5 top-3 text-gray-400"></i>
                </div>

                {{-- Filter Tahun Ajaran --}}
                <div class="w-full md:w-44">
                    <select name="tahun_ajaran" onchange="this.form.submit()" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach($tahunList as $th)
                            <option value="{{ $th }}" {{ request('tahun_ajaran') == $th ? 'selected' : '' }}>{{ $th }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Kelas --}}
                <div class="w-full md:w-40">
                    <select name="kelas" onchange="this.form.submit()" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                        <option value="" {{ empty($selectedKelas) ? 'selected' : '' }}>Semua Kelas</option>
                        <option value="1" {{ ($selectedKelas ?? '') == '1' ? 'selected' : '' }}>Kelas 1</option>
                        <option value="2" {{ ($selectedKelas ?? '') == '2' ? 'selected' : '' }}>Kelas 2</option>
                        <option value="3" {{ ($selectedKelas ?? '') == '3' ? 'selected' : '' }}>Kelas 3</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" style="background-color: #0d9488; color: #ffffff;"
                        class="px-5 py-2.5 rounded-lg text-sm font-semibold hover:opacity-90 transition flex items-center gap-2">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <a href="{{ route('admin.uang_saku.index') }}" class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-semibold flex items-center gap-1">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Santri Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto desktop-only-table">
                <table class="w-full divide-y divide-gray-200 text-sm" style="table-layout: fixed; min-width: 0;">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3.5 text-left font-bold text-gray-700 uppercase tracking-wider" style="width: 5%;">No</th>
                            <th class="px-3 py-3.5 text-left font-bold text-gray-700 uppercase tracking-wider" style="width: 14%;">Nama Santri</th>
                            <th class="px-3 py-3.5 text-left font-bold text-gray-700 uppercase tracking-wider" style="width: 16%;">Kelas & TA</th>
                            <th class="px-3 py-3.5 text-left font-bold text-gray-700 uppercase tracking-wider" style="width: 21%;">NIK / NISN</th>
                            <th class="px-3 py-3.5 text-left font-bold text-gray-700 uppercase tracking-wider" style="width: 16%;">Orang Tua (Ayah)</th>
                            <th class="px-3 py-3.5 text-right font-bold text-gray-700 uppercase tracking-wider" style="width: 14%;">Saldo Terkini</th>
                            <th class="px-3 py-3.5 text-center font-bold text-gray-700 uppercase tracking-wider" style="width: 14%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($santri as $index => $item)
                        @php
                            $currentClass = $item->kelas_sekarang;
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-3 py-4 text-gray-500 font-medium">
                                {{ $santri->firstItem() + $index }}
                            </td>
                            <td class="px-3 py-4 break-words">
                                <div class="font-bold text-gray-900">{{ $item->nama_lengkap }}</div>
                                <div class="text-xs text-gray-500">{{ $item->sekolah_asal }}</div>
                            </td>
                            <td class="px-3 py-4">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-bold bg-teal-100 text-teal-800 border border-teal-300">
                                    <i class="fas fa-graduation-cap"></i> {{ $currentClass }}
                                </span>
                                <div class="text-xs text-gray-500 font-mono mt-1">TA: {{ $item->tahun_ajaran ?? '-' }}</div>
                            </td>
                            <td class="px-3 py-4 break-all text-gray-600">
                                <div><span class="text-xs text-gray-400">NIK:</span> {{ $item->nik }}</div>
                                <div><span class="text-xs text-gray-400">NISN:</span> {{ $item->nisn }}</div>
                            </td>
                            <td class="px-3 py-4 break-words text-gray-600">
                                <div class="font-semibold text-gray-800">{{ $item->orangTua->nama_ayah ?? '-' }}</div>
                                <div class="text-xs text-teal-600"><i class="fab fa-whatsapp mr-1"></i>{{ $item->orangTua->no_wa_ayah ?? '-' }}</div>
                            </td>
                            <td class="px-3 py-4 text-right">
                                <span class="text-base font-extrabold {{ $item->saldo_uang_saku < 0 ? 'text-red-700 bg-red-50 border-red-300 font-mono' : 'text-teal-700 bg-teal-50 border-teal-200' }} px-2.5 py-1 rounded-full border inline-block whitespace-nowrap">
                                    {{ $item->saldo_uang_saku < 0 ? '-Rp ' . number_format(abs($item->saldo_uang_saku), 0, ',', '.') . ' (Talangan)' : 'Rp ' . number_format($item->saldo_uang_saku, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-3 py-4 text-center">
                                <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 6px;">
                                    <a href="{{ route('admin.uang_saku.show', $item->id) }}"
                                        style="background-color: #0d9488; color: #ffffff; display: inline-flex; align-items: center; gap: 4px; padding: 8px 10px; border-radius: 8px; font-size: 12px; font-weight: bold; text-decoration: none;"
                                        class="hover:opacity-90 transition shadow-sm">
                                        <i class="fas fa-wallet"></i> Kelola
                                    </a>
                                    @if(auth('admin')->user()?->isSuperAdmin())
                                    <form method="POST" action="{{ route('admin.uang_saku.destroySantri', $item->id) }}" 
                                        style="margin: 0; display: inline-block;"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data santri {{ $item->nama_lengkap }} ini beserta semua riwayat transaksi uang sakunya?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                            style="background-color: #dc2626; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border: none; border-radius: 8px; cursor: pointer;"
                                            class="hover:opacity-90 transition shadow-sm"
                                            title="Hapus Santri (Khusus Super Admin)">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-wallet text-4xl text-gray-300 mb-3 block"></i>
                                Belum ada data santri terdaftar.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mobile-data-cards p-4 space-y-3">
                @forelse($santri as $index => $item)
                    <article class="rounded-xl border border-gray-200 p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-gray-400">No. {{ $santri->firstItem() + $index }}</p>
                                <h3 class="font-bold text-gray-900">{{ $item->nama_lengkap }}</h3>
                                <p class="text-xs text-gray-500">{{ $item->sekolah_asal }}</p>
                            </div>
                            <span class="text-sm font-extrabold {{ $item->saldo_uang_saku < 0 ? 'text-red-700 bg-red-50 border-red-300 font-mono' : 'text-teal-700 bg-teal-50 border-teal-200' }} px-2.5 py-1 rounded-full border whitespace-nowrap">
                                {{ $item->saldo_uang_saku < 0 ? '-Rp ' . number_format(abs($item->saldo_uang_saku), 0, ',', '.') : 'Rp ' . number_format($item->saldo_uang_saku, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 mt-4 text-xs text-gray-600">
                            <div><span class="block text-gray-400">Kelas & Tahun Ajaran</span><strong>{{ $item->kelas_sekarang }}</strong><br>TA: {{ $item->tahun_ajaran ?? '-' }}</div>
                            <div><span class="block text-gray-400">Orang Tua</span><strong>{{ $item->orangTua->nama_ayah ?? '-' }}</strong><br>{{ $item->orangTua->no_wa_ayah ?? '-' }}</div>
                            <div class="col-span-2"><span class="block text-gray-400">NIK / NISN</span>{{ $item->nik }} / {{ $item->nisn }}</div>
                        </div>
                        <div class="flex items-center justify-between gap-2 mt-4 pt-3 border-t border-gray-100">
                            <a href="{{ route('admin.uang_saku.show', $item->id) }}"
                                style="background-color: #0d9488; color: white; display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 700; text-decoration: none; flex: 1; justify-content: center;">
                                <i class="fas fa-wallet"></i> Kelola Uang Saku
                            </a>
                            @if(auth('admin')->user()?->isSuperAdmin())
                            <form method="POST" action="{{ route('admin.uang_saku.destroySantri', $item->id) }}"
                                style="margin: 0; flex-shrink: 0;"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus data santri {{ $item->nama_lengkap }} ini beserta semua riwayat transaksi uang sakunya?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    style="background-color: #dc2626; color: white; display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px;"
                                    aria-label="Hapus {{ $item->nama_lengkap }}"
                                    title="Hapus Santri (Khusus Super Admin)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="py-8 text-center text-sm text-gray-500">Belum ada data santri terdaftar.</p>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($santri->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $santri->appends(request()->query())->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
