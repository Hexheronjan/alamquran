@extends('layouts.admin.app')

@section('title', 'Input Manual Uang Saku')

@section('content')
<div class="bg-gray-100 min-h-screen">
    <div class="w-full py-6 px-4 lg:px-8">

        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('admin.uang_saku.index') }}" class="text-teal-600 hover:text-teal-800 font-semibold mb-3 inline-flex items-center text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Uang Saku
            </a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">Input Manual Uang Saku</h1>
            <p class="text-gray-500 text-sm mt-1">Untuk mencatat transaksi santri yang <strong>belum terdaftar</strong> di sistem PPDB.</p>
        </div>

        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-4 rounded-lg mb-6 shadow-sm flex items-center gap-2 font-semibold">
            <i class="fas fa-check-circle text-green-600 text-lg"></i>
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-4 rounded-lg mb-6 shadow-sm">
            <div class="font-bold flex items-center gap-2 mb-1"><i class="fas fa-exclamation-circle text-red-600"></i> Terjadi Kesalahan:</div>
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div class="bg-white rounded-2xl shadow border border-gray-200 p-6 max-w-2xl mx-auto">
            <h2 class="text-base font-bold text-gray-800 mb-5 border-b pb-3 flex items-center gap-2">
                <i class="fas fa-edit text-teal-600"></i> Form Input Transaksi
            </h2>

            <form action="{{ route('admin.uang_saku.storeManual') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Nama Santri (Input Bebas) -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">
                        Nama Santri <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_santri" required
                        value="{{ old('nama_santri') }}"
                        placeholder="Ketik nama santri..."
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                    <p class="text-xs text-gray-400 mt-1"><i class="fas fa-info-circle"></i> Ketik nama santri secara manual, tidak perlu terdaftar di sistem</p>
                </div>

                <!-- Nomor WA Orang Tua / Wali -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">
                        Nomor WhatsApp Orang Tua / Wali <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="no_wa_wali" required
                        value="{{ old('no_wa_wali') }}"
                        placeholder="Contoh: 081234567890"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                    <p class="text-xs text-gray-400 mt-1"><i class="fas fa-info-circle"></i> Digunakan untuk mengirim notifikasi WhatsApp otomatis saat saldo limit</p>
                </div>

                <!-- Grid: Kelas & Tahun Ajaran -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Kelas</label>
                        <select name="kelas"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                            <option value="">-- Pilih Kelas --</option>
                            <option value="1" {{ in_array(old('kelas'), ['1', 'Kelas 1', 'Kelas 1 MTs']) ? 'selected' : '' }}>Kelas 1</option>
                            <option value="2" {{ in_array(old('kelas'), ['2', 'Kelas 2', 'Kelas 2 MTs']) ? 'selected' : '' }}>Kelas 2</option>
                            <option value="3" {{ in_array(old('kelas'), ['3', 'Kelas 3', 'Kelas 3 MTs']) ? 'selected' : '' }}>Kelas 3</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran"
                            value="{{ old('tahun_ajaran') }}" placeholder="Contoh: 2026/2027"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <!-- Tanggal -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Tanggal Transaksi <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" required
                        value="{{ old('tanggal', date('Y-m-d')) }}"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                </div>

                <!-- Nominal -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Nominal (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="jumlah" required min="500" step="500"
                        value="{{ old('jumlah') }}" placeholder="Contoh: 50000"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                </div>

                <!-- Keterangan -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Keterangan <span class="text-red-500">*</span></label>
                    <input type="text" name="keterangan" required
                        value="{{ old('keterangan') }}"
                        placeholder="Contoh: Titip uang jajan dari Ibu"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" style="background-color: #0d9488; color: #ffffff;"
                        class="flex-1 sm:flex-none px-6 py-2.5 rounded-lg text-sm font-bold shadow hover:opacity-90 transition flex items-center justify-center gap-2 border-0 cursor-pointer">
                        <i class="fas fa-save"></i> Simpan Transaksi
                    </button>
                    <a href="{{ route('admin.uang_saku.index') }}"
                        class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection
