@extends('layouts.admin.app')

@section('title', 'Katalog Produk Kantin & Aqumart')

@section('content')
<div class="bg-gray-100 min-h-screen">
    <div class="w-full py-6 px-4 lg:px-8">

        <!-- Alerts -->
        @if(session('success'))
            <div class="mb-5 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg shadow-sm flex items-start gap-3">
                <i class="ti ti-circle-check text-2xl text-green-600 flex-shrink-0 mt-0.5"></i>
                <div>
                    <h4 class="text-sm font-bold text-green-800">Berhasil!</h4>
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="ti ti-building-store text-emerald-600"></i> Manajemen Produk & Stok Kantin
                </h1>
                <p class="text-gray-600 text-sm mt-1">Kelola barang dagangan multi-toko: Kantin 1, Kantin 2, dan Minimarket Aqumart.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.kantin.kasir.index') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs transition shadow-sm flex items-center gap-1.5" style="background-color: #16a34a !important; color: #ffffff !important;">
                    <i class="ti ti-cash-register text-base"></i> Buka Kasir POS
                </a>
                <a href="{{ route('admin.kantin.laporan.index') }}" class="px-4 py-2.5 bg-gray-700 hover:bg-gray-800 text-white rounded-lg font-bold text-xs transition shadow-sm flex items-center gap-1.5" style="background-color: #374151 !important; color: #ffffff !important;">
                    <i class="ti ti-report-money text-base"></i> Laporan Laba Rugi
                </a>
                <a href="{{ route('admin.kantin.produk.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs transition shadow-sm flex items-center gap-1.5" style="background-color: #16a34a !important; color: #ffffff !important;">
                    <i class="ti ti-plus text-base"></i> + Tambah Produk Baru
                </a>
            </div>
        </div>

        {{-- Store Tabs / Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-{{ count($allowedTokos) + (count($allowedTokos) > 1 ? 1 : 0) }} gap-4 mb-6">
            @if(count($allowedTokos) > 1)
            <a href="{{ route('admin.kantin.produk.index', ['toko' => 'all']) }}"
               class="bg-white rounded-xl p-4 shadow-sm border {{ $selectedToko === 'all' ? 'border-purple-500 ring-2 ring-purple-200' : 'border-gray-200 hover:border-gray-300' }} transition">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span class="font-bold uppercase">Semua Toko</span>
                    <i class="ti ti-building-store text-lg text-purple-600"></i>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ array_sum($counts) }}</div>
                <div class="text-[11px] text-gray-400 mt-0.5">Total seluruh item</div>
            </a>
            @endif

            @if(in_array('kantin_1', $allowedTokos))
            <a href="{{ route('admin.kantin.produk.index', ['toko' => 'kantin_1']) }}"
               class="bg-white rounded-xl p-4 shadow-sm border {{ $selectedToko === 'kantin_1' ? 'border-emerald-500 ring-2 ring-emerald-200' : 'border-gray-200 hover:border-gray-300' }} transition">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span class="font-bold uppercase text-emerald-700">Kantin 1</span>
                    <i class="ti ti-tools-kitchen-2 text-lg text-emerald-600"></i>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ $counts['kantin_1'] ?? 0 }}</div>
                <div class="text-[11px] text-gray-400 mt-0.5">Makanan &amp; Minuman Berat</div>
            </a>
            @endif

            @if(in_array('kantin_2', $allowedTokos))
            <a href="{{ route('admin.kantin.produk.index', ['toko' => 'kantin_2']) }}"
               class="bg-white rounded-xl p-4 shadow-sm border {{ $selectedToko === 'kantin_2' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-200 hover:border-gray-300' }} transition">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span class="font-bold uppercase text-blue-700">Kantin 2</span>
                    <i class="ti ti-cup text-lg text-blue-600"></i>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ $counts['kantin_2'] ?? 0 }}</div>
                <div class="text-[11px] text-gray-400 mt-0.5">Snack &amp; Minuman Ringan</div>
            </a>
            @endif

            @if(in_array('aqumart', $allowedTokos))
            <a href="{{ route('admin.kantin.produk.index', ['toko' => 'aqumart']) }}"
               class="bg-white rounded-xl p-4 shadow-sm border {{ $selectedToko === 'aqumart' ? 'border-amber-500 ring-2 ring-amber-200' : 'border-gray-200 hover:border-gray-300' }} transition">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span class="font-bold uppercase text-amber-700">Aqumart</span>
                    <i class="ti ti-shopping-cart text-lg text-amber-600"></i>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ $counts['aqumart'] ?? 0 }}</div>
                <div class="text-[11px] text-gray-400 mt-0.5">Kebutuhan Santri &amp; Kitab</div>
            </a>
            @endif
        </div>

        <!-- Search Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" action="{{ route('admin.kantin.produk.index') }}" class="flex flex-col md:flex-row gap-3">
                <input type="hidden" name="toko" value="{{ $selectedToko }}">
                <div class="flex-1 relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nama produk atau kode/barcode..."
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm">
                    <i class="ti ti-search absolute left-3.5 top-3 text-gray-400 text-lg"></i>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 transition flex items-center gap-1.5 cursor-pointer">
                        <i class="ti ti-search text-base"></i> Cari
                    </button>
                    <a href="{{ route('admin.kantin.produk.index', ['toko' => $selectedToko]) }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium flex items-center gap-1">
                        <i class="ti ti-refresh text-base"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table Data Produk -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900">Daftar Produk</h2>
                <span class="text-xs bg-gray-100 text-gray-700 px-2.5 py-0.5 rounded-full font-bold">
                    {{ $produks->total() }} Produk
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-gray-600 font-semibold text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3.5">Produk</th>
                            <th class="px-6 py-3.5">Toko</th>
                            <th class="px-6 py-3.5 text-right">Harga Modal (HPP)</th>
                            <th class="px-6 py-3.5 text-right">Harga Jual</th>
                            <th class="px-6 py-3.5 text-right">Margin / Laba</th>
                            <th class="px-6 py-3.5 text-center">Stok</th>
                            <th class="px-6 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($produks as $p)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $p->nama_produk }}</div>
                                    @if($p->kode_produk)
                                        <div class="text-[11px] font-mono text-gray-400 mt-0.5">Kode: {{ $p->kode_produk }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($p->toko === 'kantin_1')
                                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">Kantin 1</span>
                                    @elseif($p->toko === 'kantin_2')
                                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-blue-100 text-blue-800">Kantin 2</span>
                                    @else
                                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-amber-100 text-amber-800">Aqumart</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-gray-600">
                                    Rp {{ number_format($p->harga_modal, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-bold text-gray-900">
                                    Rp {{ number_format($p->harga_jual, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-xs font-bold text-emerald-600">
                                    +Rp {{ number_format($p->margin, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($p->stok <= 0)
                                        <span class="px-2 py-0.5 text-xs font-bold rounded bg-red-100 text-red-700">Habis</span>
                                    @elseif($p->stok <= 5)
                                        <span class="px-2 py-0.5 text-xs font-bold rounded bg-amber-100 text-amber-800">{{ $p->stok }} (Kritis)</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-semibold text-gray-800">{{ $p->stok }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('admin.kantin.produk.edit', $p->id) }}"
                                           class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition" title="Edit Produk">
                                            <i class="ti ti-edit text-lg"></i>
                                        </a>
                                        <button type="button" onclick="confirmDeleteProduk({{ $p->id }}, '{{ addslashes($p->nama_produk) }}')"
                                                class="p-1.5 text-red-600 hover:bg-red-50 rounded transition cursor-pointer" title="Hapus Produk">
                                            <i class="ti ti-trash text-lg"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <i class="ti ti-package-off text-3xl text-gray-400 mb-2 block"></i>
                                    Belum ada produk di toko ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($produks->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $produks->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

<form id="deleteProdukForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    function confirmDeleteProduk(id, name) {
        if (confirm("Hapus produk '" + name + "'?")) {
            const form = document.getElementById('deleteProdukForm');
            form.action = "{{ url('/admin/kantin/produk') }}/" + id;
            form.submit();
        }
    }
</script>
@endsection
