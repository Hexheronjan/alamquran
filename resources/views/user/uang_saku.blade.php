<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Transaksi Uang Saku') }}
        </h2>
    </x-slot>

    <div class="py-6 bg-gradient-to-br from-gray-50 to-teal-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- JIKA TIDAK ADA SANTRI DITERIMA --}}
            @if($santris->count() === 0)
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden p-6 sm:p-8 text-center border border-yellow-100">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl sm:text-3xl">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-2">Akses Uang Saku Belum Aktif</h2>
                    <p class="text-gray-600 text-sm sm:text-base max-w-md mx-auto mb-6">
                        Menu Riwayat & Perincian Belanja Uang Saku hanya aktif untuk santri yang status pendaftarannya sudah <strong>Diterima</strong>. Santri dengan status Menunggu Verifikasi atau Ditolak tidak memiliki akses fitur uang saku.
                    </p>
                    <a href="{{ route('user.form.history') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-teal-600 text-white font-semibold text-sm rounded-lg hover:bg-teal-700 transition">
                        <i class="fas fa-list-alt"></i> Cek Status Riwayat Pendaftaran
                    </a>
                </div>

            {{-- JIKA ADA SANTRI DITERIMA --}}
            @else
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-2 h-8 bg-teal-600 rounded-full"></div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-gray-800">Uang Saku & Belanja Santri</h1>
                </div>
                <div class="mb-5">
                    <a href="{{ route('user.deposit_transfer.create') }}" class="inline-flex items-center justify-center w-full sm:w-auto gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-700 transition shadow-sm"><i class="fas fa-university"></i> Titip Uang Saku melalui Transfer Bank</a>
                </div>

                @foreach($santris as $santri)
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-teal-100 mb-8">
                        {{-- Card Body --}}
                        <div class="p-4 sm:p-6">
                            <!-- Balance Summary Box -->
                            <div class="bg-gradient-to-br {{ $santri->saldo_uang_saku < 0 ? 'from-red-50 to-rose-50 border-red-300' : 'from-teal-50 to-emerald-50 border-teal-200' }} border rounded-2xl p-5 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <div>
                                    <span class="text-xs font-bold {{ $santri->saldo_uang_saku < 0 ? 'text-red-700' : 'text-teal-700' }} uppercase tracking-wider">
                                        {{ $santri->saldo_uang_saku < 0 ? 'Defisit / Talangan Uang Saku Saat Ini' : 'Sisa Saldo Uang Saku ' . $santri->nama_lengkap . ' Saat Ini' }}
                                    </span>
                                    <h3 class="text-3xl font-extrabold {{ $santri->saldo_uang_saku < 0 ? 'text-red-700 font-mono' : 'text-teal-800' }} mt-1">
                                        {{ $santri->saldo_uang_saku < 0 ? '-Rp ' . number_format(abs($santri->saldo_uang_saku), 0, ',', '.') : 'Rp ' . number_format($santri->saldo_uang_saku, 0, ',', '.') }}
                                    </h3>
                                    @if($santri->saldo_uang_saku < 0)
                                        <p class="text-xs text-red-600 mt-1 font-semibold flex items-center gap-1">
                                            <i class="fas fa-exclamation-circle text-red-500"></i> Pengeluaran ditalangi pondok sementara. Mohon segera lakukan titipan uang saku untuk pelunasan.
                                        </p>
                                    @endif
                                </div>
                                <div class="text-xs {{ $santri->saldo_uang_saku < 0 ? 'text-red-700 border-red-200 bg-white/90' : 'text-teal-700 border-teal-200 bg-white/80' }} px-4 py-2 rounded-xl border shadow-sm text-center sm:text-right">
                                    <i class="fas fa-info-circle {{ $santri->saldo_uang_saku < 0 ? 'text-red-500' : 'text-teal-500' }} mr-1"></i> Penambahan uang saku dapat melalui transfer bank di tombol atas.
                                </div>
                            </div>

                            @php
                                $totalTitip = $santri->uangSaku->where('jenis', 'masuk')->sum('jumlah');
                                $totalPengeluaran = $santri->uangSaku->where('jenis', 'keluar')->sum('jumlah');
                            @endphp
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                                <div class="rounded-2xl border border-green-200 bg-green-50 p-5">
                                    <p class="text-sm font-bold text-green-800">Total Uang Titip</p>
                                    <p class="mt-1 text-2xl font-extrabold text-green-700">Rp {{ number_format($totalTitip, 0, ',', '.') }}</p>
                                </div>
                                <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
                                    <p class="text-sm font-bold text-red-800">Total Pengeluaran</p>
                                    <p class="mt-1 text-2xl font-extrabold text-red-700">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
                                </div>
                            </div>

                            <!-- Transaction History & Filter -->
                            <form method="GET" action="{{ route('user.uang_saku') }}" class="mb-4 border-b pb-4">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                    <h3 class="flex items-center gap-2 text-xl font-bold text-gray-800 whitespace-nowrap">
                                        <i class="fas fa-history text-teal-600"></i> Riwayat Transaksi Uang Saku
                                    </h3>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <select name="bulan" aria-label="Filter bulan" onchange="this.form.submit()"
                                            class="rounded-xl border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 focus:border-teal-500 focus:ring-teal-500">
                                            <option value="">Semua Bulan</option>
                                            @foreach($bulanList as $nomorBulan => $namaBulan)
                                                <option value="{{ $nomorBulan }}" @selected($bulanTerpilih === $nomorBulan)>{{ $namaBulan }}</option>
                                            @endforeach
                                        </select>
                                        <select name="tahun" aria-label="Filter tahun" onchange="this.form.submit()"
                                            class="rounded-xl border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 focus:border-teal-500 focus:ring-teal-500">
                                            <option value="">Semua Tahun</option>
                                            @foreach($tahunList as $tahun)
                                                <option value="{{ $tahun }}" @selected($tahunTerpilih === (int) $tahun)>{{ $tahun }}</option>
                                            @endforeach
                                        </select>
                                        <span class="ml-1 text-sm text-gray-500">{{ $santri->uangSaku->count() }} transaksi</span>
                                    </div>
                                </div>
                                <noscript>
                                    <button type="submit" class="mt-2 rounded-lg bg-teal-600 px-3 py-2 text-sm font-bold text-white">Terapkan Filter</button>
                                </noscript>
                            </form>

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
                                                <th class="py-3 px-4 text-center">Bukti / Kuitansi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach($santri->uangSaku as $trans)
                                            <tr class="hover:bg-gray-50 transition">
                                                <td class="py-3.5 px-4 text-gray-600 text-xs font-medium whitespace-nowrap">
                                                    <div>{{ $trans->created_at->translatedFormat('d M Y, H:i') }}</div>
                                                    @if($trans->jenis === 'masuk')
                                                        <div class="mt-0.5">
                                                            <span class="inline-flex items-center gap-1 text-[10px] font-mono font-semibold bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded border border-gray-200" title="Nomor Kuitansi">
                                                                <i class="fas fa-receipt text-blue-600"></i> {{ $trans->nomor_invoice }}
                                                            </span>
                                                        </div>
                                                    @endif
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
                                                <td class="py-3.5 px-4 text-right font-extrabold {{ $trans->saldo_akhir < 0 ? 'text-red-600 font-mono' : 'text-teal-700' }} whitespace-nowrap">
                                                    {{ $trans->saldo_akhir < 0 ? '-Rp ' . number_format(abs($trans->saldo_akhir), 0, ',', '.') : 'Rp ' . number_format($trans->saldo_akhir, 0, ',', '.') }}
                                                </td>
                                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                                    @if($trans->jenis === 'masuk')
                                                        <a href="{{ route('user.uang_saku.kuitansi', $trans->id) }}" target="_blank"
                                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold text-teal-700 bg-teal-50 hover:bg-teal-100 border border-teal-200 transition shadow-2xs"
                                                           title="Lihat / Cetak / Download Kuitansi Titipan Resmi">
                                                            <i class="fas fa-file-invoice text-teal-600"></i> Kuitansi
                                                        </a>
                                                    @else
                                                        <span class="text-gray-300 text-xs">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-10 text-gray-400 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                                    <i class="fas fa-receipt text-4xl mb-3 block text-gray-300"></i>
                                    <p class="text-sm font-semibold text-gray-600">Belum ada catatan transaksi uang saku.</p>
                                    <p class="text-xs text-gray-400 mt-1">Saldo ananda saat ini masih Rp 0.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif

        </div>
    </div>
</x-app-layout>
