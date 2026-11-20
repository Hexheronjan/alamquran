@extends('layouts.admin.app')

@section('title', 'Kesehatan & UKS Santri')

@section('content')
<div class="bg-gray-100 min-h-screen">
    <div class="w-full py-6 px-4 lg:px-8 space-y-6">

        {{-- Alerts --}}
        @if(session('success'))
            <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-r-xl shadow-sm flex items-start gap-3">
                <i class="ti ti-circle-check text-2xl text-green-600 flex-shrink-0 mt-0.5"></i>
                <div>
                    <h4 class="text-sm font-bold text-green-800">Berhasil!</h4>
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm">
                <div class="flex items-center gap-2 text-red-800 font-bold text-sm mb-1">
                    <i class="ti ti-alert-circle text-xl"></i> Terdapat Kesalahan:
                </div>
                <ul class="list-disc list-inside text-xs text-red-700 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ALERT KHUSUS: SANTRI SAKIT LEBIH DARI 3 HARI --}}
        @if(isset($santriPerluJemput) && $santriPerluJemput->isNotEmpty())
            <div class="p-5 bg-gradient-to-r from-amber-50 to-orange-50 border-l-4 border-amber-500 rounded-2xl shadow-sm space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div class="flex items-center gap-2.5 text-amber-900 font-bold text-base">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-600"></span>
                        </span>
                        <i class="ti ti-alert-triangle text-2xl text-amber-600"></i>
                        <span>Peringatan UKS: {{ $santriPerluJemput->count() }} Santri Sakit Sudah &ge; 3 Hari</span>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs bg-amber-200/80 text-amber-900 font-bold px-3 py-1 rounded-full border border-amber-300">
                        <i class="ti ti-info-circle"></i> Rekomendasi Jemput / Rawat di Rumah
                    </span>
                </div>
                <p class="text-xs text-amber-800 leading-relaxed">
                    Santri berikut telah dirawat di pos kesehatan (UKS) / asrama selama <strong>3 hari atau lebih</strong>. Disarankan untuk berkoordinasi dengan wali santri agar ananda dapat dijemput dan beristirahat di rumah hingga pulih.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 pt-1">
                    @foreach($santriPerluJemput as $p)
                        <div class="bg-white p-3.5 rounded-xl border border-amber-200 flex items-center justify-between shadow-xs hover:border-amber-400 transition">
                            <div class="space-y-0.5 min-w-0 pr-2">
                                <div class="font-bold text-gray-900 text-sm truncate">{{ $p->santri?->nama_lengkap }}</div>
                                <div class="text-xs text-gray-500">
                                    Kelas {{ $p->santri?->kelas }} • <span class="font-bold text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded text-[11px]">{{ $p->lama_sakit }} Hari Sakit</span>
                                </div>
                                <div class="text-[11px] text-gray-600 italic truncate mt-0.5">
                                    "{{ $p->keluhan_sakit }}"
                                </div>
                            </div>
                            <button type="button"
                                    onclick="openModalJemput({{ json_encode($p) }})"
                                    class="px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-xs flex items-center gap-1.5 transition shrink-0 cursor-pointer"
                                    style="background-color: #d97706 !important; color: #ffffff !important;">
                                <i class="ti ti-car text-base"></i> Jemput
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Header & Action Button --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="ti ti-first-aid-kit text-rose-600"></i> Rekam Medis & Kesehatan Santri (UKS)
                </h1>
                <p class="text-gray-600 text-sm mt-1">
                    Catat kondisi sakit, tindakan UKS, penjemputan istirahat di rumah, dan pemulihan santri.
                </p>
            </div>
            <div>
                <button type="button"
                        onclick="openModalTambah()"
                        class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl shadow-sm transition cursor-pointer"
                        style="background-color: #e11d48 !important; color: #ffffff !important;">
                    <i class="ti ti-plus text-lg"></i>
                    + Catat Kesehatan Santri
                </button>
            </div>
        </div>

        {{-- Status Filter Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <a href="{{ route('admin.kesehatan.index', ['status' => 'semua', 'search' => $search]) }}"
               class="bg-white rounded-xl p-3.5 shadow-sm border transition {{ $status === 'semua' ? 'border-purple-500 ring-2 ring-purple-200' : 'border-gray-200 hover:border-gray-300' }}">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span class="font-bold uppercase">Semua</span>
                    <i class="ti ti-notes text-lg text-purple-600"></i>
                </div>
                <div class="text-xl font-black text-gray-900">{{ $totalSemua }}</div>
                <div class="text-[10px] text-gray-400 mt-0.5">Total riwayat sakit</div>
            </a>

            <a href="{{ route('admin.kesehatan.index', ['status' => 'istirahat_uks', 'search' => $search]) }}"
               class="bg-white rounded-xl p-3.5 shadow-sm border transition {{ $status === 'istirahat_uks' ? 'border-amber-500 ring-2 ring-amber-200' : 'border-gray-200 hover:border-gray-300' }}">
                <div class="flex items-center justify-between text-xs text-amber-700 mb-1">
                    <span class="font-bold uppercase">Istirahat UKS</span>
                    <i class="ti ti-bed text-lg text-amber-600"></i>
                </div>
                <div class="text-xl font-black text-gray-900">{{ $totalUks }}</div>
                <div class="text-[10px] text-gray-400 mt-0.5">Dirawat di pos UKS</div>
            </a>

            <a href="{{ route('admin.kesehatan.index', ['status' => 'pantauan', 'search' => $search]) }}"
               class="bg-white rounded-xl p-3.5 shadow-sm border transition {{ $status === 'pantauan' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-200 hover:border-gray-300' }}">
                <div class="flex items-center justify-between text-xs text-blue-700 mb-1">
                    <span class="font-bold uppercase">Pantauan</span>
                    <i class="ti ti-eye text-lg text-blue-600"></i>
                </div>
                <div class="text-xl font-black text-gray-900">{{ $totalPantauan }}</div>
                <div class="text-[10px] text-gray-400 mt-0.5">Rawat jalan / asrama</div>
            </a>

            <a href="{{ route('admin.kesehatan.index', ['status' => 'rawat_inap', 'search' => $search]) }}"
               class="bg-white rounded-xl p-3.5 shadow-sm border transition {{ $status === 'rawat_inap' ? 'border-red-500 ring-2 ring-red-200' : 'border-gray-200 hover:border-gray-300' }}">
                <div class="flex items-center justify-between text-xs text-red-700 mb-1">
                    <span class="font-bold uppercase">Rawat Inap/RS</span>
                    <i class="ti ti-building-hospital text-lg text-red-600"></i>
                </div>
                <div class="text-xl font-black text-gray-900">{{ $totalRawatInap }}</div>
                <div class="text-[10px] text-gray-400 mt-0.5">Dirujuk ke klinik/RS</div>
            </a>

            <a href="{{ route('admin.kesehatan.index', ['status' => 'istirahat_dirumah', 'search' => $search]) }}"
               class="bg-white rounded-xl p-3.5 shadow-sm border transition {{ $status === 'istirahat_dirumah' ? 'border-purple-600 ring-2 ring-purple-200' : 'border-gray-200 hover:border-gray-300' }}">
                <div class="flex items-center justify-between text-xs text-purple-700 mb-1">
                    <span class="font-bold uppercase">Di Rumah</span>
                    <i class="ti ti-home-heart text-lg text-purple-600"></i>
                </div>
                <div class="text-xl font-black text-gray-900">{{ $totalDirumah }}</div>
                <div class="text-[10px] text-gray-400 mt-0.5">Dijemput istirahat</div>
            </a>

            <a href="{{ route('admin.kesehatan.index', ['status' => 'sembuh', 'search' => $search]) }}"
               class="bg-white rounded-xl p-3.5 shadow-sm border transition {{ $status === 'sembuh' ? 'border-emerald-500 ring-2 ring-emerald-200' : 'border-gray-200 hover:border-gray-300' }}">
                <div class="flex items-center justify-between text-xs text-emerald-700 mb-1">
                    <span class="font-bold uppercase">Sembuh</span>
                    <i class="ti ti-circle-check text-lg text-emerald-600"></i>
                </div>
                <div class="text-xl font-black text-gray-900">{{ $totalSembuh }}</div>
                <div class="text-[10px] text-gray-400 mt-0.5">Sudah kembali pulih</div>
            </a>
        </div>

        {{-- Search Bar --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <form method="GET" action="{{ route('admin.kesehatan.index') }}" class="flex flex-col sm:flex-row gap-3">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="flex-1 relative">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari nama santri, NIS, atau keluhan sakit..."
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent text-sm">
                    <i class="ti ti-search absolute left-3 top-3 text-gray-400 text-lg"></i>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-semibold transition flex items-center gap-1.5 cursor-pointer" style="background-color: #e11d48 !important; color: #ffffff !important;">
                        <i class="ti ti-search"></i> Cari
                    </button>
                    @if($search)
                        <a href="{{ route('admin.kesehatan.index', ['status' => $status]) }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition flex items-center gap-1">
                            <i class="ti ti-refresh"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table Data --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-800">Catatan Pemeriksaan Kesehatan</h2>
                <span class="text-xs bg-gray-100 text-gray-700 px-3 py-1 rounded-full font-bold">
                    {{ $catatanKesehatan->total() }} Data
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-gray-600 font-semibold text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5 w-10 text-center">No</th>
                            <th class="px-5 py-3.5">Santri</th>
                            <th class="px-5 py-3.5">Tanggal</th>
                            <th class="px-5 py-3.5">Keluhan Sakit</th>
                            <th class="px-5 py-3.5">Tindakan / Obat</th>
                            <th class="px-5 py-3.5 text-center">Status Kesehatan</th>
                            <th class="px-5 py-3.5 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($catatanKesehatan as $idx => $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-4 text-center text-gray-400 text-xs">
                                    {{ $catatanKesehatan->firstItem() + $idx }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-gray-900 text-sm">{{ $item->santri?->nama_lengkap ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        Kelas {{ $item->santri?->kelas ?? '-' }} • NIS: {{ $item->santri?->nis ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-xs font-mono text-gray-600">
                                    <div>{{ $item->tanggal->translatedFormat('d M Y') }}</div>
                                    @if($item->status_kesehatan !== 'sembuh')
                                        <div class="mt-1">
                                            @if($item->is_lebih_tiga_hari)
                                                <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-800 text-[10px] font-black px-2 py-0.5 rounded-full border border-amber-300">
                                                    <i class="ti ti-alert-triangle text-xs"></i> {{ $item->lama_sakit }} Hari Sakit
                                                </span>
                                            @else
                                                <span class="inline-block bg-gray-100 text-gray-600 text-[10px] font-semibold px-1.5 py-0.5 rounded">
                                                    Hari ke-{{ $item->lama_sakit }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="font-semibold text-gray-800">{{ $item->keluhan_sakit }}</span>
                                </td>
                                <td class="px-5 py-4 text-xs text-gray-600 max-w-xs">
                                    @if($item->tindakan_perawatan)
                                        <div class="font-medium text-gray-700">Tindakan: {{ $item->tindakan_perawatan }}</div>
                                    @endif
                                    @if($item->obat_diberikan)
                                        <div class="text-gray-500 mt-0.5">Obat: {{ $item->obat_diberikan }}</div>
                                    @endif
                                    @if(!$item->tindakan_perawatan && !$item->obat_diberikan)
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    @if($item->status_kesehatan === 'istirahat_uks')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            <i class="ti ti-bed text-sm"></i> Istirahat UKS
                                        </span>
                                    @elseif($item->status_kesehatan === 'pantauan')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                            <i class="ti ti-eye text-sm"></i> Dalam Pantauan
                                        </span>
                                    @elseif($item->status_kesehatan === 'rawat_inap')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                                            <i class="ti ti-building-hospital text-sm"></i> Rawat Inap / RS
                                        </span>
                                    @elseif($item->status_kesehatan === 'istirahat_dirumah')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                            <i class="ti ti-home-heart text-sm"></i> Istirahat di Rumah
                                        </span>
                                        @if($item->nama_penjemput)
                                            <div class="text-[11px] text-purple-700 font-semibold mt-1">
                                                Penjemput: {{ $item->nama_penjemput }}
                                            </div>
                                        @endif
                                        @if($item->estimasi_kembali)
                                            <div class="text-[10px] text-gray-500">
                                                Kembali: {{ \Carbon\Carbon::parse($item->estimasi_kembali)->translatedFormat('d M Y') }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <i class="ti ti-circle-check text-sm"></i> Sembuh
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- Tombol Cepat: Jemput Santri --}}
                                        @if(in_array($item->status_kesehatan, ['istirahat_uks', 'pantauan', 'rawat_inap']))
                                            <button type="button"
                                                    onclick="openModalJemput({{ json_encode($item) }})"
                                                    class="p-1.5 {{ $item->is_lebih_tiga_hari ? 'text-amber-700 bg-amber-50 hover:bg-amber-100 ring-1 ring-amber-300' : 'text-purple-600 hover:bg-purple-50' }} rounded-lg transition cursor-pointer"
                                                    title="{{ $item->is_lebih_tiga_hari ? 'Rekomendasi: Jemput Santri (Sakit ≥ 3 Hari)' : 'Jemput Santri ke Rumah' }}">
                                                <i class="ti ti-car text-lg"></i>
                                            </button>
                                        @endif

                                        {{-- Tombol Cepat: Tandai Sembuh --}}
                                        @if($item->status_kesehatan !== 'sembuh')
                                            <button type="button"
                                                    onclick="quickUpdateStatus({{ $item->id }}, 'sembuh')"
                                                    class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition cursor-pointer"
                                                    title="Tandai Sudah Sembuh">
                                                <i class="ti ti-check text-lg"></i>
                                            </button>
                                        @endif

                                        {{-- Edit --}}
                                        <button type="button"
                                                onclick="openModalEdit({{ json_encode($item) }})"
                                                class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition cursor-pointer"
                                                title="Edit Catatan">
                                            <i class="ti ti-edit text-lg"></i>
                                        </button>

                                        {{-- Delete --}}
                                        <button type="button"
                                                onclick="confirmDelete({{ $item->id }}, '{{ addslashes($item->santri?->nama_lengkap ?? 'Santri') }}')"
                                                class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer"
                                                title="Hapus Catatan">
                                            <i class="ti ti-trash text-lg"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                                    <i class="ti ti-shield-heart text-5xl text-gray-300 block mb-2"></i>
                                    <p class="font-medium text-gray-600">Alhamdulillah, tidak ada catatan santri sakit</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Klik tombol "+ Catat Kesehatan Santri" jika ada santri yang berobat ke UKS.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($catatanKesehatan->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $catatanKesehatan->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

{{-- MODAL TAMBAH / EDIT KESEHATAN --}}
<div id="modalKesehatan" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden transition-all">
        
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
            <h3 id="modalTitle" class="text-base font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-first-aid-kit text-rose-600 text-xl"></i> Catat Kondisi Kesehatan Santri
            </h3>
            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold cursor-pointer">
                &times;
            </button>
        </div>

        <form id="formKesehatan" method="POST" action="{{ route('admin.kesehatan.store') }}" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            {{-- Pilih Santri dengan Fitur Pencarian Cepat --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide">
                        Nama Santri <span class="text-red-500">*</span>
                    </label>
                    <span id="kesehatanSantriSearchCount" class="text-[11px] text-gray-400">Total: {{ $santriList->count() }} santri</span>
                </div>

                {{-- Input Pencarian Santri --}}
                <div class="relative mb-2">
                    <i class="ti ti-search absolute left-3 top-2.5 text-rose-500 text-sm"></i>
                    <input
                        type="text"
                        id="searchSantriKesehatanInput"
                        placeholder="Ketik nama santri, NIS, atau kelas untuk cari cepat..."
                        oninput="filterSantriKesehatanOptions(this.value)"
                        autocomplete="off"
                        class="w-full text-sm border border-rose-300 rounded-xl pl-9 pr-8 py-2 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent bg-rose-50/30 text-gray-800 placeholder-gray-400 font-medium"
                    />
                    <button type="button" onclick="clearSantriKesehatanSearch()" id="clearSantriKesehatanBtn" class="hidden absolute right-2.5 top-2 text-gray-400 hover:text-gray-600 text-sm p-0.5 cursor-pointer">
                        <i class="ti ti-x"></i>
                    </button>
                </div>

                <select name="santri_id" id="inputSantriId" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-rose-500 focus:border-transparent bg-white">
                    <option value="">-- Pilih Santri --</option>
                    @foreach($santriList as $s)
                        <option value="{{ $s->id }}"
                                data-nama="{{ strtolower($s->nama_lengkap) }}"
                                data-nis="{{ strtolower($s->nis ?? '') }}"
                                data-kelas="{{ strtolower((string)$s->kelas) }}">
                            {{ $s->nama_lengkap }} (Kelas {{ $s->kelas }}) - NIS: {{ $s->nis ?? '-' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tanggal & Status --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">
                        Tanggal Periksa <span class="text-red-500">*</span>
                    </label>
                    <input type="date"
                           name="tanggal"
                           id="inputTanggal"
                           required
                           value="{{ date('Y-m-d') }}"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">
                        Status Saat Ini <span class="text-red-500">*</span>
                    </label>
                    <select name="status_kesehatan" id="inputStatusKesehatan" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <option value="istirahat_uks">Istirahat di UKS</option>
                        <option value="pantauan">Dalam Pantauan (Asrama)</option>
                        <option value="rawat_inap">Dirujuk / Rawat Inap</option>
                        <option value="istirahat_dirumah">Istirahat di Rumah (Dijemput)</option>
                        <option value="sembuh">Sudah Sembuh</option>
                    </select>
                </div>
            </div>

            {{-- Keluhan Sakit --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">
                    Keluhan / Gejala Sakit <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="keluhan_sakit"
                       id="inputKeluhanSakit"
                       required
                       placeholder="Misal: Demam tinggi 38.5 C, pusing, flu batuk"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-rose-500 focus:border-transparent">
            </div>

            {{-- Tindakan Perawatan --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">
                    Tindakan Perawatan (Opsional)
                </label>
                <input type="text"
                       name="tindakan_perawatan"
                       id="inputTindakan"
                       placeholder="Misal: Diberi kompres air hangat, istirahat total"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-rose-500 focus:border-transparent">
            </div>

            {{-- Obat yang Diberikan --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">
                    Obat yang Diberikan (Opsional)
                </label>
                <input type="text"
                       name="obat_diberikan"
                       id="inputObat"
                       placeholder="Misal: Paracetamol 500mg (3x1), Madu herbal"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-rose-500 focus:border-transparent">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button"
                        onclick="closeModal()"
                        class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-100 text-sm font-semibold transition cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold shadow-md transition cursor-pointer"
                        style="background-color: #e11d48 !important; color: #ffffff !important;">
                    Simpan Catatan
                </button>
            </div>
        </form>

    </div>
</div>

{{-- MODAL PROSES JEMPUT SANTRI (ISTIRAHAT DI RUMAH) --}}
<div id="modalJemputSantri" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden transition-all">
        
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-amber-50">
            <h3 class="text-base font-bold text-amber-900 flex items-center gap-2">
                <i class="ti ti-car text-amber-600 text-xl"></i> Form Jemput Santri (Istirahat di Rumah)
            </h3>
            <button type="button" onclick="closeModalJemput()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold cursor-pointer">
                &times;
            </button>
        </div>

        <form id="formJemputSantri" method="POST" action="" class="p-6 space-y-4">
            @csrf

            {{-- Info Santri Ringkas --}}
            <div class="p-3.5 bg-amber-50/70 border border-amber-200 rounded-xl space-y-1 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Nama Santri:</span>
                    <strong id="jemputNamaSantri" class="text-gray-900 text-sm font-bold"></strong>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Kelas / Keluhan:</span>
                    <span class="text-gray-800" id="jemputKelasKeluhan"></span>
                </div>
                <div class="flex items-center justify-between pt-1 border-t border-amber-200/60">
                    <span class="text-gray-600">Durasi Sakit:</span>
                    <span id="jemputDurasiSakit" class="font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded"></span>
                </div>
            </div>

            {{-- Waktu Penjemputan & Nama Penjemput --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">
                        Waktu Penjemputan <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local"
                           name="tanggal_jemput"
                           id="inputTanggalJemput"
                           required
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">
                        Nama Penjemput <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="nama_penjemput"
                           id="inputNamaPenjemput"
                           required
                           placeholder="Misal: Bapak Budi (Ayah)"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
            </div>

            {{-- Estimasi Kembali --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">
                    Estimasi Kembali ke Pondok (Opsional)
                </label>
                <input type="date"
                       name="estimasi_kembali"
                       id="inputEstimasiKembali"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                <span class="text-[11px] text-gray-400 mt-1 block">Dapat dikosongkan jika menyesuaikan kondisi kesembuhan santri.</span>
            </div>

            {{-- Catatan Penjemputan --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">
                    Catatan / Pesan Pengurus UKS (Opsional)
                </label>
                <textarea name="catatan_penjemputan"
                          id="inputCatatanPenjemputan"
                          rows="2"
                          placeholder="Misal: Ananda demam naik turun 3 hari, disarankan kontrol dokter dan istirahat total di rumah."
                          class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm text-gray-800 focus:ring-2 focus:ring-amber-500 focus:border-transparent"></textarea>
            </div>

            {{-- Checkbox Kirim WA --}}
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 flex items-start gap-3">
                <input type="checkbox"
                       name="kirim_wa"
                       id="checkKirimWa"
                       value="1"
                       checked
                       class="mt-1 h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                <label for="checkKirimWa" class="text-xs text-gray-700 cursor-pointer select-none">
                    <span class="font-bold text-gray-900 block">Kirim Notifikasi Rekomendasi/Jemput ke WhatsApp Wali</span>
                    Otomatis kirim rincian penjemputan dan durasi sakit ananda ke nomor WhatsApp orang tua/wali santri.
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button"
                        onclick="closeModalJemput()"
                        class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-100 text-sm font-semibold transition cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold shadow-md transition cursor-pointer flex items-center gap-1.5"
                        style="background-color: #d97706 !important; color: #ffffff !important;">
                    <i class="ti ti-check"></i> Proses Jemput Santri
                </button>
            </div>
        </form>

    </div>
</div>

{{-- FORMS QUICK ACTIONS --}}
<form id="formQuickStatus" method="POST" class="hidden">
    @csrf
    @method('PATCH')
    <input type="hidden" name="status_kesehatan" id="quickStatusVal">
</form>

<form id="formDelete" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
    function filterSantriKesehatanOptions(keyword) {
        const query = keyword.toLowerCase().trim();
        const select = document.getElementById('inputSantriId');
        const clearBtn = document.getElementById('clearSantriKesehatanBtn');
        const countSpan = document.getElementById('kesehatanSantriSearchCount');
        const options = select.getElementsByTagName('option');

        if (query.length > 0) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }

        let matchCount = 0;
        let firstMatch = null;

        for (let i = 1; i < options.length; i++) { // lewati option 0 (-- Pilih Santri --)
            const opt = options[i];
            const nama = opt.getAttribute('data-nama') || '';
            const nis = opt.getAttribute('data-nis') || '';
            const kelas = opt.getAttribute('data-kelas') || '';

            if (query === '' || nama.includes(query) || nis.includes(query) || kelas.includes(query)) {
                opt.style.display = '';
                opt.disabled = false;
                opt.hidden = false;
                matchCount++;
                if (!firstMatch) {
                    firstMatch = opt;
                }
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
                opt.hidden = true;
            }
        }

        if (query !== '') {
            countSpan.textContent = matchCount + ' santri cocok';
            if (firstMatch) {
                select.value = firstMatch.value;
            }
        } else {
            countSpan.textContent = 'Total: ' + (options.length - 1) + ' santri';
        }
    }

    function clearSantriKesehatanSearch() {
        const input = document.getElementById('searchSantriKesehatanInput');
        if (input) {
            input.value = '';
            filterSantriKesehatanOptions('');
            input.focus();
        }
    }

    function openModalTambah() {
        document.getElementById('modalTitle').innerHTML = '<i class="ti ti-first-aid-kit text-rose-600 text-xl"></i> Catat Kondisi Kesehatan Santri';
        const form = document.getElementById('formKesehatan');
        form.action = "{{ route('admin.kesehatan.store') }}";
        document.getElementById('formMethod').value = 'POST';

        clearSantriKesehatanSearch();
        document.getElementById('inputSantriId').value = '';
        document.getElementById('inputTanggal').value = "{{ date('Y-m-d') }}";
        document.getElementById('inputStatusKesehatan').value = 'istirahat_uks';
        document.getElementById('inputKeluhanSakit').value = '';
        document.getElementById('inputTindakan').value = '';
        document.getElementById('inputObat').value = '';

        document.getElementById('modalKesehatan').classList.remove('hidden');
        setTimeout(() => {
            const searchInput = document.getElementById('searchSantriKesehatanInput');
            if (searchInput) searchInput.focus();
        }, 150);
    }

    function openModalEdit(item) {
        document.getElementById('modalTitle').innerHTML = '<i class="ti ti-edit text-blue-600 text-xl"></i> Edit Catatan Kesehatan';
        const form = document.getElementById('formKesehatan');
        form.action = "{{ url('/admin/kesehatan') }}/" + item.id;
        document.getElementById('formMethod').value = 'PUT';

        const searchInput = document.getElementById('searchSantriKesehatanInput');
        if (searchInput) searchInput.value = '';
        filterSantriKesehatanOptions('');

        document.getElementById('inputSantriId').value = item.santri_id;
        document.getElementById('inputTanggal').value = item.tanggal ? item.tanggal.substring(0, 10) : "{{ date('Y-m-d') }}";
        document.getElementById('inputStatusKesehatan').value = item.status_kesehatan || 'istirahat_uks';
        document.getElementById('inputKeluhanSakit').value = item.keluhan_sakit || '';
        document.getElementById('inputTindakan').value = item.tindakan_perawatan || '';
        document.getElementById('inputObat').value = item.obat_diberikan || '';

        document.getElementById('modalKesehatan').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('modalKesehatan').classList.add('hidden');
    }

    function openModalJemput(item) {
        const form = document.getElementById('formJemputSantri');
        form.action = "{{ url('/admin/kesehatan') }}/" + item.id + "/jemput";

        const santriName = item.santri ? item.santri.nama_lengkap : 'Santri';
        const santriKelas = item.santri ? item.santri.kelas : '-';
        const keluhan = item.keluhan_sakit || '-';
        const lamaSakit = item.lama_sakit ? item.lama_sakit + ' Hari' : '-';

        document.getElementById('jemputNamaSantri').innerText = santriName;
        document.getElementById('jemputKelasKeluhan').innerText = 'Kelas ' + santriKelas + ' • ' + keluhan;
        document.getElementById('jemputDurasiSakit').innerText = lamaSakit + (item.lama_sakit >= 3 ? ' (≥ 3 Hari)' : '');

        // Default Waktu Jemput: waktu sekarang YYYY-MM-DDTHH:mm
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('inputTanggalJemput').value = `${year}-${month}-${day}T${hours}:${minutes}`;

        // Default Nama Penjemput
        const wali = (item.santri && item.santri.orang_tua) ? (item.santri.orang_tua.nama_ayah || item.santri.orang_tua.nama_ibu || 'Orang Tua / Wali') : 'Orang Tua / Wali';
        document.getElementById('inputNamaPenjemput').value = item.nama_penjemput || wali;
        document.getElementById('inputEstimasiKembali').value = item.estimasi_kembali ? item.estimasi_kembali.substring(0, 10) : '';
        document.getElementById('inputCatatanPenjemputan').value = item.catatan_penjemputan || '';

        document.getElementById('modalJemputSantri').classList.remove('hidden');
    }

    function closeModalJemput() {
        document.getElementById('modalJemputSantri').classList.add('hidden');
    }

    function quickUpdateStatus(id, status) {
        if (confirm("Ubah status santri ini menjadi 'Sembuh'?")) {
            const form = document.getElementById('formQuickStatus');
            form.action = "{{ url('/admin/kesehatan') }}/" + id + "/status";
            document.getElementById('quickStatusVal').value = status;
            form.submit();
        }
    }

    function confirmDelete(id, name) {
        if (confirm("Hapus riwayat kesehatan santri '" + name + "'?")) {
            const form = document.getElementById('formDelete');
            form.action = "{{ url('/admin/kesehatan') }}/" + id;
            form.submit();
        }
    }
</script>
@endpush

@endsection
