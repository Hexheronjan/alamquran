<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pendaftaran Santri Baru
        </h2>
    </x-slot>

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        @if(!$angkatanAktif)
            {{-- Pendaftaran Belum/Sudah Ditutup --}}
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden p-8 text-center my-12">
                <div class="w-20 h-20 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                    <i class="fas fa-lock"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Pendaftaran Belum Dibuka</h2>
                <p class="text-gray-600 max-w-md mx-auto mb-6">
                    Saat ini pendaftaran santri baru untuk Pesantren Tahfizh Alam Qur'an belum dibuka.
                </p>

                @if($angkatanMendatang)
                <div class="bg-teal-50 border border-teal-200 rounded-xl p-5 max-w-sm mx-auto mb-6 text-left">
                    <p class="text-xs font-bold text-teal-600 uppercase mb-3 flex items-center gap-1.5">
                        <i class="fas fa-calendar-alt"></i> Jadwal Pendaftaran Berikutnya
                    </p>
                    <div class="space-y-2.5">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 font-medium">Angkatan</span>
                            <span class="text-sm font-bold text-gray-800">{{ $angkatanMendatang->nama_angkatan }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 font-medium">Buka Pendaftaran</span>
                            <span class="text-sm font-bold text-gray-800">
                                {{ $angkatanMendatang->tanggal_mulai_daftar->translatedFormat('d F Y') }}
                                • {{ $angkatanMendatang->tanggal_mulai_daftar->format('H.i') }} WIB
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 font-medium">Tutup Pendaftaran</span>
                            <span class="text-sm font-bold text-gray-800">
                                {{ $angkatanMendatang->tanggal_tutup_daftar->translatedFormat('d F Y') }}
                                • {{ $angkatanMendatang->tanggal_tutup_daftar->format('H.i') }} WIB
                            </span>
                        </div>
                    </div>
                </div>
                @else
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 max-w-sm mx-auto mb-6 text-sm text-gray-500 italic">
                    <i class="fas fa-info-circle text-gray-400 mr-1"></i>
                    Jadwal pendaftaran belum ditentukan oleh pihak sekolah.
                </div>
                @endif

                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-teal-600 text-white font-semibold rounded-lg hover:bg-teal-700 transition">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>

        @else
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-teal-500 to-cyan-600 px-6 py-8">
                <h1 class="text-3xl font-bold text-white text-center">Pendaftaran Santri Baru</h1>
                <p class="text-teal-100 text-center mt-2">Pesantren Tahfizh Alam Qur'an — {{ $angkatanAktif->nama_angkatan }} (Tahun Ajaran {{ $angkatanAktif->tahun_ajaran }})</p>
            </div>

            {{-- Panel: Daftar Anak yang Sudah Didaftarkan --}}
            @if($santriTerdaftar->isNotEmpty())
            <div class="px-6 pt-5 pb-2">
                <div class="bg-teal-50 border border-teal-200 rounded-xl p-4">
                    <p class="text-sm font-bold text-teal-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-users text-teal-600"></i>
                        Anak yang Sudah Anda Daftarkan ({{ $santriTerdaftar->count() }} anak)
                    </p>
                    <div class="space-y-2">
                        @foreach($santriTerdaftar as $s)
                        <div class="flex items-center justify-between bg-white rounded-lg px-4 py-2.5 border border-teal-100">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 font-bold text-xs">
                                    {{ $loop->iteration }}
                                </div>
                                <span class="text-sm font-semibold text-gray-800">{{ $s->nama_lengkap }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                @php
                                    $badge = match($s->status) {
                                        'diterima'            => ['bg-green-100 text-green-700', 'fa-check-circle', 'Diterima'],
                                        'diverifikasi'        => ['bg-blue-100 text-blue-700', 'fa-check', 'Diverifikasi'],
                                        'menunggu_verifikasi' => ['bg-yellow-100 text-yellow-700', 'fa-clock', 'Menunggu Verifikasi'],
                                        'ditolak'             => ['bg-red-100 text-red-700', 'fa-times-circle', 'Ditolak'],
                                        default               => ['bg-gray-100 text-gray-600', 'fa-circle', $s->status],
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ $badge[0] }}">
                                    <i class="fas {{ $badge[1] }}"></i> {{ $badge[2] }}
                                </span>
                                <a href="{{ route('santri.registration.status', $s->id) }}"
                                   class="text-xs text-teal-600 hover:text-teal-800 font-medium underline">
                                    Detail
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-teal-700 mt-3 flex items-center gap-1.5">
                        <i class="fas fa-info-circle"></i>
                        Formulir di bawah ini untuk mendaftarkan anak berikutnya. Data orang tua (No. KK, Ayah, Ibu) boleh sama.
                    </p>
                </div>
            </div>
            @endif

            <!-- Progress Steps -->
            <div class="px-6 py-4 bg-gray-50 border-b">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full bg-teal-500 text-white flex items-center justify-center font-semibold step-indicator" data-step="1">1</div>
                        <span class="text-sm font-medium text-gray-700 hidden sm:inline">Data Santri</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 mx-2 step-line" data-from="1" data-to="2"></div>
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-semibold step-indicator" data-step="2">2</div>
                        <span class="text-sm font-medium text-gray-700 hidden sm:inline">Data Ayah</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 mx-2 step-line" data-from="2" data-to="3"></div>
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-semibold step-indicator" data-step="3">3</div>
                        <span class="text-sm font-medium text-gray-700 hidden sm:inline">Data Ibu</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 mx-2 step-line" data-from="3" data-to="4"></div>
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-semibold step-indicator" data-step="4">4</div>
                        <span class="text-sm font-medium text-gray-700 hidden sm:inline">Dokumen</span>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form id="registrationForm" class="p-6 space-y-8" enctype="multipart/form-data">
                @csrf

                <!-- Step 1: Data Calon Santri -->
                <div class="form-step" data-step="1">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-user-graduate text-teal-500 mr-2"></i>
                        Data Calon Santri
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap Santri <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_lengkap" id="nama_lengkap" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan nama lengkap">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tempat Lahir <span class="text-red-500">*</span></label>
                            <input type="text" name="tempat_lahir" id="tempat_lahir" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan tempat lahir">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_lahir" id="tanggal_lahir" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">NIK (16 Digit) <span class="text-red-500">*</span></label>
                            <input type="text" name="nik" id="nik" required maxlength="16"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan 16 digit NIK">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenjang Sekolah Asal <span class="text-red-500">*</span></label>
                            <select name="sekolah_asal_jenjang" id="sekolah_asal_jenjang" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition">
                                <option value="">Pilih Jenjang</option>
                                <option value="SD">SD (Sekolah Dasar)</option>
                                <option value="MI">MI (Madrasah Ibtidaiyah)</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Sekolah/Madrasah Asal <span class="text-red-500">*</span></label>
                            <input type="text" name="sekolah_asal" id="sekolah_asal" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan nama sekolah asal">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">NPSN <span class="text-red-500">*</span></label>
                            <input type="text" name="npsn" id="npsn" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan NPSN">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">NSM <span class="text-gray-500 text-xs">(Wajib jika MI)</span></label>
                            <input type="text" name="nsm" id="nsm"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan NSM">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">NISN (10 Digit) <span class="text-red-500">*</span></label>
                            <input type="text" name="nisn" id="nisn" required maxlength="10"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan 10 digit NISN">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Anak Ke- <span class="text-red-500">*</span></label>
                            <input type="number" name="anak_ke" id="anak_ke" required min="1"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan anak ke-">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dari ... Bersaudara <span class="text-red-500">*</span></label>
                            <input type="number" name="dari_bersaudara" id="dari_bersaudara" required min="1"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Jumlah bersaudara">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Prestasi Akademik & Non Akademik</label>
                            <textarea name="prestasi" id="prestasi" rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Jelaskan prestasi yang pernah diraih"></textarea>
                        </div>

                        <div class="form-group md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Riwayat Penyakit / Alergi</label>
                            <textarea name="riwayat_penyakit" id="riwayat_penyakit" rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Jelaskan riwayat penyakit atau alergi jika ada"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hobi</label>
                            <input type="text" name="hobi" id="hobi"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan hobi">
                        </div>

                        <div class="form-group md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ekstrakurikuler</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="ekstrakurikuler[]" value="Hadroh" class="w-4 h-4 text-teal-500 rounded">
                                    <span class="text-sm text-gray-700">Hadroh</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="ekstrakurikuler[]" value="Voli" class="w-4 h-4 text-teal-500 rounded">
                                    <span class="text-sm text-gray-700">Voli</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="ekstrakurikuler[]" value="Futsal" class="w-4 h-4 text-teal-500 rounded">
                                    <span class="text-sm text-gray-700">Futsal</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" name="ekstrakurikuler[]" value="Seni Tilawah" class="w-4 h-4 text-teal-500 rounded">
                                    <span class="text-sm text-gray-700">Seni Tilawah</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tujuan Mendaftarkan Ananda di Pesantren Tahfizh Alam Qur'an <span class="text-red-500">*</span></label>
                            <textarea name="tujuan_mendaftar" id="tujuan_mendaftar" required rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Jelaskan tujuan mendaftarkan ananda"></textarea>
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Data Ayah -->
                <div class="form-step hidden" data-step="2">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-user text-teal-500 mr-2"></i>
                        Data Ayah
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Ayah <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_ayah" id="nama_ayah" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan nama ayah">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor WhatsApp <span class="text-red-500">*</span></label>
                            <input type="text" name="no_wa_ayah" id="no_wa_ayah" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="62xxxxxxxxxx">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Kartu Keluarga (16 Digit) <span class="text-red-500">*</span></label>
                            <input type="text" name="no_kk" id="no_kk" required maxlength="16"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan 16 digit KK">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">NIK Ayah (16 Digit) <span class="text-red-500">*</span></label>
                            <input type="text" name="nik_ayah" id="nik_ayah" required maxlength="16"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan 16 digit NIK Ayah">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tempat Lahir Ayah <span class="text-red-500">*</span></label>
                            <input type="text" name="tempat_lahir_ayah" id="tempat_lahir_ayah" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan tempat lahir ayah">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir Ayah <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_lahir_ayah" id="tanggal_lahir_ayah" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pekerjaan Ayah <span class="text-red-500">*</span></label>
                            <input type="text" name="pekerjaan_ayah" id="pekerjaan_ayah" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan pekerjaan ayah">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Penghasilan Bulanan Ayah <span class="text-red-500">*</span></label>
                            <select name="penghasilan_ayah" id="penghasilan_ayah" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition">
                                <option value="">Pilih penghasilan</option>
                                <option value="Rp500.000–Rp1.000.000">Rp500.000–Rp1.000.000</option>
                                <option value="Rp1.000.000–Rp2.000.000">Rp1.000.000–Rp2.000.000</option>
                                <option value="Rp2.000.000–Rp5.000.000">Rp2.000.000–Rp5.000.000</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            <div id="penghasilan_ayah_lainnya_wrap" class="hidden mt-3">
                                <label for="penghasilan_ayah_lainnya" class="block text-sm font-medium text-gray-700 mb-2">Tulis Penghasilan Ayah</label>
                                <input type="text" name="penghasilan_ayah_lainnya" id="penghasilan_ayah_lainnya"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                    placeholder="Contoh: Rp6.500.000 per bulan">
                            </div>
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Domisili Ayah <span class="text-red-500">*</span></label>
                            <textarea name="alamat_domisili_ayah" id="alamat_domisili_ayah" required rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan alamat domisili ayah"></textarea>
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Data Ibu -->
                <div class="form-step hidden" data-step="3">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-user text-teal-500 mr-2"></i>
                        Data Ibu
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Ibu <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_ibu" id="nama_ibu" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan nama ibu">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">NIK Ibu (16 Digit) <span class="text-red-500">*</span></label>
                            <input type="text" name="nik_ibu" id="nik_ibu" required maxlength="16"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan 16 digit NIK Ibu">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tempat Lahir Ibu <span class="text-red-500">*</span></label>
                            <input type="text" name="tempat_lahir_ibu" id="tempat_lahir_ibu" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan tempat lahir ibu">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir Ibu <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_lahir_ibu" id="tanggal_lahir_ibu" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pekerjaan Ibu <span class="text-red-500">*</span></label>
                            <input type="text" name="pekerjaan_ibu" id="pekerjaan_ibu" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan pekerjaan ibu">
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Penghasilan Bulanan Ibu <span class="text-red-500">*</span></label>
                            <select name="penghasilan_ibu" id="penghasilan_ibu" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition">
                                <option value="">Pilih penghasilan</option>
                                <option value="Rp500.000–Rp1.000.000">Rp500.000–Rp1.000.000</option>
                                <option value="Rp1.000.000–Rp2.000.000">Rp1.000.000–Rp2.000.000</option>
                                <option value="Rp2.000.000–Rp5.000.000">Rp2.000.000–Rp5.000.000</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            <div id="penghasilan_ibu_lainnya_wrap" class="hidden mt-3">
                                <label for="penghasilan_ibu_lainnya" class="block text-sm font-medium text-gray-700 mb-2">Tulis Penghasilan Ibu</label>
                                <input type="text" name="penghasilan_ibu_lainnya" id="penghasilan_ibu_lainnya"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                    placeholder="Contoh: Rp6.500.000 per bulan">
                            </div>
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Domisili Ibu <span class="text-red-500">*</span></label>
                            <textarea name="alamat_domisili_ibu" id="alamat_domisili_ibu" required rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                placeholder="Masukkan alamat domisili ibu"></textarea>
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Upload Dokumen -->
                <div class="form-step hidden" data-step="4">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-file-upload text-teal-500 mr-2"></i>
                        Upload Dokumen
                    </h2>
                    
                    <div class="space-y-6">
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        Format file yang diperbolehkan: JPG, JPEG, PNG, PDF. Maksimal ukuran 5 MB per file.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pas Foto Santri</label>
                            <input type="file" name="foto_santri" id="foto_santri" accept=".jpg,.jpeg,.png"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                            <p class="text-xs text-gray-400 mt-1">Upload pas foto santri (Format: JPG, JPEG, atau PNG).</p>
                            <div id="foto_santri_preview" class="mt-2 hidden"></div>
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bukti Transfer Pendaftaran <span class="text-red-500">*</span></label>
                            <input type="file" name="bukti_transfer" id="bukti_transfer" required accept=".jpg,.jpeg,.png,.pdf"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                            <div id="bukti_transfer_preview" class="mt-2 hidden"></div>
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kartu Keluarga (KK) <span class="text-red-500">*</span></label>
                            <input type="file" name="kartu_keluarga" id="kartu_keluarga" required accept=".jpg,.jpeg,.png,.pdf"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                            <div id="kartu_keluarga_preview" class="mt-2 hidden"></div>
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">KTP Ayah <span class="text-red-500">*</span></label>
                            <input type="file" name="ktp_ayah" id="ktp_ayah" required accept=".jpg,.jpeg,.png,.pdf"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                            <div id="ktp_ayah_preview" class="mt-2 hidden"></div>
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">KTP Ibu <span class="text-red-500">*</span></label>
                            <input type="file" name="ktp_ibu" id="ktp_ibu" required accept=".jpg,.jpeg,.png,.pdf"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                            <div id="ktp_ibu_preview" class="mt-2 hidden"></div>
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rapor Kelas 5 atau Surat Keterangan Siswa Aktif <span class="text-red-500">*</span></label>
                            <input type="file" name="rapor" id="rapor" required accept=".jpg,.jpeg,.png,.pdf"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                            <div id="rapor_preview" class="mt-2 hidden"></div>
                            <span class="error-message text-red-500 text-sm mt-1 hidden"></span>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between pt-6 border-t">
                    <button type="button" id="prevBtn" class="px-6 py-3 bg-gray-200 text-blue-700 rounded-lg hover:bg-gray-300 transition hidden">
                        <i class="fas fa-arrow-left mr-2"></i>Sebelumnya
                    </button>
                    <button type="button" id="nextBtn" class="px-6 py-3 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition ml-auto">
                        Selanjutnya<i class="fas fa-arrow-right ml-2"></i>
                    </button>
                    <button type="submit" id="submitBtn" class="hidden" style="background: #0d9488; color: white; padding: 12px 24px; border-radius: 8px; font-weight: bold; border: none; cursor: pointer;">
                        <i class="fas fa-paper-plane" style="color: white; margin-right: 8px;"></i>Kirim Pendaftaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div id="loadingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 flex flex-col items-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-teal-500 mb-4"></div>
        <p class="text-gray-700">Sedang mengirim data...</p>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 max-w-md mx-4 text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-check text-green-500 text-3xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Pendaftaran Berhasil!</h3>
        <p class="text-gray-600 mb-6">Data pendaftaran Anda telah berhasil disimpan. Admin akan memverifikasi data Anda.</p>
        <button onclick="window.location.href='{{ route('user.dashboard') }}'" class="px-6 py-3 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition">
            Kembali ke Dashboard
        </button>
    </div>
</div>

<script>
let currentStep = 1;
const totalSteps = 4;

// Dynamic NSM Logic
const jenjangSelect = document.getElementById('sekolah_asal_jenjang');
const nsmInput = document.getElementById('nsm');
const nsmLabel = nsmInput ? nsmInput.previousElementSibling : null;

if (jenjangSelect && nsmInput) {
    jenjangSelect.addEventListener('change', function() {
        if (this.value === 'SD') {
            nsmInput.value = '0';
            nsmInput.readOnly = true;
            nsmInput.removeAttribute('required');
            nsmInput.classList.remove('border-red-500', 'focus:ring-red-500');
            nsmInput.classList.add('border-green-500', 'focus:ring-green-500');
            const err = nsmInput.closest('.form-group').querySelector('.error-message');
            if (err) err.classList.add('hidden');
            if (nsmLabel) nsmLabel.innerHTML = 'NSM <span class="text-teal-600 text-xs font-semibold">(Otomatis 0 untuk SD)</span>';
        } else if (this.value === 'MI') {
            nsmInput.value = '';
            nsmInput.readOnly = false;
            nsmInput.setAttribute('required', 'required');
            if (nsmLabel) nsmLabel.innerHTML = 'NSM <span class="text-red-500">*</span> <span class="text-gray-500 text-xs">(Wajib untuk MI)</span>';
        } else {
            nsmInput.value = '';
            nsmInput.readOnly = false;
            nsmInput.removeAttribute('required');
            if (nsmLabel) nsmLabel.innerHTML = 'NSM <span class="text-gray-500 text-xs">(Opsional)</span>';
        }
    });
}

// Update progress indicators
function updateProgress() {
    document.querySelectorAll('.step-indicator').forEach(indicator => {
        const step = parseInt(indicator.dataset.step);
        if (step <= currentStep) {
            indicator.classList.remove('bg-gray-300', 'text-gray-600');
            indicator.classList.add('bg-teal-500', 'text-white');
        } else {
            indicator.classList.remove('bg-teal-500', 'text-white');
            indicator.classList.add('bg-gray-300', 'text-gray-600');
        }
    });

    document.querySelectorAll('.step-line').forEach(line => {
        const from = parseInt(line.dataset.from);
        const to = parseInt(line.dataset.to);
        if (from < currentStep) {
            line.classList.remove('bg-gray-200');
            line.classList.add('bg-teal-500');
        } else {
            line.classList.remove('bg-teal-500');
            line.classList.add('bg-gray-200');
        }
    });
}

// Show/hide form steps
function showStep(step) {
    document.querySelectorAll('.form-step').forEach(formStep => {
        formStep.classList.add('hidden');
    });
    document.querySelector(`.form-step[data-step="${step}"]`).classList.remove('hidden');

    // Update buttons
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');

    if (prevBtn) {
        prevBtn.classList.toggle('hidden', step === 1);
    }
    if (nextBtn) {
        nextBtn.classList.toggle('hidden', step === totalSteps);
    }
    if (submitBtn) {
        if (step === totalSteps) {
            submitBtn.classList.remove('hidden');
            submitBtn.style.display = 'inline-block';
        } else {
            submitBtn.classList.add('hidden');
            submitBtn.style.display = 'none';
        }
    }

    updateProgress();
}

// Validate single field
function validateField(field) {
    const formGroup = field.closest('.form-group');
    if (!formGroup) return true;
    const errorSpan = formGroup.querySelector('.error-message');
    let valid = true;
    let errorMessage = '';

    const isRequired = field.hasAttribute('required');
    const value = field.value.trim();

    // 1. Required Check
    if (isRequired && !value) {
        valid = false;
        let labelText = 'Field ini';
        const label = formGroup.querySelector('label');
        if (label) {
            labelText = label.textContent.replace('*', '').replace('(16 Digit)', '').replace('(10 Digit)', '').replace('Pendaftaran', '').trim();
        }
        errorMessage = `${labelText} wajib diisi.`;
    } else if (value) {
        // 2. Format Checks
        if (field.id === 'nik' || field.id === 'nik_ayah' || field.id === 'nik_ibu' || field.id === 'no_kk') {
            if (!/^\d+$/.test(value)) {
                valid = false;
                errorMessage = 'Harus berupa angka.';
            } else if (value.length !== 16) {
                valid = false;
                errorMessage = 'NIK/No KK harus terdiri dari 16 digit.';
            }
        } else if (field.id === 'nisn') {
            if (!/^\d+$/.test(value)) {
                valid = false;
                errorMessage = 'Harus berupa angka.';
            } else if (value.length !== 10) {
                valid = false;
                errorMessage = 'NISN tidak valid (harus 10 digit angka).';
            }
        } else if (field.id === 'npsn') {
            if (!/^\d+$/.test(value)) {
                valid = false;
                errorMessage = 'NPSN harus berupa angka.';
            }
        } else if (field.id === 'nsm') {
            if (value !== '0' && !/^\d+$/.test(value)) {
                valid = false;
                errorMessage = 'NSM harus berupa angka.';
            }
        } else if (field.id === 'no_wa_ayah') {
            const waRegex = /^(08|628|\+628)[0-9]{8,13}$/;
            if (!waRegex.test(value)) {
                valid = false;
                errorMessage = 'Nomor WhatsApp tidak valid (contoh: 08123456789).';
            }
        } else if (field.type === 'file') {
            const maxSize = 5 * 1024 * 1024; // 5MB
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            if (field.files.length > 0) {
                const file = field.files[0];
                const fileType = file.type;
                const extension = file.name.split('.').pop().toLowerCase();
                const isTypeAllowed = allowedTypes.includes(fileType) || ['jpg', 'jpeg', 'png', 'pdf'].includes(extension);
                if (!isTypeAllowed) {
                    valid = false;
                    errorMessage = 'Format file tidak didukung (harus JPG, JPEG, PNG, atau PDF).';
                } else if (file.size > maxSize) {
                    valid = false;
                    errorMessage = 'File terlalu besar (maksimal 5 MB).';
                }
            }
        }
    }

    // Update UI
    if (!valid) {
        field.classList.add('border-red-500', 'focus:ring-red-500');
        field.classList.remove('border-green-500', 'focus:ring-green-500');
        if (errorSpan) {
            errorSpan.textContent = errorMessage;
            errorSpan.classList.remove('hidden');
        }
    } else {
        if (value || (field.type === 'file' && field.files.length > 0)) {
            field.classList.add('border-green-500', 'focus:ring-green-500');
            field.classList.remove('border-red-500', 'focus:ring-red-500');
        } else {
            field.classList.remove('border-green-500', 'focus:ring-green-500', 'border-red-500', 'focus:ring-red-500');
        }
        if (errorSpan) {
            errorSpan.classList.add('hidden');
        }
    }

    return valid;
}

// AJAX Check duplicates
function checkDuplicateField(field) {
    const value = field.value.trim();
    if (!value) return;

    // Check formatting first
    if (field.id === 'nik') {
        if (value.length !== 16 || !/^\d+$/.test(value)) return;
    }
    if (field.id === 'nisn') {
        if (value.length !== 10 || !/^\d+$/.test(value)) return;
    }

    let type = '';
    if (field.id === 'nik') type = 'nik';
    else if (field.id === 'nisn') type = 'nisn';
    // no_kk tidak dicek duplikat

    if (!type) return;

    fetch('{{ route('santri.registration.checkDuplicate') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ type: type, value: value })
    })
    .then(response => response.json())
    .then(data => {
        const formGroup = field.closest('.form-group');
        const errorSpan = formGroup.querySelector('.error-message');
        if (data.duplicate) {
            field.classList.add('border-red-500', 'focus:ring-red-500');
            field.classList.remove('border-green-500', 'focus:ring-green-500');
            if (errorSpan) {
                errorSpan.textContent = 'Data santri sudah pernah didaftarkan.';
                errorSpan.classList.remove('hidden');
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

// Validate current step
function validateStep(step) {
    let valid = true;
    const currentFormStep = document.querySelector(`.form-step[data-step="${step}"]`);
    if (!currentFormStep) return true;
    const inputs = currentFormStep.querySelectorAll('input, select, textarea');

    inputs.forEach(field => {
        if (field.hasAttribute('required') || field.value.trim() !== '') {
            const fieldValid = validateField(field);
            if (!fieldValid) {
                valid = false;
            }
        }
    });

    return valid;
}

// Show file preview
function showPreview(input) {
    const previewDiv = document.getElementById(`${input.id}_preview`);
    if (!previewDiv) return;
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const ext = file.name.split('.').pop().toLowerCase();
            if (file.type.startsWith('image/') || ['jpg', 'jpeg', 'png'].includes(ext)) {
                previewDiv.innerHTML = `<img src="${e.target.result}" class="max-w-xs max-h-48 rounded-lg border mt-2 shadow-sm">`;
            } else {
                previewDiv.innerHTML = `<div class="flex items-center text-red-700 bg-red-50 p-3 rounded-lg border border-red-200 mt-2 w-fit shadow-xs"><i class="fas fa-file-pdf text-3xl mr-2 text-red-500"></i><span class="text-sm font-semibold">${file.name}</span></div>`;
            }
            previewDiv.classList.remove('hidden');
        };
        
        reader.readAsDataURL(file);
    } else {
        previewDiv.innerHTML = '';
        previewDiv.classList.add('hidden');
    }
}

// Event listeners for buttons
const nextBtn = document.getElementById('nextBtn');
if (nextBtn) {
    nextBtn.addEventListener('click', function() {
        if (validateStep(currentStep)) {
            currentStep++;
            showStep(currentStep);
        }
    });
}

const prevBtn = document.getElementById('prevBtn');
if (prevBtn) {
    prevBtn.addEventListener('click', function() {
        currentStep--;
        showStep(currentStep);
    });
}

// Form submission
const form = document.getElementById('registrationForm');
if (form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        let formValid = true;
        // Validate all steps first
        for (let i = 1; i <= totalSteps; i++) {
            if (!validateStep(i)) {
                formValid = false;
                showStep(i);
                currentStep = i;
                break;
            }
        }

        if (!formValid) {
            return;
        }

        const formData = new FormData(this);
        const loadingModal = document.getElementById('loadingModal');
        const successModal = document.getElementById('successModal');

        loadingModal.classList.remove('hidden');

        fetch('{{ route('santri.registration.store') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            loadingModal.classList.add('hidden');
            
            if (status === 200 && body.success) {
                successModal.classList.remove('hidden');
            } else {
                if (body.errors) {
                    for (const [key, messages] of Object.entries(body.errors)) {
                        // Find element by name or ID
                        let inputElement = document.getElementsByName(key)[0] || document.getElementById(key);
                        if (!inputElement && key.includes('.')) {
                            // Handler for array errors if any
                            const parts = key.split('.');
                            inputElement = document.getElementsByName(`${parts[0]}[]`)[parts[1]];
                        }
                        
                        if (inputElement) {
                            const formGroup = inputElement.closest('.form-group');
                            if (formGroup) {
                                const errorSpan = formGroup.querySelector('.error-message');
                                inputElement.classList.add('border-red-500', 'focus:ring-red-500');
                                inputElement.classList.remove('border-green-500', 'focus:ring-green-500');
                                if (errorSpan) {
                                    errorSpan.textContent = messages[0];
                                    errorSpan.classList.remove('hidden');
                                }
                            }
                        }
                    }
                    
                    // Jump to first step with error
                    const firstErrorKey = Object.keys(body.errors)[0];
                    let firstErrorInput = document.getElementsByName(firstErrorKey)[0] || document.getElementById(firstErrorKey);
                    if (firstErrorInput) {
                        const stepElement = firstErrorInput.closest('.form-step');
                        if (stepElement) {
                            const step = parseInt(stepElement.dataset.step);
                            currentStep = step;
                            showStep(currentStep);
                        }
                    }
                } else {
                    alert('Terjadi kesalahan: ' + (body.message || 'Silakan coba lagi'));
                }
            }
        })
        .catch(error => {
            loadingModal.classList.add('hidden');
            alert('Terjadi kesalahan koneksi: ' + error.message);
        });
    });
}

// Bind blur and input validation events
document.querySelectorAll('input, select, textarea').forEach(field => {
    field.addEventListener('blur', function() {
        validateField(this);
    });
    
    field.addEventListener('input', function() {
        if (this.classList.contains('border-red-500')) {
            validateField(this);
        }
    });
});

// Bind duplicate checks (hanya NIK dan NISN santri, bukan No KK)
// No KK yang sama diizinkan karena 1 keluarga bisa mendaftarkan lebih dari 1 anak
const dupFields = ['nik', 'nisn'];
dupFields.forEach(id => {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener('blur', function() {
            checkDuplicateField(this);
        });
    }
});

// File input preview handlers
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function() {
        showPreview(this);
        validateField(this);
    });
});

// =====================================================
// AUTOSAVE: Simpan & Pulihkan data form dari localStorage
// =====================================================
const DRAFT_KEY = 'ppdb_draft_form';

// Daftar field yang perlu disimpan (skip file inputs)
function getFormDraftableFields() {
    return document.querySelectorAll(
        '#registrationForm input:not([type="file"]):not([type="hidden"]), ' +
        '#registrationForm select, ' +
        '#registrationForm textarea'
    );
}

function saveDraft() {
    const draft = {};
    getFormDraftableFields().forEach(field => {
        if (!field.name) return;
        if (field.type === 'checkbox') {
            if (!draft[field.name]) draft[field.name] = [];
            if (field.checked) draft[field.name].push(field.value);
        } else {
            draft[field.name] = field.value;
        }
    });
    localStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
}

