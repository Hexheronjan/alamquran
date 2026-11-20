@extends('layouts.admin.app')

@section('title', 'Detail Santri - ' . $santri->nama_lengkap)

@section('content')
<div class="bg-gray-100 min-h-screen">
    <div class="w-full py-6 px-4 lg:px-8">

        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('admin.santri.index') }}" class="text-teal-600 hover:text-teal-800 font-semibold mb-2 inline-flex items-center text-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Santri
                </a>
                <h1 class="text-2xl font-bold text-gray-900 mt-1">Detail Santri</h1>
            </div>
            <!-- Status Badge -->
            <div class="flex-shrink-0">
                @switch($santri->status)
                    @case('menunggu_verifikasi')
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold bg-yellow-100 text-yellow-800 border border-yellow-300">
                            <i class="fas fa-clock"></i> Menunggu Verifikasi
                        </span>
                        @break
                    @case('diverifikasi')
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold bg-blue-100 text-blue-800 border border-blue-300">
                            <i class="fas fa-check"></i> Diverifikasi
                        </span>
                        @break
                    @case('diterima')
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold bg-green-100 text-green-800 border border-green-300">
                            <i class="fas fa-check-circle"></i> Diterima
                        </span>
                        @break
                    @case('ditolak')
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold bg-red-100 text-red-800 border border-red-300">
                            <i class="fas fa-times-circle"></i> Ditolak
                        </span>
                        @break
                @endswitch
            </div>
        </div>

        <!-- Profile Card -->
        <div class="bg-white rounded-2xl shadow border border-gray-100 p-4 sm:p-6 mb-6 flex flex-col lg:flex-row items-start lg:items-center gap-5">
            <div class="flex items-center gap-4 w-full lg:w-auto">
                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-teal-50 rounded-2xl overflow-hidden flex items-center justify-center flex-shrink-0 border-2 border-teal-500/20 shadow-sm">
                    @if($santri->foto_santri)
                        <img src="{{ asset('storage/' . $santri->foto_santri) }}" alt="{{ $santri->nama_lengkap }}" class="w-full h-full object-cover">
                    @else
                        <i class="fas fa-user-graduate text-teal-600 text-2xl sm:text-3xl"></i>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg sm:text-xl font-extrabold text-gray-900 truncate">{{ $santri->nama_lengkap }}</h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1"><i class="fas fa-calendar mr-1"></i> Daftar: {{ $santri->created_at->translatedFormat('d M Y, H:i') }}</p>
                </div>
            </div>
            <div class="flex-1 w-full">
                <div class="flex flex-wrap gap-2 text-xs sm:text-sm">
                    @if($santri->angkatan)
                    <span class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700"><i class="fas fa-layer-group mr-1"></i> Angkatan: <strong>{{ $santri->angkatan->nama_angkatan ?? '-' }}</strong></span>
                    @endif
                    @if($santri->kelas_sekarang)
                    <span class="px-2.5 py-1 rounded-lg bg-teal-50 text-teal-700 font-semibold"><i class="fas fa-school mr-1"></i> Kelas: <strong>{{ $santri->kelas_sekarang }}</strong></span>
                    @endif
                </div>
            </div>
            <div class="grid grid-cols-2 sm:flex sm:flex-wrap lg:flex-col gap-2 w-full lg:w-auto">
                <a href="{{ route('admin.santri.cetakKartu', $santri->id) }}" target="_blank"
                    style="background-color: #16a34a; color: #ffffff;"
                    class="px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 hover:opacity-90 transition shadow-sm whitespace-nowrap">
                    <i class="fas fa-id-card"></i> Cetak Kartu Santri
                </a>
                <a href="{{ route('admin.santri.exportSinglePDF', $santri->id) }}"
                    style="background-color: #059669; color: #ffffff;"
                    class="px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 hover:opacity-90 transition shadow-sm whitespace-nowrap">
                    <i class="fas fa-file-pdf"></i> Cetak PDF
                </a>
                <a href="{{ route('admin.santri.exportSingleExcel', $santri->id) }}"
                    style="background-color: #0284c7; color: #ffffff;"
                    class="px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 hover:opacity-90 transition shadow-sm whitespace-nowrap">
                    <i class="fas fa-file-excel"></i> Cetak Excel
                </a>
                <a href="{{ route('admin.santri.edit', $santri->id) }}"
                    style="background-color: #2563eb; color: #ffffff;"
                    class="px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 hover:opacity-90 transition shadow-sm whitespace-nowrap">
                    <i class="fas fa-edit"></i> Edit Data
                </a>
                <button onclick="updateStatus({{ $santri->id }})"
                    style="background-color: #d97706; color: #ffffff;"
                    class="px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 hover:opacity-90 transition shadow-sm border-0 cursor-pointer whitespace-nowrap">
                    <i class="fas fa-check-circle"></i> Status
                </button>
                <button onclick="document.getElementById('correctionNoteModal').classList.remove('hidden')"
                    style="background-color: #eab308; color: #ffffff;"
                    class="px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 hover:opacity-90 transition shadow-sm border-0 cursor-pointer whitespace-nowrap">
                    <i class="fas fa-comment-dots"></i> Catatan Perbaikan
                </button>
                @if(auth('admin')->user()?->isSuperAdmin())
                <button onclick="confirmDelete({{ $santri->id }})"
                    style="background-color: #dc2626; color: #ffffff;"
                    class="px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 hover:opacity-90 transition shadow-sm border-0 cursor-pointer whitespace-nowrap"
                    title="Hapus Santri (Khusus Super Admin)">
                    <i class="fas fa-trash"></i> Hapus
                </button>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Data Utama -->
            <div class="xl:col-span-2 space-y-6">
            @php
                $latestCorrection = $santri->correctionNotes->first();
            @endphp
            @if($latestCorrection?->diselesaikan_pada)
            <section class="rounded-2xl border border-green-200 bg-green-50 p-5 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-green-100 text-green-600">
                        <i class="fas fa-check-circle text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-green-900">Data telah diperbaiki oleh wali</h3>
                        <p class="mt-1 text-sm text-green-800">Perbaikan dikirim pada {{ $latestCorrection->diselesaikan_pada->translatedFormat('d M Y, H:i') }}. Silakan periksa kembali data di bawah, lalu lakukan verifikasi ulang.</p>
                    </div>
                </div>
            </section>
            @endif
            @if($santri->correctionNotes->isNotEmpty())
            <section class="bg-amber-50 rounded-2xl border border-amber-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-amber-200 font-extrabold text-amber-900"><i class="fas fa-comment-dots mr-2"></i>Riwayat Catatan Perbaikan</div>
                <div class="p-5 space-y-3">
                    @foreach($santri->correctionNotes as $note)
                    <div class="rounded-xl bg-white border border-amber-100 p-4">
                        <p class="text-sm text-gray-800">{{ $note->catatan }}</p>
                        <p class="text-xs text-gray-500 mt-2">{{ $note->diberikan_pada?->translatedFormat('d M Y, H:i') }} · {{ $note->diselesaikan_pada ? 'Data telah diperbaiki wali pada ' . $note->diselesaikan_pada->translatedFormat('d M Y, H:i') : 'Menunggu perbaikan wali' }}</p>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif
            <!-- Data Calon Santri -->
            <section class="bg-white rounded-2xl shadow-sm hover:shadow-md border border-gray-100 overflow-hidden transition-shadow duration-200">
                    <div class="px-5 py-4 bg-gradient-to-r from-teal-50 to-white border-b border-gray-100 flex items-center gap-3">
                        <div class="w-9 h-9 bg-teal-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user-graduate text-teal-600 text-sm"></i>
                        </div>
                        <h2 class="text-base font-extrabold text-gray-900">Data Calon Santri</h2>
                    </div>
                    <div class="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <x-detail-field label="Nama Lengkap" :value="$santri->nama_lengkap ?? '-'" />
                        <x-detail-field label="Tempat, Tanggal Lahir" :value="($santri->tempat_lahir ? $santri->tempat_lahir . ', ' : '') . ($santri->tanggal_lahir ? $santri->tanggal_lahir->format('d/m/Y') : '-')" />
                        <x-detail-field label="NIK" :value="$santri->nik ?? '-'" />
                        <x-detail-field label="NISN" :value="$santri->nisn ?? '-'" />
                        <x-detail-field label="Sekolah Asal" :value="$santri->sekolah_asal ?? '-'" />
                        <x-detail-field label="NPSN" :value="$santri->npsn ?? '-'" />
                        <x-detail-field label="NSM" :value="$santri->nsm ?? '-'" />
                        <x-detail-field label="Anak Ke-" :value="$santri->anak_ke ? ($santri->anak_ke . ' dari ' . ($santri->dari_bersaudara ?? '-') . ' bersaudara') : '-'" />
                        <div>
                            <x-detail-field label="Prestasi" :value="$santri->prestasi ?? '-'" />
                        </div>
                        <div>
                            <x-detail-field label="Riwayat Penyakit / Alergi" :value="$santri->riwayat_penyakit ?? '-'" />
                        </div>
                        <x-detail-field label="Hobi" :value="$santri->hobi ?? '-'" />
                        <x-detail-field label="Ekstrakurikuler" :value="is_array($santri->ekstrakurikuler) ? implode(', ', $santri->ekstrakurikuler) : ($santri->ekstrakurikuler ?? '-')" />
                        <div class="sm:col-span-2 lg:col-span-3">
                            <x-detail-field label="Tujuan Mendaftar" :value="$santri->tujuan_mendaftar ?? '-'" />
                        </div>
                    </div>
            </section>

            @if($santri->orangTua)
            <!-- Data Ayah -->
            <section class="bg-white rounded-2xl shadow-sm hover:shadow-md border border-gray-100 overflow-hidden transition-shadow duration-200">
                    <div class="px-5 py-4 bg-gradient-to-r from-blue-50 to-white border-b border-gray-100 flex items-center gap-3">
                        <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-blue-600 text-sm"></i>
                        </div>
                        <h2 class="text-base font-extrabold text-gray-900">Data Ayah / Wali</h2>
                    </div>
                    <div class="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <x-detail-field label="Nama Ayah" :value="$santri->orangTua->nama_ayah ?? '-'" />
                        <x-detail-field label="Nomor WhatsApp" :value="$santri->orangTua->no_wa_ayah ?? '-'" />
                        <x-detail-field label="NIK Ayah" :value="$santri->orangTua->nik_ayah ?? '-'" />
                        <x-detail-field label="Nomor KK" :value="$santri->orangTua->no_kk ?? '-'" />
                        <x-detail-field label="Tempat, Tanggal Lahir" :value="($santri->orangTua->tempat_lahir_ayah ? $santri->orangTua->tempat_lahir_ayah . ', ' : '') . ($santri->orangTua->tanggal_lahir_ayah ? (is_object($santri->orangTua->tanggal_lahir_ayah) ? $santri->orangTua->tanggal_lahir_ayah->format('d/m/Y') : $santri->orangTua->tanggal_lahir_ayah) : '-')" />
                        <x-detail-field label="Pekerjaan" :value="$santri->orangTua->pekerjaan_ayah ?? '-'" />
                        <x-detail-field label="Penghasilan" :value="$santri->orangTua->penghasilan_ayah ?? '-'" />
                        <div class="sm:col-span-2 lg:col-span-3">
                            <x-detail-field label="Alamat Domisili" :value="$santri->orangTua->alamat_domisili_ayah ?? '-'" />
                        </div>
                    </div>
            </section>

            <!-- Data Ibu -->
            <section class="bg-white rounded-2xl shadow-sm hover:shadow-md border border-gray-100 overflow-hidden transition-shadow duration-200">
                    <div class="px-5 py-4 bg-gradient-to-r from-pink-50 to-white border-b border-gray-100 flex items-center gap-3">
                        <div class="w-9 h-9 bg-pink-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-pink-600 text-sm"></i>
                        </div>
                        <h2 class="text-base font-extrabold text-gray-900">Data Ibu</h2>
                    </div>
                    <div class="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <x-detail-field label="Nama Ibu" :value="$santri->orangTua->nama_ibu ?? '-'" />
                        <x-detail-field label="NIK Ibu" :value="$santri->orangTua->nik_ibu ?? '-'" />
                        <x-detail-field label="Tempat, Tanggal Lahir" :value="($santri->orangTua->tempat_lahir_ibu ? $santri->orangTua->tempat_lahir_ibu . ', ' : '') . ($santri->orangTua->tanggal_lahir_ibu ? (is_object($santri->orangTua->tanggal_lahir_ibu) ? $santri->orangTua->tanggal_lahir_ibu->format('d/m/Y') : $santri->orangTua->tanggal_lahir_ibu) : '-')" />
                        <x-detail-field label="Pekerjaan" :value="$santri->orangTua->pekerjaan_ibu ?? '-'" />
                        <x-detail-field label="Penghasilan" :value="$santri->orangTua->penghasilan_ibu ?? '-'" />
                        <div class="sm:col-span-2 lg:col-span-3">
                            <x-detail-field label="Alamat Domisili" :value="$santri->orangTua->alamat_domisili_ibu ?? '-'" />
                        </div>
                    </div>
            </section>
            @endif
            </div>

            <!-- Sidebar Column -->
            <div class="space-y-6">

                <!-- Status Card -->
                <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="text-base font-extrabold text-gray-900">Status Pendaftaran</h2>
                    </div>
                    <div class="p-4 sm:p-5">
                        @switch($santri->status)
                            @case('menunggu_verifikasi')
                                <div class="flex items-center gap-3 p-4 bg-yellow-50 rounded-xl border border-yellow-200">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-clock text-yellow-600 text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-yellow-800 text-sm">Menunggu Verifikasi</p>
                                        <p class="text-xs text-yellow-600">Data sedang diverifikasi admin</p>
                                    </div>
                                </div>
                                @break
                            @case('diverifikasi')
                                <div class="flex items-center gap-3 p-4 bg-blue-50 rounded-xl border border-blue-200">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-check text-blue-600 text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-blue-800 text-sm">Diverifikasi</p>
                                        <p class="text-xs text-blue-600">Data telah diverifikasi</p>
                                    </div>
                                </div>
                                @break
                            @case('diterima')
                                <div class="flex items-center gap-3 p-4 bg-green-50 rounded-xl border border-green-200">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-check-circle text-green-600 text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-green-800 text-sm">Diterima</p>
                                        <p class="text-xs text-green-600">Selamat! Santri telah diterima</p>
                                    </div>
                                </div>
                                @break
                            @case('ditolak')
                                <div class="flex items-center gap-3 p-4 bg-red-50 rounded-xl border border-red-200">
                                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-times-circle text-red-600 text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-red-800 text-sm">Ditolak</p>
                                        <p class="text-xs text-red-600">Mohon maaf, santri tidak diterima</p>
                                    </div>
                                </div>
                                @break
                        @endswitch
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs font-bold text-gray-500 uppercase mb-1">Tanggal Pendaftaran</p>
                            <p class="text-sm text-gray-800 font-semibold">{{ $santri->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
                        </div>
                    </div>
                </section>

                <!-- Dokumen -->
                @if($santri->dokumen || $santri->foto_santri)
                <style>
                    .document-grid { display: grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 14px; }
                    @media (min-width: 640px) { .document-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
                    @media (min-width: 1280px) { .document-grid { grid-template-columns: 1fr; } }
                </style>
                <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 flex items-center gap-3" style="background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 65%); border-bottom: 1px solid #dff3ef;">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background: #ccfbf1; color: #0f766e;">
                            <i class="fas fa-folder-open text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-gray-900">Dokumen Pendaftaran</h2>
                            <p class="text-xs text-gray-500 mt-0.5">File persyaratan yang telah diunggah</p>
                        </div>
                    </div>
                    <div class="p-4 sm:p-5" style="background: #f8fafc;">
                        @php
                            $docs = [
                                'bukti_transfer' => ['label' => 'Bukti Transfer', 'icon' => 'fa-receipt', 'color' => 'green'],
                                'kartu_keluarga' => ['label' => 'Kartu Keluarga (KK)', 'icon' => 'fa-id-card', 'color' => 'blue'],
                                'ktp_ayah'       => ['label' => 'KTP Ayah', 'icon' => 'fa-address-card', 'color' => 'indigo'],
                                'ktp_ibu'        => ['label' => 'KTP Ibu', 'icon' => 'fa-address-card', 'color' => 'pink'],
                                'rapor'          => ['label' => 'Rapor / Ket. Aktif', 'icon' => 'fa-book', 'color' => 'yellow'],
                            ];
                        @endphp
                        <div class="document-grid">
                        @if($santri->foto_santri)
                            <div class="flex flex-col items-start gap-3 p-4 bg-white border border-teal-200 rounded-xl shadow-sm">
                                <div class="flex items-center gap-3 w-full">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden flex items-center justify-center flex-shrink-0 bg-teal-50 border border-teal-200">
                                        <img src="{{ asset('storage/' . $santri->foto_santri) }}" alt="Pas Foto" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <span class="text-xs sm:text-sm font-bold text-gray-800 block truncate">Pas Foto Santri</span>
                                        <p class="text-xs mt-0.5 text-teal-600 font-medium"><i class="fas fa-camera mr-1"></i>Foto tersedia</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 w-full pt-1">
                                    <button type="button" onclick="openPreview('{{ asset('storage/' . $santri->foto_santri) }}', 'Pas Foto Santri')"
                                        style="background-color: #0d9488; color: #ffffff;"
                                        class="flex-1 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center justify-center gap-1.5 hover:opacity-90 transition cursor-pointer border-0 shadow-sm">
                                        <i class="fas fa-eye"></i> Lihat
                                    </button>
                                    <a href="{{ asset('storage/' . $santri->foto_santri) }}" target="_blank"
                                        style="background-color: #475569; color: #ffffff;"
                                        class="flex-1 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center justify-center gap-1.5 hover:opacity-90 transition shadow-sm">
                                        <i class="fas fa-external-link-alt"></i> Tab Baru
                                    </a>
                                </div>
                            </div>
                        @endif
                        @foreach($docs as $field => $doc)
                            @if(!empty($santri->dokumen->$field))
                            <div class="flex flex-col items-start gap-3 p-4 bg-white border border-gray-200 rounded-xl shadow-sm">
                                <div class="flex items-center gap-3 w-full">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background: #e6fffa; color: #0f766e;">
                                        <i class="fas {{ $doc['icon'] }} text-xs"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <span class="text-xs sm:text-sm font-semibold text-gray-700 block truncate">{{ $doc['label'] }}</span>
                                        <p class="text-xs mt-0.5" style="color: #16a34a;"><i class="fas fa-check-circle mr-1"></i>File tersedia</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 w-full pt-1">
                                    <button type="button" onclick="openPreview('{{ asset('storage/dokumen/' . $santri->dokumen->$field) }}', '{{ $doc['label'] }}')"
                                        style="background-color: #0d9488; color: #ffffff;"
                                        class="flex-1 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center justify-center gap-1.5 hover:opacity-90 transition cursor-pointer border-0 shadow-sm">
                                        <i class="fas fa-eye"></i> Lihat
                                    </button>
                                    <a href="{{ asset('storage/dokumen/' . $santri->dokumen->$field) }}" target="_blank"
                                        style="background-color: #475569; color: #ffffff;"
                                        class="flex-1 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center justify-center gap-1.5 hover:opacity-90 transition shadow-sm">
                                        <i class="fas fa-external-link-alt"></i> Tab Baru
                                    </a>
                                </div>
                            </div>
                            @endif
                        @endforeach
                        </div>
                    </div>
                </section>
                @endif

            </div>
        </div>
    </div>
</div>

<div id="correctionNoteModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-[70] hidden p-4">
    <form method="POST" action="{{ route('admin.santri.correction_note.store', $santri->id) }}" class="bg-white rounded-2xl p-6 max-w-lg w-full shadow-2xl">
        @csrf
        <h3 class="text-lg font-bold text-gray-900"><i class="fas fa-comment-dots text-amber-500 mr-2"></i>Catatan Perbaikan Data</h3>
        <p class="text-sm text-gray-500 mt-2">Catatan ini akan tampil di akun wali dan membuka akses perbaikan data pendaftaran.</p>
        <textarea name="catatan" required maxlength="2000" rows="5" class="mt-4 w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500" placeholder="Contoh: NISN yang diinput tidak sesuai. Mohon periksa dan perbaiki kembali."></textarea>
        <div class="mt-4 flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('correctionNoteModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-gray-100 text-sm font-semibold">Batal</button>
            <button class="px-4 py-2 rounded-xl bg-amber-500 text-white text-sm font-bold">Kirim Catatan</button>
        </div>
    </form>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-[70] hidden p-4">
    <div class="bg-white rounded-2xl p-5 sm:p-6 max-w-md w-full shadow-2xl">
        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fas fa-check-circle text-teal-500"></i> Update Status Santri
        </h3>
        <input type="hidden" id="santriId">
        <select id="statusSelect" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent mb-5 text-sm">
            <option value="menunggu_verifikasi" {{ $santri->status == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
            <option value="diverifikasi" {{ $santri->status == 'diverifikasi' ? 'selected' : '' }}>Diverifikasi</option>
            <option value="diterima" {{ $santri->status == 'diterima' ? 'selected' : '' }}>Diterima</option>
            <option value="ditolak" {{ $santri->status == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
        </select>
        <div class="flex justify-end gap-2">
            <button onclick="closeStatusModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition text-sm font-semibold border-0 cursor-pointer">Batal</button>
            <button onclick="saveStatus()" style="background-color: #0d9488; color: #fff;" class="px-5 py-2 rounded-xl hover:opacity-90 transition text-sm font-bold border-0 cursor-pointer">Simpan</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-[70] hidden p-4">
    <div class="bg-white rounded-2xl p-5 sm:p-6 max-w-md w-full shadow-2xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Konfirmasi Hapus</h3>
        </div>
        <p class="text-gray-600 mb-6 text-sm">Apakah Anda yakin ingin menghapus data santri ini? Tindakan ini <strong>tidak dapat dibatalkan</strong>.</p>
        <input type="hidden" id="deleteSantriId">
        <div class="flex justify-end gap-2">
            <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition text-sm font-semibold border-0 cursor-pointer">Batal</button>
            <button onclick="confirmDeleteAction()" class="px-5 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition text-sm font-bold border-0 cursor-pointer">Hapus</button>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-[70] hidden p-3 sm:p-4">
    <div class="bg-white rounded-2xl max-w-4xl w-full overflow-hidden flex flex-col h-[90vh] shadow-2xl">
        <div class="bg-gray-900 px-4 sm:px-6 py-3.5 flex justify-between items-center text-white gap-3">
            <h3 class="text-sm sm:text-base font-bold text-white truncate" id="previewTitle">Preview Dokumen</h3>
            <div class="flex items-center gap-2">
                <a id="previewOpenTab" href="#" target="_blank"
                    style="background-color: #0d9488; color: #ffffff;"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 hover:opacity-90 transition">
                    <i class="fas fa-external-link-alt"></i> Tab Baru
                </a>
                <button onclick="closePreviewModal()" class="text-gray-300 hover:text-white text-2xl font-bold px-2 bg-transparent border-0 cursor-pointer leading-none">&times;</button>
            </div>
        </div>
        <div class="flex-1 bg-gray-100 p-2 sm:p-4 flex items-center justify-center overflow-auto" id="previewContent">
            <!-- Content injected by JS -->
        </div>
    </div>
</div>

<script>
function openPreview(url, title) {
    const modal = document.getElementById('previewModal');
    const titleEl = document.getElementById('previewTitle');
    const contentEl = document.getElementById('previewContent');
    const openTabBtn = document.getElementById('previewOpenTab');

    titleEl.textContent = title;
    openTabBtn.href = url;
    contentEl.innerHTML = '<div class="text-gray-500 font-bold text-base py-10 flex items-center gap-2"><i class="fas fa-spinner fa-spin text-teal-600"></i> Memuat dokumen...</div>';

    const cleanUrl = url.split('?')[0];
    const ext = cleanUrl.split('.').pop().toLowerCase();

    if (ext === 'pdf') {
        contentEl.innerHTML = `<iframe src="${url}" class="w-full h-full border-0 rounded-xl min-h-[500px]"></iframe>`;
    } else {
        const img = new Image();
        img.src = url;
        img.className = 'max-w-full max-h-[75vh] object-contain rounded-xl shadow-lg border border-gray-200';
        img.onload = () => { contentEl.innerHTML = ''; contentEl.appendChild(img); };
        img.onerror = () => {
            contentEl.innerHTML = `<div class="text-center py-10 text-red-600">
                <i class="fas fa-exclamation-circle text-5xl mb-3"></i>
                <p class="font-bold text-lg">File tidak ditemukan atau gagal dimuat.</p>
                <a href="${url}" target="_blank" style="background-color: #0d9488; color: #ffffff;" class="inline-block mt-4 px-5 py-2.5 rounded-lg text-sm font-bold shadow">Coba Buka di Tab Baru</a>
            </div>`;
        };
    }

    modal.classList.remove('hidden');
}

function closePreviewModal() {
    document.getElementById('previewModal').classList.add('hidden');
    document.getElementById('previewContent').innerHTML = '';
}

function updateStatus(id) {
    document.getElementById('santriId').value = id;
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}

function saveStatus() {
    const id = document.getElementById('santriId').value;
    const status = document.getElementById('statusSelect').value;

    fetch(`{{ route('admin.santri.updateStatus', ':id') }}`.replace(':id', id), {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { closeStatusModal(); location.reload(); }
        else { alert('Gagal mengupdate status'); }
    })
    .catch(err => alert('Terjadi kesalahan: ' + err.message));
}

function confirmDelete(id) {
    document.getElementById('deleteSantriId').value = id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

function confirmDeleteAction() {
    const id = document.getElementById('deleteSantriId').value;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `{{ route('admin.santri.destroy', ':id') }}`.replace(':id', id);
    form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}">
                      <input type="hidden" name="_method" value="DELETE">`;
    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection
