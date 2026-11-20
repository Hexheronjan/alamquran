@extends('layouts.admin.app')

@section('title', 'Biaya Masuk & Daftar Ulang Santri Baru')

@section('content')
<div class="bg-gray-100 min-h-screen py-6 px-4 lg:px-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2">
                <i class="ti ti-cash-banknote text-teal-600 text-3xl"></i>
                Biaya Masuk & Daftar Ulang Santri
            </h1>
            <p class="text-xs text-gray-500 mt-1">
                Kelola tagihan pendaftaran santri baru, daftar ulang kenaikan kelas, rekening/VA per santri, dan jadwal buka/tutup pembayaran.
            </p>
        </div>

        <!-- Status Pembayaran & Tombol Pengaturan Jadwal -->
        <div class="flex items-center gap-2.5 flex-wrap">
            <div class="flex items-center gap-2 bg-white px-3.5 py-2 rounded-xl shadow-xs border border-gray-200">
                <span class="text-xs font-semibold text-gray-500">Portal Wali:</span>
                @if($daftarUlangSetting->isOpen())
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Dibuka
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> Ditutup
                    </span>
                @endif
            </div>

            @if($daftarUlangSetting->isOpen())
                <form action="{{ route('admin.daftar_ulang.broadcast_wa_dibuka') }}" method="POST" class="inline"
                      onsubmit="return confirm('Kirim notifikasi WA resmi pembukaan sistem pembayaran ke SELURUH wali santri Angkatan {{ $selectedYear }} yang biaya & rekeningnya sudah siap diatur?')">
                    @csrf
                    <input type="hidden" name="tahun_angkatan" value="{{ $selectedYear }}">
                    <button type="submit"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold text-white shadow-xs cursor-pointer flex items-center gap-1.5 transition hover:opacity-90"
                            style="background-color: #059669 !important;"
                            title="Kirim pengumuman resmi pembukaan via WA ke wali santri yang sudah siap di angkatan ini">
                        <i class="ti ti-brand-whatsapp text-base"></i> Broadcast WA Buka ({{ $selectedYear }})
                    </button>
                </form>
            @endif

            <button type="button" onclick="openJadwalModal()"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold text-white shadow-xs cursor-pointer flex items-center gap-1.5"
                    style="background-color: #0d9488 !important;">
                <i class="ti ti-calendar-time text-base"></i> Atur Jadwal Buka / Tutup
            </button>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <i class="ti ti-circle-check text-lg text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="ti ti-x"></i></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <i class="ti ti-alert-triangle text-lg text-amber-600"></i>
                <span>{{ session('warning') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-amber-500 hover:text-amber-700"><i class="ti ti-x"></i></button>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <i class="ti ti-alert-circle text-lg text-red-600"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700"><i class="ti ti-x"></i></button>
        </div>
    @endif

    <!-- Kotak Notifikasi: Bukti Transfer Menunggu Verifikasi -->
    @if($pendingPembayarans->count() > 0)
        <div class="mb-6 rounded-2xl shadow-xs border border-amber-300 overflow-hidden" style="background-color: #fffbeb !important;">
            <div class="flex items-center justify-between px-5 py-3.5" style="background-color: #fef3c7 !important; border-bottom: 1px solid #fde68a !important;">
                <h3 class="font-bold text-sm uppercase tracking-wider flex items-center gap-2" style="color: #92400e !important;">
                    <i class="ti ti-clock-pause text-xl" style="color: #d97706 !important;"></i>
                    Bukti Pembayaran Daftar Ulang Menunggu Verifikasi ({{ $pendingPembayarans->count() }})
                </h3>
                <span class="text-xs px-3 py-1 rounded-full font-bold" style="background-color: #fde68a !important; color: #92400e !important;">
                    Perlu Tindakan Bendahara
                </span>
            </div>

            <div class="p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($pendingPembayarans as $pemb)
                        <div class="bg-white rounded-xl p-3.5 text-gray-800 shadow-xs border border-amber-200 text-xs flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between font-bold text-gray-900 mb-1">
                                    <span class="truncate">{{ $pemb->santri->nama_lengkap }}</span>
                                    <span class="text-emerald-700 font-mono font-black text-sm">Rp {{ number_format($pemb->jumlah_bayar, 0, ',', '.') }}</span>
                                </div>
                                <div class="text-[11px] text-gray-500 mb-2">
                                    <div>Angkatan: <strong class="text-gray-700">{{ $pemb->tagihan?->tahun_angkatan }}</strong> | Wali: {{ $pemb->santri->orangTua?->nama_ayah ?? '-' }}</div>
                                    @if($pemb->catatan)
                                        <div class="italic text-gray-600 mt-0.5">"{{ $pemb->catatan }}"</div>
                                    @endif
                                    <div class="text-[10px] text-gray-400 mt-1">{{ $pemb->created_at->translatedFormat('d M Y H:i') }}</div>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-gray-100 flex items-center justify-between gap-1.5 flex-wrap">
                                @if($pemb->bukti_bayar)
                                    <a href="{{ Storage::url($pemb->bukti_bayar) }}" target="_blank"
                                       class="px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700 font-bold text-[11px] hover:bg-blue-100 flex items-center gap-1">
                                        <i class="ti ti-photo text-sm"></i> Bukti Transfer
                                    </a>
                                @endif

                                <div class="flex items-center gap-1.5 ml-auto">
                                    <form action="{{ route('admin.daftar_ulang.verifikasi', $pemb->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="terima">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-xs cursor-pointer flex items-center gap-1"
                                                onclick="return confirm('Setujui pembayaran daftar ulang ananda {{ addslashes($pemb->santri->nama_lengkap) }} sebesar Rp {{ number_format($pemb->jumlah_bayar, 0, ',', '.') }}?')">
                                            <i class="ti ti-check"></i> Terima
                                        </button>
                                    </form>

                                    <button type="button" onclick="openRejectModal({{ $pemb->id }}, '{{ addslashes($pemb->santri->nama_lengkap) }}')"
                                            class="px-2.5 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 font-bold text-[11px] cursor-pointer">
                                        Tolak
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Kartu Statistik Keuangan Angkatan -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-gray-200">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                <i class="ti ti-users text-teal-600 text-base"></i> Total Santri ({{ $selectedYear === 'all' ? 'Semua Angkatan' : $selectedYear }})
            </div>
            <div class="text-2xl font-black text-gray-900 font-mono">{{ $totalSantriAngkatan }}</div>
            <div class="text-[11px] text-gray-500 mt-1">
                {{ $totalSantriAktif }} Santri Aktif
                @if($totalAlumniBerhutang > 0)
                    <span class="text-amber-600 font-bold">• {{ $totalAlumniBerhutang }} Alumni Berhutang</span>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-xs border border-gray-200">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                <i class="ti ti-wallet text-blue-600 text-base"></i> Total Kewajiban Biaya
            </div>
            <div class="text-2xl font-black text-blue-700 font-mono">Rp {{ number_format($totalKewajiban, 0, ',', '.') }}</div>
            <div class="text-[11px] text-gray-500 mt-1">{{ $countBelumDiatur }} santri belum diatur nominalnya</div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-xs border border-gray-200">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                <i class="ti ti-circle-check text-emerald-600 text-base"></i> Total Uang Masuk
            </div>
            <div class="text-2xl font-black text-emerald-700 font-mono">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</div>
            <div class="text-[11px] text-emerald-600 font-bold mt-1">{{ $countLunas }} Lunas | {{ $countCicilan }} Mengangsur</div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-xs border border-gray-200">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                <i class="ti ti-clock-exclamation text-amber-600 text-base"></i> Sisa Belum Terbayar
            </div>
            <div class="text-2xl font-black text-amber-700 font-mono">Rp {{ number_format($totalSisa, 0, ',', '.') }}</div>
            <div class="text-[11px] text-amber-600 font-bold mt-1">{{ $countBelumLunas }} Santri belum lunas</div>
        </div>
    </div>

    <!-- Filter Bar: Pilihan Tahun Angkatan PPDB, Status, dan Pencarian -->
    <div class="bg-white rounded-2xl shadow-xs border border-gray-200 p-4 mb-6 text-xs">
        <form method="GET" action="{{ route('admin.daftar_ulang.index') }}" class="flex flex-wrap items-center gap-3">
            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Tahun Angkatan / PPDB</label>
                <select name="tahun_angkatan" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-bold text-teal-800 bg-teal-50 focus:ring-2 focus:ring-teal-500">
                    <option value="all" {{ $selectedYear === 'all' ? 'selected' : '' }}>
                        🌟 Semua Santri (Semua Angkatan)
                    </option>
                    @foreach($angkatanList as $th)
                        <option value="{{ $th }}" {{ $selectedYear == $th ? 'selected' : '' }}>
                            Angkatan {{ $th }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Status Pembayaran</label>
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-teal-500">
                    <option value="all" {{ $selectedStatus === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="belum_diatur" {{ $selectedStatus === 'belum_diatur' ? 'selected' : '' }}>Belum Diatur</option>
                    <option value="belum_lunas" {{ $selectedStatus === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                    <option value="cicilan" {{ $selectedStatus === 'cicilan' ? 'selected' : '' }}>Mengangsur</option>
                    <option value="lunas" {{ $selectedStatus === 'lunas' ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>

            <div class="flex-1 min-w-[220px]">
                <label class="block font-bold text-gray-700 uppercase mb-1">Cari Santri / Wali / Rekening</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama santri, NIS, rekening, atau nama ayah..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500">
            </div>

            <div class="pt-4 flex gap-1.5">
                <button type="submit" class="px-4 py-2 rounded-lg font-bold text-xs cursor-pointer text-white shadow-xs"
                        style="background-color: #0d9488 !important;">
                    <i class="ti ti-filter"></i> Filter
                </button>
                <a href="{{ route('admin.daftar_ulang.index', ['tahun_angkatan' => $selectedYear]) }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Santri & Tagihan Daftar Ulang -->
    <div class="bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-list-details text-teal-600"></i> Daftar Santri ({{ $selectedYear === 'all' ? 'Semua Santri' : 'Angkatan ' . $selectedYear }})
            </h2>
            <span class="text-xs bg-gray-100 text-gray-700 px-3 py-1 rounded-full font-bold">
                {{ $santris->total() }} Santri
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Santri & Wali</th>
                        <th class="px-6 py-3.5">Bank & Rekening / VA</th>
                        <th class="px-6 py-3.5">Total Biaya</th>
                        <th class="px-6 py-3.5">Terbayar</th>
                        <th class="px-6 py-3.5">Sisa Tagihan</th>
                        <th class="px-5 py-3.5 text-center">Status Tagihan</th>
                        <th class="px-6 py-3.5 text-left">Petugas</th>
                        <th class="px-5 py-3.5 text-center">Status WA</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($santris as $s)
                        @php
                            $tagihan = $s->daftarUlangTagihan;
                            $isReady = $tagihan?->is_ready ?? false;
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-gray-900 text-sm">{{ $s->nama_lengkap }}</span>
                                    @if($s->isAlumni())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                            <i class="ti ti-school mr-1"></i> Alumni
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600">
                                            {{ $s->kelas_label }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-gray-500 mt-0.5">
                                    NIS: <span class="font-mono">{{ $s->nis ?: '-' }}</span> | Wali: {{ $s->orangTua?->nama_ayah ?: '-' }}
                                    @if($s->orangTua?->no_wa_ayah)
                                        <span class="text-emerald-700 font-mono">({{ $s->orangTua->no_wa_ayah }})</span>
                                    @endif
                                </div>
                                @if($isReady)
                                    <div class="mt-1">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-mono font-semibold bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded border border-gray-200" title="Nomor Invoice">
                                            <i class="ti ti-receipt text-xs text-blue-600"></i> {{ $tagihan->nomor_invoice }}
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                @if($s->nomor_rekening_spp)
                                    <div class="font-bold text-teal-800">{{ $s->nama_bank_spp ?: 'Bank' }}</div>
                                    <div class="font-mono font-bold text-teal-600">{{ $s->nomor_rekening_spp }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $s->atas_nama_rekening_spp ?: "a.n. {$s->nama_lengkap}" }}</div>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] text-amber-700 bg-amber-50 px-2 py-0.5 rounded font-semibold border border-amber-200">
                                        <i class="ti ti-alert-triangle"></i> Belum Diatur
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                @if($isReady)
                                    @if($tagihan->is_beasiswa)
                                        <div class="font-bold font-mono text-purple-700 text-sm">
                                            Rp 0
                                        </div>
                                        <span class="text-[10px] text-purple-600 font-semibold block">Beasiswa Penuh</span>
                                    @else
                                        <div class="font-bold font-mono text-gray-900 text-sm">
                                            Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}
                                        </div>
                                        @if(($tagihan->nominal_addon ?? 0) > 0)
                                            <div class="text-[10px] text-purple-700 font-semibold leading-tight">
                                                (Pokok: {{ number_format($tagihan->nominal_biaya, 0, ',', '.') }} + Add: {{ number_format($tagihan->nominal_addon, 0, ',', '.') }})
                                            </div>
                                        @endif
                                    @endif
                                    @if($tagihan->gelombang)
                                        <span class="text-[10px] text-gray-500 block">{{ $tagihan->gelombang }}</span>
                                    @endif
                                @else
                                    <span class="text-gray-400 italic">Belum diatur</span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                @if($isReady)
                                    <div class="font-bold font-mono text-emerald-700">
                                        Rp {{ number_format($tagihan->nominal_terbayar, 0, ',', '.') }}
                                    </div>
                                    @if($tagihan->pembayarans->count() > 0)
                                        <span class="text-[10px] text-gray-500">{{ $tagihan->pembayarans->where('status', 'diterima')->count() }}x Angsuran</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                @if($isReady)
                                    <div class="font-bold font-mono {{ $tagihan->sisa_tagihan > 0 ? 'text-amber-700' : 'text-emerald-700' }} text-sm">
                                        Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if(!$isReady)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        Belum Diatur
                                    </span>
                                @elseif($tagihan->is_beasiswa)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                        <i class="ti ti-award"></i> Beasiswa Full
                                    </span>
                                @elseif($tagihan->status === 'lunas')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Lunas
                                    </span>
                                @elseif($tagihan->status === 'cicilan')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                        Mengangsur
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-800">
                                        Belum Bayar
                                    </span>
                                @endif
                            </td>

                            <!-- Kolom Petugas Input / Verifikasi -->
                            <td class="px-6 py-4 text-left whitespace-nowrap">
                                @php
                                    $approvedPayments = $tagihan?->pembayarans->where('status', 'diterima')->sortByDesc('id');
                                    $lastDU = $approvedPayments?->first();
                                @endphp
                                @if($lastDU && $lastDU->verifiedBy)
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-50 border border-gray-200 text-gray-700 font-medium text-xs">
                                        <i class="ti ti-user-check text-emerald-600 text-sm"></i>
                                        <span>{{ $lastDU->verifiedBy->name }}</span>
                                    </div>
                                    @if($approvedPayments->count() > 1)
                                        <div class="text-[10px] text-gray-400 mt-0.5" title="{{ $approvedPayments->pluck('verifiedBy.name')->filter()->unique()->implode(', ') }}">
                                            {{ $approvedPayments->count() }}x angsuran
                                        </div>
                                    @endif
                                @elseif($isReady && $tagihan->is_beasiswa)
                                    <span class="text-xs text-purple-600 font-medium">Sistem (Beasiswa)</span>
                                @else
                                    <span class="text-xs text-gray-400 italic">-</span>
                                @endif
                            </td>

                            <!-- Kolom Status Notifikasi WA -->
                            @php
                                $latestWa = $waLogsBySantri->get($s->id)?->first();
                            @endphp
                            <td class="px-5 py-4 text-center">
                                @if($latestWa)
                                    @if($latestWa->status === 'sent')
                                        <button type="button" onclick="showWaDetail({{ json_encode($latestWa) }}, '{{ addslashes($s->nama_lengkap) }}')"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 cursor-pointer shadow-2xs transition"
                                                title="Terkirim: {{ $latestWa->sent_at?->format('d/m/Y H:i') }}. Klik untuk melihat isi chat.">
                                            <i class="ti ti-check-double text-emerald-600"></i> Terkirim
                                        </button>
                                        <div class="text-[10px] text-gray-400 mt-0.5 font-mono">
                                            {{ $latestWa->sent_at?->format('d/m H:i') }}
                                        </div>
                                    @elseif($latestWa->status === 'failed')
                                        <button type="button" onclick="showWaDetail({{ json_encode($latestWa) }}, '{{ addslashes($s->nama_lengkap) }}')"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-red-50 text-red-800 border border-red-200 hover:bg-red-100 cursor-pointer shadow-2xs transition"
                                                title="Gagal: {{ $latestWa->error_message ?: 'Gagal kirim' }}. Klik untuk melihat alasan & kirim ulang.">
                                            <i class="ti ti-alert-triangle text-red-600"></i> Gagal Kirim
                                        </button>
                                        <form action="{{ route('admin.daftar_ulang.retry_wa', $latestWa->id) }}" method="POST" class="inline block mt-1">
                                            @csrf
                                            <button type="submit" class="text-[10px] font-bold text-red-600 hover:underline cursor-pointer inline-flex items-center gap-0.5"
                                                    title="Kirim ulang pesan WA sekarang">
                                                <i class="ti ti-rotate-clockwise"></i> Retry
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" onclick="showWaDetail({{ json_encode($latestWa) }}, '{{ addslashes($s->nama_lengkap) }}')"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 cursor-pointer shadow-2xs transition"
                                                title="Sedang dalam antrean pengiriman pesan">
                                            <i class="ti ti-clock text-amber-600 animate-spin"></i> Antrean
                                        </button>
                                    @endif
                                @else
                                    @if($isReady && !$tagihan->is_beasiswa && !empty($s->nomor_rekening_spp) && $s->nomor_rekening_spp !== 'Belum Diatur')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-500">
                                            <i class="ti ti-minus"></i> Belum Dikirim
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-xs">-</span>
                                    @endif
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    @if($isReady)
                                        <a href="{{ route('admin.daftar_ulang.tagihan.invoice', $tagihan->id) }}" target="_blank"
                                           class="px-2.5 py-1.5 font-bold rounded-lg transition inline-flex items-center gap-1 cursor-pointer text-xs shadow-xs"
                                           style="background-color: #f0fdf4 !important; color: #166534 !important; border: 1px solid #bbf7d0 !important;"
                                           title="Cetak Invoice / Bukti Pembayaran Resmi (PDF)">
                                            <i class="ti ti-printer text-sm"></i> Invoice
                                        </a>
                                    @endif
                                    @if($isReady && $tagihan->status === 'lunas')
                                        <!-- Tombol Add-on (Bisa tambah add-on meski biaya pokok lunas) -->
                                        <button type="button"
                                                onclick='openDaftarUlangAddonModal({{ $tagihan->id }}, "{{ addslashes($s->nama_lengkap) }}", {{ json_encode($tagihan->addons) }}, {{ (float)$tagihan->nominal_biaya }})'
                                                class="px-2.5 py-1.5 font-bold rounded-lg transition inline-flex items-center gap-1 cursor-pointer text-xs shadow-xs"
                                                style="background-color: #f5f3ff !important; color: #6d28d9 !important; border: 1px solid #ddd6fe !important;"
                                                title="Kelola Biaya Tambahan (Add-on)">
                                            <i class="ti ti-plus text-xs"></i> Add-on
                                            @if($tagihan->addons && $tagihan->addons->count() > 0)
                                                <span class="ml-0.5 px-1.5 py-0.2 rounded-full text-[10px] bg-purple-600 text-white font-mono">{{ $tagihan->addons->count() }}</span>
                                            @endif
                                        </button>

                                        <!-- Jika sudah Lunas atau Beasiswa Full: Cuma tombol Reset yang muncul -->
                                        <form action="{{ route('admin.daftar_ulang.batalkan_lunas', $tagihan->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin membatalkan status lunas / mereset status santri ananda {{ addslashes($s->nama_lengkap) }}?')">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1.5 font-bold rounded-lg transition inline-flex items-center gap-1 cursor-pointer text-xs shadow-xs"
                                                    style="background-color: #fff1f2 !important; color: #e11d48 !important; border: 1px solid #fecdd3 !important;"
                                                    title="Batalkan Lunas / Reset Status Santri Ini">
                                                <i class="ti ti-rotate-clockwise-2 text-sm"></i> Reset
                                            </button>
                                        </form>
                                    @else
                                        <!-- Tombol Atur Rekening & Biaya -->
                                        <button type="button"
                                                onclick="openAturBiayaModal({{ $s->id }}, '{{ addslashes($s->nama_lengkap) }}', '{{ addslashes($s->nama_bank_spp ?? 'BNI') }}', '{{ addslashes($s->nomor_rekening_spp ?? '') }}', '{{ addslashes($s->atas_nama_rekening_spp ?? '') }}', {{ $isReady ? (float)$tagihan->nominal_biaya : 6000000 }}, '{{ addslashes($tagihan->gelombang ?? 'Gelombang 1') }}', '{{ addslashes($tagihan->keterangan ?? '') }}', {{ $tagihan?->is_beasiswa ? 1 : 0 }})"
                                                class="px-2.5 py-1.5 font-bold rounded-lg transition inline-flex items-center gap-1 cursor-pointer text-xs"
                                                style="background-color: #f0fdf4 !important; color: #15803d !important; border: 1px solid #bbf7d0 !important;"
                                                title="Atur Rekening/VA dan Nominal Biaya Santri Ini">
                                            <i class="ti ti-settings text-sm"></i> Atur Biaya & Rekening
                                        </button>

                                        <!-- Tombol Add-on -->
                                        @if($isReady)
                                            <button type="button"
                                                    onclick='openDaftarUlangAddonModal({{ $tagihan->id }}, "{{ addslashes($s->nama_lengkap) }}", {{ json_encode($tagihan->addons) }}, {{ (float)$tagihan->nominal_biaya }})'
                                                    class="px-2.5 py-1.5 font-bold rounded-lg transition inline-flex items-center gap-1 cursor-pointer text-xs shadow-xs"
                                                    style="background-color: #f5f3ff !important; color: #6d28d9 !important; border: 1px solid #ddd6fe !important;"
                                                    title="Kelola Biaya Tambahan (Add-on)">
                                                <i class="ti ti-plus text-xs"></i> Add-on
                                                @if($tagihan->addons && $tagihan->addons->count() > 0)
                                                    <span class="ml-0.5 px-1.5 py-0.2 rounded-full text-[10px] bg-purple-600 text-white font-mono">{{ $tagihan->addons->count() }}</span>
                                                @endif
                                            </button>
                                        @endif

                                        <!-- Tombol Catat Bayar / Angsuran Manual -->
                                        @if($isReady && $tagihan->status !== 'lunas')
                                            <button type="button"
                                                    onclick="openBayarManualModal({{ $tagihan->id }}, '{{ addslashes($s->nama_lengkap) }}', {{ $tagihan->sisa_tagihan }})"
                                                    class="px-2.5 py-1.5 font-bold rounded-lg transition inline-flex items-center gap-1 cursor-pointer text-xs"
                                                    style="background-color: #eff6ff !important; color: #1d4ed8 !important; border: 1px solid #bfdbfe !important;"
                                                    title="Catat Pembayaran Tunai / Langsung di Kantor">
                                                <i class="ti ti-cash text-sm"></i> Catat Bayar
                                            </button>
                                        @endif

                                        <!-- Tombol Kirim WA Notifikasi Buka -->
                                        @if($isReady && $tagihan->status !== 'lunas' && !$tagihan->is_beasiswa && !empty($s->nomor_rekening_spp) && $s->nomor_rekening_spp !== 'Belum Diatur')
                                            <form action="{{ route('admin.daftar_ulang.kirim_wa_dibuka', $s->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Kirim notifikasi WA resmi pembukaan sistem pembayaran ke wali ananda {{ addslashes($s->nama_lengkap) }}?')">
                                                @csrf
                                                <button type="submit"
                                                        class="px-2.5 py-1.5 font-bold rounded-lg transition inline-flex items-center gap-1 cursor-pointer text-xs"
                                                        style="background-color: #ecfdf5 !important; color: #047857 !important; border: 1px solid #a7f3d0 !important;"
                                                        title="Kirim Notifikasi WA Resmi Pembukaan ke Wali Santri">
                                                    <i class="ti ti-brand-whatsapp text-sm"></i> Kirim WA Buka
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Tombol Batalkan Lunas / Reset untuk yang sedang mengangsur (cicilan) -->
                                        @if($isReady && $tagihan->nominal_terbayar > 0)
                                            <form action="{{ route('admin.daftar_ulang.batalkan_lunas', $tagihan->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin mereset angsuran ananda {{ addslashes($s->nama_lengkap) }}?')">
                                                @csrf
                                                <button type="submit"
                                                        class="px-2 py-1.5 font-bold rounded-lg transition inline-flex items-center gap-1 cursor-pointer text-xs"
                                                        style="background-color: #fff1f2 !important; color: #e11d48 !important; border: 1px solid #fecdd3 !important;"
                                                        title="Reset Riwayat Pembayaran Santri Ini">
                                                    <i class="ti ti-rotate-clockwise-2 text-sm"></i> Reset
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                                <i class="ti ti-folder-x text-3xl mb-2 block"></i>
                                Tidak ada data santri pada Angkatan {{ $selectedYear }} (atau santri alumni telah lunas seluruhnya).
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200">
            {{ $santris->links() }}
        </div>
    </div>

    <!-- Modal Atur Rekening & Biaya Daftar Ulang Santri -->
    <div id="modalAturBiaya" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden animate-in fade-in" style="max-height: 85vh; display: flex; flex-direction: column;">
            <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between" style="background-color: #0d9488 !important; color: #ffffff !important; flex-shrink: 0;">
                <h3 class="text-base font-bold flex items-center gap-2">
                    <i class="ti ti-credit-card text-lg"></i> Atur Rekening & Biaya Daftar Ulang
                </h3>
                <button type="button" onclick="closeAturBiayaModal()" class="text-white/80 hover:text-white cursor-pointer">
                    <i class="ti ti-x text-xl"></i>
                </button>
            </div>

            <form id="formAturBiaya" method="POST" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin-bottom: 0;">
                @csrf
                <div class="p-4 sm:p-5 space-y-3.5 text-xs" style="overflow-y: auto; flex: 1; min-height: 0;">
                    <div class="bg-teal-50 border border-teal-200 p-3 rounded-xl text-teal-900 leading-relaxed">
                        Santri: <strong id="aturSantriNama" class="text-teal-950 font-bold block text-sm"></strong>
                        <span class="text-[11px] text-teal-700 mt-1 block">Nomor rekening/VA yang diisi di sini otomatis langsung tersimpan ke profil santri dan terhubung ke menu SPP.</span>
                    </div>

                    <!-- Opsi Beasiswa Penuh -->
                    <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl space-y-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_beasiswa" id="aturIsBeasiswa" value="1"
                                   onchange="toggleBeasiswa(this.checked)"
                                   class="w-4 h-4 text-purple-600 rounded border-gray-300 focus:ring-purple-500">
                            <span class="font-bold text-purple-900 text-xs">
                                🏆 Fasilitas Beasiswa Penuh (Bebas Biaya Masuk Rp 0)
                            </span>
                        </label>
                        <p class="text-[11px] text-purple-700 pl-6 leading-tight">
                            Jika dicentang, santri dibebaskan dari biaya masuk (Rp 0), status otomatis <strong>Lunas</strong>, dan profil santri otomatis diset Beasiswa Penuh (termasuk SPP gratis).
                        </p>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Total Biaya Masuk / Daftar Ulang (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="nominal_biaya" id="aturNominalBiaya" min="0" step="10000" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono font-bold text-gray-900 focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-gray-700 uppercase mb-1">Nama Bank / VA</label>
                            <input type="text" name="nama_bank_spp" id="aturBank" placeholder="Contoh: BNI / BSI"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 uppercase mb-1">Gelombang / Jalur</label>
                            <input type="text" name="gelombang" id="aturGelombang" placeholder="Contoh: Gelombang 1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Nomor Rekening / Virtual Account (VA) <span class="text-red-500">*</span></label>
                        <input type="text" name="nomor_rekening_spp" id="aturNoRekening" placeholder="Contoh: 988701372335xxxx" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-mono font-bold text-teal-800 focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Atas Nama Rekening</label>
                        <input type="text" name="atas_nama_rekening_spp" id="aturAtasNama" placeholder="Contoh: an. Pesantren Alam Quran / Nama Santri"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Catatan Tambahan (Opsional)</label>
                        <input type="text" name="keterangan" id="aturKeterangan" placeholder="Contoh: Termasuk seragam & uang gedung"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <div class="p-4 border-t border-gray-100 flex items-center justify-end gap-2" style="flex-shrink: 0; background-color: #f9fafb !important;">
                    <button type="button" onclick="closeAturBiayaModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 cursor-pointer text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 font-bold rounded-lg text-xs transition shadow-sm cursor-pointer text-white"
                            style="background-color: #0d9488 !important;">
                        Simpan & Terbitkan Tagihan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Catat Bayar / Cicilan Manual -->
    <div id="modalBayarManual" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden animate-in fade-in" style="max-height: 85vh; display: flex; flex-direction: column;">
            <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between" style="background-color: #1d4ed8 !important; color: #ffffff !important; flex-shrink: 0;">
                <h3 class="text-base font-bold flex items-center gap-2">
                    <i class="ti ti-cash text-lg"></i> Catat Pembayaran / Angsuran Manual
                </h3>
                <button type="button" onclick="closeBayarManualModal()" class="text-white/80 hover:text-white cursor-pointer">
                    <i class="ti ti-x text-xl"></i>
                </button>
            </div>

            <form id="formBayarManual" method="POST" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin-bottom: 0;">
                @csrf
                <div class="p-4 sm:p-5 space-y-4 text-xs" style="overflow-y: auto; flex: 1; min-height: 0;">
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
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono font-bold text-emerald-700 focus:ring-2 focus:ring-blue-500">
                        <p class="text-[11px] text-gray-400 mt-1">Dapat dibayar sebagian (cicilan) atau lunas sekaligus.</p>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Metode Pembayaran</label>
                        <select name="metode_bayar" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="transfer">Transfer Bank</option>
                            <option value="tunai">Tunai / Langsung di Kantor</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Catatan Tambahan (Opsional)</label>
                        <input type="text" name="catatan" placeholder="Contoh: Angsuran ke-1 / Titip via bendahara"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="p-4 border-t border-gray-100 flex items-center justify-end gap-2" style="flex-shrink: 0; background-color: #f9fafb !important;">
                    <button type="button" onclick="closeBayarManualModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 cursor-pointer text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 font-bold rounded-lg text-xs transition shadow-sm cursor-pointer text-white"
                            style="background-color: #1d4ed8 !important;">
                        Simpan Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tolak Bukti Transfer -->
    <div id="modalReject" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full shadow-2xl overflow-hidden animate-in fade-in">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between" style="background-color: #b91c1c !important; color: #ffffff !important;">
                <h3 class="text-base font-bold flex items-center gap-2">
                    <i class="ti ti-x text-lg"></i> Tolak Bukti Pembayaran
                </h3>
                <button type="button" onclick="closeRejectModal()" class="text-white/80 hover:text-white cursor-pointer">
                    <i class="ti ti-x text-xl"></i>
                </button>
            </div>

            <form id="formReject" method="POST">
                @csrf
                <input type="hidden" name="action" value="tolak">
                <div class="p-5 space-y-4 text-xs">
                    <div>
                        <span class="text-gray-500">Santri:</span>
                        <h4 id="rejectSantriNama" class="text-sm font-bold text-gray-900"></h4>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                        <textarea name="alasan_penolakan" rows="3" required placeholder="Contoh: Bukti transfer tidak terbaca / nominal tidak sesuai mutasi..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-red-500"></textarea>
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                        <button type="button" onclick="closeRejectModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 font-bold rounded-lg text-xs transition shadow-sm cursor-pointer text-white"
                                style="background-color: #b91c1c !important;">
                            Kirim Penolakan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Pengaturan Jadwal Pembayaran Daftar Ulang -->
    <div id="modalJadwal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden animate-in fade-in" style="max-height: 85vh; display: flex; flex-direction: column;">
            <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between" style="background-color: #0d9488 !important; color: #ffffff !important; flex-shrink: 0;">
                <h3 class="text-base font-bold flex items-center gap-2">
                    <i class="ti ti-calendar-time text-lg"></i> Pengaturan Jadwal Pembayaran
                </h3>
                <button type="button" onclick="closeJadwalModal()" class="text-white/80 hover:text-white cursor-pointer">
                    <i class="ti ti-x text-xl"></i>
                </button>
            </div>

            <form action="{{ route('admin.daftar_ulang.setting') }}" method="POST" style="display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; margin-bottom: 0;">
                @csrf
                <div class="p-4 sm:p-5 space-y-4 text-xs" style="overflow-y: auto; flex: 1; min-height: 0;">
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-2">Status Pembayaran di Portal Wali</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                                <input type="radio" name="status" value="buka" {{ $daftarUlangSetting->status === 'buka' ? 'checked' : '' }}
                                       onchange="toggleJadwalTanggal(this.value)" class="text-teal-600 focus:ring-teal-500">
                                <div>
                                    <div class="font-bold text-gray-900">🟢 Dibuka Sekarang (Buka Manual)</div>
                                    <div class="text-[11px] text-gray-500">Wali santri dapat langsung membayar tanpa batasan tanggal.</div>
                                </div>
                            </label>

                            <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                                <input type="radio" name="status" value="otomatis" {{ $daftarUlangSetting->status === 'otomatis' ? 'checked' : '' }}
                                       onchange="toggleJadwalTanggal(this.value)" class="text-teal-600 focus:ring-teal-500">
                                <div>
                                    <div class="font-bold text-gray-900">⏰ Otomatis Berdasarkan Tanggal Buka</div>
                                    <div class="text-[11px] text-gray-500">Otomatis dibuka saat tanggal tiba, dan tetap buka terus seterusnya.</div>
                                </div>
                            </label>

                            <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                                <input type="radio" name="status" value="tutup" {{ $daftarUlangSetting->status === 'tutup' ? 'checked' : '' }}
                                       onchange="toggleJadwalTanggal(this.value)" class="text-teal-600 focus:ring-teal-500">
                                <div>
                                    <div class="font-bold text-gray-900">🔒 Ditutup Sementara (Kunci Manual)</div>
                                    <div class="text-[11px] text-gray-500">Tombol bayar di portal wali santri dikunci sementara oleh admin.</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div id="wrapTanggalBuka" class="{{ $daftarUlangSetting->status === 'otomatis' ? '' : 'hidden' }}">
                        <label class="block font-bold text-gray-700 uppercase mb-1">Tanggal Mulai Dibuka</label>
                        <input type="date" name="tanggal_buka" value="{{ $daftarUlangSetting->tanggal_buka ? $daftarUlangSetting->tanggal_buka->format('Y-m-d') : '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-teal-500">
                        <p class="text-[10px] text-gray-400 mt-1">Begitu tanggal ini tiba, portal otomatis terbuka terus tanpa batas waktu.</p>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Pesan saat Pembayaran Ditutup</label>
                        <textarea name="pesan_tutup" rows="2" placeholder="Tulis pengumuman untuk wali santri saat pembayaran ditutup..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500">{{ $daftarUlangSetting->pesan_tutup }}</textarea>
                    </div>

                    <div class="p-3 rounded-xl border border-emerald-200 bg-emerald-50 space-y-1">
                        <div class="font-bold text-emerald-900 flex items-center gap-1.5 text-xs">
                            <i class="ti ti-brand-whatsapp text-emerald-600 text-base"></i>
                            Notifikasi WA Otomatis ke Wali Santri
                        </div>
                        <p class="text-[11px] text-emerald-700 leading-relaxed">
                            Begitu sistem berstatus <strong>DIBUKA</strong> (baik dibuka manual sekarang maupun saat tanggal buka otomatis tiba), sistem secara otomatis mengirimkan pesan pengumuman resmi ke nomor WA wali santri yang <strong>biaya & nomor rekeningnya sudah siap diatur</strong>. Santri yang belum diatur tidak akan dikirimi.
                        </p>
                    </div>
                </div>

                <div class="p-4 border-t border-gray-100 flex items-center justify-end gap-2" style="flex-shrink: 0; background-color: #f9fafb !important;">
                    <button type="button" onclick="closeJadwalModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 cursor-pointer text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 font-bold rounded-lg text-xs transition shadow-xs cursor-pointer text-white"
                            style="background-color: #0d9488 !important;">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detail Chat WhatsApp -->
    <div id="modalWaDetail" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden animate-in fade-in flex flex-col max-h-[90vh]">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between text-white" style="background-color: #075e54 !important;">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center text-lg text-white">
                        <i class="ti ti-brand-whatsapp"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-white" id="waDetailSantriNama">Detail Pesan WhatsApp</h3>
                        <p class="text-[11px] text-emerald-100 font-mono" id="waDetailNomor">-</p>
                    </div>
                </div>
                <button type="button" onclick="closeWaDetailModal()" class="text-white/80 hover:text-white cursor-pointer text-lg">
                    <i class="ti ti-x"></i>
                </button>
            </div>

            <div class="p-5 space-y-4 overflow-y-auto flex-1 text-xs">
                <!-- Status Bar -->
                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-200">
                    <div>
                        <span class="text-gray-500 font-semibold block text-[10px] uppercase">Status Pengiriman</span>
                        <div id="waDetailStatusBadge" class="mt-0.5"></div>
                    </div>
                    <div class="text-right">
                        <span class="text-gray-500 font-semibold block text-[10px] uppercase">Waktu Pengiriman</span>
                        <span id="waDetailWaktu" class="font-mono text-gray-700 font-bold text-xs">-</span>
                    </div>
                </div>

                <!-- Alert Error jika status gagal -->
                <div id="waDetailErrorBox" class="hidden p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-800 space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-xs text-red-700">
                        <i class="ti ti-alert-triangle text-base"></i> Penyebab Gagal Terkirim:
                    </div>
                    <p id="waDetailErrorMessage" class="text-[11px] text-red-600 leading-relaxed font-mono"></p>
                </div>

                <!-- Chat Bubble Mockup -->
                <div class="space-y-1.5">
                    <label class="block font-bold text-gray-700 uppercase tracking-wider text-[10px]">Tampilan Isi Chat WhatsApp:</label>
                    <div class="p-4 rounded-2xl border border-emerald-200 shadow-inner relative" style="background-color: #efeae2 !important;">
                        <div class="p-3.5 rounded-2xl shadow-sm text-gray-800 text-xs leading-relaxed max-w-full font-sans whitespace-pre-wrap select-all"
                             style="background-color: #dcf8c6 !important; border-top-left-radius: 4px;"
                             id="waDetailMessageBody">
                        </div>
                        <div class="text-[10px] text-gray-400 text-right mt-1.5 flex items-center justify-end gap-1 font-mono">
                            <span id="waDetailSentTime">-</span>
                            <i id="waDetailDoubleCheck" class="ti ti-check-double text-blue-500"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between gap-2">
                <div id="waDetailRetryAction"></div>
                <button type="button" onclick="closeWaDetailModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 cursor-pointer font-bold text-xs ml-auto">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Form Hapus Add-on Daftar Ulang (Hidden) -->
    <form id="formDeleteDaftarUlangAddon" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- Modal Kelola Add-on Daftar Ulang -->
    <div id="modalAddonDaftarUlang" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden animate-in fade-in flex flex-col my-auto" style="max-height: 90vh;">
            <div class="p-4 sm:p-5 border-b border-purple-700 flex items-center justify-between shrink-0" style="background-color: #7e22ce !important; color: #ffffff !important;">
                <div>
                    <h3 class="text-sm sm:text-base font-bold flex items-center gap-1.5" style="color: #ffffff !important;">
                        <i class="ti ti-receipt text-lg" style="color: #ffffff !important;"></i> Biaya Tambahan (Add-on) Daftar Ulang
                    </h3>
                    <div class="text-xs text-purple-200 font-semibold mt-0.5" id="modalAddonSantriNama">-</div>
                </div>
                <button type="button" onclick="closeDaftarUlangAddonModal()" class="cursor-pointer p-1 transition opacity-90 hover:opacity-100" style="color: #ffffff !important;">
                    <i class="ti ti-x text-xl" style="color: #ffffff !important;"></i>
                </button>
            </div>

            <div class="p-5 space-y-4 overflow-y-auto flex-1 text-xs">
                <!-- Rincian Biaya Pokok -->
                <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl flex items-center justify-between text-purple-900 font-semibold">
                    <span>Biaya Masuk / Daftar Ulang Pokok:</span>
                    <span id="modalAddonPokok" class="font-mono font-bold text-sm">Rp 0</span>
                </div>

                <!-- List Add-on Saat Ini -->
                <div>
                    <div class="font-bold text-gray-700 uppercase mb-2 flex items-center justify-between">
                        <span>Daftar Add-on Aktif:</span>
                        <span id="modalAddonTotalNominal" class="text-purple-700 font-mono font-bold">Total: Rp 0</span>
                    </div>
                    <div id="modalAddonList" class="space-y-1.5">
                        <!-- Dinamis via JS -->
                    </div>
                </div>

                <!-- Form Tambah Add-on Baru -->
                <div class="border-t border-gray-100 pt-3">
                    <div class="font-bold text-gray-900 uppercase mb-2 flex items-center gap-1.5">
                        <i class="ti ti-plus text-purple-700 text-sm"></i> Tambah Biaya Add-on Baru
                    </div>
                    <form id="formStoreDaftarUlangAddon" method="POST" class="space-y-3 bg-gray-50 p-3.5 rounded-xl border border-gray-200">
                        @csrf
                        <div>
                            <label class="block font-bold text-gray-700 uppercase mb-1">Nama Biaya Add-on <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_biaya" id="inputAddonNama" required
                                   placeholder="Contoh: Seragam Tambahan / Buku / Kasur"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none">
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                <button type="button" onclick="setDaftarUlangAddonPreset('Seragam Tambahan', 150000)" class="px-2.5 py-1 bg-white border border-gray-300 hover:border-purple-400 hover:text-purple-700 text-gray-700 rounded-lg text-[11px] font-semibold cursor-pointer transition shadow-2xs">
                                    + Seragam Tambahan (150rb)
                                </button>
                                <button type="button" onclick="setDaftarUlangAddonPreset('Kitab & Buku Paket', 200000)" class="px-2.5 py-1 bg-white border border-gray-300 hover:border-purple-400 hover:text-purple-700 text-gray-700 rounded-lg text-[11px] font-semibold cursor-pointer transition shadow-2xs">
                                    + Kitab/Buku (200rb)
                                </button>
                                <button type="button" onclick="setDaftarUlangAddonPreset('Kasur & Perlengkapan Asrama', 350000)" class="px-2.5 py-1 bg-white border border-gray-300 hover:border-purple-400 hover:text-purple-700 text-gray-700 rounded-lg text-[11px] font-semibold cursor-pointer transition shadow-2xs">
                                    + Kasur/Asrama (350rb)
                                </button>
                                <button type="button" onclick="setDaftarUlangAddonPreset('Ekstrakurikuler Khusus', 100000)" class="px-2.5 py-1 bg-white border border-gray-300 hover:border-purple-400 hover:text-purple-700 text-gray-700 rounded-lg text-[11px] font-semibold cursor-pointer transition shadow-2xs">
                                    + Ekskul (100rb)
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 uppercase mb-1">Nominal Biaya (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="nominal" id="inputAddonNominal" required step="500" min="500"
                                   placeholder="Contoh: 150000"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-mono font-bold focus:ring-2 focus:ring-purple-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 uppercase mb-1">Keterangan Tambahan (Opsional)</label>
                            <input type="text" name="keterangan" id="inputAddonKet"
                                   placeholder="Contoh: 2 stel gamis harian ukuran L"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none">
                        </div>

                        <div class="pt-1 flex justify-end">
                            <button type="submit" class="px-4 py-2 text-white font-bold rounded-lg text-xs transition cursor-pointer flex items-center gap-1.5 shadow-sm"
                                    style="background-color: #7e22ce !important;">
                                <i class="ti ti-plus"></i> Simpan Add-on Ini
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="p-4 border-t border-gray-100 flex justify-end bg-gray-50 shrink-0">
                <button type="button" onclick="closeDaftarUlangAddonModal()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-bold rounded-xl text-xs cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function openJadwalModal() {
            document.getElementById('modalJadwal').classList.remove('hidden');
        }

        function closeJadwalModal() {
            document.getElementById('modalJadwal').classList.add('hidden');
        }

        function toggleJadwalTanggal(status) {
            const wrap = document.getElementById('wrapTanggalBuka');
            if (status === 'otomatis') {
                wrap.classList.remove('hidden');
            } else {
                wrap.classList.add('hidden');
            }
        }
        function toggleBeasiswa(isChecked) {
            const inputNominal = document.getElementById('aturNominalBiaya');
            const inputKet = document.getElementById('aturKeterangan');
            if (isChecked) {
                inputNominal.value = 0;
                inputNominal.readOnly = true;
                inputNominal.classList.add('bg-purple-50', 'text-purple-800');
                if (!inputKet.value || inputKet.value.trim() === '') {
                    inputKet.value = 'Beasiswa Penuh (Bebas Biaya Masuk)';
                }
            } else {
                if (inputNominal.value == 0) {
                    inputNominal.value = 6000000;
                }
                inputNominal.readOnly = false;
                inputNominal.classList.remove('bg-purple-50', 'text-purple-800');
                if (inputKet.value === 'Beasiswa Penuh (Bebas Biaya Masuk)') {
                    inputKet.value = '';
                }
            }
        }

        function openAturBiayaModal(santriId, nama, bank, noRek, atasNama, nominal, gelombang, ket, isBeasiswa) {
            document.getElementById('aturSantriNama').textContent = nama;
            document.getElementById('aturBank').value = bank || 'BNI';
            document.getElementById('aturNoRekening').value = noRek || '';
            document.getElementById('aturAtasNama').value = atasNama || '';
            document.getElementById('aturNominalBiaya').value = (isBeasiswa == 1) ? 0 : (nominal || 6000000);
            document.getElementById('aturGelombang').value = gelombang || 'Gelombang 1';
            document.getElementById('aturKeterangan').value = ket || '';
            
            const cbBeasiswa = document.getElementById('aturIsBeasiswa');
            cbBeasiswa.checked = (isBeasiswa == 1);
            toggleBeasiswa(cbBeasiswa.checked);

            document.getElementById('formAturBiaya').action = "{{ url('/admin/daftar-ulang/santri') }}/" + santriId + "/atur";
            document.getElementById('modalAturBiaya').classList.remove('hidden');
        }

        function closeAturBiayaModal() {
            document.getElementById('modalAturBiaya').classList.add('hidden');
        }

        function openBayarManualModal(tagihanId, nama, sisa) {
            document.getElementById('bayarSantriNama').textContent = nama;
            document.getElementById('bayarSisaTagihan').textContent = 'Rp ' + Number(sisa).toLocaleString('id-ID');
            const inputBayar = document.getElementById('inputJumlahBayar');
            inputBayar.value = sisa;
            inputBayar.max = sisa;
            document.getElementById('formBayarManual').action = "{{ url('/admin/daftar-ulang') }}/" + tagihanId + "/bayar-manual";
            document.getElementById('modalBayarManual').classList.remove('hidden');
        }

        function closeBayarManualModal() {
            document.getElementById('modalBayarManual').classList.add('hidden');
        }

        function openRejectModal(pembayaranId, nama) {
            document.getElementById('rejectSantriNama').textContent = nama;
            document.getElementById('formReject').action = "{{ url('/admin/daftar-ulang/verifikasi') }}/" + pembayaranId;
            document.getElementById('modalReject').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('modalReject').classList.add('hidden');
        }

        function showWaDetail(wa, fallbackNama) {
            if (!wa) return;
            const santriNama = fallbackNama || wa.recipient_name || 'Santri';
            document.getElementById('waDetailSantriNama').textContent = 'Pesan WhatsApp: ' + santriNama;
            document.getElementById('waDetailNomor').textContent = 'Tujuan: ' + (wa.recipient_number || '-') + ' (' + (wa.recipient_name || 'Wali') + ')';
            document.getElementById('waDetailMessageBody').textContent = wa.message_body || '';
            document.getElementById('waDetailWaktu').textContent = wa.sent_at ? wa.sent_at : (wa.created_at || '-');
            document.getElementById('waDetailSentTime').textContent = wa.sent_at ? wa.sent_at : 'Belum Terkirim';

            const statusBadge = document.getElementById('waDetailStatusBadge');
            const errorBox = document.getElementById('waDetailErrorBox');
            const errorMsg = document.getElementById('waDetailErrorMessage');
            const retryAction = document.getElementById('waDetailRetryAction');
            const checkIcon = document.getElementById('waDetailDoubleCheck');

            if (wa.status === 'sent') {
                statusBadge.innerHTML = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 inline-flex items-center gap-1"><i class="ti ti-check-double text-emerald-600"></i> Terkirim Sukses</span>';
                errorBox.classList.add('hidden');
                retryAction.innerHTML = '';
                checkIcon.className = 'ti ti-check-double text-blue-500';
            } else if (wa.status === 'failed') {
                statusBadge.innerHTML = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 inline-flex items-center gap-1"><i class="ti ti-alert-triangle text-red-600"></i> Gagal Terkirim</span>';
                errorBox.classList.remove('hidden');
                errorMsg.textContent = wa.error_message || 'Koneksi gateway gagal atau nomor tidak terdaftar.';
                checkIcon.className = 'ti ti-x text-red-500';
                retryAction.innerHTML = `
                    <form action="{{ url('/admin/daftar-ulang/wa') }}/${wa.id}/retry" method="POST" class="inline">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <button type="submit" class="px-4 py-2 font-bold rounded-lg text-xs text-white cursor-pointer inline-flex items-center gap-1.5" style="background-color: #dc2626 !important;">
                            <i class="ti ti-rotate-clockwise"></i> Kirim Ulang Sekarang (Retry)
                        </button>
                    </form>
                `;
            } else {
                statusBadge.innerHTML = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 inline-flex items-center gap-1"><i class="ti ti-clock text-amber-600 animate-spin"></i> Dalam Antrean Queue</span>';
                errorBox.classList.add('hidden');
                retryAction.innerHTML = '';
                checkIcon.className = 'ti ti-clock text-amber-500';
            }

            document.getElementById('modalWaDetail').classList.remove('hidden');
        }

        function closeWaDetailModal() {
            document.getElementById('modalWaDetail').classList.add('hidden');
        }

        function openDaftarUlangAddonModal(tagihanId, santriNama, addons, nominalBiaya) {
            document.getElementById('modalAddonSantriNama').innerText = santriNama;
            document.getElementById('modalAddonPokok').innerText = nominalBiaya === 0 ? 'Rp 0 (Beasiswa Full)' : 'Rp ' + Number(nominalBiaya).toLocaleString('id-ID');
            document.getElementById('formStoreDaftarUlangAddon').action = "{{ url('/admin/daftar-ulang/tagihan') }}/" + tagihanId + "/addon";

            const listContainer = document.getElementById('modalAddonList');
            listContainer.innerHTML = '';
            let totalAddon = 0;

            if (!addons || addons.length === 0) {
                listContainer.innerHTML = '<div class="text-center py-4 bg-gray-50 rounded-xl text-gray-400 border border-dashed border-gray-200">Belum ada biaya add-on pada tagihan ini.</div>';
            } else {
                addons.forEach(function(item) {
                    totalAddon += Number(item.nominal);
                    const row = document.createElement('div');
                    row.className = 'flex items-center justify-between p-2.5 bg-white border border-gray-200 rounded-xl hover:bg-purple-50/30 transition';
                    row.innerHTML = `
                        <div>
                            <div class="font-bold text-gray-900">${item.nama_biaya}</div>
                            ${item.keterangan ? `<div class="text-[11px] text-gray-500">${item.keterangan}</div>` : ''}
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-mono font-bold text-purple-700">Rp ${Number(item.nominal).toLocaleString('id-ID')}</span>
                            <button type="button" onclick="deleteDaftarUlangAddon(${item.id}, '${item.nama_biaya.replace(/'/g, "\\'")}')" class="text-red-500 hover:text-red-700 p-1 cursor-pointer transition" title="Hapus biaya ini">
                                <i class="ti ti-trash text-sm"></i>
                            </button>
                        </div>
                    `;
                    listContainer.appendChild(row);
                });
            }

            document.getElementById('modalAddonTotalNominal').innerText = 'Total: Rp ' + totalAddon.toLocaleString('id-ID');
            document.getElementById('inputAddonNama').value = '';
            document.getElementById('inputAddonNominal').value = '';
            document.getElementById('inputAddonKet').value = '';
            document.getElementById('modalAddonDaftarUlang').classList.remove('hidden');
        }

        function closeDaftarUlangAddonModal() {
            document.getElementById('modalAddonDaftarUlang').classList.add('hidden');
        }

        function setDaftarUlangAddonPreset(nama, nominal) {
            document.getElementById('inputAddonNama').value = nama;
            document.getElementById('inputAddonNominal').value = nominal;
        }

        function deleteDaftarUlangAddon(addonId, nama) {
            if (confirm("Hapus biaya add-on '" + nama + "'? Total tagihan santri akan otomatis dikurangi.")) {
                const form = document.getElementById('formDeleteDaftarUlangAddon');
                form.action = "{{ url('/admin/daftar-ulang/addon') }}/" + addonId;
                form.submit();
            }
        }
    </script>
</div>
@endsection