function restoreDraft() {
    const raw = localStorage.getItem(DRAFT_KEY);
    if (!raw) return;

    let draft;
    try { draft = JSON.parse(raw); } catch(e) { return; }

    getFormDraftableFields().forEach(field => {
        if (!field.name || !(field.name in draft) && !(field.name.replace('[]','') in draft)) return;

        const key = field.name in draft ? field.name : field.name.replace('[]','');
        const val = draft[key];

        if (field.type === 'checkbox') {
            const arr = Array.isArray(val) ? val : [];
            field.checked = arr.includes(field.value);
        } else if (field.type === 'radio') {
            field.checked = (field.value === val);
        } else {
            field.value = val || '';
        }
    });

    // Trigger NSM logic after restore in case jenjang was saved
    const jenjangEl = document.getElementById('sekolah_asal_jenjang');
    if (jenjangEl) jenjangEl.dispatchEvent(new Event('change'));

    syncCustomIncomeFields();

    // Show saved draft notice
    showDraftNotice();
}

// Jika memilih "Lainnya", wali dapat menulis nominal/keterangan sendiri.
function syncCustomIncomeField(selectId) {
    const select = document.getElementById(selectId);
    const input = document.getElementById(selectId + '_lainnya');
    const wrapper = document.getElementById(selectId + '_lainnya_wrap');
    if (!select || !input || !wrapper) return;

    const isCustom = select.value === 'Lainnya';
    wrapper.classList.toggle('hidden', !isCustom);
    input.required = isCustom;
}

