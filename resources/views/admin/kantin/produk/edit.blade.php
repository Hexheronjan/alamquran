@extends('layouts.admin.app')

@section('title', 'Edit Produk')

@section('content')
<div class="bg-gray-100 min-h-screen">
    <div class="w-full py-6 px-4 lg:px-8 max-w-3xl mx-auto">

        <div class="mb-6">
            <a href="{{ route('admin.kantin.produk.index', ['toko' => $produk->toko]) }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-bold inline-flex items-center gap-1 mb-2">
                <i class="ti ti-arrow-left"></i> Kembali ke Katalog Produk
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Edit Produk: {{ $produk->nama_produk }}</h1>
            <p class="text-gray-600 text-xs mt-0.5">Perbarui informasi harga, stok, dan deskripsi produk.</p>
        </div>

        @if($errors->any())
            <div class="mb-5 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm">
                <ul class="list-disc list-inside text-xs text-red-700 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.kantin.produk.update', $produk->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Pilih Toko / Lokasi <span class="text-red-500">*</span></label>
                    <select name="toko" id="tokoSelect" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                        <option value="kantin_1" {{ old('toko', $produk->toko) == 'kantin_1' ? 'selected' : '' }}>Kantin 1 (Makanan/Minuman Berat)</option>
                        <option value="kantin_2" {{ old('toko', $produk->toko) == 'kantin_2' ? 'selected' : '' }}>Kantin 2 (Snack/Minuman Ringan)</option>
                        <option value="aqumart" {{ old('toko', $produk->toko) == 'aqumart' ? 'selected' : '' }}>Minimarket Aqumart (Kebutuhan & Kitab)</option>
                    </select>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-gray-700 uppercase">Kode / Barcode Produk</label>
                        <button type="button" id="btnGenerateKode" class="text-[11px] text-emerald-600 hover:text-emerald-800 font-bold inline-flex items-center gap-1 cursor-pointer">
                            <i class="ti ti-refresh"></i> Generate Kode Baru
                        </button>
                    </div>
                    <input type="text" name="kode_produk" id="kodeProdukInput" value="{{ old('kode_produk', $produk->kode_produk) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono font-bold text-gray-800 focus:ring-2 focus:ring-emerald-500">
                    <p class="text-[11px] text-gray-500 mt-1">
                        <i class="ti ti-info-circle text-emerald-600"></i> Bisa menggunakan barcode fisik kemasan pabrik atau kode otomatis sistem.
                    </p>
                </div>

                <div class="md:col-span-2">
                    <label class="block font-bold text-gray-700 uppercase mb-1">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_produk" value="{{ old('nama_produk', $produk->nama_produk) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 font-bold text-gray-900">
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Harga Modal / HPP (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="harga_modal" value="{{ old('harga_modal', (int)$produk->harga_modal) }}" min="0" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Harga Jual Kasir (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="harga_jual" value="{{ old('harga_jual', (int)$produk->harga_jual) }}" min="0" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono font-bold text-emerald-700 focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Jumlah Stok <span class="text-red-500">*</span></label>
                    <input type="number" name="stok" value="{{ old('stok', $produk->stok) }}" min="0" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="flex items-center gap-2 pt-5">
                    <input type="checkbox" name="is_active" id="isActive" value="1" {{ $produk->is_active ? 'checked' : '' }}
                           class="w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500">
                    <label for="isActive" class="font-bold text-gray-700">Produk Aktif Dijual</label>
                </div>

                <div class="md:col-span-2">
                    <label class="block font-bold text-gray-700 uppercase mb-1">Deskripsi Produk</label>
                    <textarea name="deskripsi" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-2">
                <a href="{{ route('admin.kantin.produk.index', ['toko' => $produk->toko]) }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white rounded-lg text-sm font-bold" style="background-color: #374151 !important; color: #ffffff !important;">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-sm transition shadow-sm cursor-pointer flex items-center gap-1.5" style="background-color: #16a34a !important; color: #ffffff !important;">
                    <i class="ti ti-device-floppy text-base"></i> Simpan Perubahan Produk
                </button>
            </div>
        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tokoSelect = document.getElementById('tokoSelect');
    const kodeInput = document.getElementById('kodeProdukInput');
    const btnGenerate = document.getElementById('btnGenerateKode');

    async function fetchKode(toko) {
        try {
            if (btnGenerate) {
                btnGenerate.disabled = true;
                btnGenerate.classList.add('opacity-50');
            }
            const res = await fetch(`{{ route('admin.kantin.produk.generateKode') }}?toko=${encodeURIComponent(toko)}`);
            const data = await res.json();
            if (data && data.kode) {
                kodeInput.value = data.kode;
            }
        } catch (e) {
            console.error('Gagal generate kode produk:', e);
        } finally {
            if (btnGenerate) {
                btnGenerate.disabled = false;
                btnGenerate.classList.remove('opacity-50');
            }
        }
    }

    if (btnGenerate) {
        btnGenerate.addEventListener('click', function() {
            fetchKode(tokoSelect.value);
        });
    }
});
</script>
@endsection
