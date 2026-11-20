@extends('layouts.admin.app')

@section('title', 'Edit Data Alumni')

@section('content')
<div class="bg-gray-100 min-h-screen">
    <div class="w-full py-6 px-4 lg:px-8 max-w-4xl mx-auto">

        <!-- Back Link & Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <a href="{{ route('admin.alumni.index') }}" class="text-xs text-purple-600 hover:text-purple-700 font-bold inline-flex items-center gap-1 mb-2">
                    <i class="ti ti-arrow-left"></i> Kembali ke Buku Alumni
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Edit Data Alumni: {{ $alumni->nama_lengkap }}</h1>
                <p class="text-gray-600 text-xs mt-0.5">Perbarui data kelulusan santri dan informasi wali.</p>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-5 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm">
                <h4 class="text-xs font-bold text-red-800 mb-1">Ada kesalahan input:</h4>
                <ul class="list-disc list-inside text-xs text-red-700 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.alumni.update', $alumni->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section Data Alumni -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <div class="border-b border-gray-100 pb-3 flex items-center gap-2">
                    <i class="ti ti-school text-purple-600 text-lg"></i>
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Data Santri / Alumni</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Nama Lengkap Santri <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $alumni->nama_lengkap) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">NIS (Nomor Induk Santri)</label>
                        <input type="text" name="nis" value="{{ old('nis', $alumni->nis) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">NISN (10 Digit)</label>
                        <input type="text" name="nisn" value="{{ old('nisn', $alumni->nisn) }}" maxlength="10"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tahun Masuk</label>
                        <input type="number" name="tahun_masuk" value="{{ old('tahun_masuk', $alumni->tahun_masuk) }}" min="2000" max="2099"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Tahun Kelulusan <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="tahun_lulus" value="{{ old('tahun_lulus', $alumni->tahun_lulus) }}" min="2000" max="2099" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 font-bold text-purple-700">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Angkatan (Opsional)</label>
                        <select name="angkatan_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                            <option value="">-- Pilih Angkatan --</option>
                            @foreach($angkatanList as $angkatan)
                                <option value="{{ $angkatan->id }}" {{ old('angkatan_id', $alumni->angkatan_id) == $angkatan->id ? 'selected' : '' }}>
                                    Angkatan {{ $angkatan->nomor_angkatan }} ({{ $angkatan->tahun_ajaran }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $alumni->tempat_lahir) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $alumni->tanggal_lahir?->format('Y-m-d')) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
            </div>

            <!-- Section Data Wali -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <div class="border-b border-gray-100 pb-3 flex items-center gap-2">
                    <i class="ti ti-user-circle text-purple-600 text-lg"></i>
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Data Orang Tua / Wali</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Ayah / Wali</label>
                        <input type="text" name="nama_ayah" value="{{ old('nama_ayah', $alumni->orangTua?->nama_ayah) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nomor WhatsApp Wali</label>
                        <input type="text" name="no_wa_ayah" value="{{ old('no_wa_ayah', $alumni->orangTua?->no_wa_ayah) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Alamat Domisili</label>
                        <input type="text" name="alamat" value="{{ old('alamat', $alumni->orangTua?->alamat_domisili_ayah) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.alumni.index') }}" class="px-5 py-2.5 bg-gray-700 hover:bg-gray-800 text-white text-sm font-bold rounded-lg transition" style="background-color: #374151 !important; color: #ffffff !important;">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg transition shadow-sm flex items-center gap-1.5 cursor-pointer" style="background-color: #16a34a !important; color: #ffffff !important;">
                    <i class="ti ti-device-floppy text-base"></i> Simpan Perubahan
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