function syncCustomIncomeFields() {
    syncCustomIncomeField('penghasilan_ayah');
    syncCustomIncomeField('penghasilan_ibu');
}

['penghasilan_ayah', 'penghasilan_ibu'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', () => syncCustomIncomeField(id));
});

function clearDraft() {
    localStorage.removeItem(DRAFT_KEY);
}

function showDraftNotice() {
    const raw = localStorage.getItem(DRAFT_KEY);
    if (!raw) return;
    let draft;
    try { draft = JSON.parse(raw); } catch(e) { return; }

    // Only show if there's meaningful data
    const hasData = Object.values(draft).some(v => v && v !== '' && !(Array.isArray(v) && v.length === 0));
    if (!hasData) return;

    // Create notice bar
    const notice = document.createElement('div');
    notice.id = 'draftNotice';
    notice.style.cssText = `
        position: fixed; top: 0; left: 0; right: 0; z-index: 9999;
        background: #0d9488; color: white;
        padding: 10px 20px;
        display: flex; align-items: center; justify-content: space-between;
        font-size: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        animation: slideDown 0.3s ease-out;
    `;
    notice.innerHTML = `
        <span>📝 <strong>Draft tersimpan</strong> — Data sebelumnya telah dipulihkan secara otomatis.</span>
        <button id="clearDraftBtn" style="
            background: rgba(255,255,255,0.25); border: none; color: white;
            padding: 4px 12px; border-radius: 6px; cursor: pointer; font-size: 13px; margin-left:16px;
        ">Hapus Draft</button>
    `;
    document.body.prepend(notice);

    document.getElementById('clearDraftBtn').addEventListener('click', () => {
        clearDraft();
        notice.remove();
        // Clear all fields
        getFormDraftableFields().forEach(field => {
            if (field.type === 'checkbox' || field.type === 'radio') {
                field.checked = false;
            } else {
                field.value = '';
            }
        });
    });

    // Auto-hide after 8 seconds
    setTimeout(() => { if (notice.parentNode) notice.remove(); }, 8000);
}

// Listen for any input/change and save draft
getFormDraftableFields().forEach(field => {
    field.addEventListener('input', saveDraft);
    field.addEventListener('change', saveDraft);
});

// Also save when navigating between steps
document.addEventListener('click', function(e) {
    if (e.target.closest('#nextBtn') || e.target.closest('#prevBtn')) {
        saveDraft();
    }
}, true);

// Clear draft on successful submission
const origSuccessModal = document.getElementById('successModal');
if (origSuccessModal) {
    const observer = new MutationObserver(() => {
        if (!origSuccessModal.classList.contains('hidden')) {
            clearDraft();
        }
    });
    observer.observe(origSuccessModal, { attributes: true, attributeFilter: ['class'] });
}

// Restore draft when page loads
restoreDraft();

// Initialize
showStep(1);
</script>
<style>
@keyframes slideDown {
    from { transform: translateY(-100%); opacity: 0; }
    to   { transform: translateY(0); opacity: 1; }
}
</style>
@endif
</x-app-layout>
