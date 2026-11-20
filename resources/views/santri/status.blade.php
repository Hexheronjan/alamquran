<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detail Pendaftaran & Uang Saku
        </h2>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-teal-50 py-10 px-4">
        <div class="max-w-4xl mx-auto space-y-6">

            {{-- Tombol Kembali --}}
            <a href="{{ route('user.form.history') }}" class="inline-flex items-center gap-2 text-teal-600 hover:text-teal-800 font-medium text-sm">
                <i class="fas fa-arrow-left"></i> Kembali ke Riwayat
            </a>

            {{-- STATUS CARD --}}
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-teal-500 to-cyan-600 px-6 py-6 text-center">
                    <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-3">
                        @switch($santri->status)
                            @case('diterima')
                                <i class="fas fa-check-circle text-white text-4xl"></i>
                                @break
                            @case('ditolak')
                                <i class="fas fa-times-circle text-white text-4xl"></i>
                                @break
                            @case('diverifikasi')
                                <i class="fas fa-check text-white text-4xl"></i>
                                @break
                            @default
                                <i class="fas fa-clock text-white text-4xl"></i>
                        @endswitch
                    </div>
                    <h1 class="text-2xl font-bold text-white">{{ $santri->nama_lengkap }}</h1>
                    <p class="text-teal-100 text-sm mt-1">Pendaftaran Santri Baru — Pesantren Tahfizh Alam Qur'an</p>
                </div>

                <div class="px-6 py-5">
                    @if(session('success'))
                    <div class="mb-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
                    @endif
                    @if($santri->correctionNoteOpen)
                    <div class="mb-6 rounded-xl border border-amber-300 bg-amber-50 p-5">
                        <h3 class="font-bold text-amber-900"><i class="fas fa-exclamation-triangle mr-2"></i>Data Perlu Diperbaiki</h3>
                        <p class="mt-2 text-sm text-amber-800">{{ $santri->correctionNoteOpen->catatan }}</p>
                        <a href="{{ route('santri.registration.correction.edit', $santri->id) }}" class="inline-flex mt-4 items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-bold text-white"><i class="fas fa-edit"></i> Edit Data</a>
                    </div>
                    @endif
                    {{-- Status Badge --}}
                    <div class="text-center mb-6">
                        @if($santri->status_santri === 'mutasi')
                            <span class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-orange-100 text-orange-800 font-bold text-base border border-orange-300">
                                <i class="fas fa-sign-out-alt"></i> Status Santri: Mutasi / Keluar
                            </span>
                            @if($santri->tanggal_mutasi)
                                <p class="text-xs text-gray-500 mt-2">
                                    Tanggal Mutasi: <span class="font-semibold text-gray-700">{{ $santri->tanggal_mutasi->translatedFormat('d F Y') }}</span>
                                </p>
                            @endif
                            @if($santri->keterangan_mutasi)
                                <div class="mt-3 p-3.5 bg-orange-50 border border-orange-200 rounded-xl text-left max-w-lg mx-auto">
                                    <p class="text-xs font-bold text-orange-950 uppercase tracking-wide">Keterangan / Alasan:</p>
                                    <p class="text-sm text-orange-800 mt-0.5">{{ $santri->keterangan_mutasi }}</p>
                                </div>
                            @endif
                        @else
                            @switch($santri->status)
                                @case('menunggu_verifikasi')
                                    <span class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-yellow-100 text-yellow-800 font-semibold text-base">
                                        <i class="fas fa-clock"></i> Menunggu Verifikasi
                                    </span>
                                    <p class="text-gray-500 text-sm mt-2">Data Anda sedang dalam proses verifikasi oleh admin.</p>
                                    @break
                                @case('diverifikasi')
                                    <span class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-blue-100 text-blue-800 font-semibold text-base">
                                        <i class="fas fa-check"></i> Diverifikasi
                                    </span>
                                    <p class="text-gray-500 text-sm mt-2">Data Anda telah diverifikasi. Harap tunggu pengumuman selanjutnya.</p>
                                    @break
                                @case('diterima')
                                    <span class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-green-100 text-green-800 font-semibold text-base">
                                        <i class="fas fa-check-circle"></i> Diterima
                                    </span>
                                    <p class="text-gray-500 text-sm mt-2">Selamat! Anda telah diterima di Pesantren Tahfizh Alam Qur'an.</p>
                                    @break
                                @case('ditolak')
                                    <span class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-red-100 text-red-800 font-semibold text-base">
                                        <i class="fas fa-times-circle"></i> Ditolak
                                    </span>
                                    <p class="text-gray-500 text-sm mt-2">Maaf, pendaftaran Anda tidak dapat diproses lebih lanjut.</p>
                                    @break
                            @endswitch
                        @endif
                    </div>

                    {{-- Info Singkat --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-gray-500 text-xs font-medium uppercase tracking-wide mb-1">Tanggal Daftar</p>
                            <p class="text-gray-800 font-semibold">{{ $santri->created_at->translatedFormat('d F Y, H:i') }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-gray-500 text-xs font-medium uppercase tracking-wide mb-1">NIK</p>
                            <p class="text-gray-800 font-semibold">{{ $santri->nik }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-gray-500 text-xs font-medium uppercase tracking-wide mb-1">NISN</p>
                            <p class="text-gray-800 font-semibold">{{ $santri->nisn }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-gray-500 text-xs font-medium uppercase tracking-wide mb-1">Sekolah Asal</p>
                            <p class="text-gray-800 font-semibold">{{ $santri->sekolah_asal }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABUNGAN / UANG SAKU CARD FOR WALISANTRI --}}
            @if($santri->status === 'diterima')
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-teal-100">
                <div class="bg-gradient-to-r from-teal-600 to-emerald-600 px-6 py-5 flex items-center justify-between text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-2xl font-bold">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-white">Uang Saku & Belanja Santri</h2>
                            <p class="text-xs text-teal-100">Pantau sisa uang saku dan perincian belanja ananda secara transparan</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Balance Summary Bar -->
                    <div class="bg-gradient-to-br from-teal-50 to-emerald-50 border border-teal-200 rounded-2xl p-5 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div>
                            <span class="text-xs font-bold text-teal-700 uppercase tracking-wider">Sisa Saldo Uang Saku {{ $santri->nama_lengkap }} Saat Ini</span>
                            <h3 class="text-3xl font-extrabold text-teal-800 mt-1">
                                Rp {{ number_format($santri->saldo_uang_saku, 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="text-xs text-teal-700 bg-white/80 px-4 py-2 rounded-xl border border-teal-200 shadow-sm text-center sm:text-right">
                            <i class="fas fa-info-circle text-teal-500 mr-1"></i> Penambahan uang saku dilakukan melalui pengurus / admin pesantren.
                        </div>
                    </div>

                    <!-- Transaction History -->
                    <h3 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2 border-b pb-2">
                        <i class="fas fa-list-alt text-teal-600"></i> Riwayat & Perincian Belanja
                    </h3>

                    @if($santri->uangSaku && $santri->uangSaku->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 text-xs uppercase font-bold">
                                        <th class="py-3 px-4">Tanggal</th>
                                        <th class="py-3 px-4 text-center">Jenis</th>
                                        <th class="py-3 px-4 text-right">Mutasi Pemasukan &amp; Pengeluaran (Rp)</th>
                                        <th class="py-3 px-4">Perincian / Catatan</th>
                                        <th class="py-3 px-4 text-right">Saldo Akhir</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($santri->uangSaku as $trans)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="py-3.5 px-4 text-gray-600 text-xs font-medium whitespace-nowrap">
                                            {{ $trans->created_at->translatedFormat('d M Y, H:i') }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            @if($trans->jenis === 'masuk')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                    <i class="fas fa-arrow-down"></i> Titip
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                                    <i class="fas fa-shopping-bag"></i> Belanja
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-bold whitespace-nowrap">
                                            @if($trans->jenis === 'masuk')
                                                <span class="text-green-600">+ {{ number_format($trans->jumlah, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-red-600">- {{ number_format($trans->jumlah, 0, ',', '.') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-gray-800 font-medium">
                                            {{ $trans->keterangan }}
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-extrabold text-teal-700 whitespace-nowrap">
                                            Rp {{ number_format($trans->saldo_akhir, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-400 bg-gray-50 rounded-xl">
                            <i class="fas fa-receipt text-3xl mb-2 block"></i>
                            <p class="text-sm font-medium">Belum ada catatan transaksi uang saku.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Info tambahan --}}
            <div class="bg-teal-50 border border-teal-200 rounded-xl p-4 text-sm text-teal-800 flex items-start gap-3">
                <i class="fas fa-info-circle text-teal-500 mt-0.5"></i>
                <div>
                    <strong>Informasi:</strong> Jika ada pertanyaan mengenai uang saku atau status pendaftaran, silakan hubungi pengurus pesantren melalui WhatsApp.
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
