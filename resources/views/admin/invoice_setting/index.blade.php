@extends('layouts.admin.app')

@section('content')
<div class="container full-container py-5 flex flex-col gap-6">
    <div class="mb-5 my-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-3xl font-semibold text-gray-800">Pengaturan Kop & Template Invoice</h2>
            <p class="text-sm text-gray-500 mt-1">Sesuaikan kop surat, logo, nomor kontak, serta penandatangan pada kuitansi SPP, Daftar Ulang, dan Uang Saku.</p>
        </div>
        <nav>
            <ol class="flex items-center gap-2">
                <li>
                    <a class="font-medium text-sm text-gray-500 hover:text-blue-600" href="{{ route('admin.dashboard') }}">Dashboard /</a>
                </li>
                <li class="font-medium text-primary text-sm">Pengaturan Invoice</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3 text-sm">
            <i class="ti ti-check text-xl text-emerald-600"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl flex items-center gap-3 text-sm">
            <i class="ti ti-alert-triangle text-xl text-rose-600"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <!-- Card Tarik Data Madrasah -->
    <div class="bg-blue-50/70 border border-blue-200 rounded-2xl p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="p-2.5 bg-blue-600 text-white rounded-xl shadow-xs">
                    <i class="ti ti-refresh text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-sm">Tarik / Sinkronisasi Data dari Informasi Madrasah</h3>
                    <p class="text-xs text-gray-600 mt-0.5">Ingin mengisi otomatis nama, alamat, telepon dari data madrasah? Klik tombol di samping.</p>
                </div>
            </div>
            <form action="{{ route('admin.invoice_setting.copy_school') }}" method="POST" class="flex items-center gap-2" onsubmit="return confirm('Apakah Anda yakin ingin menimpa data kop invoice dengan data madrasah?')">
                @csrf
                @if($schools->count() > 1)
                    <select name="school_id" class="text-xs border border-gray-300 rounded-lg px-3 py-2 bg-white focus:ring-blue-500 focus:border-blue-500">
                        @foreach($schools as $sch)
                            <option value="{{ $sch->id }}">{{ $sch->name }}</option>
                        @endforeach
                    </select>
                @endif
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                    <i class="ti ti-download"></i> Tarik Data Madrasah
                </button>
            </form>
        </div>
    </div>

    <!-- Live Preview Kop Surat -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <i class="ti ti-eye text-teal-600 text-xl"></i>
                <h3 class="font-bold text-gray-900 text-base">Pratinjau Tampilan Kop Invoice</h3>
            </div>
            <span class="text-xs text-gray-400">Pratinjau otomatis sesuai data di bawah</span>
        </div>

        <div class="p-5 border-2 border-dashed border-teal-200 rounded-xl bg-teal-50/20">
            <div class="flex items-center gap-5 border-b-2 border-teal-600 pb-4">
                <div class="shrink-0">
                    @if($setting->logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($setting->logo))
                        <img src="{{ asset('storage/' . $setting->logo) }}" alt="Logo Kop" class="h-16 w-16 object-contain rounded-md border border-gray-200 bg-white p-1">
                    @else
                        <img src="{{ asset('assets/images/logos/icon-logo-ppdb.png') }}" alt="Logo Default" class="h-16 w-16 object-contain rounded-md border border-gray-200 bg-white p-1">
                    @endif
                </div>
                <div class="flex-1">
                    <h1 class="text-lg font-black text-teal-800 uppercase tracking-wide leading-tight">
                        {{ $setting->nama_lembaga }}
                    </h1>
                    <p class="text-xs font-semibold text-gray-700 mt-0.5">
                        {{ $setting->tagline_kop ?: 'Lembaga Pendidikan & Pembinaan Penghafal Al-Qur\'an' }}
                    </p>
                    <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">
                        {{ $setting->alamat }}
                        @if($setting->telepon) · Telp/WA: <span class="font-medium text-gray-700">{{ $setting->telepon }}</span> @endif
                        @if($setting->email) · Email: <span class="font-medium text-gray-700">{{ $setting->email }}</span> @endif
                        @if($setting->website) · Web: <span class="font-medium text-teal-700">{{ $setting->website }}</span> @endif
                    </p>
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-gray-700">Contoh Label Status:</span>
                    <span class="px-2.5 py-0.5 rounded font-black text-[10px] bg-emerald-100 text-emerald-800 border border-emerald-300">L U N A S</span>
                    <span class="text-[11px] text-gray-400 italic">(Posisi stamp berada di bawah garis kop, tidak menutupi kop)</span>
                </div>
                <div class="text-[11px]">
                    Penandatangan: <strong class="text-gray-700">{{ $setting->nama_bendahara ?: 'Bendahara Pesantren' }}</strong> ({{ $setting->jabatan_penandatangan ?: 'Bendahara Pesantren' }})
                </div>
            </div>
        </div>
    </div>

    <!-- Form Pengaturan -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.invoice_setting.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Lembaga -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Nama Lembaga / Pondok Pesantren <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_lembaga" value="{{ old('nama_lembaga', $setting->nama_lembaga) }}" required
                           class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-teal-500 focus:border-teal-500 @error('nama_lembaga') border-red-500 @enderror"
                           placeholder="Contoh: Pesantren Tahfizh Alam Qur'an">
                    @error('nama_lembaga')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tagline Kop -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Sub-Judul / Tagline Kop Surat
                    </label>
                    <input type="text" name="tagline_kop" value="{{ old('tagline_kop', $setting->tagline_kop) }}"
                           class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-teal-500 focus:border-teal-500"
                           placeholder="Contoh: Lembaga Pendidikan & Pembinaan Penghafal Al-Qur'an">
                </div>

                <!-- Alamat Lengkap -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Alamat Lengkap Lembaga
                    </label>
                    <textarea name="alamat" rows="2"
                              class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-teal-500 focus:border-teal-500"
                              placeholder="Contoh: JL. Pandanarum - Winong - Jetis - Ponorogo, Jawa Timur">{{ old('alamat', $setting->alamat) }}</textarea>
                </div>

                <!-- Telepon / WhatsApp -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Nomor Telepon / WhatsApp
                    </label>
                    <input type="text" name="telepon" value="{{ old('telepon', $setting->telepon) }}"
                           class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-teal-500 focus:border-teal-500"
                           placeholder="087756563507 / 085853410025">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Email Resmi
                    </label>
                    <input type="email" name="email" value="{{ old('email', $setting->email) }}"
                           class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-teal-500 focus:border-teal-500"
                           placeholder="info@alamquran.sch.id">
                </div>

                <!-- Website -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Website Lembaga
                    </label>
                    <input type="text" name="website" value="{{ old('website', $setting->website) }}"
                           class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-teal-500 focus:border-teal-500"
                           placeholder="alamquran.sch.id">
                </div>

                <!-- Upload Logo -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Logo Kop Invoice (Opsional)
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="file" name="logo" accept="image/*"
                               class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 border border-gray-300 rounded-xl">
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Format: JPG, PNG, WebP. Maks 2MB. Disarankan rasio kotak / transparan.</p>
                </div>

                <!-- Bagian Penandatangan -->
                <div class="md:col-span-2 pt-4 border-t border-gray-100">
                    <h4 class="font-bold text-gray-800 text-sm mb-3 flex items-center gap-1.5">
                        <i class="ti ti-signature text-teal-600"></i> Informasi Penandatangan Dokumen
                    </h4>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Nama Bendahara / Penandatangan
                    </label>
                    <input type="text" name="nama_bendahara" value="{{ old('nama_bendahara', $setting->nama_bendahara) }}"
                           class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-teal-500 focus:border-teal-500"
                           placeholder="Contoh: Ustadzah Nurul Hidayah, S.Pd.">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Jabatan Penandatangan
                    </label>
                    <input type="text" name="jabatan_penandatangan" value="{{ old('jabatan_penandatangan', $setting->jabatan_penandatangan) }}"
                           class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-teal-500 focus:border-teal-500"
                           placeholder="Contoh: Bendahara Pesantren / Pengelola Keuangan">
                </div>

                <!-- Catatan Bawah -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Catatan Bawah / Footer Dokumen
                    </label>
                    <textarea name="catatan_bawah" rows="2"
                              class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-teal-500 focus:border-teal-500"
                              placeholder="Simpan tanda bukti pembayaran ini sebagai bukti transaksi resmi.">{{ old('catatan_bawah', $setting->catatan_bawah) }}</textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl transition shadow-sm inline-flex items-center gap-2 cursor-pointer text-sm">
                    <i class="ti ti-device-floppy"></i> Simpan Pengaturan Invoice
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
