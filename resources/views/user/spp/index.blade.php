<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 flex items-center gap-2">
            <i class="ti ti-receipt text-blue-600"></i> Tagihan Administrasi & SPP Bulanan
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 pb-20 lg:pb-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="flex items-center gap-3 bg-green-50 border border-green-300 text-green-800 rounded-xl p-4 shadow-xs">
                    <i class="fas fa-check-circle text-green-600 text-xl flex-shrink-0"></i>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 bg-red-50 border border-red-300 text-red-800 rounded-xl p-4 shadow-xs">
                    <i class="fas fa-exclamation-circle text-red-600 text-xl flex-shrink-0"></i>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Santri Selector if parent has multiple children -->
            @if($santris->count() > 1)
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-700 uppercase">Pilih Santri:</span>
                    <div class="flex gap-2">
                        @foreach($santris as $s)
                            <a href="{{ route('user.spp.index', ['santri_id' => $s->id]) }}"
                               class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $selectedSantriId == $s->id ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                {{ $s->nama_lengkap }} (Kelas {{ $s->kelas }})
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($activeSantri)
                <!-- Santri Summary Card -->
                <div class="rounded-2xl bg-white p-5 shadow-sm border border-gray-100 flex flex-col sm:flex-row items-start justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">{{ $activeSantri->nama_lengkap }}</h3>
                            <span class="text-xs text-gray-500 font-mono">Kelas {{ $activeSantri->kelas }} | NIS: {{ $activeSantri->nis ?? '-' }}</span>
                        </div>
                    </div>
                    {{-- Info Rekening Tujuan Pembayaran Milik Santri --}}
                    @php
                        $rekInfo = $activeSantri->info_rekening_spp;
                        $hasRekening = !empty($activeSantri->nomor_rekening_spp);
                    @endphp
                    <div class="text-right">
                        <span class="text-[11px] font-bold text-gray-400 uppercase block mb-1">Rekening Pembayaran Santri Ini ({{ $activeSantri->nama_lengkap }})</span>
                        @if($hasRekening)
                        <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-right">
                            <div class="text-xs font-bold text-blue-900">{{ $rekInfo['bank'] }}</div>
                            <div class="text-base font-black font-mono text-blue-700 tracking-wider">
                                {{ $rekInfo['nomor_rekening'] }}
                            </div>
                            <div class="text-xs text-blue-800 font-semibold">{{ $rekInfo['atas_nama'] }}</div>
                        </div>
                        @else
                        <div class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-right">
                            <div class="text-xs font-semibold text-gray-500 italic">Rekening pembayaran sedang dipersiapkan.</div>
                            <div class="text-xs text-gray-400 mt-0.5">Silakan hubungi admin pondok untuk info lebih lanjut.</div>
                        </div>
                        @endif
                    </div>

                </div>

                <!-- Info Box -->
                <div class="rounded-xl border border-blue-200 bg-blue-50/80 p-4 text-xs text-blue-900 space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-blue-950">
                        <i class="fas fa-info-circle text-base text-blue-600"></i> Ketentuan Pembayaran SPP:
                    </div>
                    <p>1. Tagihan SPP diterbitkan setiap bulan (apabila bulan tersebut sudah dibayar di muka / lunas, maka tidak akan ditagih lagi).</p>
                    <p>2. Tagihan bulanan <strong>dapat dibayar lunas atau diangsur (dicicil)</strong> melalui menu pembayaran per bulan sesuai kesanggupan transfer.</p>
                    <p>3. Jika terdapat tunggakan beberapa bulan, wali santri dapat memilih <strong>Bayar Semua Tunggakan Sekaligus</strong> untuk berhemat (<strong>Biaya Admin cukup 1 kali transaksi</strong>).</p>
                </div>

                <!-- RINGKASAN TOTAL TAGIHAN KESELURUHAN & PEMBAYARAN LUNAS SEKALIGUS (COMPACT & PROPORSIONAL) -->
                <div class="rounded-xl bg-white shadow-sm border border-emerald-200 overflow-hidden">
                    <!-- Header Card Gradient Ringkas & Elegan -->
                    <div class="px-4 py-3 sm:px-5 sm:py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5"
                         style="background: linear-gradient(135deg, #065f46 0%, #047857 55%, #0f766e 100%); color: #ffffff;">
                        <div>
                            <div class="flex items-center gap-1.5 mb-0.5 flex-wrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white backdrop-blur-xs border border-white/25">
                                    <i class="fas fa-wallet text-emerald-300"></i>
                                    @if($unpaidTagihans->count() > 1)
                                        TOTAL KESELURUHAN TUNGGAKAN SPP
                                    @else
                                        TOTAL KEWAJIBAN SPP BULAN INI
                                    @endif
                                </span>
                                <span class="text-[11px] text-emerald-100 font-semibold">• {{ $activeSantri->nama_lengkap }}</span>
                            </div>
                            <h3 class="text-2xl font-black font-mono text-white tracking-tight">
                                Rp {{ number_format($totalHarusDibayar, 0, ',', '.') }}
                            </h3>
                            <div class="text-[11px] text-emerald-100/90 mt-0.5">
                                @if($totalTagihanKeseluruhan > 0)
                                    @if($unpaidTagihans->count() > 1)
                                        <span>Sudah mencakup <strong>{{ $unpaidTagihans->count() }} bulan</strong> tagihan + <strong>Cukup 1x Biaya Admin (Rp {{ number_format($biayaAdmin, 0, ',', '.') }})</strong></span>
                                    @else
                                        <span>Sudah mencakup 1 bulan tagihan ({{ $unpaidTagihans->first()?->bulan_label }}) + 1x Biaya Admin (Rp {{ number_format($biayaAdmin, 0, ',', '.') }})</span>
                                    @endif
                                @else
                                    <span>Alhamdulillah, seluruh tagihan SPP saat ini sudah LUNAS.</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            @if($hasPendingKonfirmasi)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-400 text-amber-950 font-bold rounded-lg text-xs shadow-xs">
                                    <i class="fas fa-hourglass-half text-amber-900 animate-spin"></i> Menunggu Verifikasi (Rp {{ number_format($totalPendingNominal, 0, ',', '.') }})
                                </span>
                            @else
                                @if($totalTagihanKeseluruhan > 0 && $unpaidTagihans->count() > 1)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-white/20 text-emerald-50 border border-white/30 backdrop-blur-xs">
                                        <i class="fas fa-check-circle text-emerald-300"></i> Hemat: Cukup 1x Admin Seluruh Bulan
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>

                    @if($totalTagihanKeseluruhan > 0)
                        <!-- Rincian Item Tagihan & Tombol Utama Bayar (Compact & Proporsional) -->
                        <div class="p-3 sm:p-4 bg-slate-50/70 border-t border-emerald-100 space-y-2.5 text-xs">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 pb-2 border-b border-gray-200">
                                <div class="font-bold text-gray-800 text-xs flex items-center gap-1.5">
                                    <i class="fas fa-list-check text-emerald-600"></i>
                                    <span>Rincian Seluruh Tagihan yang Harus Dilunasi ({{ $unpaidTagihans->count() }} Bulan)</span>
                                </div>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-900 border border-emerald-200 self-start sm:self-auto">
                                    @if($unpaidTagihans->count() > 1)
                                        <i class="fas fa-tags text-emerald-700"></i> Pelunasan Semua Bulan (Hemat 1x Admin)
                                    @else
                                        <i class="fas fa-calendar-check text-emerald-700"></i> Tagihan Periode Bulan Ini
                                    @endif
                                </span>
                            </div>

                            <!-- List Card Per Bulan -->
                            <div class="space-y-1.5">
                                @foreach($unpaidTagihans as $ut)
                                    <div class="p-2.5 px-3 bg-white rounded-lg border border-gray-200 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5">
                                        <div class="space-y-0.5">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="font-extrabold text-gray-900 text-xs">{{ $ut->bulan_label }}</span>
                                                <span class="text-[9px] font-mono font-bold bg-gray-100 text-gray-600 px-1.5 py-0.2 rounded border border-gray-200" title="Nomor Tagihan">{{ $ut->nomor_invoice }}</span>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-1.5 text-[11px] text-gray-600">
                                                <span class="inline-flex items-center gap-0.5 font-medium">
                                                    SPP Pokok: <strong class="font-mono text-gray-900 ml-0.5">Rp {{ number_format($ut->nominal_spp, 0, ',', '.') }}</strong>
                                                </span>
                                                @foreach($ut->addons as $addon)
                                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded bg-purple-50 border border-purple-200 font-semibold text-purple-700 text-[10px]">
                                                        + {{ $addon->nama_biaya }}: <strong class="font-mono ml-0.5">Rp {{ number_format($addon->nominal, 0, ',', '.') }}</strong>
                                                    </span>
                                                @endforeach
                                                @if($ut->nominal_terbayar > 0)
                                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded bg-green-50 border border-green-200 font-semibold text-green-700 text-[10px]">
                                                        - Sudah Dibayar: <strong class="font-mono ml-0.5">Rp {{ number_format($ut->nominal_terbayar, 0, ',', '.') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-left sm:text-right shrink-0">
                                            <span class="text-[9px] text-gray-400 block font-semibold uppercase">Subtotal</span>
                                            <span class="font-mono font-bold text-xs sm:text-sm text-gray-900">
                                                Rp {{ number_format($ut->sisa_tagihan, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Summary Box Kwitansi (Compact & Proporsional) -->
                            <div class="p-3 bg-white rounded-lg border border-gray-200 shadow-xs space-y-1.5">
                                <div class="flex justify-between items-center text-gray-600 text-[11px]">
                                    <span>Total Tagihan SPP ({{ $unpaidTagihans->count() }} Bulan SPP + Add-on):</span>
                                    <strong class="font-mono text-gray-900 text-xs">Rp {{ number_format($totalTagihanKeseluruhan, 0, ',', '.') }}</strong>
                                </div>
                                <div class="flex justify-between items-center text-[11px]">
                                    <span class="flex items-center gap-1 text-blue-900 font-medium">
                                        <i class="fas fa-tag text-blue-500 text-[10px]"></i> Biaya Admin (Cukup 1x Transaksi):
                                    </span>
                                    <strong class="font-mono text-blue-700 text-xs">+ Rp {{ number_format($biayaAdmin, 0, ',', '.') }}</strong>
                                </div>
                                <div class="pt-2 border-t border-emerald-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 p-2 px-3 bg-emerald-50 rounded-lg border border-emerald-200">
                                    <div>
                                        <span class="text-[10px] font-black uppercase text-emerald-950 tracking-wider block">TOTAL KESELURUHAN HARUS DITRANSFER (LUNAS):</span>
                                        <span class="text-[9px] text-emerald-700">Wajib transfer tepat sesuai nominal ini agar verifikasi lunas berjalan lancar.</span>
                                    </div>
                                    <div class="text-left sm:text-right">
                                        <span class="text-xl font-black font-mono text-emerald-800">
                                            Rp {{ number_format($totalHarusDibayar, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- TOMBOL UTAMA BAYAR LUNAS (COMPACT & PAS) -->
                                @if(!$hasPendingKonfirmasi)
                                    <div class="pt-1">
                                        <button type="button"
                                                onclick="openBayarSemuaModal()"
                                                class="w-full py-2.5 px-4 rounded-lg font-bold text-xs sm:text-sm text-white shadow-sm hover:shadow-md transition flex items-center justify-center gap-2 cursor-pointer"
                                                style="background: linear-gradient(135deg, #059669 0%, #0d9488 100%) !important;">
                                            <i class="fas fa-credit-card"></i>
                                            @if($unpaidTagihans->count() > 1)
                                                <span>KLIK DI SINI UNTUK BAYAR LUNAS SEMUA (Rp {{ number_format($totalHarusDibayar, 0, ',', '.') }})</span>
                                            @else
                                                <span>KLIK DI SINI UNTUK BAYAR LUNAS (Rp {{ number_format($totalHarusDibayar, 0, ',', '.') }})</span>
                                            @endif
                                            <i class="fas fa-arrow-right text-xs"></i>
                                        </button>
                                        <div class="text-[10px] text-gray-500 mt-1 text-center font-medium">
                                            @if($unpaidTagihans->count() > 1)
                                                <i class="fas fa-shield-alt text-emerald-600 mr-1"></i> Melunasi sekaligus {{ $unpaidTagihans->count() }} bulan tunggakan dengan cukup 1 kali biaya transfer bank / admin.
                                            @else
                                                <i class="fas fa-shield-alt text-emerald-600 mr-1"></i> Klik tombol di atas untuk melihat nomor rekening tujuan & konfirmasi upload bukti transfer.
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl space-y-1.5 text-xs text-amber-950">
                                        <div class="flex items-center gap-2 font-bold text-amber-900">
                                            <i class="fas fa-hourglass-half text-amber-700 animate-spin"></i>
                                            <span>Pembayaran Anda Sebesar Rp {{ number_format($totalPendingNominal, 0, ',', '.') }} Sedang Menunggu Verifikasi Bendahara</span>
                                        </div>
                                        <div class="text-amber-800 text-[11px]">
                                            Bukti transfer untuk periode <strong>{{ $pendingBulanLabels ?: 'SPP' }}</strong> telah dikirim dan sedang dalam proses validasi oleh bendahara pondok.
                                        </div>
                                        @php
                                            $tagihanBulanLain = $unpaidTagihans->filter(fn($t) => $t->pembayarans->where('status', 'menunggu_konfirmasi')->isEmpty());
                                        @endphp
                                        @if($tagihanBulanLain->isNotEmpty())
                                            <div class="text-[11px] text-amber-900 font-medium pt-1.5 border-t border-amber-200">
                                                <i class="fas fa-info-circle text-amber-700 mr-1"></i>
                                                Untuk tagihan bulan lainnya (<strong>{{ $tagihanBulanLain->pluck('bulan_label')->implode(', ') }}</strong>), Anda tetap dapat membayarnya melalui tombol pada daftar per bulan di bawah.
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Daftar Tagihan Bulanan -->
                <div class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-blue-600"></i> Riwayat Tagihan & Pembayaran Per Bulan
                        </h3>
                        @if($unpaidTagihans->count() > 1)
                            <span class="text-[11px] text-gray-500 font-medium">
                                Atau bayar bertahap per bulan di bawah (biaya admin per transaksi transfer)
                            </span>
                        @endif
                    </div>

                    @forelse($tagihans as $tagihan)
                        @php
                            $isWaiting = $tagihan->pembayarans->where('status', 'menunggu_konfirmasi')->isNotEmpty();
                        @endphp
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-100 pb-3">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="font-bold text-gray-900 text-base">{{ $tagihan->bulan_label }}</h4>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-mono font-semibold bg-gray-100 text-gray-700 px-2 py-0.5 rounded border border-gray-200" title="Nomor Invoice">
                                            <i class="fas fa-receipt text-blue-600"></i> {{ $tagihan->nomor_invoice }}
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-gray-400">Pembayaran melalui transfer ke Virtual Account / Rekening Santri.</span>
                                </div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if((float)$tagihan->nominal_spp === 0.0 && $tagihan->status === 'lunas')
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                                            <i class="fas fa-award"></i> LUNAS (BEASISWA FULL)
                                        </span>
                                    @elseif($tagihan->status === 'lunas')
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle"></i> SUDAH LUNAS
                                        </span>
                                    @elseif($isWaiting)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                            <i class="fas fa-clock"></i> MENUNGGU VERIFIKASI
                                        </span>
                                    @elseif($tagihan->status === 'cicilan' || $tagihan->nominal_terbayar > 0)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                            <i class="fas fa-coins text-blue-600"></i> MENGANGSUR (DICICIL)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-circle"></i> BELUM LUNAS
                                        </span>
                                    @endif

                                    <a href="{{ route('user.spp.tagihan.invoice', $tagihan->id) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition shadow-xs"
                                       title="Lihat / Cetak / Download Invoice Resmi">
                                        <i class="fas fa-file-invoice text-emerald-600"></i> Invoice
                                    </a>
                                </div>
                            </div>

                            <!-- Banner Jika Beasiswa Full dan Sudah Lunas -->
                            @if((float)$tagihan->nominal_spp === 0.0 && $tagihan->addons->isEmpty() && $tagihan->status === 'lunas')
                                <div class="p-3.5 rounded-xl bg-purple-50 border border-purple-200 flex items-center gap-3 text-xs text-purple-900 font-semibold">
                                    <i class="fas fa-award text-purple-600 text-2xl shrink-0"></i>
                                    <div>
                                        <div>Santri mendapatkan fasilitas <strong>Beasiswa Penuh (SPP Rp 0)</strong>.</div>
                                        <div class="text-[11px] text-purple-700 font-normal mt-0.5">Tagihan periode ini otomatis Lunas tanpa perlu melakukan transfer pembayaran.</div>
                                    </div>
                                </div>
                            @elseif($tagihan->status === 'lunas')
                                <div class="p-3 rounded-xl bg-green-50 border border-green-200 flex items-center justify-between text-xs text-green-900">
                                    <div class="flex items-center gap-2 font-semibold">
                                        <i class="fas fa-check-circle text-green-600 text-base"></i>
                                        <span>Tagihan bulan {{ $tagihan->bulan_label }} sudah <strong>LUNAS</strong> (Tidak akan ditagih lagi).</span>
                                    </div>
                                    <strong class="font-mono text-green-800">Rp {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}</strong>
                                </div>
                            @elseif($tagihan->nominal_terbayar > 0 && $tagihan->status !== 'lunas')
                                <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 text-xs text-blue-900">
                                    <div class="flex items-center gap-2 font-semibold">
                                        <i class="fas fa-coins text-blue-600 text-base"></i>
                                        <span>Status tagihan bulan {{ $tagihan->bulan_label }} sedang <strong>MENGANGSUR (DICICIL)</strong>.</span>
                                    </div>
                                    <span class="text-blue-800 font-mono font-bold text-xs">
                                        Sudah Masuk: Rp {{ number_format($tagihan->nominal_terbayar, 0, ',', '.') }} / {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif

                            <!-- Rincian Tagihan Bulan Ini -->
                            <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200/80 space-y-1.5 text-xs">
                                <div class="flex justify-between items-center text-gray-700">
                                    <span>SPP Pokok ({{ $tagihan->bulan_label }}):</span>
                                    <strong class="font-mono">
                                        @if((float)$tagihan->nominal_spp === 0.0)
                                            <span class="text-purple-700 font-bold">Rp 0 (Beasiswa Full)</span>
                                        @else
                                            Rp {{ number_format($tagihan->nominal_spp, 0, ',', '.') }}
                                        @endif
                                    </strong>
                                </div>
                                @foreach($tagihan->addons as $addon)
                                    <div class="flex justify-between items-center text-purple-800 font-semibold">
                                        <span>+ Add-on ({{ $addon->nama_biaya }}{{ $addon->keterangan ? ' - ' . $addon->keterangan : '' }}):</span>
                                        <strong class="font-mono">+ Rp {{ number_format($addon->nominal, 0, ',', '.') }}</strong>
                                    </div>
                                @endforeach
                                <div class="flex justify-between items-center text-gray-900 font-bold pt-1 border-t border-gray-200">
                                    <span>Total Tagihan Bulan Ini:</span>
                                    <strong class="font-mono">Rp {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}</strong>
                                </div>
                                @if($tagihan->nominal_terbayar > 0)
                                    <div class="flex justify-between items-center text-emerald-700 font-bold">
                                        <span>- Sudah Dibayar (Angsuran):</span>
                                        <strong class="font-mono">- Rp {{ number_format($tagihan->nominal_terbayar, 0, ',', '.') }}</strong>
                                    </div>
                                @endif
                                @if($tagihan->status !== 'lunas')
                                    <div class="flex justify-between items-center text-red-700 font-bold">
                                        <span>Sisa Tagihan Bulan Ini:</span>
                                        <strong class="font-mono">Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}</strong>
                                    </div>
                                    @if(!$isWaiting)
                                        <div class="pt-2 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                            <span class="text-[11px] text-gray-500">
                                                <i class="fas fa-info-circle text-emerald-600 mr-1"></i>
                                                @if($tagihan->nominal_terbayar > 0)
                                                    Sisa angsuran SPP Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }} + 1x Biaya Admin Rp {{ number_format($biayaAdmin, 0, ',', '.') }}. Bisa dilunasi atau diangsur kembali.
                                                @else
                                                    Bisa dibayar lunas atau diangsur (SPP Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }} + 1x Biaya Admin Rp {{ number_format($biayaAdmin, 0, ',', '.') }}).
                                                @endif
                                            </span>
                                            <button type="button"
                                                    onclick='openBayarSingleModal({{ $tagihan->id }}, {{ json_encode($tagihan->bulan_label) }}, {{ (float)$tagihan->nominal_spp }}, {{ json_encode($tagihan->addons) }}, {{ (float)$tagihan->sisa_tagihan }}, {{ (float)$biayaAdmin }})'
                                                    class="inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs shadow-xs transition cursor-pointer shrink-0">
                                                <i class="fas fa-credit-card"></i> Bayar Bulan Ini (Rp {{ number_format($tagihan->sisa_tagihan + $biayaAdmin, 0, ',', '.') }})
                                            </button>
                                        </div>
                                    @else
                                        @php
                                            $pembPending = $tagihan->pembayarans->where('status', 'menunggu_konfirmasi')->first();
                                        @endphp
                                        <div class="pt-2 border-t border-amber-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5 bg-amber-50/80 p-2.5 rounded-lg border border-amber-200 text-amber-900 text-xs">
                                            <span class="flex items-center gap-1.5 font-semibold">
                                                <i class="fas fa-hourglass-half text-amber-600 animate-spin"></i>
                                                Pembayaran bulan {{ $tagihan->bulan_label }} sebesar <strong>Rp {{ number_format($pembPending?->jumlah_bayar, 0, ',', '.') }}</strong> sedang menunggu verifikasi bendahara.
                                            </span>
                                            <span class="text-[10px] text-amber-700 italic">Sedang divalidasi admin</span>
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <!-- Riwayat Pembayaran -->
                            @if($tagihan->pembayarans->isNotEmpty())
                                <div class="pt-2 border-t border-gray-100">
                                    <span class="text-[11px] font-bold text-gray-400 uppercase block mb-1.5">Riwayat Pembayaran & Bukti Transfer:</span>
                                    <div class="space-y-1.5">
                                        @foreach($tagihan->pembayarans as $pemb)
                                            <div class="text-xs flex items-center justify-between text-gray-700 bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                                                <div>
                                                    <span class="font-semibold">{{ $pemb->created_at->format('d M Y H:i') }}</span>
                                                    <span class="text-gray-400 ml-1">(via {{ strtoupper($pemb->metode_bayar) }})</span>
                                                    @if($pemb->status === 'menunggu_konfirmasi')
                                                        <span class="ml-1 font-bold text-amber-700">MENUNGGU VERIFIKASI BENDAHARA</span>
                                                    @elseif($pemb->status === 'ditolak')
                                                        <span class="ml-1 font-bold text-red-700">DITOLAK</span>
                                                    @else
                                                        <span class="ml-1 font-bold text-emerald-700">LUNAS / DIVERIFIKASI</span>
                                                    @endif
                                                    @if($pemb->catatan)
                                                        <span class="text-gray-500 block text-[11px]">{{ $pemb->catatan }}</span>
                                                    @endif
                                                    @if($pemb->status === 'ditolak' && $pemb->alasan_penolakan)
                                                        <span class="block text-[11px] font-semibold text-red-700">Alasan penolakan: {{ $pemb->alasan_penolakan }}</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <span class="font-mono font-bold {{ $pemb->status === 'diterima' ? 'text-green-700' : 'text-gray-600' }} text-sm">Rp {{ number_format($pemb->jumlah_bayar, 0, ',', '.') }}</span>
                                                    @if($pemb->bukti_bayar)
                                                        <a href="{{ Storage::url($pemb->bukti_bayar) }}" target="_blank"
                                                           class="inline-flex items-center gap-1 text-[11px] text-blue-600 font-bold hover:underline">
                                                            <i class="fas fa-file-image"></i> Bukti
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="bg-white rounded-2xl p-12 text-center text-gray-400 text-xs border border-gray-200">
                            <i class="fas fa-file-invoice text-4xl mb-2 block text-gray-300"></i>
                            Belum ada riwayat tagihan SPP untuk santri ini.
                        </div>
                    @endforelse
                </div>
            @endif

        </div>
    </div>

    @if($activeSantri)
    @php
        $rekInfoModal = $activeSantri->info_rekening_spp;
        $paidMonthsMap = $tagihans->where('status', 'lunas')->mapWithKeys(fn($t) => [$t->tahun . '-' . $t->bulan => true]);
        $unpaidMonthsMap = $unpaidTagihans->mapWithKeys(fn($t) => [$t->tahun . '-' . $t->bulan => (float)$t->sisa_tagihan]);
        $tarifDefaultSantri = (float) $activeSantri->tarif_spp;
    @endphp

    <!-- Modal 1: Bayar Lunas Semua Tagihan Keseluruhan (Tidak Bisa Mengangsur, Biaya Admin 1x) -->
    <div id="modalBayarSemua" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in">
            <div class="px-5 py-3.5 border-b border-emerald-700 flex items-center justify-between shrink-0" style="background-color: #047857; color: #ffffff;">
                <div class="flex items-center gap-2">
                    <i class="fas fa-check-double text-lg text-emerald-200"></i>
                    <h3 class="text-sm font-bold text-white tracking-wide">
                        Pembayaran Lunas Keseluruhan SPP
                    </h3>
                </div>
                <button type="button" onclick="closeBayarSemuaModal()" class="cursor-pointer p-1 text-white/80 hover:text-white transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <form action="{{ route('user.spp.konfirmasi_semua') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <input type="hidden" name="santri_id" value="{{ $activeSantri->id }}">
                <input type="hidden" name="mode_bayar" value="semua">
                <input type="hidden" name="metode_bayar" value="transfer">

                <div class="p-4 sm:p-5 space-y-3.5 text-xs overflow-y-auto flex-1">
                    <!-- Info Rekening Tujuan -->
                    <div class="p-3 bg-emerald-50/90 border border-emerald-200 rounded-xl space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-700 font-bold text-[11px]">Tujuan Transfer ({{ $activeSantri->nama_lengkap }}):</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-200 text-emerald-900">{{ $rekInfoModal['bank'] }}</span>
                        </div>
                        <div class="flex items-center justify-between bg-white px-2.5 py-1.5 rounded-lg border border-emerald-200">
                            <div>
                                <span class="text-[9px] text-gray-400 block font-semibold leading-tight">No. VA / Rekening Tujuan:</span>
                                <span id="modalRekNomorAll" class="font-mono font-black text-xs text-emerald-800 tracking-wider">{{ $rekInfoModal['nomor_rekening'] }}</span>
                                <span class="text-[10px] text-gray-500 block">{{ $rekInfoModal['atas_nama'] }}</span>
                            </div>
                            <button type="button" onclick="copyTextById('modalRekNomorAll', 'btnCopyRekAll')" id="btnCopyRekAll" class="px-2.5 py-1 text-[10px] font-bold bg-emerald-700 hover:bg-emerald-800 text-white rounded shadow-xs transition flex items-center gap-1 cursor-pointer">
                                <i class="fas fa-copy"></i> Salin
                            </button>
                        </div>

                        <div class="space-y-1 pt-1 border-t border-emerald-200/80 text-[11px]">
                            <div class="font-bold text-gray-700">Rincian Bulan yang Dilunasi ({{ $unpaidTagihans->count() }} Bulan):</div>
                            @foreach($unpaidTagihans as $ut)
                                <div class="flex justify-between text-gray-700">
                                    <span>• {{ $ut->bulan_label }}{{ $ut->addons->isNotEmpty() ? ' (+Add-on)' : '' }}</span>
                                    <strong class="font-mono">Rp {{ number_format($ut->sisa_tagihan, 0, ',', '.') }}</strong>
                                </div>
                            @endforeach
                            <div class="flex justify-between text-blue-800 pt-1 border-t border-emerald-200/60">
                                <span>+ Biaya Admin (Cukup 1x Transaksi):</span>
                                <strong class="font-mono">Rp {{ number_format($biayaAdmin, 0, ',', '.') }}</strong>
                            </div>
                            <div class="flex justify-between items-center text-sm pt-1 border-t border-emerald-300 text-emerald-900 font-black">
                                <span>Total Wajib Transfer (Lunas):</span>
                                <strong class="font-mono text-emerald-700">Rp {{ number_format($totalHarusDibayar, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Nominal Terkunci (Tidak Bisa Mengangsur) -->
                    <div>
                        <label class="block text-gray-700 font-bold uppercase mb-1 text-[11px]">
                            Nominal Pembayaran Lunas (Rp) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="jumlah_bayar" value="{{ $totalHarusDibayar }}" readonly
                               class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-lg text-sm font-mono font-black text-emerald-800 cursor-not-allowed">
                        <span class="text-[10px] text-amber-700 font-semibold mt-0.5 block">
                            <i class="fas fa-lock mr-1"></i> Nominal dikunci otomatis sesuai Total Keseluruhan + 1x Biaya Admin (Tidak dapat diangsur).
                        </span>
                    </div>

                    <!-- Input Upload File Bukti -->
                    <div>
                        <label class="block text-gray-700 font-bold uppercase mb-1 text-[11px]">
                            Upload Bukti Transfer <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="bukti_bayar" accept="image/*,.pdf" required
                               class="w-full text-xs text-gray-500 border border-gray-300 rounded-lg file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-bold file:text-xs">
                        <span class="text-[10px] text-gray-400 mt-0.5 block">Format: JPG, JPEG, PNG, PDF (Maksimal 5MB).</span>
                    </div>

                    <!-- Input Catatan Opsional -->
                    <div>
                        <label class="block text-gray-700 font-bold uppercase mb-1 text-[11px]">
                            Catatan (Opsional)
                        </label>
                        <input type="text" name="catatan" placeholder="Contoh: Pelunasan SPP via rekening Ayah"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="px-5 py-3 border-t border-gray-200 bg-gray-50 flex items-center justify-end gap-2 shrink-0">
                    <button type="button" onclick="closeBayarSemuaModal()" class="px-4 py-2 border border-gray-300 hover:bg-gray-100 text-gray-700 font-bold rounded-lg text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs shadow transition cursor-pointer flex items-center gap-1.5">
                        <i class="fas fa-paper-plane"></i> Kirim Bukti Pelunasan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Bayar Lunas Single Bulan (Jika wali santri memilih bayar per bulan tertentu) -->
    <div id="modalBayarSingle" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-gray-100 overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in">
            <div class="px-5 py-3.5 border-b border-emerald-700 flex items-center justify-between shrink-0" style="background-color: #047857; color: #ffffff;">
                <div class="flex items-center gap-2">
                    <i class="fas fa-calendar-check text-lg text-emerald-200"></i>
                    <h3 class="text-sm font-bold text-white tracking-wide" id="singleModalTitle">
                        Pembayaran SPP Bulan ...
                    </h3>
                </div>
                <button type="button" onclick="closeBayarSingleModal()" class="cursor-pointer p-1 text-white/80 hover:text-white transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <form id="singleBayarForm" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <input type="hidden" name="metode_bayar" value="transfer">

                <div class="p-4 sm:p-5 space-y-3.5 text-xs overflow-y-auto flex-1">
                    <!-- Info Rekening Tujuan -->
                    <div class="p-3 bg-emerald-50/90 border border-emerald-200 rounded-xl space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-700 font-bold text-[11px]">Tujuan Transfer ({{ $activeSantri->nama_lengkap }}):</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-200 text-emerald-900">{{ $rekInfoModal['bank'] }}</span>
                        </div>
                        <div class="flex items-center justify-between bg-white px-2.5 py-1.5 rounded-lg border border-emerald-200">
                            <div>
                                <span class="text-[9px] text-gray-400 block font-semibold leading-tight">No. VA / Rekening Tujuan:</span>
                                <span id="modalRekNomorSingle" class="font-mono font-black text-xs text-emerald-800 tracking-wider">{{ $rekInfoModal['nomor_rekening'] }}</span>
                                <span class="text-[10px] text-gray-500 block">{{ $rekInfoModal['atas_nama'] }}</span>
                            </div>
                            <button type="button" onclick="copyTextById('modalRekNomorSingle', 'btnCopyRekSingle')" id="btnCopyRekSingle" class="px-2.5 py-1 text-[10px] font-bold bg-emerald-700 hover:bg-emerald-800 text-white rounded shadow-xs transition flex items-center gap-1 cursor-pointer">
                                <i class="fas fa-copy"></i> Salin
                            </button>
                        </div>

                        <div class="space-y-1 pt-1 border-t border-emerald-200/80 text-[11px]">
                            <div class="flex justify-between text-gray-700">
                                <span id="singleLabelSpp">SPP Pokok:</span>
                                <strong class="font-mono" id="singleValSpp">Rp 0</strong>
                            </div>
                            <div id="singleAddonsContainer" class="space-y-1"></div>
                            <div class="flex justify-between text-blue-800 pt-1 border-t border-emerald-200/60">
                                <span>+ Biaya Admin (1x Transaksi):</span>
                                <strong class="font-mono" id="singleValAdmin">Rp 0</strong>
                            </div>
                            <div class="flex justify-between items-center text-sm pt-1 border-t border-emerald-300 text-emerald-900 font-black">
                                <span>Total Wajib Transfer (Lunas):</span>
                                <strong class="font-mono text-emerald-700" id="singleValTotal">Rp 0</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Input Nominal Pembayaran (Bisa Diangsur / Tidak Terkunci) -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-gray-700 font-bold uppercase text-[11px]">
                                Nominal Pembayaran (Rp) <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                <i class="fas fa-check-circle"></i> Bisa Dicicil / Diangsur
                            </span>
                        </div>
                        <input type="number" id="singleInputJumlahBayar" name="jumlah_bayar" min="1000" step="500" required
                               class="w-full px-3 py-2 border border-emerald-400 bg-white rounded-lg text-sm font-mono font-bold text-gray-900 focus:ring-2 focus:ring-emerald-500">
                        <span class="text-[10px] text-gray-500 mt-1 block">
                            <i class="fas fa-pencil-alt text-emerald-600 mr-1"></i>
                            Silakan ubah nominal di atas jika ingin mengangsur/mencicil, atau biarkan tetap jika ingin bayar lunas (Maksimal pelunasan: <span id="singleMaxLabel" class="font-mono font-bold text-gray-800">Rp 0</span>).
                        </span>
                    </div>

                    <!-- Upload File Bukti -->
                    <div>
                        <label class="block text-gray-700 font-bold uppercase mb-1 text-[11px]">
                            Upload Bukti Transfer <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="bukti_bayar" accept="image/*,.pdf" required
                               class="w-full text-xs text-gray-500 border border-gray-300 rounded-lg file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-bold file:text-xs">
                        <span class="text-[10px] text-gray-400 mt-0.5 block">Format: JPG, JPEG, PNG, PDF (Maksimal 5MB).</span>
                    </div>

                    <!-- Input Catatan Opsional -->
                    <div>
                        <label class="block text-gray-700 font-bold uppercase mb-1 text-[11px]">
                            Catatan (Opsional)
                        </label>
                        <input type="text" name="catatan" placeholder="Contoh: Pembayaran SPP via transfer"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="px-5 py-3 border-t border-gray-200 bg-gray-50 flex items-center justify-end gap-2 shrink-0">
                    <button type="button" onclick="closeBayarSingleModal()" class="px-4 py-2 border border-gray-300 hover:bg-gray-100 text-gray-700 font-bold rounded-lg text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs shadow transition cursor-pointer flex items-center gap-1.5">
                        <i class="fas fa-paper-plane"></i> Kirim Bukti Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openBayarSemuaModal() {
            document.getElementById('modalBayarSemua').classList.remove('hidden');
        }
        function closeBayarSemuaModal() {
            document.getElementById('modalBayarSemua').classList.add('hidden');
        }

        function formatRupiahJs(val) {
            return 'Rp ' + Number(val).toLocaleString('id-ID');
        }

        function openBayarSingleModal(tagihanId, bulanLabel, nominalSpp, addons, sisaTagihan, biayaAdmin) {
            const form = document.getElementById('singleBayarForm');
            form.action = "{{ url('/user/spp') }}/" + tagihanId + "/konfirmasi";

            document.getElementById('singleModalTitle').textContent = 'Pembayaran SPP Bulan ' + bulanLabel;
            document.getElementById('singleLabelSpp').textContent = 'SPP Pokok (' + bulanLabel + '):';
            document.getElementById('singleValSpp').textContent = formatRupiahJs(nominalSpp);

            const addonsContainer = document.getElementById('singleAddonsContainer');
            addonsContainer.innerHTML = '';
            if (addons && addons.length > 0) {
                addons.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'flex justify-between text-purple-800 font-medium';
                    div.innerHTML = `<span>+ ${item.nama_biaya}:</span><strong class="font-mono">${formatRupiahJs(item.nominal)}</strong>`;
                    addonsContainer.appendChild(div);
                });
            }

            const totalWajib = Number(sisaTagihan) + Number(biayaAdmin);
            document.getElementById('singleValAdmin').textContent = formatRupiahJs(biayaAdmin);
            document.getElementById('singleValTotal').textContent = formatRupiahJs(totalWajib);
            document.getElementById('singleInputJumlahBayar').value = totalWajib;
            document.getElementById('singleInputJumlahBayar').max = totalWajib;
            document.getElementById('singleMaxLabel').textContent = formatRupiahJs(totalWajib);

            document.getElementById('modalBayarSingle').classList.remove('hidden');
        }

        function closeBayarSingleModal() {
            document.getElementById('modalBayarSingle').classList.add('hidden');
        }

        function copyTextById(textElemId, btnElemId) {
            const noRek = document.getElementById(textElemId).innerText.trim();
            if (!noRek || noRek === '-') return;

            navigator.clipboard.writeText(noRek).then(() => {
                const btn = document.getElementById(btnElemId);
                const prev = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Disalin!';
                setTimeout(() => {
                    btn.innerHTML = prev;
                }, 2000);
            }).catch(() => {
                alert('Gagal menyalin nomor rekening.');
            });
        }
    </script>
    @endif
</x-app-layout>
