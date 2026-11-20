<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 leading-tight flex items-center gap-2">
            <i class="fas fa-money-check-alt text-emerald-600"></i>
            {{ __('Biaya Masuk & Daftar Ulang Santri') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Alert Notifikasi -->
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-check-circle text-base text-emerald-600"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="fas fa-times"></i></button>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-semibold flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-exclamation-circle text-base text-red-600"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700"><i class="fas fa-times"></i></button>
                </div>
            @endif

            <!-- Tab Pilihan Santri (Jika Wali memiliki lebih dari 1 anak) -->
            @if($santris->count() > 1)
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold text-gray-500 mr-2 flex items-center gap-1.5">
                        <i class="fas fa-user-graduate text-emerald-600"></i> Pilih Santri:
                    </span>
                    @foreach($santris as $s)
                        <a href="{{ route('user.daftar_ulang.index', ['santri_id' => $s->id]) }}"
                           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $s->id === $activeSantri->id ? 'bg-emerald-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            <i class="fas fa-user"></i>
                            <span>{{ $s->nama_lengkap }}</span>
                            <span class="text-[10px] opacity-80">(Angkatan {{ $s->tahun_masuk }})</span>
                        </a>
                    @endforeach
                </div>
            @endif

            @php
                $isReady = $tagihan?->is_ready ?? false;
                $isBeasiswa = (bool) ($tagihan?->is_beasiswa ?? false);
            @endphp

            <!-- Header Info Santri -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-base font-bold text-gray-900">{{ $activeSantri->nama_lengkap }}</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                Diterima di Pondok
                            </span>
                            @if($isBeasiswa)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200 flex items-center gap-1">
                                    <i class="fas fa-award text-purple-600"></i> Penerima Beasiswa Penuh
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            NIS: <span class="font-mono font-semibold text-gray-700">{{ $activeSantri->nis ?: '-' }}</span> |
                            Angkatan: <span class="font-semibold text-gray-700">{{ $activeSantri->tahun_masuk }}</span>
                        </p>
                    </div>
                </div>
            </div>

            @if($isBeasiswa)
                <!-- Banner Beasiswa Penuh -->
                <div class="rounded-2xl shadow-sm p-6 text-white space-y-3" style="background: linear-gradient(135deg, #581c87 0%, #312e81 100%) !important;">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-2xl text-yellow-300 shrink-0">
                            <i class="fas fa-award"></i>
                        </div>
                        <div>
                            <span class="text-xs uppercase tracking-wider font-extrabold text-purple-200">Fasilitas Beasiswa Penuh</span>
                            <h3 class="text-lg font-black text-white">Bebas Seluruh Biaya Masuk & Daftar Ulang</h3>
                        </div>
                    </div>
                    <p class="text-xs text-purple-100 leading-relaxed">
                        Alhamdulillah, ananda <strong>{{ $activeSantri->nama_lengkap }}</strong> terdaftar sebagai santri penerima <strong>Beasiswa Penuh</strong> di Pesantren Alam Qur'an. Seluruh kewajiban biaya masuk dan daftar ulang santri baru dibebaskan (Rp 0), dan status administrasi ananda telah dinyatakan <strong>LUNAS</strong>.
                    </p>
                    <div class="pt-2 flex flex-wrap items-center gap-3">
                        <span class="px-3 py-1.5 rounded-xl bg-white/20 backdrop-blur-xs font-mono font-bold text-xs text-yellow-300">
                            Total Kewajiban: Rp 0
                        </span>
                        <span class="px-3 py-1.5 rounded-xl bg-emerald-500 font-bold text-xs text-white flex items-center gap-1.5 shadow-xs">
                            <i class="fas fa-check-circle"></i> Status: Lunas (Beasiswa Penuh)
                        </span>
                    </div>
                </div>
            @endif

            @if(!$isReady && !$isBeasiswa)
                <!-- KONDISI 1: JIKA ADMIN BELUM MENGATUR BIAYA & REKENING (SANGAT AMAN) -->
                <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 p-8 text-center space-y-4">
                    <div class="w-16 h-16 rounded-3xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl mx-auto shadow-xs">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="max-w-md mx-auto space-y-2">
                        <h3 class="text-lg font-bold text-gray-900">Rincian Biaya Daftar Ulang Sedang Disiapkan</h3>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Alhamdulillah, ananda <strong>{{ $activeSantri->nama_lengkap }}</strong> telah dinyatakan <strong>DITERIMA</strong>. 
                            Rincian tagihan biaya masuk & nomor rekening tujuan transfer sedang disiapkan oleh bagian bendahara pesantren.
                        </p>
                    </div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-50 border border-gray-200 text-gray-500 text-xs font-semibold">
                        <i class="fas fa-shield-alt text-emerald-600"></i> Tombol pembayaran akan otomatis aktif setelah diverifikasi oleh panitia.
                    </div>
                </div>
            @else
                <!-- KONDISI 2: JIKA SUDAH DIATUR OLEH ADMIN (ATAU BEASISWA) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    <!-- Kartu Rekening Tujuan Transfer (Desain Kontras Tinggi & Jelas Terbaca) -->
                    <div class="lg:col-span-5 bg-white rounded-2xl shadow-sm border-2 border-emerald-500 p-6 space-y-4 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3">
                                <span class="text-xs font-bold text-gray-600 uppercase flex items-center gap-1.5">
                                    <i class="fas fa-university text-emerald-600 text-base"></i> Rekening Tujuan Transfer:
                                </span>
                                <span class="px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-900 font-extrabold text-xs">
                                    {{ $rekInfo['bank'] }}
                                </span>
                            </div>

                            <div class="bg-emerald-50 border-2 border-emerald-300 rounded-xl p-4 space-y-1.5 mb-3">
                                <span class="text-[11px] text-emerald-900 block uppercase font-bold tracking-wide">
                                    Nomor Rekening / Virtual Account:
                                </span>
                                <div class="flex items-center justify-between gap-2">
                                    <span id="noRekText" class="text-2xl font-mono font-black tracking-wider text-emerald-950 select-all">
                                        {{ $rekInfo['nomor_rekening'] }}
                                    </span>
                                    <button type="button" onclick="copyRekening()" id="btnCopyRek"
                                            class="px-3 py-1.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm transition cursor-pointer flex items-center gap-1.5 shrink-0">
                                        <i class="fas fa-copy"></i> Salin
                                    </button>
                                </div>
                            </div>

                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs flex justify-between items-center">
                                <span class="text-gray-600 font-medium">Atas Nama:</span>
                                <strong class="text-gray-900 font-bold text-sm">{{ $rekInfo['atas_nama'] }}</strong>
                            </div>
                        </div>

                        @if($isBeasiswa)
                            <div class="text-[11px] text-purple-900 bg-purple-50 p-3 rounded-xl border border-purple-200 flex items-start gap-2">
                                <i class="fas fa-info-circle text-purple-600 text-xs mt-0.5 shrink-0"></i>
                                <span>Nomor rekening / VA ini adalah rekening identitas ananda di pondok. Ananda bebas biaya daftar ulang (tidak ada kewajiban transfer).</span>
                            </div>
                        @else
                            <div class="text-[11px] text-amber-900 bg-amber-50 p-3 rounded-xl border border-amber-200 flex items-start gap-2">
                                <i class="fas fa-info-circle text-amber-600 text-xs mt-0.5 shrink-0"></i>
                                <span>Transfer angsuran dapat dilakukan bertahap (dicicil) sesuai kesepakatan dengan pondok.</span>
                            </div>
                        @endif
                    </div>

                    <!-- Kartu Ringkasan Biaya & Progres Cicilan -->
                    <div class="lg:col-span-7 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3 flex-wrap gap-2">
                            <h3 class="font-bold text-sm text-gray-900 flex items-center gap-2 flex-wrap">
                                <span class="flex items-center gap-1.5">
                                    <i class="fas fa-file-invoice-dollar text-emerald-600"></i> Ringkasan Biaya Daftar Ulang
                                </span>
                                @if($tagihan)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-mono font-semibold bg-gray-100 text-gray-700 px-2 py-0.5 rounded border border-gray-200" title="Nomor Invoice">
                                        <i class="fas fa-receipt text-blue-600"></i> {{ $tagihan->nomor_invoice }}
                                    </span>
                                @endif
                            </h3>
                            <div class="flex items-center gap-2 flex-wrap">
                                @if($isBeasiswa)
                                    <span class="px-3 py-1 rounded-full text-xs font-black bg-purple-100 text-purple-800 border border-purple-200">
                                        🏆 BEASISWA PENUH
                                    </span>
                                @elseif($tagihan?->status === 'lunas')
                                    <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800">
                                        ✓ LUNAS
                                    </span>
                                @elseif($tagihan?->status === 'cicilan')
                                    <span class="px-3 py-1 rounded-full text-xs font-black bg-blue-100 text-blue-800">
                                        MENGANGSUR
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800">
                                        BELUM BAYAR
                                    </span>
                                @endif

                                @if($tagihan)
                                    <a href="{{ route('user.daftar_ulang.tagihan.invoice', $tagihan->id) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition shadow-xs"
                                       title="Lihat / Cetak / Download Invoice Resmi">
                                        <i class="fas fa-file-invoice text-emerald-600"></i> Invoice
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                <span class="text-[10px] text-gray-500 block uppercase font-bold">Total Kewajiban</span>
                                <span class="text-sm font-mono font-bold text-gray-900">
                                    Rp {{ number_format($isBeasiswa ? 0 : $totalKewajiban, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="bg-emerald-50 p-3 rounded-xl border border-emerald-100">
                                <span class="text-[10px] text-emerald-700 block uppercase font-bold">Sudah Masuk</span>
                                <span class="text-sm font-mono font-bold text-emerald-700">
                                    Rp {{ number_format($isBeasiswa ? 0 : $totalTerbayar, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="bg-amber-50 p-3 rounded-xl border border-amber-100">
                                <span class="text-[10px] text-amber-700 block uppercase font-bold">Sisa Tagihan</span>
                                <span class="text-sm font-mono font-bold text-amber-700">
                                    Rp {{ number_format($isBeasiswa ? 0 : $totalSisa, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        @if($isAccumulated)
                            <!-- Rincian Akumulasi Tagihan Tahunan -->
                            <div class="p-3.5 bg-blue-50/80 border border-blue-200 rounded-xl space-y-2 text-xs">
                                <div class="font-bold text-blue-950 flex items-center justify-between">
                                    <span class="flex items-center gap-1.5"><i class="fas fa-layer-group text-blue-600"></i> Akumulasi Tagihan Tahunan:</span>
                                    <span class="text-[10px] text-blue-700 font-semibold">{{ $tagihans->count() }} Periode</span>
                                </div>
                                <div class="space-y-1.5 text-[11px]">
                                    @foreach($tagihans as $t)
                                        <div class="flex items-center justify-between py-1 border-b border-blue-100 last:border-0">
                                            <div>
                                                <span class="font-bold text-gray-800">Daftar Ulang {{ $t->tahun_angkatan }}</span>
                                                <span class="text-gray-500 text-[10px]">({{ $t->gelombang ?: 'Reguler' }})</span>
                                            </div>
                                            <div class="font-mono text-right">
                                                <span class="text-gray-600">Biaya: Rp {{ number_format($t->total_tagihan, 0, ',', '.') }}</span> |
                                                <span class="{{ $t->sisa_tagihan > 0 ? 'text-amber-700 font-bold' : 'text-emerald-700 font-bold' }}">
                                                    Sisa: Rp {{ number_format($t->sisa_tagihan, 0, ',', '.') }}
                                                </span>
                                                @if($t->status === 'lunas')
                                                    <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] bg-emerald-100 text-emerald-800 font-bold">Lunas</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($tagihan && $tagihan->addons && $tagihan->addons->count() > 0)
                            <!-- Rincian Biaya Tambahan (Add-on) -->
                            <div class="p-3.5 bg-purple-50/70 border border-purple-200 rounded-xl space-y-2 text-xs">
                                <div class="font-bold text-purple-950 flex items-center justify-between">
                                    <span class="flex items-center gap-1.5"><i class="fas fa-receipt text-purple-600"></i> Rincian Tagihan & Biaya Tambahan (Add-on):</span>
                                    <span class="text-[10px] text-purple-700 font-semibold">{{ $tagihan->addons->count() }} Item Add-on</span>
                                </div>
                                <div class="space-y-1 text-[11px]">
                                    <div class="flex justify-between items-center text-gray-700 py-0.5 border-b border-purple-100">
                                        <span>Biaya Pokok ({{ $tagihan->gelombang ?: 'Reguler' }}):</span>
                                        <strong class="font-mono text-gray-900">Rp {{ number_format($tagihan->nominal_biaya, 0, ',', '.') }}</strong>
                                    </div>
                                    @foreach($tagihan->addons as $addon)
                                        <div class="flex justify-between items-center text-purple-900 py-0.5 border-b border-purple-100">
                                            <span>Add-on: {{ $addon->nama_biaya }}{{ $addon->keterangan ? ' ('.$addon->keterangan.')' : '' }}</span>
                                            <strong class="font-mono text-purple-800">+ Rp {{ number_format($addon->nominal, 0, ',', '.') }}</strong>
                                        </div>
                                    @endforeach
                                    <div class="flex justify-between items-center text-purple-950 font-bold pt-1">
                                        <span>Total Biaya Keseluruhan:</span>
                                        <strong class="font-mono text-purple-900 text-xs">Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Progress Bar Cicilan -->
                        @php
                            $persen = $isBeasiswa ? 100 : (($totalKewajiban > 0) ? min(100, round(($totalTerbayar / $totalKewajiban) * 100)) : 100);
                        @endphp
                        <div>
                            <div class="flex justify-between text-[11px] mb-1 font-semibold">
                                <span class="text-gray-500">Progres Pelunasan:</span>
                                <span class="text-emerald-700 font-mono">{{ $persen }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="h-2.5 rounded-full transition-all duration-500 {{ $persen >= 100 ? 'bg-emerald-500' : 'bg-blue-500' }}"
                                     style="width: {{ $persen }}%"></div>
                            </div>
                        </div>

                        @if($isBeasiswa)
                            <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl text-xs text-purple-900 font-semibold flex items-center gap-2">
                                <i class="fas fa-award text-purple-600 text-base shrink-0"></i>
                                <span>Ananda mendapatkan fasilitas Beasiswa Penuh (Bebas Biaya Masuk Rp 0). Tidak perlu melakukan transfer atau pembayaran apapun.</span>
                            </div>
                        @elseif($totalSisa <= 0)
                            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 font-semibold flex items-center gap-2">
                                <i class="fas fa-check-circle text-emerald-600 text-base"></i>
                                <span>Alhamdulillah, seluruh biaya daftar ulang santri ananda telah lunas.</span>
                            </div>
                        @elseif(!$isPaymentOpen)
                            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 font-medium space-y-1">
                                <div class="font-bold flex items-center gap-1.5 text-amber-800">
                                    <i class="fas fa-lock"></i> Pembayaran Ditutup Sementara
                                </div>
                                <p class="text-[11px] text-amber-700 leading-relaxed">
                                    {{ $daftarUlangSetting->pesan_tutup ?: 'Pembayaran / angsuran daftar ulang periode ini belum dibuka. Silakan menunggu informasi resmi dari pesantren.' }}
                                </p>
                                @if($daftarUlangSetting->status === 'otomatis' && $daftarUlangSetting->tanggal_buka)
                                    <div class="text-[10px] text-amber-600 font-bold mt-1">
                                        📅 Jadwal Buka: Mulai {{ $daftarUlangSetting->tanggal_buka->translatedFormat('d F Y') }}
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="pt-2 flex justify-end">
                                <button type="button" onclick="openBayarModal()"
                                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md transition cursor-pointer flex items-center gap-2">
                                    <i class="fas fa-credit-card"></i> Bayar / Kirim Bukti Angsuran
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Riwayat Pembayaran Angsuran -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 flex-wrap gap-2">
                        <h3 class="font-bold text-sm text-gray-900 flex items-center gap-2">
                            <i class="fas fa-history text-emerald-600"></i> Riwayat Angsuran & Bukti Pembayaran
                        </h3>
                        @if($tagihan)
                            <a href="{{ route('user.daftar_ulang.tagihan.invoice', $tagihan->id) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition shadow-xs"
                               title="Lihat / Cetak / Download Invoice Resmi">
                                <i class="fas fa-file-invoice text-emerald-600"></i> Invoice / Kuitansi
                            </a>
                        @endif
                    </div>

                    @if($tagihan->pembayarans->isEmpty())
                        <div class="p-8 text-center text-gray-400 text-xs bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            <i class="fas fa-receipt text-3xl mb-2 block text-gray-300"></i>
                            Belum ada riwayat pembayaran yang dikirimkan.
                        </div>
                    @else
                        <div class="space-y-2.5">
                            @foreach($tagihan->pembayarans as $pemb)
                                <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200 text-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div>
                                        <div class="font-bold text-gray-900 flex items-center gap-2">
                                            <span>{{ $pemb->created_at->translatedFormat('d F Y, H:i') }}</span>
                                            <span class="text-gray-400 font-normal">(via {{ strtoupper($pemb->metode_bayar) }})</span>
                                        </div>
                                        @if($pemb->catatan)
                                            <div class="text-gray-600 text-[11px] mt-0.5">Catatan: "{{ $pemb->catatan }}"</div>
                                        @endif
                                        @if($pemb->status === 'ditolak' && $pemb->alasan_penolakan)
                                            <div class="text-red-600 text-[11px] font-semibold mt-0.5">Alasan Penolakan: {{ $pemb->alasan_penolakan }}</div>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <div class="text-right">
                                            <div class="font-mono font-bold text-sm text-gray-900">
                                                Rp {{ number_format($pemb->jumlah_bayar, 0, ',', '.') }}
                                            </div>
                                            <div>
                                                @if($pemb->status === 'menunggu_konfirmasi')
                                                    <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                                                        Menunggu Verifikasi
                                                    </span>
                                                @elseif($pemb->status === 'ditolak')
                                                    <span class="text-[10px] font-bold text-red-700 bg-red-100 px-2 py-0.5 rounded-full">
                                                        Ditolak
                                                    </span>
                                                @else
                                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">
                                                        Diterima / Masuk
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        @if($pemb->bukti_bayar)
                                            <a href="{{ Storage::url($pemb->bukti_bayar) }}" target="_blank"
                                               class="p-2 text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 rounded-lg text-xs font-bold transition flex items-center gap-1"
                                               title="Lihat Bukti Transfer">
                                                <i class="fas fa-image"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Modal Upload Bukti Pembayaran / Angsuran (Ringkas & Tidak Terpotong) -->
                <div id="modalBayar" class="fixed inset-0 z-[100] hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
                    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in">
                        <div class="px-5 py-3.5 border-b border-emerald-700 flex items-center justify-between shrink-0" style="background-color: #047857; color: #ffffff;">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-receipt text-lg text-emerald-200"></i>
                                <h3 class="text-sm font-bold text-white tracking-wide">
                                    Kirim Bukti Pembayaran Daftar Ulang
                                </h3>
                            </div>
                            <button type="button" onclick="closeBayarModal()" class="cursor-pointer p-1 text-white/80 hover:text-white transition">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>

                        <form action="{{ route('user.daftar_ulang.konfirmasi', $tagihan->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                            @csrf
                            <div class="p-4 sm:p-5 space-y-3.5 text-xs overflow-y-auto flex-1">
                                <!-- Ringkasan Singkat -->
                                <div class="p-3 bg-emerald-50/80 border border-emerald-200 rounded-xl space-y-1.5">
                                    <div class="flex justify-between items-center text-[11px]">
                                        <span class="text-gray-600 font-semibold">Santri:</span>
                                        <strong class="text-gray-900">{{ $activeSantri->nama_lengkap }}</strong>
                                    </div>
                                    <div class="flex justify-between items-center text-[11px]">
                                        <span class="text-gray-600">Total Sisa Tagihan Saat Ini:</span>
                                        <strong class="font-mono text-amber-700 font-bold">Rp {{ number_format($totalSisa, 0, ',', '.') }}</strong>
                                    </div>
                                    <div class="flex justify-between items-center text-[10px] text-gray-500 pt-1 border-t border-emerald-200">
                                        <span>No. Rekening Tujuan:</span>
                                        <span class="font-mono font-bold text-emerald-800">{{ $rekInfo['bank'] }} - {{ $rekInfo['nomor_rekening'] }}</span>
                                    </div>
                                    @if($tagihan && $tagihan->addons && $tagihan->addons->count() > 0)
                                        <div class="pt-1.5 border-t border-emerald-200 text-[10px] text-purple-900 space-y-0.5">
                                            <div class="font-bold text-purple-950 flex items-center justify-between">
                                                <span>Termasuk Add-on:</span>
                                                <span class="font-mono font-semibold text-purple-700">+ Rp {{ number_format($tagihan->nominal_addon, 0, ',', '.') }}</span>
                                            </div>
                                            @foreach($tagihan->addons as $ad)
                                                <div class="flex justify-between text-purple-800">
                                                    <span>• {{ $ad->nama_biaya }}</span>
                                                    <span class="font-mono">Rp {{ number_format($ad->nominal, 0, ',', '.') }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <!-- Input Nominal Bayar -->
                                <div>
                                    <label class="block text-gray-700 font-bold uppercase mb-1 text-[11px]">
                                        Nominal yang Ditransfer (Rp) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="jumlah_bayar" min="1000" max="{{ $totalSisa }}" value="{{ $totalSisa }}" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono font-bold text-emerald-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                    <span class="text-[10px] text-gray-400 mt-0.5 block">Ketik nominal sesuai yang Anda transfer (bisa dicicil).</span>
                                </div>

                                <input type="hidden" name="metode_bayar" value="transfer">

                                <!-- Upload Bukti -->
                                <div>
                                    <label class="block text-gray-700 font-bold uppercase mb-1 text-[11px]">
                                        Upload File Bukti Transfer <span class="text-red-500">*</span>
                                    </label>
                                    <input type="file" name="bukti_bayar" accept="image/*,.pdf" required
                                           class="w-full text-xs text-gray-500 border border-gray-300 rounded-lg file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-bold file:text-xs">
                                    <span class="text-[10px] text-gray-400 mt-0.5 block">Format: JPG, JPEG, PNG, PDF (Maksimal 5MB).</span>
                                </div>

                                <!-- Catatan -->
                                <div>
                                    <label class="block text-gray-700 font-bold uppercase mb-1 text-[11px]">
                                        Catatan / Keterangan (Opsional)
                                    </label>
                                    <input type="text" name="catatan" placeholder="Contoh: Angsuran ke-1 transfer via BCA a.n. Ayah"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500">
                                </div>
                            </div>

                            <div class="px-5 py-3 border-t border-gray-200 bg-gray-50 flex items-center justify-end gap-2 shrink-0">
                                <button type="button" onclick="closeBayarModal()" class="px-4 py-2 border border-gray-300 hover:bg-gray-100 text-gray-700 font-bold rounded-lg text-xs transition cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs shadow transition cursor-pointer flex items-center gap-1.5">
                                    <i class="fas fa-paper-plane"></i> Kirim Bukti Pembayaran
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <script>
        function copyRekening() {
            const noRek = document.getElementById('noRekText').innerText.trim();
            if (!noRek || noRek === 'Belum Diatur') return;

            navigator.clipboard.writeText(noRek).then(() => {
                const btn = document.getElementById('btnCopyRek');
                const prev = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Disalin!';
                setTimeout(() => { btn.innerHTML = prev; }, 2000);
            });
        }

        function openBayarModal() {
            const m = document.getElementById('modalBayar');
            if (m) m.classList.remove('hidden');
        }

        function closeBayarModal() {
            const m = document.getElementById('modalBayar');
            if (m) m.classList.add('hidden');
        }
    </script>
</x-app-layout>
