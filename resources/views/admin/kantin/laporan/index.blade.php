@extends('layouts.admin.app')

@section('title', 'Laporan Laba Rugi Kantin')

@section('content')
<div class="bg-gray-100 min-h-screen py-6 px-4 lg:px-8">

    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-report-money text-emerald-600"></i> Laporan Keuangan & Laba Rugi Kantin
            </h1>
            <p class="text-gray-600 text-xs mt-0.5">Pantau omset, modal (HPP), dan laba bersih per toko secara harian, mingguan, atau bulanan.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.kantin.kasir.index') }}" class="px-4 py-2.5 bg-emerald-600 text-white rounded-lg font-semibold text-xs hover:bg-emerald-700 transition shadow-sm flex items-center gap-1.5">
                <i class="ti ti-cash-register text-base"></i> Buka Kasir POS
            </a>
            <a href="{{ route('admin.kantin.produk.index') }}" class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg font-medium text-xs hover:bg-gray-50 transition shadow-sm flex items-center gap-1.5">
                <i class="ti ti-packages text-base"></i> Kelola Produk
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 mb-6 text-xs">
        <form method="GET" action="{{ route('admin.kantin.laporan.index') }}" class="flex flex-wrap items-center gap-3">
            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Pilih Toko</label>
                <select name="toko" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-emerald-500">
                    <option value="all" {{ $selectedToko === 'all' ? 'selected' : '' }}>Semua Toko</option>
                    <option value="kantin_1" {{ $selectedToko === 'kantin_1' ? 'selected' : '' }}>Kantin 1</option>
                    <option value="kantin_2" {{ $selectedToko === 'kantin_2' ? 'selected' : '' }}>Kantin 2</option>
                    <option value="aqumart" {{ $selectedToko === 'aqumart' ? 'selected' : '' }}>Minimarket Aqumart</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Periode Laporan</label>
                <select name="periode" id="periodeSelect" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-emerald-500">
                    <option value="harian" {{ $periode === 'harian' ? 'selected' : '' }}>Harian (Per Tanggal)</option>
                    <option value="mingguan" {{ $periode === 'mingguan' ? 'selected' : '' }}>Minggu Ini (Senin - Ahad)</option>
                    <option value="bulanan" {{ $periode === 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                </select>
            </div>

            @if($periode === 'harian')
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ request('tanggal', date('Y-m-d')) }}" onchange="this.form.submit()"
                           class="px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500">
                </div>
            @elseif($periode === 'bulanan')
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Bulan</label>
                    <select name="bulan" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ request('bulan', date('n')) == $m ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Tahun</label>
                    <input type="number" name="tahun" value="{{ request('tahun', date('Y')) }}" min="2020" max="2099" onchange="this.form.submit()"
                           class="px-3 py-2 border border-gray-300 rounded-lg text-xs w-24 focus:ring-2 focus:ring-emerald-500">
                </div>
            @endif

            <div class="pt-4">
                <a href="{{ route('admin.kantin.laporan.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold inline-flex items-center gap-1 transition">
                    <i class="ti ti-refresh"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- 4 Summary Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <!-- Total Omset -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span class="font-bold uppercase">Total Omset (Penjualan)</span>
                <i class="ti ti-cash text-lg text-blue-600"></i>
            </div>
            <div class="text-2xl font-black text-gray-900 font-mono">
                Rp {{ number_format($totalOmset, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-gray-400 mt-1">Pendapatan kotor</div>
        </div>

        <!-- Total Modal HPP -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span class="font-bold uppercase">Total Modal (HPP)</span>
                <i class="ti ti-wallet text-lg text-amber-600"></i>
            </div>
            <div class="text-2xl font-black text-gray-700 font-mono">
                Rp {{ number_format($totalModal, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-gray-400 mt-1">Biaya pokok barang terjual</div>
        </div>

        <!-- Laba Bersih -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-emerald-300 bg-emerald-50/20">
            <div class="flex items-center justify-between text-xs text-emerald-800 mb-1">
                <span class="font-bold uppercase">Laba Bersih</span>
                <i class="ti ti-trending-up text-lg text-emerald-600"></i>
            </div>
            <div class="text-2xl font-black text-emerald-700 font-mono">
                Rp {{ number_format($labaBersih, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-emerald-600 mt-1 font-semibold">Keuntungan bersih toko</div>
        </div>

        <!-- Total Transaksi -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span class="font-bold uppercase">Total Transaksi</span>
                <i class="ti ti-receipt text-lg text-purple-600"></i>
            </div>
            <div class="text-2xl font-black text-gray-900 font-mono">
                {{ number_format($totalTransaksi) }}
            </div>
            <div class="text-[11px] text-gray-400 mt-1">Struk tercetak</div>
        </div>
    </div>

    <!-- Table Rincian Transaksi -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900">Riwayat Transaksi Penjualan</h2>
            <span class="text-xs bg-gray-100 text-gray-700 px-2.5 py-0.5 rounded-full font-bold">
                {{ $transaksis->total() }} Transaksi
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Nota / Waktu</th>
                        <th class="px-6 py-3.5">Toko</th>
                        <th class="px-6 py-3.5">Santri</th>
                        <th class="px-6 py-3.5">Rincian Item</th>
                        <th class="px-6 py-3.5 text-right">Total Modal</th>
                        <th class="px-6 py-3.5 text-right">Total Belanja</th>
                        <th class="px-6 py-3.5 text-right">Laba Bersih</th>
                        <th class="px-6 py-3.5 text-center">Kasir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($transaksis as $trx)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-mono font-bold text-gray-900">#{{ $trx->no_transaksi }}</div>
                                <div class="text-[11px] text-gray-500 mt-0.5">{{ $trx->created_at->format('d M Y H:i') }} WIB</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-full font-bold text-[10px]
                                    {{ $trx->toko === 'kantin_1' ? 'bg-emerald-100 text-emerald-800' : ($trx->toko === 'kantin_2' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $trx->toko_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ $trx->santri->nama_lengkap }}</div>
                                <div class="text-[11px] text-gray-500">{{ $trx->santri->kelas_label }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-0.5">
                                    @foreach($trx->items as $it)
                                        <div class="text-gray-700">
                                            {{ $it->nama_produk }} <span class="text-gray-400">({{ $it->qty }}x)</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right font-mono text-gray-500">
                                Rp {{ number_format($trx->total_modal, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-mono font-bold text-gray-900">
                                Rp {{ number_format($trx->total_harga, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-mono font-bold text-emerald-600">
                                +Rp {{ number_format($trx->laba_bersih, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center text-gray-500">
                                {{ $trx->kasir?->name ?? 'Kasir' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <i class="ti ti-receipt-off text-3xl text-gray-400 mb-2 block"></i>
                                Tidak ada transaksi pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transaksis->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $transaksis->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
