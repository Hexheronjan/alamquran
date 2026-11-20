@extends('layouts.admin.app')

@section('title', 'Transaksi Uang Saku - ' . $santri->nama_lengkap)

@section('content')
@php
    $regYear = (int) substr($santri->tahun_ajaran ?? '', 0, 4);
    if (!$regYear && $santri->created_at) {
        $regYear = (int) $santri->created_at->format('Y');
    }
    $currentYear = (int) date('Y');
    if ($santri->kelas && in_array((string)$santri->kelas, ['1', '2', '3'])) {
        $defaultKelas = (string) $santri->kelas;
    } else {
        $diff = $currentYear - ($regYear > 0 ? $regYear : $currentYear);
        if ($diff <= 0) {
            $defaultKelas = '1';
        } elseif ($diff == 1) {
            $defaultKelas = '2';
        } else {
            $defaultKelas = '3';
        }
    }
    $selectedKelas = old('kelas', $defaultKelas);
@endphp
<div class="bg-gray-100 min-h-screen">
    <div class="w-full py-6 px-4 lg:px-8">

        <!-- Top Nav & Header -->
        <div class="mb-6">
            <a href="{{ route('admin.uang_saku.index') }}" class="text-teal-600 hover:text-teal-800 font-semibold mb-3 inline-flex items-center text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Uang Saku
            </a>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Uang Saku: {{ $santri->nama_lengkap }}</h1>
                    <p class="text-gray-600 text-sm">NIK: {{ $santri->nik }} | NISN: {{ $santri->nisn }} | Sekolah: {{ $santri->sekolah_asal }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.uang_saku.exportPDF', $santri->id) }}"
                        style="background-color: #059669; color: #ffffff;"
                        class="px-4 py-2.5 rounded-lg text-sm font-bold shadow hover:opacity-90 transition inline-flex items-center gap-2">
                        <i class="fas fa-file-pdf"></i> Export Mutasi PDF
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-4 rounded-lg mb-6 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-2 font-semibold">
                <i class="fas fa-check-circle text-green-600 text-lg"></i>
                {{ session('success') }}
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-4 rounded-lg mb-6 shadow-sm">
            <div class="font-bold flex items-center gap-2 mb-1">
                <i class="fas fa-exclamation-circle text-red-600"></i> Terjadi Kesalahan:
            </div>
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Saldo & Action Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
            <!-- Saldo Card -->
            <div style="background-color: {{ $santri->saldo_uang_saku < 0 ? '#b91c1c' : '#0d9488' }}; color: #ffffff;" class="rounded-2xl shadow-lg p-6 flex flex-col justify-between transition-colors">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span style="background-color: rgba(255, 255, 255, 0.2); color: #ffffff;" class="text-xs uppercase font-bold tracking-wider px-3 py-1 rounded-full">
                            {{ $santri->saldo_uang_saku < 0 ? 'Defisit / Talangan Pondok' : 'Saldo Terkini' }}
                        </span>
                        <i class="fas {{ $santri->saldo_uang_saku < 0 ? 'fa-exclamation-triangle' : 'fa-wallet' }} text-2xl text-white"></i>
                    </div>
                    <h2 style="color: #ffffff;" class="text-4xl font-extrabold font-mono">
                        {{ $santri->saldo_uang_saku < 0 ? '-Rp ' . number_format(abs($santri->saldo_uang_saku), 0, ',', '.') : 'Rp ' . number_format($santri->saldo_uang_saku, 0, ',', '.') }}
                    </h2>
                    @if($santri->saldo_uang_saku < 0)
                        <div class="mt-2 inline-flex items-center gap-1.5 text-xs bg-black/25 text-red-100 px-2.5 py-1 rounded-lg font-semibold">
                            <i class="fas fa-info-circle"></i> Biaya ditalangi pondok sementara
                        </div>
                    @endif
                </div>
                <div style="border-color: rgba(255, 255, 255, 0.25); color: #ffffff;" class="mt-6 pt-4 border-t text-xs">
                    <p><i class="fas fa-user text-white mr-1"></i> Ayah: <strong>{{ $santri->orangTua->nama_ayah ?? '-' }}</strong></p>
                    <p class="mt-1"><i class="fab fa-whatsapp text-white mr-1"></i> WA: <strong>{{ $santri->orangTua->no_wa_ayah ?? '-' }}</strong></p>
                </div>
            </div>

            <!-- Form + Titip Uang Saku (Masuk) -->
            <div class="bg-white rounded-2xl shadow p-5 border border-gray-200">
                <h3 class="text-base font-bold text-gray-900 mb-3 flex items-center gap-2 border-b pb-2 text-green-700">
                    <i class="fas fa-plus-circle text-lg"></i> + Titip Uang Saku (Orang Tua)
                </h3>
                <form action="{{ route('admin.uang_saku.deposit', $santri->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Kelas</label>
                            <select name="kelas" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 font-semibold">
                                <option value="">-- Pilih Kelas --</option>
                                <option value="1" {{ in_array($selectedKelas, ['1', 'Kelas 1', 'Kelas 1 MTs']) ? 'selected' : '' }}>Kelas 1</option>
                                <option value="2" {{ in_array($selectedKelas, ['2', 'Kelas 2', 'Kelas 2 MTs']) ? 'selected' : '' }}>Kelas 2</option>
                                <option value="3" {{ in_array($selectedKelas, ['3', 'Kelas 3', 'Kelas 3 MTs']) ? 'selected' : '' }}>Kelas 3</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tahun Ajaran</label>
                            <input type="text" name="tahun_ajaran" value="{{ old('tahun_ajaran', $santri->tahun_ajaran ?: date('Y').'/'.(date('Y')+1)) }}" placeholder="2026/2027"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 font-semibold">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nominal Titipan (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="jumlah" min="1000" step="500" required placeholder="Contoh: 500000"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Titipan <span class="text-red-500">*</span></label>
                        <input type="text" name="keterangan" required placeholder="Contoh: Titip dari Ayah via Transfer BSI"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                    </div>
                    <button type="submit" style="background-color: #059669; color: #ffffff;"
                        class="w-full py-2.5 rounded-lg text-sm font-bold shadow hover:opacity-90 transition flex items-center justify-center gap-2 border-0 cursor-pointer">
                        <i class="fas fa-save"></i> Simpan Titipan (Masuk)
                    </button>
                </form>
            </div>

            <!-- Form - Belanja Santri (Keluar) -->
            <div class="bg-white rounded-2xl shadow p-5 border border-gray-200">
                <h3 class="text-base font-bold text-gray-900 mb-3 flex items-center gap-2 border-b pb-2 text-red-600">
                    <i class="fas fa-shopping-cart text-lg"></i> - Catat Belanja Santri (Pengeluaran)
                </h3>
                <form action="{{ route('admin.uang_saku.expense', $santri->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Kelas</label>
                            <select name="kelas" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 font-semibold">
                                <option value="">-- Pilih Kelas --</option>
                                <option value="1" {{ in_array($selectedKelas, ['1', 'Kelas 1', 'Kelas 1 MTs']) ? 'selected' : '' }}>Kelas 1</option>
                                <option value="2" {{ in_array($selectedKelas, ['2', 'Kelas 2', 'Kelas 2 MTs']) ? 'selected' : '' }}>Kelas 2</option>
                                <option value="3" {{ in_array($selectedKelas, ['3', 'Kelas 3', 'Kelas 3 MTs']) ? 'selected' : '' }}>Kelas 3</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tahun Ajaran</label>
                            <input type="text" name="tahun_ajaran" value="{{ old('tahun_ajaran', $santri->tahun_ajaran ?: date('Y').'/'.(date('Y')+1)) }}" placeholder="2026/2027"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500 font-semibold">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Pengeluaran Rp <span class="text-red-500">*</span></label>
                        <input type="number" name="jumlah" min="500" step="500" required placeholder="Contoh: 25000"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Perincian Barang Belanjaan <span class="text-red-500">*</span></label>
                        <input type="text" name="keterangan" required placeholder="Contoh: Beli Sabun, Shampo, & Buku Tulis"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    </div>
                    <button type="submit" style="background-color: #dc2626; color: #ffffff;"
                        class="w-full py-2.5 rounded-lg text-sm font-bold shadow hover:opacity-90 transition flex items-center justify-center gap-2 border-0 cursor-pointer">
                        <i class="fas fa-minus-circle"></i> Catat Belanja (Keluar)
                    </button>
                </form>
            </div>
        </div>

        <!-- Ringkasan Mutasi Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow p-5 border border-green-100">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-arrow-down text-green-600"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-500 uppercase">Total Uang Titip</span>
                </div>
                <p class="text-xl font-extrabold text-green-700">Rp {{ number_format($totalUangTitip, 0, ',', '.') }}</p>
                @if(request('bulan') || request('tahun'))<p class="text-xs text-gray-400 mt-1"><i class="fas fa-filter"></i> Sesuai filter aktif</p>@endif
            </div>
            <div class="bg-white rounded-xl shadow p-5 border border-red-100">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-arrow-up text-red-600"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-500 uppercase">Total Pengeluaran</span>
                </div>
                <p class="text-xl font-extrabold text-red-700">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
                @if(request('bulan') || request('tahun'))<p class="text-xs text-gray-400 mt-1"><i class="fas fa-filter"></i> Sesuai filter aktif</p>@endif
            </div>
            <div class="bg-white rounded-xl shadow p-5 border border-teal-100">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 bg-teal-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-wallet text-teal-600"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-500 uppercase">Saldo Saat Ini</span>
                </div>
                <p class="text-xl font-extrabold {{ $santri->saldo_uang_saku >= 0 ? 'text-teal-700' : 'text-red-700' }}">Rp {{ number_format($santri->saldo_uang_saku, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400 mt-1">Saldo aktual keseluruhan</p>
            </div>
        </div>

        <!-- Transaction History Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-history text-teal-600"></i> Riwayat Transaksi Uang Saku
                </h2>
                <div class="flex flex-wrap items-center gap-2">
                    <form method="GET" action="{{ route('admin.uang_saku.show', $santri->id) }}" class="flex flex-wrap gap-2" id="filterForm">
                        <input type="hidden" name="kelas" value="{{ request('kelas') }}">
                        <input type="hidden" name="tahun_ajaran" value="{{ request('tahun_ajaran') }}">
                        <select name="bulan" class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500" onchange="document.getElementById('filterForm').submit()">
                            <option value="">Semua Bulan</option>
                            @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                            @endforeach
                        </select>
                        <select name="tahun" class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500" onchange="document.getElementById('filterForm').submit()">
                            <option value="">Semua Tahun</option>
                            @foreach($availableYears as $yr)
                            <option value="{{ $yr }}" {{ request('tahun') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                        @if(request('bulan') || request('tahun'))
                        <a href="{{ route('admin.uang_saku.show', $santri->id) }}" class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-300 transition">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                        @endif
                    </form>
                    <span class="text-xs text-gray-500">{{ $transaksi->total() }} transaksi</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3.5 text-left font-bold text-gray-700 uppercase tracking-wider">Tanggal & Waktu</th>
                            <th class="px-6 py-3.5 text-center font-bold text-gray-700 uppercase tracking-wider">Jenis</th>
                            <th class="px-6 py-3.5 text-center font-bold text-gray-700 uppercase tracking-wider">Kelas & TA</th>
                            <th class="px-6 py-3.5 text-right font-bold text-gray-700 uppercase tracking-wider">Mutasi Pemasukan & Pengeluaran (Rp)</th>
                            <th class="px-6 py-3.5 text-left font-bold text-gray-700 uppercase tracking-wider">Catatan / Perincian Belanja</th>
                            <th class="px-6 py-3.5 text-right font-bold text-gray-700 uppercase tracking-wider">Saldo Akhir</th>
                            <th class="px-6 py-3.5 text-left font-bold text-gray-700 uppercase tracking-wider">Petugas</th>
                            <th class="px-6 py-3.5 text-center font-bold text-gray-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">

                        @forelse($transaksi as $t)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600 font-medium">
                                <div>{{ $t->created_at->translatedFormat('d M Y, H:i') }}</div>
                                @if($t->jenis === 'masuk')
                                    <div class="mt-0.5">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-mono font-semibold bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded border border-gray-200" title="Nomor Kuitansi">
                                            <i class="ti ti-receipt text-xs text-blue-600"></i> {{ $t->nomor_invoice }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($t->jenis === 'masuk')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-extrabold bg-green-100 text-green-800">
                                        <i class="fas fa-arrow-down"></i> Masuk (Titip)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-extrabold bg-red-100 text-red-800">
                                        <i class="fas fa-shopping-bag"></i> Keluar (Belanja)
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-xs text-gray-600 font-medium">
                                @if($t->kelas || $t->tahun_ajaran)
                                    <span class="inline-block bg-gray-100 rounded px-2 py-1">
                                        {{ $t->kelas ?? '-' }} {{ $t->tahun_ajaran ? '('.$t->tahun_ajaran.')' : '' }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-bold">
                                @if($t->jenis === 'masuk')
                                    <span class="text-green-600">+ {{ number_format($t->jumlah, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-red-600">- {{ number_format($t->jumlah, 0, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-800 font-medium">
                                {{ $t->keterangan }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-extrabold {{ $t->saldo_akhir < 0 ? 'text-red-600 font-mono' : 'text-teal-700' }}">
                                {{ $t->saldo_akhir < 0 ? '-Rp ' . number_format(abs($t->saldo_akhir), 0, ',', '.') : 'Rp ' . number_format($t->saldo_akhir, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                {{ $t->admin->name ?? 'Admin' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="inline-flex items-center justify-center gap-2">
                                    @if($t->jenis === 'masuk')
                                        <a href="{{ route('admin.uang_saku.kuitansi', $t->id) }}" target="_blank"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition shadow-2xs"
                                           title="Cetak Kuitansi Titipan Uang Saku Resmi (PDF)">
                                            <i class="fas fa-print"></i> Kuitansi
                                        </a>
                                    @endif
                                    <form action="{{ route('admin.uang_saku.destroy', $t->id) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi ini? Saldo akan dihitung ulang secara otomatis.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 p-1.5 rounded transition bg-transparent border-0 cursor-pointer" title="Hapus Transaksi">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-receipt text-4xl text-gray-300 mb-3 block"></i>
                                Belum ada riwayat transaksi uang saku untuk santri ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transaksi->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $transaksi->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
