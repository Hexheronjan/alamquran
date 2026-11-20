@extends('layouts.admin.app')

@section('title', 'Administrasi Bulanan & SPP')

@section('content')
<div class="bg-gray-100 min-h-screen py-6 px-4 lg:px-8">

    <!-- Alerts -->
    @if(session('success'))
        <div class="mb-5 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg shadow-sm flex items-start gap-3">
            <i class="ti ti-circle-check text-2xl text-green-600 flex-shrink-0 mt-0.5"></i>
            <div>
                <h4 class="text-sm font-bold text-green-800">Berhasil!</h4>
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm flex items-start gap-3">
            <i class="ti ti-alert-circle text-2xl text-red-600 flex-shrink-0 mt-0.5"></i>
            <div>
                <h4 class="text-sm font-bold text-red-800">Perhatian</h4>
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if($pendingPembayarans->isNotEmpty())
        @php
            $groupedPending = $pendingPembayarans->groupBy(function ($item) {
                return ($item->santri_id ?? '0') . '|' . ($item->bukti_bayar ?: ('id-' . $item->id));
            });
        @endphp
        <section class="mb-6 overflow-hidden rounded-xl border border-amber-200 bg-white shadow-sm">
            <div class="flex items-center justify-between bg-amber-50 px-5 py-3">
                <h2 class="text-sm font-bold text-amber-900"><i class="ti ti-file-check mr-1"></i> Bukti Transfer Menunggu Verifikasi ({{ $groupedPending->count() }} Transaksi)</h2>
                <span class="text-xs text-amber-700">Saldo tagihan belum berubah sebelum disetujui.</span>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($groupedPending as $groupItems)
                    @php
                        $pembayaran = $groupItems->first();
                        $bulanLabelsPending = $groupItems->map(fn($p) => $p->tagihan?->bulan_label)->filter()->implode(', ');
                        $totalPokokPending = (float) $groupItems->sum('jumlah_bayar');
                        $biayaAdminPending = $sppSetting?->biaya_admin ? (float) $sppSetting->biaya_admin : 2500.0;

                        // Cek apakah pembayaran ini cicilan atau pelunasan penuh
                        $isCicilan = false;
                        if ($groupItems->count() === 1 && $pembayaran->tagihan) {
                            $sisaSebelum = (float) $pembayaran->tagihan->sisa_tagihan;
                            if ($totalPokokPending < $sisaSebelum || str_contains(strtolower($pembayaran->catatan ?? ''), 'cicilan') || str_contains(strtolower($pembayaran->catatan ?? ''), 'angsuran')) {
                                $isCicilan = true;
                            }
                        }
                    @endphp
                    <div class="p-4 text-sm">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="font-bold text-gray-900">
                                    {{ $pembayaran->santri?->nama_lengkap }} ·
                                    <span class="text-blue-700">{{ $groupItems->count() }} Bulan ({{ $bulanLabelsPending }})</span>
                                    @if($isCicilan)
                                        <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                            Cicilan / Angsuran
                                        </span>
                                    @endif
                                </p>
                                <p class="mt-1 text-xs text-gray-600">
                                    @if($isCicilan)
                                        Nominal Cicilan: <strong class="text-emerald-700">Rp {{ number_format($totalPokokPending, 0, ',', '.') }}</strong>
                                        @if($pembayaran->tagihan)
                                            (Sisa Tagihan: <strong>Rp {{ number_format($pembayaran->tagihan->sisa_tagihan, 0, ',', '.') }}</strong>)
                                        @endif
                                    @else
                                        Nominal Dibayar: <strong class="text-emerald-700">Rp {{ number_format($totalPokokPending, 0, ',', '.') }}</strong>
                                        (Transfer + 1x Admin: <strong>Rp {{ number_format($totalPokokPending + $biayaAdminPending, 0, ',', '.') }}</strong>)
                                    @endif
                                    · Diunggah {{ $pembayaran->created_at->format('d/m/Y H:i') }}
                                </p>
                                @if($pembayaran->catatan)
                                    <p class="mt-0.5 text-[11px] text-gray-500">{{ $pembayaran->catatan }}</p>
                                @endif
                                @if($pembayaran->bukti_bayar)<a class="mt-1 inline-block text-xs font-bold text-blue-700 underline" target="_blank" href="{{ Storage::url($pembayaran->bukti_bayar) }}">Lihat bukti transfer</a>@endif
                            </div>
                            <div class="flex flex-wrap gap-2 items-center">
                                <form method="POST" action="{{ route('admin.spp.pembayaran.verify', $pembayaran) }}">
                                    @csrf
                                    @if($isCicilan)
                                        <button onclick="return confirm('Terima bukti pembayaran cicilan ini sebesar Rp {{ number_format($totalPokokPending, 0, ',', '.') }} untuk {{ $bulanLabelsPending }}?')"
                                                class="rounded-lg px-3 py-2 text-xs font-bold text-white cursor-pointer flex items-center gap-1 shadow-sm transition hover:opacity-90"
                                                style="background-color: #059669 !important;">
                                            <i class="ti ti-check"></i> Terima Cicilan
                                        </button>
                                    @else
                                        <button onclick="return confirm('Terima bukti pembayaran ini dan lunasi {{ $groupItems->count() }} bulan ({{ $bulanLabelsPending }})?')"
                                                class="rounded-lg px-3 py-2 text-xs font-bold text-white cursor-pointer flex items-center gap-1 shadow-sm transition hover:opacity-90"
                                                style="background-color: #059669 !important;">
                                            <i class="ti ti-checks"></i> Terima & Lunasi
                                        </button>
                                    @endif
                                </form>
                                <form method="POST" action="{{ route('admin.spp.pembayaran.reject', $pembayaran) }}" class="flex gap-2">@csrf<input required name="alasan_penolakan" maxlength="500" class="w-52 rounded-lg border-gray-300 px-2 text-xs" placeholder="Alasan penolakan"><button class="rounded-lg bg-red-600 px-3 py-2 text-xs font-bold text-white cursor-pointer">Tolak</button></form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-calendar-month text-blue-600"></i> Administrasi Bulanan & SPP
            </h1>
            <p class="text-gray-600 text-xs mt-0.5">Kelola tagihan bulanan, input multi-bulan (3 bulan/lebih), rekap wali menunggak, dan pengaturan rekening & add-on santri.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="openMultiTagihanModal()"
                    class="px-4 py-2.5 rounded-lg font-bold text-xs shadow-sm flex items-center gap-1.5 cursor-pointer"
                    style="background-color: #7c3aed !important; color: #ffffff !important;">
                <i class="ti ti-checkbox text-base"></i> Input Centang (Multi-Bulan & Santri)
            </button>
            @if($activeTab === 'tagihan')
                <button type="button" onclick="openGenerateModal()"
                        class="px-4 py-2.5 rounded-lg font-bold text-xs shadow-sm flex items-center gap-1.5 cursor-pointer"
                        style="background-color: #2563eb !important; color: #ffffff !important;">
                    <i class="ti ti-plus text-base"></i> Buat Tagihan Bulanan
                </button>
                <button type="button" onclick="openSingleTagihanModal()"
                        class="px-4 py-2.5 rounded-lg font-bold text-xs shadow-sm flex items-center gap-1.5 cursor-pointer"
                        style="background-color: #4f46e5 !important; color: #ffffff !important;">
                    <i class="ti ti-user-plus text-base"></i> Input 1 Santri
                </button>
                <button type="button" onclick="openDeletePeriodModal()"
                        class="px-4 py-2.5 rounded-lg font-bold text-xs shadow-sm flex items-center gap-1.5 cursor-pointer"
                        style="background-color: #dc2626 !important; color: #ffffff !important;">
                    <i class="ti ti-trash text-base"></i> Hapus Tagihan Periode Ini
                </button>
                <form action="{{ route('admin.spp.broadcast') }}" method="POST" onsubmit="return confirm('Kirim broadcast WA rincian tagihan (termasuk no rekening, add-on, dan tunggakan bulan lalu) ke seluruh wali santri yang belum lunas pada periode ini?\n\nCatatan: Santri yang sudah lunas tidak akan ditagih lagi.')">
                    @csrf
                    <input type="hidden" name="bulan" value="{{ $bulan }}">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <button type="submit" class="px-4 py-2.5 rounded-lg font-bold text-xs shadow-sm flex items-center gap-1.5 cursor-pointer"
                            style="background-color: #16a34a !important; color: #ffffff !important;">
                        <i class="ti ti-brand-whatsapp text-base"></i> Broadcast WA Tagihan
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Navigation Tabs (3 Menu Sejajar dalam 1 Baris) -->
    <div class="mb-6 grid grid-cols-3 border-b border-gray-200 gap-2"
         style="display: grid !important; grid-template-columns: repeat(3, minmax(0, 1fr)) !important; gap: 0.5rem !important;">
        <a href="{{ route('admin.spp.index', ['tab' => 'tagihan', 'bulan' => $bulan, 'tahun' => $tahun]) }}"
           class="py-3 px-3 sm:px-4 text-xs sm:text-sm font-bold border-b-2 flex items-center justify-center gap-2 transition whitespace-nowrap {{ $activeTab === 'tagihan' ? 'border-blue-600 text-blue-600 bg-white rounded-t-xl shadow-xs' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 bg-white/50 rounded-t-xl' }}">
            <i class="ti ti-receipt text-base sm:text-lg shrink-0"></i>
            <span class="truncate">Tagihan & Pembayaran SPP</span>
        </a>
        <a href="{{ route('admin.spp.index', ['tab' => 'tunggakan', 'bulan' => $bulan, 'tahun' => $tahun]) }}"
           class="py-3 px-3 sm:px-4 text-xs sm:text-sm font-bold border-b-2 flex items-center justify-center gap-2 transition whitespace-nowrap {{ $activeTab === 'tunggakan' ? 'border-rose-600 text-rose-600 bg-white rounded-t-xl shadow-xs' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 bg-white/50 rounded-t-xl' }}">
            <i class="ti ti-users-group text-base sm:text-lg shrink-0"></i>
            <span class="truncate">Rekap Wali Santri Menunggak</span>
            <span class="px-2 py-0.5 text-[11px] font-bold rounded-full shrink-0 {{ $activeTab === 'tunggakan' ? 'bg-rose-100 text-rose-800' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                {{ $totalWaliMenunggak ?? 0 }} Wali
            </span>
        </a>
        <a href="{{ route('admin.spp.index', ['tab' => 'rekening', 'bulan' => $bulan, 'tahun' => $tahun]) }}"
           class="py-3 px-3 sm:px-4 text-xs sm:text-sm font-bold border-b-2 flex items-center justify-center gap-2 transition whitespace-nowrap {{ $activeTab === 'rekening' ? 'border-teal-600 text-teal-600 bg-white rounded-t-xl shadow-xs' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 bg-white/50 rounded-t-xl' }}">
            <i class="ti ti-credit-card text-base sm:text-lg shrink-0"></i>
            <span class="truncate">Pengaturan Rekening Santri & Add-on</span>
            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full shrink-0 {{ $activeTab === 'rekening' ? 'bg-teal-100 text-teal-800' : 'bg-gray-200 text-gray-700' }}">
                {{ $countSantriRekeningKhusus }}/{{ $totalSantriAktif }}
            </span>
        </a>
    </div>

    @if($activeTab === 'tunggakan')
        <!-- TAB BARU: REKAP SEMUA WALI SANTRI YANG MENUNGGAK -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-rose-200">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span class="font-bold uppercase">Total Wali / Santri Menunggak</span>
                    <i class="ti ti-alert-circle text-xl text-rose-600"></i>
                </div>
                <div class="text-3xl font-black text-rose-700 font-mono">
                    {{ $totalWaliMenunggak }}
                </div>
                <div class="text-[11px] text-rose-600 mt-1 font-semibold">Wali santri dengan tagihan belum lunas</div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-amber-200">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span class="font-bold uppercase">Total Bulan Menunggak</span>
                    <i class="ti ti-calendar-exclamation text-xl text-amber-600"></i>
                </div>
                <div class="text-3xl font-black text-amber-700 font-mono">
                    {{ $totalBulanMenunggak }} <span class="text-base font-bold">Bulan</span>
                </div>
                <div class="text-[11px] text-amber-600 mt-1 font-semibold">Akumulasi bulan belum terbayar</div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span class="font-bold uppercase">Total Keseluruhan Tunggakan</span>
                    <i class="ti ti-cash-banknote text-xl text-blue-600"></i>
                </div>
                <div class="text-2xl font-black text-gray-900 font-mono">
                    Rp {{ number_format($grandTotalTunggakan, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-gray-500 mt-1">Total SPP pokok + seluruh Add-on tertunggak</div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-emerald-200">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span class="font-bold uppercase">Ketentuan Biaya Admin</span>
                    <i class="ti ti-receipt-tax text-xl text-emerald-600"></i>
                </div>
                <div class="text-2xl font-black text-emerald-700 font-mono">
                    Rp {{ number_format($sppSetting?->biaya_admin ?? 2500, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-emerald-600 mt-1 font-semibold">Cukup 1x transaksi per pelunasan wali</div>
            </div>
        </div>

        <!-- Filter Rekap Tunggakan -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 mb-6 text-xs">
            <form method="GET" action="{{ route('admin.spp.index') }}" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="tab" value="tunggakan">
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                <input type="hidden" name="tahun" value="{{ $tahun }}">

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Bulan Tunggakan</label>
                    <select name="bulan_tunggakan" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-rose-500">
                        <option value="all" {{ ($tunggakanBulan ?? 'all') === 'all' ? 'selected' : '' }}>Semua Bulan (Akumulasi)</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ (string)($tunggakanBulan ?? '') === (string)$m ? 'selected' : '' }}>
                                {{ \App\Models\SppTagihan::getNamaBulan($m) }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Tahun</label>
                    <select name="tahun_tunggakan" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-rose-500">
                        <option value="all" {{ ($tunggakanTahun ?? 'all') === 'all' ? 'selected' : '' }}>Semua Tahun</option>
                        @for($y = (int)date('Y') - 1; $y <= (int)date('Y') + 2; $y++)
                            <option value="{{ $y }}" {{ (string)($tunggakanTahun ?? '') === (string)$y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Tipe / Kategori</label>
                    <select name="tipe_tunggakan" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-rose-500">
                        <option value="all" {{ ($tunggakanTipe ?? 'all') === 'all' ? 'selected' : '' }}>Semua Tagihan (Akumulasi)</option>
                        <option value="multi" {{ ($tunggakanTipe ?? '') === 'multi' ? 'selected' : '' }}>Khusus Multi-Bulan (2 Bulan / Lebih)</option>
                        <option value="future" {{ ($tunggakanTipe ?? '') === 'future' ? 'selected' : '' }}>Tagihan Bulan Depan / Mendatang</option>
                        <option value="single" {{ ($tunggakanTipe ?? '') === 'single' ? 'selected' : '' }}>Tagihan 1 Bulan Saja</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Filter Kelas</label>
                    <select name="kelas_tunggakan" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-rose-500">
                        <option value="all" {{ ($tunggakanKelas ?? 'all') === 'all' ? 'selected' : '' }}>Semua Kelas (1, 2, 3)</option>
                        <option value="1" {{ ($tunggakanKelas ?? '') === '1' ? 'selected' : '' }}>Kelas 1</option>
                        <option value="2" {{ ($tunggakanKelas ?? '') === '2' ? 'selected' : '' }}>Kelas 2</option>
                        <option value="3" {{ ($tunggakanKelas ?? '') === '3' ? 'selected' : '' }}>Kelas 3</option>
                    </select>
                </div>

                <div class="flex-1 min-w-[240px]">
                    <label class="block font-bold text-gray-700 uppercase mb-1">Cari Wali Santri / Nama Santri / No. WA</label>
                    <input type="text" name="search_tunggakan" value="{{ $tunggakanSearch ?? '' }}"
                           placeholder="Ketik nama wali, nomor WA, nama santri, atau NIS..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-rose-500">
                </div>

                <div class="pt-4 flex gap-1.5">
                    <button type="submit" class="px-4 py-2 rounded-lg font-bold text-xs cursor-pointer"
                            style="background-color: #e11d48 !important; color: #ffffff !important;">
                        Filter
                    </button>
                    <a href="{{ route('admin.spp.index', ['tab' => 'tunggakan']) }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Rekap Wali Santri yang Menunggak -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <i class="ti ti-users-group text-rose-600"></i> Daftar Semua Wali Santri yang Menunggak & Total Harus Dibayar
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Menampilkan rincian berapa bulan menunggak dan akumulasi total keseluruhan yang wajib dilunasi oleh masing-masing wali santri.</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="text-xs bg-rose-100 text-rose-800 px-3 py-1.5 rounded-full font-bold">
                        {{ $rekapTunggakan->count() }} Wali Santri
                    </span>
                    @if($rekapTunggakan->isNotEmpty())
                        <form action="{{ route('admin.spp.tunggakan.destroy_all') }}" method="POST"
                              onsubmit="return confirm('HAPUS SEMUA TAGIHAN MENUNGGAK?\n\nTindakan ini akan menghapus seluruh data tagihan yang belum lunas untuk semua wali santri ({{ $rekapTunggakan->count() }} santri).\n\nLanjutkan?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3.5 py-1.5 rounded-xl font-bold text-xs text-white flex items-center gap-1.5 shadow-sm hover:opacity-90 transition cursor-pointer"
                                    style="background-color: #dc2626 !important; color: #ffffff !important;"
                                    title="Hapus semua tagihan yang menunggak untuk semua wali santri">
                                <i class="ti ti-trash"></i> Hapus Semua Tunggakan
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">No</th>
                            <th class="px-5 py-3.5">Wali Santri & WhatsApp</th>
                            <th class="px-5 py-3.5">Santri & Rekening Tujuan</th>
                            <th class="px-5 py-3.5 text-center">Jumlah Nunggak</th>
                            <th class="px-5 py-3.5">Rincian Bulan & Add-on Menunggak</th>
                            <th class="px-5 py-3.5 text-right">Total Keseluruhan Harus Dibayar</th>
                            <th class="px-5 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($rekapTunggakan as $idx => $item)
                            <tr class="hover:bg-rose-50/20 transition">
                                <td class="px-5 py-4 font-bold text-gray-500">{{ $idx + 1 }}</td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-gray-900 text-sm">{{ $item->nama_wali }}</div>
                                    <div class="text-xs text-emerald-700 font-mono font-semibold mt-0.5 flex items-center gap-1">
                                        <i class="ti ti-brand-whatsapp text-sm"></i> {{ $item->no_wa_wali }}
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-gray-900">{{ $item->santri->nama_lengkap }}</div>
                                    <div class="text-[11px] text-gray-500 font-mono">
                                        {{ $item->santri->kelas_label }} | NIS: {{ $item->santri->nis ?? '-' }}
                                    </div>
                                    @if($item->santri->nomor_rekening_spp)
                                        <div class="mt-1 inline-flex items-center gap-1 text-[10px] font-mono bg-teal-50 text-teal-800 border border-teal-200 px-2 py-0.5 rounded">
                                            <i class="ti ti-credit-card"></i> {{ $item->santri->nama_bank_spp ?? 'Bank' }}: {{ $item->santri->nomor_rekening_spp }}
                                        </div>
                                    @else
                                        <div class="mt-1 inline-flex items-center gap-1 text-[10px] bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded">
                                            Rekening Belum Diatur
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full font-black text-xs bg-rose-100 text-rose-800 border border-rose-300 inline-block whitespace-nowrap">
                                        {{ $item->jumlah_bulan }} Bulan
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="space-y-1">
                                        @foreach($item->tagihans as $tgk)
                                            <div class="p-1.5 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-between gap-2 text-[11px] hover:border-gray-300 transition">
                                                <div>
                                                    <strong class="text-gray-800">{{ $tgk->bulan_label }}</strong>
                                                    @if($tgk->addons->isNotEmpty())
                                                        <span class="text-purple-700 font-semibold block text-[10px]">
                                                            SPP: Rp {{ number_format($tgk->nominal_spp, 0, ',', '.') }}
                                                            @foreach($tgk->addons as $ad)
                                                                + {{ $ad->nama_biaya }} (Rp {{ number_format($ad->nominal, 0, ',', '.') }})
                                                            @endforeach
                                                        </span>
                                                    @endif
                                                </div>
                                                <span class="font-mono font-bold text-rose-700 whitespace-nowrap">
                                                    Rp {{ number_format($tgk->sisa_tagihan, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right font-mono">
                                    <div class="text-xs text-gray-600">
                                        Pokok + Add-on: <strong>Rp {{ number_format($item->total_sisa_tagihan, 0, ',', '.') }}</strong>
                                    </div>
                                    <div class="text-[11px] text-blue-700">
                                        + 1x Biaya Admin: Rp {{ number_format($item->biaya_admin, 0, ',', '.') }}
                                    </div>
                                    <div class="text-sm font-black text-rose-700 mt-1 pt-1 border-t border-gray-200">
                                        Total: Rp {{ number_format($item->total_harus_dibayar, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <div class="flex flex-col gap-1.5 items-center justify-center">
                                        @if(empty($item->santri->nomor_rekening_spp))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-300" title="Atur rekening / VA santri terlebih dahulu di tab Kelola Rekening agar bisa mengirim WA penagihan.">
                                                <i class="ti ti-alert-triangle text-amber-600"></i> Rekening Belum Diatur
                                            </span>
                                        @else
                                            <form action="{{ route('admin.spp.kirim_wa_tunggakan', $item->santri->id) }}" method="POST"
                                                  onsubmit="return confirm('Kirim notifikasi WA rincian total tunggakan ({{ $item->jumlah_bulan }} bulan: Rp {{ number_format($item->total_harus_dibayar, 0, ',', '.') }}) ke wali {{ addslashes($item->santri->nama_lengkap) }}?')">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-lg font-bold text-xs text-white flex items-center gap-1 cursor-pointer whitespace-nowrap"
                                                        style="background-color: #16a34a !important;">
                                                    <i class="ti ti-brand-whatsapp"></i> Kirim WA Total
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.spp.lunasi_semua', $item->santri->id) }}" method="POST"
                                              onsubmit="return confirm('Tandai LUNAS seluruh {{ $item->jumlah_bulan }} bulan tunggakan ({{ $item->bulan_labels }}) untuk {{ addslashes($item->santri->nama_lengkap) }} sebesar Rp {{ number_format($item->total_sisa_tagihan, 0, ',', '.') }}?')">
                                            @csrf
                                            <button type="submit" class="w-full px-3 py-1.5 rounded-lg font-bold text-xs text-white flex items-center justify-center gap-1 cursor-pointer whitespace-nowrap"
                                                    style="background-color: #2563eb !important;">
                                                <i class="ti ti-checks"></i> Lunasi Semua
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.spp.hapus_semua_tunggakan', $item->santri->id) }}" method="POST"
                                              onsubmit="return confirm('Hapus tagihan menunggak ({{ $item->bulan_labels }}) untuk {{ addslashes($item->santri->nama_lengkap) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full px-3 py-1.5 rounded-lg font-bold text-xs text-white flex items-center justify-center gap-1 cursor-pointer whitespace-nowrap transition"
                                                    style="background-color: #dc2626 !important;">
                                                <i class="ti ti-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <i class="ti ti-circle-check text-4xl text-emerald-500 mb-2 block"></i>
                                    Alhamdulillah, tidak ada wali santri yang menunggak sesuai filter saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif($activeTab === 'rekening')
        <!-- Tab 3: Pengaturan Rekening Santri & Fitur Add-on -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span class="font-bold uppercase">Total Santri Aktif</span>
                    <i class="ti ti-users text-xl text-blue-600"></i>
                </div>
                <div class="text-3xl font-black text-gray-900 font-mono">
                    {{ $totalSantriAktif }}
                </div>
                <div class="text-[11px] text-gray-400 mt-1">Santri berstatus aktif & diterima</div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span class="font-bold uppercase">Rekening Khusus Diatur</span>
                    <i class="ti ti-credit-card text-xl text-emerald-600"></i>
                </div>
                <div class="text-3xl font-black text-emerald-700 font-mono">
                    {{ $countSantriRekeningKhusus }}
                </div>
                <div class="text-[11px] text-emerald-600 mt-1 font-semibold">Memiliki rekening/VA khusus</div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span class="font-bold uppercase">Belum Diatur Rekening</span>
                    <i class="ti ti-clock text-xl text-amber-600"></i>
                </div>
                <div class="text-3xl font-black text-amber-700 font-mono">
                    {{ $totalSantriAktif - $countSantriRekeningKhusus }}
                </div>
                <div class="text-[11px] text-amber-600 mt-1 font-semibold">Menunggu diatur oleh admin</div>
            </div>
        </div>

        <!-- Info Box Rekening Santri -->
        <div class="mb-6 p-4 bg-teal-50 border border-teal-200 rounded-2xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-teal-900">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="ti ti-info-circle text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-teal-950">Pengaturan Rekening Pembayaran & Biaya Tambahan (Add-on) Santri</h3>
                    <p class="text-teal-800 mt-0.5">
                        Kelola <strong>Nama Bank</strong>, <strong>Nomor Rekening / VA</strong>, <strong>Tarif SPP</strong>, serta tambahkan/edit <strong>Biaya Add-on (+)</strong> untuk masing-masing santri.
                        Add-on yang ditambahkan di sini otomatis muncul sebagai <strong>+ Add-on</strong> di daftar tagihan dan mengirimkan notifikasi WA ke wali santri.
                    </p>
                </div>
            </div>
        </div>

        <!-- Pengaturan Notifikasi Bendahara & Biaya Admin -->
        <div class="mb-6 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-200 bg-gray-50">
                <h3 class="font-bold text-sm text-gray-900 flex items-center gap-2">
                    <i class="ti ti-settings text-teal-600 text-base"></i> Pengaturan Bendahara & Biaya Admin SPP (1x per Transaksi)
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Atur nomor WhatsApp bendahara penerima notifikasi pembayaran dan biaya admin (hanya dikenakan 1 kali per transaksi pelunasan).</p>
            </div>
            <form action="{{ route('admin.spp.setting') }}" method="POST" class="p-5 text-xs">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                    <div class="md:col-span-5">
                        <label class="block font-bold text-gray-800 uppercase mb-1 text-[11px] flex items-center gap-1.5">
                            <i class="ti ti-brand-whatsapp text-green-600 text-sm"></i> Nomor WhatsApp Bendahara <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="no_wa_bendahara" value="{{ old('no_wa_bendahara', $sppSetting?->no_wa_bendahara) }}" placeholder="Contoh: 081234567890" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-mono font-bold text-gray-900 focus:ring-2 focus:ring-teal-500">
                        <span class="text-[11px] text-gray-500 mt-1 block">Otomatis menerima notifikasi WA saat wali upload bukti pembayaran.</span>
                    </div>

                    <div class="md:col-span-4">
                        <label class="block font-bold text-gray-800 uppercase mb-1 text-[11px] flex items-center gap-1.5">
                            <i class="ti ti-receipt-tax text-blue-600 text-sm"></i> Biaya Admin (1x Transaksi) (Rp)
                        </label>
                        <input type="number" name="biaya_admin" value="{{ old('biaya_admin', $sppSetting?->biaya_admin ?? 2500) }}" step="100" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-mono font-bold text-gray-900 focus:ring-2 focus:ring-teal-500">
                        <span class="text-[11px] text-gray-500 mt-1 block">Cukup 1 kali transaksi (tidak dikalikan jumlah bulan).</span>
                    </div>

                    <div class="md:col-span-3 pt-5 flex md:justify-end">
                        <button type="submit" class="w-full md:w-auto px-5 py-2.5 font-bold rounded-lg text-xs transition shadow-sm text-white cursor-pointer flex items-center justify-center gap-1.5"
                                style="background-color: #0d9488 !important;">
                            <i class="ti ti-device-floppy text-sm"></i> Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Filter Bar Rekening Santri -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 mb-6 text-xs">
            <form method="GET" action="{{ route('admin.spp.index') }}" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="tab" value="rekening">
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                <input type="hidden" name="tahun" value="{{ $tahun }}">

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Jenjang Kelas</label>
                    <select name="kelas_santri" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-teal-500">
                        <option value="all" {{ $santriKelas === 'all' ? 'selected' : '' }}>Semua Kelas (1, 2, 3)</option>
                        <option value="1" {{ $santriKelas === '1' ? 'selected' : '' }}>Kelas 1</option>
                        <option value="2" {{ $santriKelas === '2' ? 'selected' : '' }}>Kelas 2</option>
                        <option value="3" {{ $santriKelas === '3' ? 'selected' : '' }}>Kelas 3</option>
                    </select>
                </div>

                <div class="flex-1 min-w-[220px]">
                    <label class="block font-bold text-gray-700 uppercase mb-1">Cari Santri / Rekening</label>
                    <input type="text" name="search_santri" value="{{ $santriSearch }}" placeholder="Ketik nama santri, NIS, bank, atau no rekening..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500">
                </div>

                <div class="pt-4 flex gap-1.5">
                    <button type="submit" class="px-4 py-2 rounded-lg font-bold text-xs cursor-pointer"
                            style="background-color: #0d9488 !important; color: #ffffff !important;">
                        Filter
                    </button>
                    <a href="{{ route('admin.spp.index', ['tab' => 'rekening']) }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table Santri Rekening & Add-on -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="ti ti-credit-card text-teal-600"></i> Daftar Rekening Pembayaran & Add-on Santri (Periode: {{ \App\Models\SppTagihan::getNamaBulan($bulan) }} {{ $tahun }})
                </h2>
                <span class="text-xs bg-gray-100 text-gray-700 px-2.5 py-0.5 rounded-full font-bold">
                    {{ $santris->total() }} Santri
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3.5">Santri & Kelas</th>
                            <th class="px-6 py-3.5">Wali Santri</th>
                            <th class="px-6 py-3.5">Nominal SPP & Add-on (+)</th>
                            <th class="px-6 py-3.5">Bank Pembayaran</th>
                            <th class="px-6 py-3.5">Nomor Rekening / VA</th>
                            <th class="px-6 py-3.5">Atas Nama Rekening</th>
                            <th class="px-6 py-3.5 text-center">Status Rekening</th>
                            <th class="px-6 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($santris as $s)
                            @php
                                $tagihanSantriCur = $santriTagihanMap[$s->id] ?? null;
                                $addonsSantriCur = $tagihanSantriCur ? $tagihanSantriCur->addons : collect();
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $s->nama_lengkap }}</div>
                                    <div class="text-[11px] text-gray-500 font-mono mt-0.5">
                                        {{ $s->kelas_label }} | NIS: {{ $s->nis ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-gray-900 font-semibold">{{ $s->orangTua?->nama_ayah ?? '-' }}</div>
                                    <div class="text-[11px] text-gray-500 font-mono">{{ $s->orangTua?->no_wa_ayah ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($s->is_beasiswa)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                            <i class="ti ti-award"></i> Beasiswa Full (Rp 0)
                                        </span>
                                    @elseif(!empty($s->nominal_spp) && (float)$s->nominal_spp > 0)
                                        <div class="font-bold font-mono text-emerald-700 text-sm">Rp {{ number_format($s->nominal_spp, 0, ',', '.') }}</div>
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Tarif Santri</span>
                                    @else
                                        <div class="font-bold font-mono text-gray-800 text-sm">Rp {{ number_format($s->tarif_spp, 0, ',', '.') }}</div>
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                            Tarif Kelas
                                        </span>
                                    @endif

                                    @if($s->addonTemplates && $s->addonTemplates->isNotEmpty())
                                        <div class="mt-2 space-y-1">
                                            <div class="text-[10px] font-bold text-purple-900 uppercase tracking-wider flex items-center gap-1">
                                                <i class="ti ti-receipt text-purple-600"></i> Add-on Rutin Bulanan:
                                            </div>
                                            @foreach($s->addonTemplates as $adTpl)
                                                <div class="flex items-center justify-between text-[11px] font-mono font-bold text-purple-700 bg-purple-50 border border-purple-200 px-2 py-0.5 rounded gap-2">
                                                    <span>+ {{ $adTpl->nama_biaya }}: Rp {{ number_format($adTpl->nominal, 0, ',', '.') }}</span>
                                                    <div class="flex items-center gap-1 shrink-0">
                                                        <button type="button" 
                                                                onclick='openEditSantriAddonTemplateModal({{ $adTpl->id }}, {{ json_encode($adTpl->nama_biaya) }}, {{ (float) $adTpl->nominal }}, {{ json_encode($adTpl->keterangan ?? "") }}, {{ json_encode($s->nama_lengkap) }})'
                                                                class="text-amber-600 hover:text-amber-800 cursor-pointer p-0.5" 
                                                                title="Edit Template Add-on Rutin">
                                                            <i class="ti ti-pencil text-xs"></i>
                                                        </button>
                                                        <form action="{{ route('admin.spp.santri_addon_template.destroy', $adTpl->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus template add-on rutin {{ $adTpl->nama_biaya }}?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-500 hover:text-red-700 cursor-pointer p-0.5" title="Hapus Template Add-on Rutin">
                                                                <i class="ti ti-x text-xs"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-900">
                                    {{ $s->nama_bank_spp ?: '-' }}
                                </td>
                                <td class="px-6 py-4 font-mono font-bold {{ $s->nomor_rekening_spp ? 'text-teal-700' : 'text-gray-400' }}">
                                    {{ $s->nomor_rekening_spp ?: '-' }}
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    {{ $s->atas_nama_rekening_spp ?: '-' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($s->nomor_rekening_spp)
                                        <span class="px-2.5 py-1 rounded-full font-bold text-[11px] border"
                                              style="background-color: #ccfbf1 !important; color: #0f766e !important; border-color: #99f6e4 !important;">
                                            Sudah Diatur
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full font-semibold text-[11px] bg-gray-100 text-gray-500 border border-gray-200">
                                            Belum Diatur
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                        <button type="button"
                                                onclick='openSantriAddonDirectModal({{ $s->id }}, {{ json_encode($s->nama_lengkap, JSON_HEX_APOS | JSON_HEX_QUOT) }})'
                                                class="px-2.5 py-1.5 font-bold rounded-lg transition inline-flex items-center gap-1 cursor-pointer text-xs"
                                                style="background-color: #f5f3ff !important; color: #6d28d9 !important; border: 1px solid #ddd6fe !important;"
                                                title="Tambah Biaya Add-on Rutin (Template Bulanan) untuk Santri Ini">
                                            <i class="ti ti-plus text-xs"></i> Add-on Rutin
                                            @if($s->addonTemplates && $s->addonTemplates->count() > 0)
                                                <span class="ml-0.5 px-1.5 py-0.2 rounded-full text-[10px] bg-purple-700 text-white font-bold">{{ $s->addonTemplates->count() }}</span>
                                            @endif
                                        </button>

                                        <button type="button"
                                                onclick="openSantriRekeningModal({{ $s->id }}, '{{ addslashes($s->nama_lengkap) }}', '{{ addslashes($s->nama_bank_spp ?? '') }}', '{{ addslashes($s->nomor_rekening_spp ?? '') }}', '{{ addslashes($s->atas_nama_rekening_spp ?? '') }}', {{ $s->nominal_spp ?? 'null' }}, {{ $s->is_beasiswa ? 'true' : 'false' }})"
                                                class="px-3 py-1.5 font-bold rounded-lg transition inline-flex items-center gap-1 cursor-pointer text-xs"
                                                style="background-color: #0d9488 !important; color: #ffffff !important;"
                                                title="Atur SPP & Rekening Santri Ini">
                                            <i class="ti ti-pencil text-sm"></i> Atur SPP & Rekening
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <i class="ti ti-users text-3xl text-gray-400 mb-2 block"></i>
                                    Tidak ada data santri yang sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($santris->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $santris->links() }}
                </div>
            @endif
        </div>
    @else
        {{-- Tab 1: Tagihan & Pembayaran SPP --}}



    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 mb-6 text-xs">
        <form method="GET" action="{{ route('admin.spp.index') }}" class="flex flex-wrap items-center gap-3">
            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Bulan</label>
                <select name="bulan" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-blue-500">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                            {{ \App\Models\SppTagihan::getNamaBulan($m) }}
                        </option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Tahun</label>
                <input type="number" name="tahun" value="{{ $tahun }}" min="2020" max="2099" onchange="this.form.submit()"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-xs w-24 font-semibold focus:ring-2 focus:ring-blue-500">
            </div>

            @php
                $prevDate = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->subMonth();
                $nextDate = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->addMonth();
                $nowDate  = now();
            @endphp
            <div class="flex items-center gap-1.5 self-end pb-0.5">
                <a href="{{ route('admin.spp.index', ['tab' => 'tagihan', 'bulan' => $prevDate->month, 'tahun' => $prevDate->year, 'kelas' => $selectedKelas, 'status' => $selectedStatus]) }}"
                   class="px-2.5 py-2 bg-gray-50 border border-gray-200 text-gray-700 hover:bg-gray-100 rounded-lg font-bold text-[11px] transition flex items-center gap-1"
                   title="Lihat tagihan bulan sebelumnya">
                    <i class="ti ti-chevron-left"></i> {{ \App\Models\SppTagihan::getNamaBulan($prevDate->month) }}
                </a>
                <a href="{{ route('admin.spp.index', ['tab' => 'tagihan', 'bulan' => $nowDate->month, 'tahun' => $nowDate->year, 'kelas' => $selectedKelas, 'status' => $selectedStatus]) }}"
                   class="px-2.5 py-2 {{ $bulan == $nowDate->month && $tahun == $nowDate->year ? 'bg-blue-600 text-white' : 'bg-gray-50 border border-gray-200 text-gray-700 hover:bg-gray-100' }} rounded-lg font-bold text-[11px] transition"
                   title="Kembali ke bulan ini">
                    Bulan Ini
                </a>
                <a href="{{ route('admin.spp.index', ['tab' => 'tagihan', 'bulan' => $nextDate->month, 'tahun' => $nextDate->year, 'kelas' => $selectedKelas, 'status' => $selectedStatus]) }}"
                   class="px-2.5 py-2 bg-purple-50 border border-purple-200 text-purple-700 hover:bg-purple-100 rounded-lg font-bold text-[11px] transition flex items-center gap-1"
                   title="Lihat tagihan bulan depan (yang dibuka multi-bulan)">
                    {{ \App\Models\SppTagihan::getNamaBulan($nextDate->month) }} <i class="ti ti-chevron-right"></i>
                </a>
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Jenjang Kelas</label>
                <select name="kelas" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-blue-500">
                    <option value="all" {{ $selectedKelas === 'all' ? 'selected' : '' }}>Semua Kelas (1, 2, 3)</option>
                    <option value="1" {{ $selectedKelas === '1' ? 'selected' : '' }}>Kelas 1</option>
                    <option value="2" {{ $selectedKelas === '2' ? 'selected' : '' }}>Kelas 2</option>
                    <option value="3" {{ $selectedKelas === '3' ? 'selected' : '' }}>Kelas 3</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Status Pembayaran</label>
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-blue-500">
                    <option value="all" {{ $selectedStatus === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="lunas" {{ $selectedStatus === 'lunas' ? 'selected' : '' }}>Lunas</option>
                    <option value="cicilan" {{ $selectedStatus === 'cicilan' ? 'selected' : '' }}>Dalam Cicilan</option>
                    <option value="belum_lunas" {{ $selectedStatus === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                </select>
            </div>

            <div class="flex-1 min-w-[200px]">
                <label class="block font-bold text-gray-700 uppercase mb-1">Cari Santri</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama atau NIS..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="pt-4 flex gap-1.5">
                <button type="submit" class="px-4 py-2 rounded-lg font-bold text-xs cursor-pointer"
                        style="background-color: #2563eb !important; color: #ffffff !important;">
                    Filter
                </button>
                <a href="{{ route('admin.spp.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span class="font-bold uppercase">Total Tagihan</span>
                <i class="ti ti-file-invoice text-lg text-blue-600"></i>
            </div>
            <div class="text-2xl font-black text-gray-900 font-mono">
                Rp {{ number_format($totalTagihan, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-gray-400 mt-1">Target penerimaan bulan ini</div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span class="font-bold uppercase">Total Terbayar</span>
                <i class="ti ti-check text-lg text-green-600"></i>
            </div>
            <div class="text-2xl font-black text-green-700 font-mono">
                Rp {{ number_format($totalTerbayar, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-green-600 mt-1 font-semibold">{{ $countLunas }} Lunas, {{ $countCicilan }} Cicilan</div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span class="font-bold uppercase">Sisa Piutang</span>
                <i class="ti ti-clock text-lg text-amber-600"></i>
            </div>
            <div class="text-2xl font-black text-amber-700 font-mono">
                Rp {{ number_format($totalSisa, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-amber-600 mt-1 font-semibold">{{ $countBelumLunas }} Santri belum lunas</div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span class="font-bold uppercase">Persentase Pelunasan</span>
                <i class="ti ti-chart-pie text-lg text-purple-600"></i>
            </div>
            @php
                $pct = $totalTagihan > 0 ? round(($totalTerbayar / $totalTagihan) * 100, 1) : 0;
            @endphp
            <div class="text-2xl font-black text-purple-700 font-mono">
                {{ $pct }}%
            </div>
            <div class="text-[11px] text-gray-400 mt-1">Realisasi bulan {{ \App\Models\SppTagihan::getNamaBulan($bulan) }}</div>
        </div>
    </div>

    <!-- Table Tagihan -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900">
                Daftar Tagihan SPP: {{ \App\Models\SppTagihan::getNamaBulan($bulan) }} {{ $tahun }}
            </h2>
            <span class="text-xs bg-gray-100 text-gray-700 px-2.5 py-0.5 rounded-full font-bold">
                {{ $tagihans->total() }} Data
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Santri & Kelas</th>
                        <th class="px-6 py-3.5 text-right">Tagihan</th>
                        <th class="px-6 py-3.5 text-right">Terbayar</th>
                        <th class="px-6 py-3.5 text-right">Sisa Tagihan</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-left">Petugas</th>
                        <th class="px-6 py-3.5 text-center">Broadcast WA</th>
                        <th class="px-6 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($tagihans as $tagihan)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ $tagihan->santri->nama_lengkap }}</div>
                                <div class="text-[11px] text-gray-500 font-mono mt-0.5">
                                    {{ $tagihan->santri->kelas_label }} | Wali: {{ $tagihan->santri->orangTua?->nama_ayah ?? '-' }}
                                </div>
                                <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-mono font-semibold bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded border border-gray-200" title="Nomor Invoice Tagihan">
                                        <i class="ti ti-receipt text-xs text-blue-600"></i> {{ $tagihan->nomor_invoice }}
                                    </span>
                                    @if($tagihan->santri->nomor_rekening_spp)
                                        <span class="inline-flex items-center gap-1 text-teal-700 bg-teal-50 border border-teal-200 px-1.5 py-0.5 rounded font-mono text-[10px]">
                                            <i class="ti ti-credit-card text-xs"></i> {{ $tagihan->santri->nama_bank_spp ?? 'Bank' }}: {{ $tagihan->santri->nomor_rekening_spp }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded text-[10px]">
                                            <i class="ti ti-alert-circle text-xs"></i> Rekening/VA Belum Diatur
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right font-mono text-xs">
                                <div class="font-bold text-gray-900">Rp {{ number_format($tagihan->nominal_tagihan, 0, ',', '.') }}</div>
                                @if((float)$tagihan->nominal_tagihan === 0.0 || $tagihan->santri?->is_beasiswa)
                                    <span class="inline-block text-[10px] text-purple-700 font-bold bg-purple-50 border border-purple-200 px-1.5 py-0.2 rounded mt-0.5">Beasiswa SPP</span>
                                @elseif($tagihan->nominal_addon > 0)
                                    <span class="inline-block text-[10px] text-gray-500">SPP Pokok: Rp {{ number_format($tagihan->nominal_spp, 0, ',', '.') }}</span>
                                @endif
                                @if($tagihan->addons->count() > 0)
                                    <div class="mt-1 space-y-0.5 text-right">
                                        @foreach($tagihan->addons as $addonItem)
                                            <div class="text-[10px] text-purple-700 font-bold bg-purple-50 border border-purple-200 px-1.5 py-0.5 rounded inline-block">
                                                + {{ $addonItem->nama_biaya }}: Rp {{ number_format($addonItem->nominal, 0, ',', '.') }}
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($tagihan->nominal_addon > 0)
                                    <div class="text-[10px] text-purple-700 font-bold mt-0.5">+ Add-on: Rp {{ number_format($tagihan->nominal_addon, 0, ',', '.') }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-mono text-green-700 font-semibold">
                                <div>Rp {{ number_format($tagihan->nominal_terbayar, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4 text-right font-mono font-bold {{ $tagihan->sisa_tagihan > 0 ? 'text-amber-700' : 'text-gray-400' }}">
                                Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if((float)$tagihan->nominal_spp === 0.0 && $tagihan->status === 'lunas')
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] border inline-flex items-center gap-1"
                                          style="background-color: #f3e8ff !important; color: #7e22ce !important; border-color: #d8b4fe !important;">
                                        <i class="ti ti-award"></i> BEASISWA FULL
                                    </span>
                                @elseif($tagihan->status === 'lunas')
                                    <span class="px-3 py-1 rounded-full font-bold text-[11px] border"
                                          style="background-color: #dcfce7 !important; color: #15803d !important; border-color: #86efac !important;">
                                        LUNAS
                                    </span>
                                @elseif($tagihan->status === 'cicilan')
                                    <span class="px-3 py-1 rounded-full font-bold text-[11px] border"
                                          style="background-color: #fef3c7 !important; color: #b45309 !important; border-color: #fde68a !important;">
                                        CICILAN
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full font-bold text-[11px] border"
                                          style="background-color: #fee2e2 !important; color: #b91c1c !important; border-color: #fca5a5 !important;">
                                        BELUM LUNAS
                                    </span>
                                @endif
                            </td>

                            <!-- Kolom Petugas Input / Verifikasi -->
                            <td class="px-6 py-4 text-left whitespace-nowrap">
                                @php
                                    $approvedPayments = $tagihan->pembayarans->where('status', 'diterima')->sortByDesc('id');
                                    $lastPayment = $approvedPayments->first();
                                @endphp
                                @if($lastPayment && $lastPayment->verifiedBy)
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-50 border border-gray-200 text-gray-700 font-medium text-xs">
                                        <i class="ti ti-user-check text-emerald-600 text-sm"></i>
                                        <span>{{ $lastPayment->verifiedBy->name }}</span>
                                    </div>
                                    @if($approvedPayments->count() > 1)
                                        <div class="text-[10px] text-gray-400 mt-0.5" title="{{ $approvedPayments->pluck('verifiedBy.name')->filter()->unique()->implode(', ') }}">
                                            {{ $approvedPayments->count() }}x pembayaran
                                        </div>
                                    @endif
                                @elseif((float)$tagihan->nominal_spp === 0.0 && $tagihan->status === 'lunas')
                                    <span class="text-xs text-purple-600 font-medium">Sistem (Beasiswa)</span>
                                @else
                                    <span class="text-xs text-gray-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($tagihan->broadcast_wa_at)
                                    <span class="text-green-600 font-semibold flex items-center justify-center gap-1">
                                        <i class="ti ti-check text-sm"></i> Terkirim
                                    </span>
                                    <span class="text-[10px] text-gray-400 block">{{ $tagihan->broadcast_wa_at->format('d/m H:i') }}</span>
                                @else
                                    @if(empty($tagihan->santri->nomor_rekening_spp))
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200" title="Rekening/VA santri belum diatur. Broadcast WA otomatis dilewati sampai rekening diatur.">
                                            <i class="ti ti-clock text-xs"></i> Menunggu VA
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">Belum</span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <button type="button"
                                            onclick='openAddonModal({{ $tagihan->id }}, {{ json_encode($tagihan->santri->nama_lengkap, JSON_HEX_APOS | JSON_HEX_QUOT) }}, {{ json_encode($tagihan->addons, JSON_HEX_APOS | JSON_HEX_QUOT) }}, {{ (float)$tagihan->nominal_spp }})'
                                            class="px-2.5 py-1.5 font-bold rounded-lg transition inline-flex items-center gap-1 cursor-pointer text-xs"
                                            style="background-color: #f5f3ff !important; color: #6d28d9 !important; border: 1px solid #ddd6fe !important;"
                                            title="Kelola Biaya Tambahan (Add-on)">
                                        <i class="ti ti-plus text-xs"></i> Add-on
                                        @if($tagihan->addons->count() > 0)
                                            <span class="ml-0.5 px-1.5 py-0.2 rounded-full text-[10px] bg-purple-700 text-white font-bold">{{ $tagihan->addons->count() }}</span>
                                        @endif
                                    </button>
                                    <button type="button"
                                            onclick="openSantriRekeningModal({{ $tagihan->santri->id }}, '{{ addslashes($tagihan->santri->nama_lengkap) }}', '{{ addslashes($tagihan->santri->nama_bank_spp ?? '') }}', '{{ addslashes($tagihan->santri->nomor_rekening_spp ?? '') }}', '{{ addslashes($tagihan->santri->atas_nama_rekening_spp ?? '') }}', {{ $tagihan->santri->nominal_spp ?? 'null' }}, {{ $tagihan->santri->is_beasiswa ? 'true' : 'false' }})"
                                            class="px-2.5 py-1.5 font-bold rounded-lg transition inline-flex items-center gap-1 cursor-pointer text-xs"
                                            style="background-color: #f0fdf4 !important; color: #15803d !important; border: 1px solid #bbf7d0 !important;"
                                            title="Atur Rekening Pembayaran Santri Ini">
                                        <i class="ti ti-credit-card text-sm"></i> Rekening
                                    </button>
                                    <button type="button"
                                            onclick="openEditTagihanModal({{ $tagihan->id }}, '{{ addslashes($tagihan->santri->nama_lengkap) }}', {{ (float) $tagihan->nominal_spp }}, '{{ addslashes($tagihan->keterangan ?? '') }}', {{ (float) $tagihan->nominal_terbayar }})"
                                            class="px-2.5 py-1.5 font-bold rounded-lg transition inline-flex items-center gap-1 cursor-pointer text-xs"
                                            style="background-color: #fffbeb !important; color: #b45309 !important; border: 1px solid #fde68a !important;"
                                            title="Edit Tagihan SPP Pokok">
                                        <i class="ti ti-pencil text-sm"></i> Edit
                                    </button>
                                    @if($tagihan->status !== 'lunas')
                                        <button type="button" onclick="openBayarModal({{ $tagihan->id }}, '{{ addslashes($tagihan->santri->nama_lengkap) }}', {{ $tagihan->sisa_tagihan }})"
                                                class="px-2.5 py-1.5 font-bold rounded-lg transition inline-flex items-center gap-1 cursor-pointer text-xs"
                                                style="background-color: #eff6ff !important; color: #1d4ed8 !important; border: 1px solid #bfdbfe !important;"
                                                title="Catat Bayar / Cicilan">
                                            <i class="ti ti-cash text-sm"></i> Bayar
                                        </button>
                                    @else
                                        <span class="text-green-600 font-bold px-1.5 py-1 text-xs">Lunas</span>
                                    @endif
                                    <a href="{{ route('admin.spp.tagihan.invoice', $tagihan->id) }}" target="_blank"
                                       class="px-2.5 py-1.5 font-bold rounded-lg transition inline-flex items-center gap-1 cursor-pointer text-xs"
                                       style="background-color: #f0fdf4 !important; color: #166534 !important; border: 1px solid #bbf7d0 !important;"
                                       title="Cetak Invoice / Bukti Pembayaran Resmi (PDF)">
                                        <i class="ti ti-printer text-sm"></i> Invoice
                                    </a>
                                    @if($tagihan->nominal_terbayar > 0 || ($tagihan->status === 'lunas' && (float)$tagihan->nominal_tagihan > 0))
                                        <form action="{{ route('admin.spp.batalkan_lunas', $tagihan->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin membatalkan status lunas/pembayaran ananda {{ addslashes($tagihan->santri->nama_lengkap) }} periode {{ $tagihan->bulan_label }}?\n\nRiwayat pembayaran akan di-reset dan tagihan akan kembali menjadi Belum Lunas.')">
                                            @csrf
                                            <button type="submit"
                                                    class="px-2.5 py-1.5 font-bold rounded-lg transition inline-flex items-center gap-1 cursor-pointer text-xs"
                                                    style="background-color: #fff1f2 !important; color: #e11d48 !important; border: 1px solid #fecdd3 !important;"
                                                    title="Batalkan Pelunasan (Kembalikan ke status Belum Lunas)">
                                                <i class="ti ti-rotate-clockwise-2 text-sm"></i> Batalkan Lunas
                                            </button>
                                        </form>
                                    @endif
                                    @if($tagihan->nominal_terbayar == 0)
                                        <form action="{{ route('admin.spp.destroy', $tagihan->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus tagihan SPP ananda {{ addslashes($tagihan->santri->nama_lengkap) }} periode {{ $tagihan->bulan_label }}?\n\nTagihan ini otomatis akan hilang dari portal wali santri.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-2.5 py-1.5 font-bold rounded-lg transition inline-flex items-center gap-1 cursor-pointer text-xs"
                                                    style="background-color: #fef2f2 !important; color: #b91c1c !important; border: 1px solid #fecaca !important;"
                                                    title="Hapus Tagihan (Otomatis hilang di user juga)">
                                                <i class="ti ti-trash text-sm"></i> Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                <i class="ti ti-receipt-off text-3xl text-gray-400 mb-2 block"></i>
                                Belum ada tagihan SPP pada periode ini. Klik "Buat Tagihan Bulanan" di kanan atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tagihans->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $tagihans->links() }}
            </div>
        @endif
    </div>
    @endif


</div>

<!-- Modal Hapus Tagihan Periode -->
<div id="deletePeriodModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">
        <div class="p-5 bg-red-600 text-white flex items-center justify-between">
            <h3 class="text-base font-bold flex items-center gap-2"><i class="ti ti-alert-triangle"></i> Hapus Tagihan Periode</h3>
            <button type="button" onclick="closeDeletePeriodModal()" class="text-white/80 hover:text-white"><i class="ti ti-x text-xl"></i></button>
        </div>
        <form action="{{ route('admin.spp.destroy_period') }}" method="POST">
            @csrf
            @method('DELETE')
            <input type="hidden" name="bulan" value="{{ $bulan }}">
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <div class="p-5 space-y-4 text-sm">
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-3">
                    Hapus semua tagihan <strong>{{ \App\Models\SppTagihan::getNamaBulan($bulan) }} {{ $tahun }}</strong> yang belum memiliki pembayaran?
                </div>
                <p class="text-xs text-gray-600">Tagihan yang sudah memiliki riwayat pembayaran tidak akan dihapus demi menjaga laporan keuangan.</p>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeDeletePeriodModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold">Ya, Hapus Tagihan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Tagihan -->
<div id="editTagihanModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2"><i class="ti ti-pencil text-amber-600"></i> Edit Tagihan SPP</h3>
            <button type="button" onclick="closeEditTagihanModal()" class="text-gray-400 hover:text-gray-600"><i class="ti ti-x text-xl"></i></button>
        </div>
        <form id="formEditTagihan" method="POST">
            @csrf
            @method('PUT')
            <div class="p-5 space-y-4 text-xs">
                <div>
                    <span class="text-gray-500">Santri:</span>
                    <h4 id="editTagihanSantriNama" class="text-sm font-bold text-gray-900"></h4>
                    <p id="editTagihanPaidInfo" class="text-[11px] text-gray-500 mt-1"></p>
                </div>
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Nominal Tagihan (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="nominal_tagihan" id="editNominalTagihan" min="1000" step="1000" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono font-bold focus:ring-2 focus:ring-amber-500">
                    <p class="text-[11px] text-gray-400 mt-1">Nominal tidak dapat lebih kecil dari total pembayaran yang sudah diterima.</p>
                </div>
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Keterangan (Opsional)</label>
                    <input type="text" name="keterangan" id="editKeteranganTagihan" maxlength="255" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500" placeholder="Contoh: Penyesuaian nominal SPP">
                </div>
                <div class="pt-3 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" onclick="closeEditTagihanModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Generate Tagihan Bulanan -->
<div id="generateModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden animate-in fade-in">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-calendar-plus text-blue-600"></i> Buat Tagihan SPP Bulanan (Massal)
            </h3>
            <button type="button" onclick="closeGenerateModal()" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>

        <form action="{{ route('admin.spp.generate') }}" method="POST">
            @csrf
            <div class="p-5 space-y-4 text-xs">
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-blue-800">
                    Sistem akan membuat tagihan SPP untuk seluruh santri aktif (santri alumni otomatis dikecualikan). Anda dapat menyesuaikan nominal per jenjang kelas di bawah ini.
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Bulan</label>
                        <select name="bulan" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                    {{ \App\Models\SppTagihan::getNamaBulan($m) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Tahun</label>
                        <input type="number" name="tahun" value="{{ $tahun }}" min="2020" max="2099" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-semibold focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="border-t border-b border-gray-100 py-3 space-y-2">
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Nominal Cadangan / Default (Rp)</label>
                        <input type="number" name="nominal_default" value="{{ $sppSetting->nominal_default ?? 1000000 }}" step="1000" min="1000" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono font-bold text-gray-900 focus:ring-2 focus:ring-blue-500">
                        <p class="text-[11px] text-gray-500 mt-1">Sistem otomatis menggunakan nominal SPP masing-masing santri. Nominal cadangan di atas hanya digunakan jika ada santri yang belum diatur nominalnya.</p>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeGenerateModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 font-bold rounded-lg text-xs transition shadow-sm cursor-pointer"
                            style="background-color: #2563eb !important; color: #ffffff !important;">
                        Terbitkan Tagihan Massal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Input Tagihan SPP 1 Santri -->
<div id="singleTagihanModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden animate-in fade-in">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between" style="background-color: #4f46e5 !important; color: #ffffff !important;">
            <h3 class="text-base font-bold flex items-center gap-2">
                <i class="ti ti-user-plus text-lg"></i> Input Tagihan SPP (1 Santri)
            </h3>
            <button type="button" onclick="closeSingleTagihanModal()" class="text-white/80 hover:text-white cursor-pointer">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>

        <form action="{{ route('admin.spp.store_single') }}" method="POST">
            @csrf
            <div class="p-5 space-y-4 text-xs">
                <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-3 text-indigo-900 leading-relaxed">
                    Pilih santri untuk menerbitkan tagihan SPP bulanan. Nominal tagihan dan rekening tujuan akan otomatis menggunakan tarif yang berlaku di sistem.
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Pilih Santri <span class="text-red-500">*</span></label>
                    
                    <!-- Kolom Pencarian Santri -->
                    <div class="relative mb-2">
                        <i class="ti ti-search absolute left-3 top-2.5 text-gray-400"></i>
                        <input type="text" id="searchSantriSingleInput" oninput="filterSantriSingleOptions(this.value)"
                               placeholder="Ketik untuk mencari nama santri..."
                               class="w-full pl-9 pr-8 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <button type="button" onclick="clearSearchSantriSingle()" id="btnClearSearchSantriSingle" class="hidden absolute right-2.5 top-2.5 text-gray-400 hover:text-gray-600 cursor-pointer">
                            <i class="ti ti-x text-sm"></i>
                        </button>
                    </div>

                    <select name="santri_id" id="singleSantriSelect" required onchange="handleSingleSantriChange(this)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih Santri Aktif --</option>
                        @foreach($allSantriAktif as $s)
                            <option value="{{ $s->id }}"
                                    data-nominal="{{ $s->tarif_spp }}"
                                    data-nominal-text="Rp {{ number_format($s->tarif_spp, 0, ',', '.') }}">
                                {{ $s->nama_lengkap }} ({{ $s->kelas_label }}) - Rp {{ number_format($s->tarif_spp, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    <div id="santriSearchCountInfo" class="text-[11px] text-gray-500 mt-1 hidden"></div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Bulan</label>
                        <select name="bulan" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                    {{ \App\Models\SppTagihan::getNamaBulan($m) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Tahun</label>
                        <input type="number" name="tahun" value="{{ $tahun }}" min="2020" max="2099" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Info Tarif SPP Otomatis -->
                <div class="p-3 bg-indigo-50/70 border border-indigo-200 rounded-xl flex items-center justify-between">
                    <div>
                        <span class="text-gray-500 block text-[11px] font-semibold">Tarif SPP Terdeteksi:</span>
                        <span id="singleTarifPreview" class="text-sm font-bold font-mono text-indigo-700">-- Pilih santri terlebih dahulu --</span>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        ✓ Otomatis dari Sistem
                    </span>
                </div>

                <!-- Pilihan Status Tagihan (Belum Lunas / Sudah Lunas) -->
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Status Tagihan</label>
                    <select name="status_awal" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-bold focus:ring-2 focus:ring-indigo-500">
                        <option value="belum_lunas">Belum Lunas (Untuk Ditagihkan / Dibayar via Aplikasi)</option>
                        <option value="lunas">Sudah Lunas (Sudah Dibayar - Tidak Menagih WA Lagi)</option>
                    </select>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeSingleTagihanModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 font-bold rounded-lg text-xs transition shadow-sm cursor-pointer"
                            style="background-color: #4f46e5 !important; color: #ffffff !important;">
                        Simpan Tagihan Santri
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Catat Bayar / Cicilan SPP -->
<div id="bayarModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden animate-in fade-in">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-cash text-green-600"></i> Catat Pembayaran / Cicilan SPP
            </h3>
            <button type="button" onclick="closeBayarModal()" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>

        <form id="formBayarSpp" method="POST">
            @csrf
            <div class="p-5 space-y-4 text-xs">
                <div>
                    <span class="text-gray-500">Santri:</span>
                    <h4 id="bayarSantriNama" class="text-sm font-bold text-gray-900"></h4>
                    <div class="text-[11px] text-amber-700 mt-0.5">
                        Sisa tagihan yang harus dibayar: <strong id="bayarSisaTagihan" class="font-mono"></strong>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Nominal Pembayaran (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="jumlah_bayar" id="inputJumlahBayar" min="1000" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono font-bold text-green-700 focus:ring-2 focus:ring-green-500">
                    <p class="text-[11px] text-gray-400 mt-1">Dapat dibayar sebagian (cicilan) atau lunas sekaligus.</p>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Metode Pembayaran</label>
                    <select name="metode_bayar" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500">
                        <option value="transfer">Transfer Bank</option>
                        <option value="tunai">Tunai / Langsung di Kantor</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Catatan Tambahan (Opsional)</label>
                    <input type="text" name="catatan" placeholder="Contoh: Cicilan ke-1 / Bukti mutasi BCA"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500">
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeBayarModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 font-bold rounded-lg text-sm transition shadow-sm cursor-pointer"
                            style="background-color: #16a34a !important; color: #ffffff !important;">
                        Simpan Pembayaran
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openGenerateModal() {
        document.getElementById('generateModal').classList.remove('hidden');
    }
    function closeGenerateModal() {
        document.getElementById('generateModal').classList.add('hidden');
    }

    function openSingleTagihanModal() {
        clearSearchSantriSingle();
        document.getElementById('singleTagihanModal').classList.remove('hidden');
        setTimeout(() => {
            const input = document.getElementById('searchSantriSingleInput');
            if (input) input.focus();
        }, 100);
    }
    function closeSingleTagihanModal() {
        document.getElementById('singleTagihanModal').classList.add('hidden');
    }

    function filterSantriSingleOptions(keyword) {
        const select = document.getElementById('singleSantriSelect');
        const clearBtn = document.getElementById('btnClearSearchSantriSingle');
        const info = document.getElementById('santriSearchCountInfo');
        const search = (keyword || '').toLowerCase().trim();

        if (search.length > 0) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }

        let matchCount = 0;
        let firstMatchOpt = null;

        for (let i = 1; i < select.options.length; i++) {
            const opt = select.options[i];
            const text = opt.text.toLowerCase();
            if (search === '' || text.includes(search)) {
                opt.hidden = false;
                opt.disabled = false;
                matchCount++;
                if (!firstMatchOpt) firstMatchOpt = opt;
            } else {
                opt.hidden = true;
                opt.disabled = true;
            }
        }

        if (search.length > 0) {
            info.classList.remove('hidden');
            info.textContent = `Ditemukan ${matchCount} santri cocok.`;
            if (firstMatchOpt && (select.selectedIndex <= 0 || select.options[select.selectedIndex]?.disabled)) {
                select.value = firstMatchOpt.value;
                handleSingleSantriChange(select);
            }
        } else {
            info.classList.add('hidden');
        }
    }

    function clearSearchSantriSingle() {
        const input = document.getElementById('searchSantriSingleInput');
        if (input) input.value = '';
        filterSantriSingleOptions('');
    }

    function handleSingleSantriChange(select) {
        const selectedOpt = select.options[select.selectedIndex];
        const preview = document.getElementById('singleTarifPreview');
        if (selectedOpt && selectedOpt.dataset.nominalText) {
            preview.textContent = selectedOpt.dataset.nominalText;
        } else {
            preview.textContent = '-- Pilih santri terlebih dahulu --';
        }
    }

    function openDeletePeriodModal() {
        document.getElementById('deletePeriodModal').classList.remove('hidden');
    }
    function closeDeletePeriodModal() {
        document.getElementById('deletePeriodModal').classList.add('hidden');
    }

    function openEditTagihanModal(id, santriNama, nominal, keterangan, terbayar) {
        document.getElementById('editTagihanSantriNama').innerText = santriNama;
        document.getElementById('editTagihanPaidInfo').innerText = 'Sudah dibayar: Rp ' + Number(terbayar).toLocaleString('id-ID');
        const nominalInput = document.getElementById('editNominalTagihan');
        nominalInput.value = nominal;
        nominalInput.min = Math.max(1000, Number(terbayar));
        document.getElementById('editKeteranganTagihan').value = keterangan || '';
        document.getElementById('formEditTagihan').action = "{{ url('/admin/spp') }}/" + id;
        document.getElementById('editTagihanModal').classList.remove('hidden');
    }
    function closeEditTagihanModal() {
        document.getElementById('editTagihanModal').classList.add('hidden');
    }

    function openBayarModal(tagihanId, santriNama, sisaTagihan) {
        document.getElementById('bayarSantriNama').innerText = santriNama;
        document.getElementById('bayarSisaTagihan').innerText = 'Rp ' + sisaTagihan.toLocaleString('id-ID');
        document.getElementById('inputJumlahBayar').value = sisaTagihan;
        document.getElementById('inputJumlahBayar').max = sisaTagihan;

        const form = document.getElementById('formBayarSpp');
        form.action = "{{ url('/admin/spp') }}/" + tagihanId + "/bayar";

        document.getElementById('bayarModal').classList.remove('hidden');
    }
    function closeBayarModal() {
        document.getElementById('bayarModal').classList.add('hidden');
    }

    function openSantriRekeningModal(santriId, santriNama, bank, nomor, atasNama, nominalSpp, isBeasiswa) {
        document.getElementById('modalSantriRekeningNama').innerText = santriNama;
        document.getElementById('modalSantriRekeningBank').value = bank || '';
        document.getElementById('modalSantriRekeningNomor').value = nomor || '';
        document.getElementById('modalSantriRekeningAtasNama').value = atasNama || '';
        
        const beasiswaCheckbox = document.getElementById('modalSantriRekeningBeasiswa');
        const nominalInput = document.getElementById('modalSantriRekeningNominal');
        
        const isBeasiswaTrue = (isBeasiswa === true || isBeasiswa === 'true' || (nominalSpp !== null && Number(nominalSpp) === 0));
        beasiswaCheckbox.checked = isBeasiswaTrue;
        toggleBeasiswa(isBeasiswaTrue);

        if (!isBeasiswaTrue) {
            nominalInput.value = (nominalSpp !== null && nominalSpp !== undefined) ? nominalSpp : '';
        }

        document.getElementById('formSantriRekening').action = "{{ url('/admin/spp/santri') }}/" + santriId + "/rekening";
        document.getElementById('santriRekeningModal').classList.remove('hidden');
    }

    function toggleBeasiswa(isBeasiswa) {
        const nominalInput = document.getElementById('modalSantriRekeningNominal');
        const nominalWrap = document.getElementById('modalSantriRekeningNominalWrap');
        if (isBeasiswa) {
            nominalInput.value = 0;
            nominalInput.setAttribute('disabled', 'disabled');
            nominalWrap.classList.add('opacity-50');
        } else {
            nominalInput.removeAttribute('disabled');
            nominalWrap.classList.remove('opacity-50');
            if (nominalInput.value == 0) {
                nominalInput.value = '';
            }
        }
    }

    function closeSantriRekeningModal() {
        document.getElementById('santriRekeningModal').classList.add('hidden');
    }

    let currentAddonTagihanId = null;

    function openAddonModal(tagihanId, santriNama, addons, nominalSpp) {
        currentAddonTagihanId = tagihanId;
        document.getElementById('modalAddonSantriNama').innerText = santriNama;
        document.getElementById('modalAddonSppPokok').innerText = nominalSpp === 0 ? 'Rp 0 (Beasiswa Full)' : 'Rp ' + Number(nominalSpp).toLocaleString('id-ID');
        cancelEditAddon();

        const listContainer = document.getElementById('modalAddonList');
        listContainer.innerHTML = '';
        let totalAddon = 0;

        if (!addons || addons.length === 0) {
            listContainer.innerHTML = '<div class="text-center py-4 bg-gray-50 rounded-xl text-gray-400 border border-dashed border-gray-200">Belum ada biaya add-on pada tagihan ini.</div>';
        } else {
            addons.forEach(function(item) {
                totalAddon += Number(item.nominal);
                const safeNama = (item.nama_biaya || '').replace(/'/g, "\\'");
                const safeKet = (item.keterangan || '').replace(/'/g, "\\'");
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between p-2.5 bg-white border border-gray-200 rounded-xl hover:bg-purple-50/30 transition';
                row.innerHTML = `
                    <div>
                        <div class="font-bold text-gray-900">+ ${item.nama_biaya}</div>
                        ${item.keterangan ? `<div class="text-[11px] text-gray-500">${item.keterangan}</div>` : ''}
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-bold text-purple-700">Rp ${Number(item.nominal).toLocaleString('id-ID')}</span>
                        <button type="button" onclick="editAddonItem(${item.id}, '${safeNama}', ${Number(item.nominal)}, '${safeKet}')" class="px-2 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 font-bold text-[11px] cursor-pointer transition inline-flex items-center gap-1" title="Edit biaya add-on ini">
                            <i class="ti ti-pencil text-xs"></i> Edit
                        </button>
                        <button type="button" onclick="deleteAddon(${item.id}, '${safeNama}')" class="text-red-500 hover:text-red-700 p-1 cursor-pointer transition" title="Hapus biaya ini">
                            <i class="ti ti-trash text-sm"></i>
                        </button>
                    </div>
                `;
                listContainer.appendChild(row);
            });
        }

        document.getElementById('modalAddonTotalNominal').innerText = 'Total: Rp ' + totalAddon.toLocaleString('id-ID');
        document.getElementById('modalAddonSpp').classList.remove('hidden');
    }

    function editAddonItem(addonId, nama, nominal, keterangan) {
        document.getElementById('formStoreAddon').action = "{{ url('/admin/spp/addon') }}/" + addonId;
        document.getElementById('addonFormMethodInput').value = 'PUT';
        document.getElementById('addonFormMethodInput').disabled = false;
        document.getElementById('addonFormTitle').innerHTML = '<i class="ti ti-pencil text-amber-600 text-sm"></i> Edit Biaya Add-on: <span class="text-purple-700">' + nama + '</span>';
        document.getElementById('inputAddonNama').value = nama;
        document.getElementById('inputAddonNominal').value = nominal;
        document.getElementById('inputAddonKeterangan').value = keterangan || '';
        document.getElementById('btnSubmitAddonText').innerText = 'Simpan Perubahan Add-on';
        document.getElementById('btnCancelEditAddon').classList.remove('hidden');
        document.getElementById('inputAddonNama').focus();
    }

    function cancelEditAddon() {
        if (currentAddonTagihanId) {
            document.getElementById('formStoreAddon').action = "{{ url('/admin/spp/tagihan') }}/" + currentAddonTagihanId + "/addon";
        }
        document.getElementById('addonFormMethodInput').value = 'POST';
        document.getElementById('addonFormMethodInput').disabled = true;
        document.getElementById('addonFormTitle').innerHTML = '<i class="ti ti-plus text-purple-700 text-sm"></i> Tambah Biaya Add-on Baru';
        document.getElementById('inputAddonNama').value = '';
        document.getElementById('inputAddonNominal').value = '';
        document.getElementById('inputAddonKeterangan').value = '';
        document.getElementById('btnSubmitAddonText').innerText = 'Simpan Biaya Add-on';
        document.getElementById('btnCancelEditAddon').classList.add('hidden');
    }

    function closeAddonModal() {
        document.getElementById('modalAddonSpp').classList.add('hidden');
    }

    function setAddonPreset(nama, nominal) {
        document.getElementById('inputAddonNama').value = nama;
        document.getElementById('inputAddonNominal').value = nominal;
    }

    function deleteAddon(addonId, nama) {
        if (confirm("Hapus biaya add-on '" + nama + "'? Total tagihan santri akan otomatis dikurangi.")) {
            const form = document.getElementById('formDeleteAddon');
            form.action = "{{ url('/admin/spp/addon') }}/" + addonId;
            form.submit();
        }
    }

    function openSantriAddonDirectModal(santriId, santriNama) {
        document.getElementById('directAddonSantriNama').innerText = santriNama;
        document.getElementById('formDirectSantriAddon').action = "{{ url('/admin/spp/santri') }}/" + santriId + "/addon-template";
        document.getElementById('directAddonNama').value = '';
        document.getElementById('directAddonNominal').value = '';
        document.getElementById('directAddonKeterangan').value = '';
        document.getElementById('modalSantriAddonDirect').classList.remove('hidden');
    }

    function closeSantriAddonDirectModal() {
        document.getElementById('modalSantriAddonDirect').classList.add('hidden');
    }

    function openEditSantriAddonTemplateModal(templateId, nama, nominal, keterangan, santriNama) {
        document.getElementById('editAddonTemplateSantriNama').innerText = santriNama;
        document.getElementById('formEditSantriAddonTemplate').action = "{{ url('/admin/spp/addon-template') }}/" + templateId;
        document.getElementById('editAddonTemplateNama').value = nama;
        document.getElementById('editAddonTemplateNominal').value = nominal;
        document.getElementById('editAddonTemplateKeterangan').value = keterangan || '';
        document.getElementById('modalEditSantriAddonTemplate').classList.remove('hidden');
    }

    function closeEditSantriAddonTemplateModal() {
        document.getElementById('modalEditSantriAddonTemplate').classList.add('hidden');
    }

    function setDirectAddonPreset(nama, nominal) {
        document.getElementById('directAddonNama').value = nama;
        document.getElementById('directAddonNominal').value = nominal;
    }

    // Modal Centang Multi-Bulan & Multi-Santri
    function openMultiTagihanModal() {
        const m = document.getElementById('multiTagihanModal');
        m.classList.remove('hidden');
        m.style.display = 'block';
        document.body.style.overflow = 'hidden';
        updateMultiTagihanCounter();
    }

    function closeMultiTagihanModal() {
        const m = document.getElementById('multiTagihanModal');
        m.classList.add('hidden');
        m.style.display = 'none';
        document.body.style.overflow = '';
    }

    function selectMultiMonthsPreset(count) {
        const checkboxes = document.querySelectorAll('.chk-multi-bulan-admin');
        const tahunInput = document.querySelector('#modalMultiMonthTagihan input[name="tahun"]') || document.querySelector('input[name="tahun"]');
        const selectedYear = tahunInput ? parseInt(tahunInput.value) : {{ (int)$tahun }};
        const currentYear = {{ (int)$tahun }};
        const currentMonth = {{ (int)$bulan }};
        checkboxes.forEach(cb => cb.checked = false);

        if (count === 12) {
            // 12 Bulan (1 Tahun Penuh pada tahun yang dipilih: Januari s/d Desember)
            checkboxes.forEach(cb => cb.checked = true);
        } else if (count === 6) {
            // 6 Bulan (1 Semester pada tahun yang dipilih):
            // Jika tahun depan (misal 2027): centang Januari s/d Juni (1 s/d 6)
            // Jika tahun berjalan: jika di semester 2 (bulan >= 7), centang Juli s/d Desember (7 s/d 12), selain itu 1 s/d 6
            let start = 1;
            if (selectedYear === currentYear && currentMonth >= 7) {
                start = 7;
            }
            for (let m = start; m < start + 6; m++) {
                const target = document.querySelector('.chk-multi-bulan-admin[value="' + m + '"]');
                if (target) target.checked = true;
            }
        } else if (count === 3) {
            // 3 Bulan pada tahun yang dipilih:
            let start = 1;
            if (selectedYear === currentYear) {
                if (currentMonth <= 10) {
                    start = currentMonth;
                } else {
                    start = 10; // Triwulan akhir (Okt, Nov, Des)
                }
            }
            for (let m = start; m < start + 3; m++) {
                const target = document.querySelector('.chk-multi-bulan-admin[value="' + m + '"]');
                if (target) target.checked = true;
            }
        }
        updateMultiTagihanCounter();
    }

    function filterMultiSantriList() {
        const keyword = (document.getElementById('searchMultiSantriInput')?.value || '').toLowerCase().trim();
        const kelasFilter = document.getElementById('filterMultiSantriKelas')?.value || '';
        const items = document.querySelectorAll('.multi-santri-item');
        let visibleCount = 0;

        items.forEach(item => {
            const name = (item.dataset.nama || '').toLowerCase();
            const kelas = item.dataset.kelas || '';
            const matchName = keyword === '' || name.includes(keyword);
            const matchKelas = kelasFilter === '' || kelas === kelasFilter;

            if (matchName && matchKelas) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        });

        const info = document.getElementById('multiSantriVisibleInfo');
        if (info) {
            info.innerText = 'Menampilkan ' + visibleCount + ' santri';
        }
    }

    function toggleAllMultiSantri(checked) {
        const items = document.querySelectorAll('.multi-santri-item:not(.hidden) .chk-multi-santri-admin');
        items.forEach(cb => {
            cb.checked = checked;
        });
        updateMultiTagihanCounter();
    }

    function updateMultiTagihanCounter() {
        const checkedMonths = document.querySelectorAll('.chk-multi-bulan-admin:checked').length;
        const checkedSantris = document.querySelectorAll('.chk-multi-santri-admin:checked').length;
        const badgeBulan = document.getElementById('countMultiBulanBadge');
        const badgeSantri = document.getElementById('countMultiSantriBadge');
        if (badgeBulan) badgeBulan.innerText = checkedMonths + ' Bulan dipilih';
        if (badgeSantri) badgeSantri.innerText = checkedSantris + ' Santri dipilih';
    }

    function toggleMultiStatusInfo(statusVal) {
        const waWrap = document.getElementById('multiKirimWaWrap');
        const lunasInfo = document.getElementById('multiLunasNotice');
        if (statusVal === 'lunas') {
            if (waWrap) waWrap.classList.add('hidden');
            if (lunasInfo) lunasInfo.classList.remove('hidden');
        } else {
            if (waWrap) waWrap.classList.remove('hidden');
            if (lunasInfo) lunasInfo.classList.add('hidden');
        }
    }
</script>

<!-- Form Hapus Add-on SPP (Hidden) -->
<form id="formDeleteAddon" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Modal Kelola Add-on SPP (Tambah & Edit) -->
<div id="modalAddonSpp" class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
     style="z-index: 99999 !important; background-color: rgba(15, 23, 42, 0.7) !important; backdrop-filter: blur(4px);">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden my-auto max-h-[92vh] flex flex-col animate-in fade-in">
        <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between shrink-0" style="background-color: #6d28d9 !important; color: #ffffff !important;">
            <div>
                <h3 class="text-sm sm:text-base font-bold flex items-center gap-1.5" style="color: #ffffff !important;">
                    <i class="ti ti-receipt text-lg" style="color: #ffffff !important;"></i> Biaya Tambahan (Add-on) SPP
                </h3>
                <div class="text-xs text-purple-200 font-semibold mt-0.5" id="modalAddonSantriNama">-</div>
            </div>
            <button type="button" onclick="closeAddonModal()" class="cursor-pointer p-1 transition opacity-90 hover:opacity-100" style="color: #ffffff !important;">
                <i class="ti ti-x text-xl" style="color: #ffffff !important;"></i>
            </button>
        </div>

        <div class="p-5 space-y-4 overflow-y-auto flex-1 text-xs">
            <!-- Rincian Biaya Pokok -->
            <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl flex items-center justify-between text-purple-900 font-semibold">
                <span>SPP Pokok Santri:</span>
                <span id="modalAddonSppPokok" class="font-mono font-bold text-sm">Rp 0</span>
            </div>

            <!-- List Add-on Saat Ini -->
            <div>
                <div class="font-bold text-gray-700 uppercase mb-2 flex items-center justify-between">
                    <span>Daftar Add-on Aktif (Klik Edit untuk mengubah):</span>
                    <span id="modalAddonTotalNominal" class="text-purple-700 font-mono font-bold">Total: Rp 0</span>
                </div>
                <div id="modalAddonList" class="space-y-1.5">
                    <!-- Dinamis via JS -->
                </div>
            </div>

            <!-- Form Tambah / Edit Add-on -->
            <div class="pt-4 border-t border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <div id="addonFormTitle" class="font-bold text-gray-900 uppercase flex items-center gap-1.5">
                        <i class="ti ti-plus text-purple-700 text-sm"></i> Tambah Biaya Add-on Baru
                    </div>
                    <button type="button" id="btnCancelEditAddon" onclick="cancelEditAddon()" class="hidden px-2.5 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold text-[11px] cursor-pointer">
                        Batal Edit
                    </button>
                </div>
                <form id="formStoreAddon" method="POST" class="space-y-3 bg-gray-50 p-3.5 rounded-xl border border-gray-200">
                    @csrf
                    <input type="hidden" name="_method" id="addonFormMethodInput" value="POST" disabled>
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Nama Biaya Add-on <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_biaya" id="inputAddonNama" required
                               placeholder="Contoh: Laundry / Uang Buku / Kegiatan"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none">
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            <button type="button" onclick="setAddonPreset('Laundry', 60000)" class="px-2.5 py-1 bg-white border border-gray-300 hover:border-purple-400 hover:text-purple-700 text-gray-700 rounded-lg text-[11px] font-semibold cursor-pointer transition shadow-2xs">
                                + Laundry (60rb)
                            </button>
                            <button type="button" onclick="setAddonPreset('Uang Buku / Kitab', 100000)" class="px-2.5 py-1 bg-white border border-gray-300 hover:border-purple-400 hover:text-purple-700 text-gray-700 rounded-lg text-[11px] font-semibold cursor-pointer transition shadow-2xs">
                                + Uang Buku
                            </button>
                            <button type="button" onclick="setAddonPreset('Ekstrakurikuler', 50000)" class="px-2.5 py-1 bg-white border border-gray-300 hover:border-purple-400 hover:text-purple-700 text-gray-700 rounded-lg text-[11px] font-semibold cursor-pointer transition shadow-2xs">
                                + Ekskul
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Nominal Biaya (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="nominal" id="inputAddonNominal" required step="500" min="500"
                               placeholder="Contoh: 60000"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-mono font-bold focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Keterangan (Opsional)</label>
                        <input type="text" name="keterangan" id="inputAddonKeterangan" placeholder="Contoh: Biaya laundry bulan berjalan"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>

                    <div class="p-2.5 bg-green-50 border border-green-200 rounded-lg">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="kirim_wa" value="1" checked class="w-4 h-4 text-green-600 rounded border-gray-300">
                            <span class="font-bold text-green-900 text-[11px]">Kirim Notifikasi WA Tagihan Terbaru (+ Add-on) ke Wali Santri</span>
                        </label>
                    </div>

                    <div class="pt-1 flex justify-end">
                        <button type="submit" class="px-4 py-2 font-bold rounded-lg text-xs text-white shadow-sm cursor-pointer transition flex items-center gap-1.5"
                                style="background-color: #6d28d9 !important; color: #ffffff !important;">
                            <i class="ti ti-device-floppy"></i> <span id="btnSubmitAddonText">Simpan Biaya Add-on</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="p-4 border-t border-gray-100 flex justify-end bg-gray-50 shrink-0">
            <button type="button" onclick="closeAddonModal()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-bold rounded-xl text-xs cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Modal Tambah Add-on Rutin Santri (Template Bulanan) -->
<div id="modalSantriAddonDirect" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden my-auto animate-in fade-in">
        <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between text-white" style="background-color: #7c3aed !important;">
            <div>
                <h3 class="text-sm font-bold flex items-center gap-1.5">
                    <i class="ti ti-receipt"></i> Tambah Biaya Add-on Rutin Santri
                </h3>
                <div class="text-xs text-purple-200 font-semibold mt-0.5" id="directAddonSantriNama">-</div>
            </div>
            <button type="button" onclick="closeSantriAddonDirectModal()" class="text-white/80 hover:text-white cursor-pointer">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>
        <form id="formDirectSantriAddon" method="POST" class="p-5 space-y-3.5 text-xs">
            @csrf
            
            <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl">
                <p class="text-xs text-purple-900 font-semibold leading-relaxed">
                    <i class="ti ti-info-circle text-purple-700"></i> Add-on Rutin ini disimpan sebagai template bulanan santri dan <strong>otomatis dimasukkan ke tagihan SPP</strong> saat penagihan bulanan dibuat/dikirim. <em>(Tidak langsung menerbitkan tagihan/WA sekarang)</em>.
                </p>
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Nama Biaya Add-on Rutin <span class="text-red-500">*</span></label>
                <input type="text" name="nama_biaya" id="directAddonNama" required placeholder="Contoh: Laundry / Uang Buku / Ekskul"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs">
                <div class="flex flex-wrap gap-1.5 mt-2">
                    <button type="button" onclick="setDirectAddonPreset('Laundry', 60000)" class="px-2 py-1 bg-purple-50 border border-purple-200 text-purple-700 rounded text-[11px] font-semibold cursor-pointer">+ Laundry (60rb)</button>
                    <button type="button" onclick="setDirectAddonPreset('Uang Buku / Kitab', 100000)" class="px-2 py-1 bg-purple-50 border border-purple-200 text-purple-700 rounded text-[11px] font-semibold cursor-pointer">+ Uang Buku</button>
                    <button type="button" onclick="setDirectAddonPreset('Ekstrakurikuler', 50000)" class="px-2 py-1 bg-purple-50 border border-purple-200 text-purple-700 rounded text-[11px] font-semibold cursor-pointer">+ Ekskul</button>
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Nominal Biaya (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="nominal" id="directAddonNominal" required min="500" step="500" placeholder="Contoh: 60000"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-mono font-bold">
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Keterangan (Opsional)</label>
                <input type="text" name="keterangan" id="directAddonKeterangan" placeholder="Contoh: Biaya rutin bulanan"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs">
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeSantriAddonDirectModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-bold hover:bg-gray-100 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-lg font-bold text-xs text-white shadow-sm transition cursor-pointer flex items-center gap-1.5"
                        style="background-color: #7c3aed !important; color: #ffffff !important;">
                    <i class="ti ti-device-floppy"></i> Simpan Add-on Rutin
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Template Add-on Rutin Santri -->
<div id="modalEditSantriAddonTemplate" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden my-auto animate-in fade-in">
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between text-white" style="background-color: #6d28d9 !important;">
            <div>
                <h3 class="text-sm font-bold flex items-center gap-1.5">
                    <i class="ti ti-pencil"></i> Edit Add-on Rutin Bulanan
                </h3>
                <div class="text-xs text-purple-200 font-semibold mt-0.5" id="editAddonTemplateSantriNama">-</div>
            </div>
            <button type="button" onclick="closeEditSantriAddonTemplateModal()" class="text-white/80 hover:text-white cursor-pointer">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>

        <form id="formEditSantriAddonTemplate" method="POST" class="p-5 space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Nama Biaya Add-on Rutin <span class="text-red-500">*</span></label>
                <input type="text" name="nama_biaya" id="editAddonTemplateNama" required placeholder="Contoh: Laundry / Ekskul"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs">
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Nominal Biaya (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="nominal" id="editAddonTemplateNominal" required min="500" step="500" placeholder="Contoh: 60000"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-mono font-bold">
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Keterangan (Opsional)</label>
                <input type="text" name="keterangan" id="editAddonTemplateKeterangan" placeholder="Contoh: Biaya rutin bulanan"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs">
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeEditSantriAddonTemplateModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-bold hover:bg-gray-100 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-lg font-bold text-xs text-white shadow-sm transition cursor-pointer flex items-center gap-1.5"
                        style="background-color: #6d28d9 !important; color: #ffffff !important;">
                    <i class="ti ti-device-floppy"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pengaturan Rekening & SPP Santri (+ Fitur Tambah Add-on) -->
<div id="santriRekeningModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
     style="z-index: 99999 !important; background-color: rgba(15, 23, 42, 0.7) !important; backdrop-filter: blur(4px);">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden my-auto max-h-[92vh] flex flex-col animate-in fade-in">
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between text-white shrink-0" style="background-color: #0d9488 !important;">
            <div>
                <h3 class="text-sm font-bold flex items-center gap-1.5">
                    <i class="ti ti-credit-card"></i> Rekening, Tarif SPP & Add-on Santri
                </h3>
                <div class="text-xs text-teal-100 font-semibold mt-0.5" id="modalSantriRekeningNama">-</div>
            </div>
            <button type="button" onclick="closeSantriRekeningModal()" class="text-white/80 hover:text-white cursor-pointer">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>

        <form id="formSantriRekening" method="POST" class="overflow-y-auto p-5 space-y-3.5 text-xs flex-1">
            @csrf

            <!-- Pilihan Beasiswa Full -->
            <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl space-y-1.5">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_beasiswa" value="1" id="modalSantriRekeningBeasiswa" onchange="toggleBeasiswa(this.checked)"
                           class="w-4 h-4 text-purple-600 rounded border-gray-300 focus:ring-purple-500">
                    <span class="font-bold text-purple-900 text-xs">Penerima Beasiswa Penuh (SPP Rp 0 / Gratis)</span>
                </label>
                <p class="text-[11px] text-purple-700 leading-relaxed">
                    Jika dicentang, santri ini otomatis bertarif SPP <strong>Rp 0</strong> dan tagihannya otomatis langsung <strong>Lunas</strong> setiap bulan.
                </p>
            </div>

            <div id="modalSantriRekeningNominalWrap">
                <label class="block font-bold text-gray-700 uppercase mb-1">Nominal SPP Santri (Rp)</label>
                <input type="number" name="nominal_spp" id="modalSantriRekeningNominal" step="1000" min="0"
                       placeholder="Contoh: 1000000"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-mono font-bold focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <span class="text-[11px] text-gray-400 mt-1 block">Ketik nominal SPP bulanan untuk santri ini. Kosongkan jika mengikuti tarif kelas standar pondok.</span>
            </div>

            <div class="pt-2 border-t border-gray-100">
                <label class="block font-bold text-gray-700 uppercase mb-1">Nama Bank Pembayaran</label>
                <input type="text" name="nama_bank_spp" id="modalSantriRekeningBank"
                       placeholder="Contoh: BSI / Bank Muamalat / BCA / Mandiri"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Nomor Rekening / Pembayaran</label>
                <input type="text" name="nomor_rekening_spp" id="modalSantriRekeningNomor"
                       placeholder="Contoh: 7123456789"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-mono focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <span class="text-[11px] text-gray-400 mt-1 block">Kosongkan jika santri ini memakai rekening pondok.</span>
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Atas Nama Rekening</label>
                <input type="text" name="atas_nama_rekening_spp" id="modalSantriRekeningAtasNama"
                       placeholder="Contoh: a.n. Santri Ahmad"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
            </div>

            <!-- Bagian Tambah Biaya Add-on Rutin (Opsional) -->
            <div class="pt-3 border-t border-purple-200 bg-purple-50/60 p-3 rounded-xl space-y-2.5">
                <div class="font-bold text-purple-900 flex items-center gap-1.5 text-xs">
                    <i class="ti ti-plus text-purple-700"></i> Tambah Biaya Add-on Rutin Sekaligus (Opsional)
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-purple-800 uppercase mb-1">Nama Add-on Rutin</label>
                        <input type="text" name="addon_nama_biaya" placeholder="Misal: Laundry / Ekskul"
                               class="w-full px-2.5 py-1.5 border border-purple-200 rounded-lg text-xs bg-white">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-purple-800 uppercase mb-1">Nominal Add-on (Rp)</label>
                        <input type="number" name="addon_nominal" min="500" step="500" placeholder="Misal: 60000"
                               class="w-full px-2.5 py-1.5 border border-purple-200 rounded-lg text-xs font-mono font-bold bg-white">
                    </div>
                </div>
                <div>
                    <input type="text" name="addon_keterangan" placeholder="Keterangan add-on rutin (opsional)"
                           class="w-full px-2.5 py-1.5 border border-purple-200 rounded-lg text-xs bg-white">
                </div>
                <p class="text-[10px] text-purple-700 leading-relaxed">
                    <i class="ti ti-info-circle"></i> Jika diisi, add-on ini disimpan sebagai template bulanan santri dan otomatis masuk setiap tagihan SPP diterbitkan.
                </p>
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2 shrink-0">
                <button type="button" onclick="closeSantriRekeningModal()" class="px-3.5 py-1.5 border border-gray-300 text-gray-700 font-bold rounded-lg cursor-pointer text-xs hover:bg-gray-100 transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-1.5 font-bold rounded-lg text-xs transition shadow-sm cursor-pointer"
                        style="background-color: #0d9488 !important; color: #ffffff !important;">
                    <i class="ti ti-device-floppy"></i> Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Input Centang Multi-Bulan & Multi-Santri (Buka Tagihan 3 Bulan / Lebih untuk Wali Tertentu) -->
<div id="multiTagihanModal" class="fixed inset-0 hidden"
     style="z-index: 99999 !important; background-color: rgba(15, 23, 42, 0.7) !important; backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); overflow-y: auto !important; padding: 25px 12px !important; display: none;">
    <div style="min-height: 100%; display: flex; align-items: center; justify-content: center; width: 100%;">
        <div class="bg-white rounded-2xl w-full shadow-2xl overflow-hidden animate-in fade-in"
             style="max-width: 800px !important; max-height: 90vh !important; display: flex !important; flex-direction: column !important; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45) !important;">
            <div class="p-4 sm:p-5 flex items-center justify-between text-white"
                 style="background-color: #7c3aed !important; flex-shrink: 0 !important;">
                <div>
                    <h3 class="text-base font-bold flex items-center gap-2 text-white">
                        <i class="ti ti-checkbox text-lg"></i> Buka Tagihan SPP Multi-Bulan (Menu Centang Bulan & Santri)
                    </h3>
                    <p class="text-xs text-purple-100 mt-0.5">Centang bulan apa saja (misal 3 bulan atau lebih) dan nama santri siapa saja agar tagihan terbuka di akun wali santri tersebut.</p>
                </div>
                <button type="button" onclick="closeMultiTagihanModal()" class="text-white/80 hover:text-white cursor-pointer p-1">
                    <i class="ti ti-x text-2xl"></i>
                </button>
            </div>

            <form action="{{ route('admin.spp.store_multi') }}" method="POST"
                  class="p-5 space-y-4 text-xs"
                  style="overflow-y: auto !important; flex: 1 1 auto !important; max-height: calc(90vh - 140px) !important;">
                @csrf
                <input type="hidden" name="status_set" value="belum_lunas">

                <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl text-purple-900 leading-relaxed">
                    <strong>Fungsi Menu Ini:</strong> Membuka tagihan SPP lebih awal (misal 3 bulan atau lebih) khusus untuk santri yang dicentang agar muncul di aplikasi wali santri dan bisa langsung dibayar. Setelah dibayar/diverifikasi, statusnya otomatis <strong>Sudah Lunas</strong> dan saat tiba tanggal pembukaan tagihan bulanan nanti <strong>tidak akan ditagih notifikasi WA lagi</strong>.
                </div>

            <!-- Langkah 1: Pilih Tahun & Centang Bulan -->
            <div class="p-4 bg-purple-50/60 border border-purple-200 rounded-xl space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="font-bold text-purple-900 uppercase flex items-center gap-1.5">
                        <span>1. Pilih Tahun & Centang Bulan yang Dibuka</span>
                        <span id="countMultiBulanBadge" class="px-2 py-0.5 rounded-full bg-purple-700 text-white text-[10px]">1 Bulan dipilih</span>
                    </div>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-[11px] text-purple-700 font-semibold">Pilih Cepat:</span>
                        <button type="button" onclick="selectMultiMonthsPreset(3)" class="px-2.5 py-1 bg-white border border-purple-300 hover:bg-purple-100 text-purple-800 rounded-lg font-bold text-[11px] cursor-pointer">
                            3 Bulan
                        </button>
                        <button type="button" onclick="selectMultiMonthsPreset(6)" class="px-2.5 py-1 bg-white border border-purple-300 hover:bg-purple-100 text-purple-800 rounded-lg font-bold text-[11px] cursor-pointer">
                            6 Bulan (1 Semester)
                        </button>
                        <button type="button" onclick="selectMultiMonthsPreset(12)" class="px-2.5 py-1 bg-white border border-purple-300 hover:bg-purple-100 text-purple-800 rounded-lg font-bold text-[11px] cursor-pointer">
                            12 Bulan (1 Tahun)
                        </button>
                    </div>
                </div>

                <div class="w-40">
                    <label class="block font-bold text-gray-700 uppercase mb-1">Tahun Tagihan</label>
                    <input type="number" name="tahun" value="{{ $tahun }}" min="2020" max="2099" required
                           class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-bold bg-white">
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2">
                    @for($m = 1; $m <= 12; $m++)
                        <label class="flex items-center gap-2 p-2 bg-white border border-gray-200 rounded-lg cursor-pointer hover:border-purple-400 transition">
                            <input type="checkbox" name="bulans[]" value="{{ $m }}"
                                   {{ (int)$bulan === $m ? 'checked' : '' }}
                                   onchange="updateMultiTagihanCounter()"
                                   class="chk-multi-bulan-admin w-4 h-4 text-purple-600 rounded border-gray-300">
                            <span class="font-semibold text-gray-800 text-xs">{{ \App\Models\SppTagihan::getNamaBulan($m) }}</span>
                        </label>
                    @endfor
                </div>
            </div>

            <!-- Langkah 2: Centang Nama Santri -->
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="font-bold text-gray-900 uppercase flex items-center gap-1.5">
                        <span>2. Centang Nama Santri</span>
                        <span id="countMultiSantriBadge" class="px-2 py-0.5 rounded-full bg-indigo-600 text-white text-[10px]">0 Santri dipilih</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="toggleAllMultiSantri(true)" class="px-2.5 py-1 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded-lg font-bold text-[11px] cursor-pointer hover:bg-indigo-100">
                            Centang Semua yg Tampil
                        </button>
                        <button type="button" onclick="toggleAllMultiSantri(false)" class="px-2.5 py-1 bg-white border border-gray-300 text-gray-600 rounded-lg font-bold text-[11px] cursor-pointer hover:bg-gray-100">
                            Reset Centang
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <div class="sm:col-span-2 relative">
                        <i class="ti ti-search absolute left-3 top-2.5 text-gray-400"></i>
                        <input type="text" id="searchMultiSantriInput" oninput="filterMultiSantriList()"
                               placeholder="Cari nama santri..."
                               class="w-full pl-8 pr-3 py-1.5 border border-gray-300 rounded-lg text-xs bg-white">
                    </div>
                    <div>
                        <select id="filterMultiSantriKelas" onchange="filterMultiSantriList()" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs bg-white font-semibold">
                            <option value="">Semua Kelas ({{ $allSantriAktif->count() }})</option>
                            <option value="1">Kelas 1</option>
                            <option value="2">Kelas 2</option>
                            <option value="3">Kelas 3</option>
                        </select>
                    </div>
                </div>
                <div id="multiSantriVisibleInfo" class="text-[11px] text-gray-500">Menampilkan {{ $allSantriAktif->count() }} santri</div>

                <div class="max-h-52 overflow-y-auto border border-gray-200 rounded-xl bg-white divide-y divide-gray-100">
                    @foreach($allSantriAktif as $s)
                        <label class="multi-santri-item flex items-center justify-between px-3 py-2 hover:bg-purple-50/40 cursor-pointer transition"
                               data-nama="{{ strtolower($s->nama_lengkap) }}"
                               data-kelas="{{ $s->kelas }}">
                            <div class="flex items-center gap-2.5">
                                <input type="checkbox" name="santri_ids[]" value="{{ $s->id }}"
                                       onchange="updateMultiTagihanCounter()"
                                       class="chk-multi-santri-admin w-4 h-4 text-purple-600 rounded border-gray-300">
                                <div>
                                    <span class="font-bold text-gray-900">{{ $s->nama_lengkap }}</span>
                                    <span class="ml-1 px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 text-[10px] font-bold">{{ $s->kelas_label }}</span>
                                </div>
                            </div>
                            <span class="font-mono font-bold text-gray-700 text-xs">
                                {{ $s->is_beasiswa ? 'Beasiswa (Rp 0)' : 'Rp ' . number_format($s->tarif_spp, 0, ',', '.') . '/bln' }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="p-4 bg-white border border-gray-200 rounded-xl space-y-3">
                <div class="p-2.5 bg-blue-50 border border-blue-200 rounded-lg">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="kirim_wa" value="1" class="w-4 h-4 text-blue-600 rounded border-gray-300">
                        <span class="font-bold text-blue-900 text-xs">Sekaligus Kirim Notifikasi WA Rincian Tagihan ke Wali Santri yang Dicentang</span>
                    </label>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Keterangan / Catatan (Opsional)</label>
                    <input type="text" name="keterangan" placeholder="Contoh: Pembukaan tagihan SPP 3 bulan"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs">
                </div>
            </div>

            <div class="pt-3 border-t border-gray-200 flex items-center justify-end gap-2 shrink-0">
                <button type="button" onclick="closeMultiTagihanModal()" class="px-4 py-2 border border-gray-300 rounded-xl text-gray-700 font-bold cursor-pointer hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 font-bold rounded-xl text-xs text-white shadow-sm cursor-pointer hover:opacity-95"
                        style="background-color: #7c3aed !important; color: #ffffff !important;">
                    <i class="ti ti-check"></i> Buka Tagihan untuk Bulan & Santri yang Dicentang
                </button>
            </div>
        </form>
    </div>
    </div>
</div>

@endsection
