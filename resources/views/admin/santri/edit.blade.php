@extends('layouts.admin.app')

@section('title', 'Edit Santri')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('admin.santri.show', $santri->id) }}" class="text-teal-600 hover:text-teal-800 mb-4 inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Detail
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Edit Data Santri</h1>
        </div>

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.santri.update', $santri->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- ===== DATA CALON SANTRI ===== --}}
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center border-b pb-2">
                    <i class="fas fa-user-graduate text-teal-500 mr-2"></i> Data Calon Santri
                </h2>

                <div class="mb-6 flex items-center gap-5 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <div class="w-20 h-20 rounded-xl overflow-hidden bg-teal-100 flex items-center justify-center border-2 border-teal-500/20 flex-shrink-0">
                        @if($santri->foto_santri)
                            <img src="{{ asset('storage/' . $santri->foto_santri) }}" alt="{{ $santri->nama_lengkap }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-user-graduate text-teal-600 text-3xl"></i>
                        @endif
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-gray-800 mb-1">Pas Foto Santri</label>
                        <input type="file" name="foto_santri" accept="image/*"
                            class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer">
                        <p class="text-xs text-gray-500 mt-1">Format: JPG, JPEG, PNG (Maksimal 5MB). Biarkan kosong jika tidak ingin mengubah foto.</p>
                        @error('foto_santri')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $santri->nama_lengkap) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('nama_lengkap') border-red-500 @enderror">
                        @error('nama_lengkap')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir <span class="text-red-500">*</span></label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $santri->tempat_lahir) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('tempat_lahir') border-red-500 @enderror">
                        @error('tempat_lahir')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', optional($santri->tanggal_lahir)->format('Y-m-d')) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('tanggal_lahir') border-red-500 @enderror">
                        @error('tanggal_lahir')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIK (16 Digit) <span class="text-red-500">*</span></label>
                        <input type="text" name="nik" value="{{ old('nik', $santri->nik) }}" maxlength="16"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('nik') border-red-500 @enderror">
                        @error('nik')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sekolah Asal <span class="text-red-500">*</span></label>
                        <input type="text" name="sekolah_asal" value="{{ old('sekolah_asal', $santri->sekolah_asal) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('sekolah_asal') border-red-500 @enderror">
                        @error('sekolah_asal')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NPSN <span class="text-red-500">*</span></label>
                        <input type="text" name="npsn" value="{{ old('npsn', $santri->npsn) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('npsn') border-red-500 @enderror">
                        @error('npsn')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NSM <span class="text-gray-500 text-xs">(Wajib jika MI)</span></label>
                        <input type="text" name="nsm" value="{{ old('nsm', $santri->nsm) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('nsm') border-red-500 @enderror">
                        @error('nsm')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NISN (10 Digit) <span class="text-red-500">*</span></label>
                        <input type="text" name="nisn" value="{{ old('nisn', $santri->nisn) }}" maxlength="10"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('nisn') border-red-500 @enderror">
                        @error('nisn')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Anak Ke- <span class="text-red-500">*</span></label>
                        <input type="number" name="anak_ke" value="{{ old('anak_ke', $santri->anak_ke) }}" min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('anak_ke') border-red-500 @enderror">
                        @error('anak_ke')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dari ... Bersaudara <span class="text-red-500">*</span></label>
                        <input type="number" name="dari_bersaudara" value="{{ old('dari_bersaudara', $santri->dari_bersaudara) }}" min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('dari_bersaudara') border-red-500 @enderror">
                        @error('dari_bersaudara')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hobi</label>
                        <input type="text" name="hobi" value="{{ old('hobi', $santri->hobi) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('status') border-red-500 @enderror">
                            <option value="menunggu_verifikasi" {{ old('status', $santri->status) == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                            <option value="diverifikasi" {{ old('status', $santri->status) == 'diverifikasi' ? 'selected' : '' }}>Diverifikasi</option>
                            <option value="diterima" {{ old('status', $santri->status) == 'diterima' ? 'selected' : '' }}>Diterima</option>
                            <option value="ditolak" {{ old('status', $santri->status) == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                        @error('status')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prestasi Akademik & Non Akademik</label>
                        <textarea name="prestasi" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent">{{ old('prestasi', $santri->prestasi) }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Riwayat Penyakit / Alergi</label>
                        <textarea name="riwayat_penyakit" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent">{{ old('riwayat_penyakit', $santri->riwayat_penyakit) }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ekstrakurikuler</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach(['Hadroh', 'Voli', 'Futsal', 'Seni Tilawah'] as $ekskul)
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="ekstrakurikuler[]" value="{{ $ekskul }}"
                                    {{ is_array($santri->ekstrakurikuler) && in_array($ekskul, $santri->ekstrakurikuler) ? 'checked' : '' }}
                                    class="w-4 h-4 text-teal-500 rounded">
                                <span class="text-sm text-gray-700">{{ $ekskul }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tujuan Mendaftar <span class="text-red-500">*</span></label>
                        <textarea name="tujuan_mendaftar" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('tujuan_mendaftar') border-red-500 @enderror">{{ old('tujuan_mendaftar', $santri->tujuan_mendaftar) }}</textarea>
                        @error('tujuan_mendaftar')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            @if($santri->orangTua)
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center border-b pb-2">
                    <i class="fas fa-user text-blue-500 mr-2"></i> Data Ayah
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ayah <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_ayah" value="{{ old('nama_ayah', $santri->orangTua->nama_ayah) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('nama_ayah') border-red-500 @enderror">
                        @error('nama_ayah')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp <span class="text-red-500">*</span></label>
                        <input type="text" name="no_wa_ayah" value="{{ old('no_wa_ayah', $santri->orangTua->no_wa_ayah) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('no_wa_ayah') border-red-500 @enderror"
                            placeholder="628xxxxxxxxxx">
                        @error('no_wa_ayah')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor KK (16 Digit) <span class="text-red-500">*</span></label>
                        <input type="text" name="no_kk" value="{{ old('no_kk', $santri->orangTua->no_kk) }}" maxlength="16"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('no_kk') border-red-500 @enderror">
                        @error('no_kk')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIK Ayah (16 Digit) <span class="text-red-500">*</span></label>
                        <input type="text" name="nik_ayah" value="{{ old('nik_ayah', $santri->orangTua->nik_ayah) }}" maxlength="16"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('nik_ayah') border-red-500 @enderror">
                        @error('nik_ayah')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir Ayah <span class="text-red-500">*</span></label>
                        <input type="text" name="tempat_lahir_ayah" value="{{ old('tempat_lahir_ayah', $santri->orangTua->tempat_lahir_ayah) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('tempat_lahir_ayah') border-red-500 @enderror">
                        @error('tempat_lahir_ayah')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir Ayah <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_lahir_ayah" value="{{ old('tanggal_lahir_ayah', optional($santri->orangTua->tanggal_lahir_ayah)->format('Y-m-d')) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('tanggal_lahir_ayah') border-red-500 @enderror">
                        @error('tanggal_lahir_ayah')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan Ayah <span class="text-red-500">*</span></label>
                        <input type="text" name="pekerjaan_ayah" value="{{ old('pekerjaan_ayah', $santri->orangTua->pekerjaan_ayah) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('pekerjaan_ayah') border-red-500 @enderror">
                        @error('pekerjaan_ayah')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Penghasilan Bulanan Ayah <span class="text-red-500">*</span></label>
                        <select name="penghasilan_ayah" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('penghasilan_ayah') border-red-500 @enderror">
                            <option value="">Pilih penghasilan</option>
                            @foreach(['Rp500.000–Rp1.000.000', 'Rp1.000.000–Rp2.000.000', 'Rp2.000.000–Rp5.000.000', 'Lainnya'] as $p)
                            <option value="{{ $p }}" {{ old('penghasilan_ayah', $santri->orangTua->penghasilan_ayah) == $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                        @error('penghasilan_ayah')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Domisili Ayah <span class="text-red-500">*</span></label>
                        <textarea name="alamat_domisili_ayah" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('alamat_domisili_ayah') border-red-500 @enderror">{{ old('alamat_domisili_ayah', $santri->orangTua->alamat_domisili_ayah) }}</textarea>
                        @error('alamat_domisili_ayah')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            {{-- ===== DATA IBU ===== --}}
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center border-b pb-2">
                    <i class="fas fa-user text-pink-500 mr-2"></i> Data Ibu
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ibu <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_ibu" value="{{ old('nama_ibu', $santri->orangTua->nama_ibu) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('nama_ibu') border-red-500 @enderror">
                        @error('nama_ibu')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIK Ibu (16 Digit) <span class="text-red-500">*</span></label>
                        <input type="text" name="nik_ibu" value="{{ old('nik_ibu', $santri->orangTua->nik_ibu) }}" maxlength="16"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('nik_ibu') border-red-500 @enderror">
                        @error('nik_ibu')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir Ibu <span class="text-red-500">*</span></label>
                        <input type="text" name="tempat_lahir_ibu" value="{{ old('tempat_lahir_ibu', $santri->orangTua->tempat_lahir_ibu) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('tempat_lahir_ibu') border-red-500 @enderror">
                        @error('tempat_lahir_ibu')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir Ibu <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_lahir_ibu" value="{{ old('tanggal_lahir_ibu', optional($santri->orangTua->tanggal_lahir_ibu)->format('Y-m-d')) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('tanggal_lahir_ibu') border-red-500 @enderror">
                        @error('tanggal_lahir_ibu')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan Ibu <span class="text-red-500">*</span></label>
                        <input type="text" name="pekerjaan_ibu" value="{{ old('pekerjaan_ibu', $santri->orangTua->pekerjaan_ibu) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('pekerjaan_ibu') border-red-500 @enderror">
                        @error('pekerjaan_ibu')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Penghasilan Bulanan Ibu <span class="text-red-500">*</span></label>
                        <select name="penghasilan_ibu" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('penghasilan_ibu') border-red-500 @enderror">
                            <option value="">Pilih penghasilan</option>
                            @foreach(['Rp500.000–Rp1.000.000', 'Rp1.000.000–Rp2.000.000', 'Rp2.000.000–Rp5.000.000', 'Lainnya'] as $p)
                            <option value="{{ $p }}" {{ old('penghasilan_ibu', $santri->orangTua->penghasilan_ibu) == $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                        @error('penghasilan_ibu')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Domisili Ibu <span class="text-red-500">*</span></label>
                        <textarea name="alamat_domisili_ibu" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('alamat_domisili_ibu') border-red-500 @enderror">{{ old('alamat_domisili_ibu', $santri->orangTua->alamat_domisili_ibu) }}</textarea>
                        @error('alamat_domisili_ibu')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
            @endif

            {{-- ===== ACTION BUTTONS ===== --}}
            <div class="bg-white rounded-lg shadow p-6 flex justify-end gap-3">
                <a href="{{ route('admin.santri.show', $santri->id) }}"
                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
