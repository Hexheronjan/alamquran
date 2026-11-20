@extends('layouts.admin.app')

@section('title', 'Tambah Santri oleh Admin')

@section('content')
<div class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto py-6 px-4 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('admin.santri.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-teal-700 hover:text-teal-900">
                <i class="fas fa-arrow-left"></i> Kembali ke Data Santri Baru
            </a>
            <h1 class="mt-4 text-2xl font-bold text-gray-900">Tambah Santri oleh Admin</h1>
            <p class="mt-1 text-sm text-gray-600">Data ini langsung masuk ke Data Santri sesuai angkatan yang dipilih. Periode PPDB tidak digunakan.</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-xl border-l-4 border-red-500 bg-red-50 p-4 text-sm text-red-800">
                <p class="font-bold">Data belum dapat disimpan.</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.santri.store') }}" class="space-y-6">
            @csrf

            <section class="rounded-2xl bg-white p-6 shadow-sm border border-gray-200">
                <h2 class="flex items-center gap-2 text-lg font-bold text-gray-900"><i class="fas fa-calendar-alt text-teal-600"></i> Penempatan Data</h2>
                <div class="mt-5">
                    <label for="angkatan_id" class="mb-2 block text-sm font-bold text-gray-700">Angkatan / Tahun Masuk <span class="text-red-600">*</span></label>
                    <select id="angkatan_id" name="angkatan_id" required class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="">-- Pilih angkatan untuk data ini --</option>
                        @foreach($angkatanList as $angkatan)
                            <option value="{{ $angkatan->id }}" @selected(old('angkatan_id') == $angkatan->id)>
                                {{ $angkatan->nama_angkatan }} — Tahun Masuk {{ $angkatan->tahun_angkatan ?? $angkatan->year }} ({{ $angkatan->tahun_ajaran }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-gray-500">Santri akan tampil pada kelompok Tahun Masuk dari angkatan yang dipilih.</p>
                </div>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm border border-gray-200">
                <h2 class="flex items-center gap-2 text-lg font-bold text-gray-900"><i class="fas fa-user-graduate text-teal-600"></i> Data Santri</h2>
                <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="nama_lengkap" class="mb-2 block text-sm font-bold text-gray-700">Nama lengkap <span class="text-red-600">*</span></label>
                        <input id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required maxlength="255" class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Nama santri">
                    </div>
                    <div>
                        <label for="kelas" class="mb-2 block text-sm font-bold text-gray-700">Kelas <span class="text-red-600">*</span></label>
                        <select id="kelas" name="kelas" required class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                            @foreach(['1', '2', '3'] as $kelas)
                                <option value="{{ $kelas }}" @selected(old('kelas', '1') === $kelas)>Kelas {{ $kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="tanggal_lahir" class="mb-2 block text-sm font-bold text-gray-700">Tanggal lahir</label>
                        <input id="tanggal_lahir" type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                    </div>
                    <div>
                        <label for="tempat_lahir" class="mb-2 block text-sm font-bold text-gray-700">Tempat lahir</label>
                        <input id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir') }}" class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                    </div>
                    <div>
                        <label for="sekolah_asal" class="mb-2 block text-sm font-bold text-gray-700">Sekolah asal</label>
                        <input id="sekolah_asal" name="sekolah_asal" value="{{ old('sekolah_asal') }}" class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                    </div>
                    <div>
                        <label for="nik" class="mb-2 block text-sm font-bold text-gray-700">NIK</label>
                        <input id="nik" name="nik" value="{{ old('nik') }}" inputmode="numeric" maxlength="16" class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                    </div>
                    <div>
                        <label for="nisn" class="mb-2 block text-sm font-bold text-gray-700">NISN</label>
                        <input id="nisn" name="nisn" value="{{ old('nisn') }}" inputmode="numeric" maxlength="10" class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                    </div>
                    <div>
                        <label for="npsn" class="mb-2 block text-sm font-bold text-gray-700">NPSN sekolah asal</label>
                        <input id="npsn" name="npsn" value="{{ old('npsn') }}" maxlength="20" class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">
                    </div>
                </div>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm border border-gray-200">
                <h2 class="flex items-center gap-2 text-lg font-bold text-gray-900"><i class="fab fa-whatsapp text-green-600"></i> Wali Santri</h2>
                <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label for="nama_ayah" class="mb-2 block text-sm font-bold text-gray-700">Nama wali / ayah <span class="text-red-600">*</span></label>
                        <input id="nama_ayah" name="nama_ayah" value="{{ old('nama_ayah') }}" required maxlength="255" class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Nama wali yang bertanggung jawab">
                    </div>
                    <div>
                        <label for="no_wa_ayah" class="mb-2 block text-sm font-bold text-gray-700">Nomor WhatsApp wali <span class="text-red-600">*</span></label>
                        <input id="no_wa_ayah" name="no_wa_ayah" value="{{ old('no_wa_ayah') }}" required inputmode="tel" maxlength="20" class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Contoh: 081234567890">
                    </div>
                    <div class="md:col-span-2">
                        <label for="alamat_domisili_ayah" class="mb-2 block text-sm font-bold text-gray-700">Alamat</label>
                        <textarea id="alamat_domisili_ayah" name="alamat_domisili_ayah" rows="3" class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500">{{ old('alamat_domisili_ayah') }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label for="catatan" class="mb-2 block text-sm font-bold text-gray-700">Catatan admin</label>
                        <textarea id="catatan" name="catatan" rows="2" maxlength="1000" class="w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Opsional">{{ old('catatan') }}</textarea>
                    </div>
                </div>
                <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    <i class="fas fa-info-circle mr-1"></i> Menyimpan form ini tidak membuat akun wali dan tidak mengirim WhatsApp. Setelah tersimpan, gunakan tombol <strong>Generate Akun</strong> di daftar santri bila diperlukan.
                </div>
            </section>

            <div class="flex flex-col-reverse gap-3 pb-8 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.santri.index') }}" class="rounded-lg bg-gray-200 px-5 py-3 text-center text-sm font-bold text-gray-700 hover:bg-gray-300">Batal</a>
                <button type="submit" class="rounded-lg bg-teal-600 px-5 py-3 text-sm font-bold text-white shadow hover:bg-teal-700"><i class="fas fa-save mr-2"></i>Simpan ke Data Santri</button>
            </div>
        </form>
    </div>
</div>
@endsection
