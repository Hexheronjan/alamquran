@extends('layouts.admin.app')

@section('title', 'Kedisiplinan & Pelanggaran Santri')

@section('content')
<div class="bg-gray-100 min-h-screen py-6 px-4 lg:px-8">

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="mb-5 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-xl shadow-xs flex items-start gap-3">
            <i class="ti ti-circle-check text-2xl text-green-600 shrink-0 mt-0.5"></i>
            <div>
                <h4 class="text-sm font-bold text-green-800">Berhasil!</h4>
                <p class="text-xs text-green-700 mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-xs flex items-start gap-3">
            <i class="ti ti-alert-triangle text-2xl text-red-600 shrink-0 mt-0.5"></i>
            <div class="text-xs text-red-700">
                <h4 class="text-sm font-bold text-red-800">Terdapat Kesalahan Input:</h4>
                <ul class="list-disc list-inside mt-1 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <i class="ti ti-gavel text-rose-600"></i> Kedisiplinan & Pelanggaran Santri
            </h1>
            <p class="text-xs text-gray-500 mt-1">Pencatatan pelanggaran tata tertib, pembinaan, sanksi/konsekuensi, dan ketertiban santri.</p>
        </div>
        <button type="button" onclick="openTambahModal()"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white shadow-sm transition hover:opacity-95 cursor-pointer"
                style="background-color: #e11d48 !important;">
            <i class="ti ti-plus text-base"></i> Catat Pelanggaran
        </button>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span class="font-bold uppercase">Total Catatan</span>
                <i class="ti ti-clipboard-list text-xl text-blue-600"></i>
            </div>
            <div class="text-2xl font-black text-gray-900 font-mono">{{ number_format($totalPelanggaran) }}</div>
            <div class="text-[11px] text-gray-400 mt-0.5">Seluruh riwayat tercatat</div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span class="font-bold uppercase">Dalam Pembinaan</span>
                <i class="ti ti-hourglass-low text-xl text-amber-600"></i>
            </div>
            <div class="text-2xl font-black text-amber-700 font-mono">{{ number_format($sedangDijalani) }}</div>
            <div class="text-[11px] text-amber-600 font-semibold mt-0.5">Sedang menjalani sanksi</div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span class="font-bold uppercase">Selesai Dibina</span>
                <i class="ti ti-circle-check text-xl text-emerald-600"></i>
            </div>
            <div class="text-2xl font-black text-emerald-700 font-mono">{{ number_format($selesaiPembinaan) }}</div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-0.5">Sanksi tuntas dijalani</div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span class="font-bold uppercase">Kasus Berat</span>
                <i class="ti ti-alert-octagon text-xl text-rose-600"></i>
            </div>
            <div class="text-2xl font-black text-rose-700 font-mono">{{ number_format($sanksiBeratCount) }}</div>
            <div class="text-[11px] text-rose-600 font-semibold mt-0.5">Skorsing / Botak / DO</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl shadow-xs border border-gray-200 p-4 mb-6 text-xs">
        <form method="GET" action="{{ route('admin.pelanggaran.index') }}" class="flex flex-wrap items-center gap-3">
            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Kategori</label>
                <select name="kategori" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-rose-500">
                    <option value="all" {{ request('kategori') === 'all' || !request('kategori') ? 'selected' : '' }}>Semua Kategori</option>
                    @foreach($kategoriList as $k => $label)
                        <option value="{{ $k }}" {{ request('kategori') === $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Status Pembinaan</label>
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-rose-500">
                    <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                    <option value="sedang_dijalani" {{ request('status') === 'sedang_dijalani' ? 'selected' : '' }}>Sedang Dijalani</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="dibatalkan" {{ request('status') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            <div class="flex-1 min-w-[220px]">
                <label class="block font-bold text-gray-700 uppercase mb-1">Cari Pelanggaran / Santri</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama santri, pelanggaran, atau sanksi..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-rose-500">
            </div>

            <div class="pt-4 flex gap-1.5">
                <button type="submit" class="px-4 py-2 rounded-lg font-bold text-xs cursor-pointer text-white shadow-xs"
                        style="background-color: #e11d48 !important;">
                    <i class="ti ti-filter"></i> Filter
                </button>
                <a href="{{ route('admin.pelanggaran.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Pelanggaran -->
    <div class="bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-list text-rose-600"></i> Riwayat Pelanggaran & Tindakan Santri
            </h2>
            <span class="text-xs bg-gray-100 text-gray-700 px-3 py-1 rounded-full font-bold">
                {{ $pelanggarans->total() }} Data
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Santri</th>
                        <th class="px-4 py-3.5 text-center">Tingkat</th>
                        <th class="px-5 py-3.5">Pelanggaran</th>
                        <th class="px-5 py-3.5">Konsekuensi / Hukuman</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5">Petugas</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($pelanggarans as $p)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-4 whitespace-nowrap text-gray-600 font-medium">
                                {{ $p->tanggal ? $p->tanggal->translatedFormat('d M Y') : '-' }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-bold text-gray-900">{{ $p->santri->nama_lengkap ?? '-' }}</div>
                                <div class="text-[11px] text-gray-400">NIS: {{ $p->santri->nis ?? '-' }} • {{ $p->santri->kelas_label ?? 'Kelas -' }}</div>
                            </td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                @if($p->kategori === 'berat')
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-red-100 text-red-800 border border-red-200">
                                        BERAT
                                    </span>
                                @elseif($p->kategori === 'sedang')
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-amber-100 text-amber-800 border border-amber-200">
                                        SEDANG
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-blue-100 text-blue-800 border border-blue-200">
                                        RINGAN
                                    </span>
                                @endif
                                @if($p->poin > 0)
                                    <div class="text-[10px] text-gray-500 font-mono mt-0.5">+{{ $p->poin }} Poin</div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-bold text-gray-800">{{ $p->nama_pelanggaran }}</div>
                                @if($p->catatan)
                                    <div class="text-[11px] text-gray-500 mt-0.5 line-clamp-1" title="{{ $p->catatan }}">{{ $p->catatan }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-lg inline-block">
                                    {{ $p->konsekuensi }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                @if($p->status_pembinaan === 'selesai')
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        ✓ Selesai
                                    </span>
                                @elseif($p->status_pembinaan === 'dibatalkan')
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-gray-100 text-gray-600">
                                        Dibatalkan
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-amber-100 text-amber-800 border border-amber-200">
                                        Sedang Dijalani
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-gray-600 whitespace-nowrap">
                                <div class="font-semibold">{{ $p->petugas_ustadz ?: ($p->admin->name ?? 'Admin') }}</div>
                            </td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- Toggle Status --}}
                                    @if($p->status_pembinaan === 'sedang_dijalani')
                                        <form action="{{ route('admin.pelanggaran.status', $p->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status_pembinaan" value="selesai">
                                            <button type="submit" class="px-2 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition cursor-pointer" title="Tandai Selesai Menjalani Sanksi">
                                                <i class="ti ti-check"></i> Selesai
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Edit Button --}}
                                    <button type="button"
                                            onclick='openEditModal({{ json_encode($p) }})'
                                            class="px-2 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 transition cursor-pointer" title="Edit Data">
                                        <i class="ti ti-edit"></i>
                                    </button>

                                    {{-- Delete Button --}}
                                    <form action="{{ route('admin.pelanggaran.destroy', $p->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan pelanggaran ananda {{ addslashes($p->santri->nama_lengkap ?? '') }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 rounded-lg text-[11px] font-bold bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition cursor-pointer" title="Hapus">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                <i class="ti ti-mood-smile text-4xl text-gray-300 mb-2 block"></i>
                                Alhamdulillah, tidak ada catatan pelanggaran santri pada filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pelanggarans->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $pelanggarans->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Pelanggaran -->
<div id="modalTambah" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-5 py-3.5 border-b border-rose-700 flex items-center justify-between text-white shrink-0" style="background-color: #e11d48;">
            <div class="flex items-center gap-2">
                <i class="ti ti-gavel text-lg"></i>
                <h3 class="text-sm font-bold">Catat Pelanggaran & Sanksi Santri</h3>
            </div>
            <button type="button" onclick="closeTambahModal()" class="cursor-pointer p-1 text-white/80 hover:text-white transition">
                <i class="ti ti-x text-base"></i>
            </button>
        </div>

        <form action="{{ route('admin.pelanggaran.store') }}" method="POST" enctype="multipart/form-data" class="overflow-y-auto p-5 space-y-3.5 text-xs">
            @csrf
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block font-bold text-gray-800 uppercase">Pilih Santri <span class="text-red-500">*</span></label>
                    <span id="pelanggaranSearchCount" class="text-[10px] text-gray-400 font-semibold">{{ $santris->count() }} Santri</span>
                </div>

                {{-- Input Pencarian Cepat Santri --}}
                <div class="relative mb-2">
                    <i class="ti ti-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                    <input
                        type="text"
                        id="searchSantriPelanggaranInput"
                        placeholder="Ketik nama atau NIS santri..."
                        oninput="filterSantriPelanggaran(this.value)"
                        autocomplete="off"
                        class="w-full text-xs border border-rose-300 rounded-lg pl-8 pr-7 py-2 focus:outline-none focus:ring-2 focus:ring-rose-500 bg-rose-50/40 text-gray-800 placeholder-gray-400 font-medium"
                    />
                    <button type="button" onclick="clearSantriPelanggaranSearch()" id="clearSantriPelanggaranBtn" class="hidden absolute right-2.5 top-2 text-gray-400 hover:text-gray-600 text-xs p-0.5">
                        <i class="ti ti-x"></i>
                    </button>
                </div>

                <select name="santri_id" id="tambah_pelanggaran_santri_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-rose-500">
                    <option value="">-- Pilih Santri --</option>
                    @foreach($santris as $s)
                        <option value="{{ $s->id }}" data-nama="{{ strtolower($s->nama_lengkap) }}" data-nis="{{ strtolower($s->nis ?? '') }}" data-kelas="{{ strtolower($s->kelas_label ?? '') }}">
                            {{ $s->nama_lengkap }} ({{ $s->kelas_label ?? 'Kelas -' }} - NIS: {{ $s->nis ?? '-' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-800 uppercase mb-1">Tanggal Kejadian <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-rose-500">
                </div>
                <div>
                    <label class="block font-bold text-gray-800 uppercase mb-1">Tingkat Pelanggaran <span class="text-red-500">*</span></label>
                    <select name="kategori" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-rose-500">
                        <option value="ringan">Pelanggaran Ringan</option>
                        <option value="sedang" selected>Pelanggaran Sedang</option>
                        <option value="berat">Pelanggaran Berat</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-800 uppercase mb-1">Nama / Bentuk Pelanggaran <span class="text-red-500">*</span></label>
                <input type="text" name="nama_pelanggaran" placeholder="Contoh: Terlambat shalat berjamaah 3x, Membawa barang terlarang, dll." required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-rose-500">
            </div>

            <div>
                <label class="block font-bold text-gray-800 uppercase mb-1">Konsekuensi / Hukuman <span class="text-red-500">*</span></label>
                <input type="text" name="konsekuensi" id="inputKonsekuensi" list="listKonsekuensi" placeholder="Ketik atau pilih konsekuensi sanksi..." required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-bold text-rose-800 focus:ring-2 focus:ring-rose-500">
                <datalist id="listKonsekuensi">
                    @foreach($konsekuensiPresets as $preset)
                        <option value="{{ $preset }}"></option>
                    @endforeach
                </datalist>
                <span class="text-[11px] text-gray-400 mt-1 block">Bisa pilih: Cukur Botak, Diskorsing, Dikeluarkan, Ta'zir Hafalan, dsb.</span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-800 uppercase mb-1">Status Pembinaan</label>
                    <select name="status_pembinaan" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-rose-500">
                        <option value="sedang_dijalani" selected>Sedang Dijalani</option>
                        <option value="selesai">Selesai</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-gray-800 uppercase mb-1">Poin Pelanggaran</label>
                    <input type="number" name="poin" value="0" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-mono focus:ring-2 focus:ring-rose-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-800 uppercase mb-1">Ustadz Penindak / Saksi</label>
                    <input type="text" name="petugas_ustadz" placeholder="Nama Ustadz / Bag. Keamanan"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-rose-500">
                </div>
                <div>
                    <label class="block font-bold text-gray-800 uppercase mb-1">Foto Bukti (Opsional)</label>
                    <input type="file" name="foto_bukti" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-800 uppercase mb-1">Catatan Tambahan / Kronologi</label>
                <textarea name="catatan" rows="2" placeholder="Tuliskan keterangan detail kronologi atau perjanjian santri..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-rose-500"></textarea>
            </div>

            <div class="pt-3 border-t border-gray-200 flex justify-end gap-2 shrink-0">
                <button type="button" onclick="closeTambahModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 text-white font-bold rounded-xl cursor-pointer shadow-sm hover:opacity-95"
                        style="background-color: #e11d48 !important;">
                    Simpan Pelanggaran
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pelanggaran -->
<div id="modalEdit" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-5 py-3.5 border-b border-amber-600 flex items-center justify-between text-white shrink-0" style="background-color: #d97706;">
            <div class="flex items-center gap-2">
                <i class="ti ti-edit text-lg"></i>
                <h3 class="text-sm font-bold">Edit Catatan Pelanggaran</h3>
            </div>
            <button type="button" onclick="closeEditModal()" class="cursor-pointer p-1 text-white/80 hover:text-white transition">
                <i class="ti ti-x text-base"></i>
            </button>
        </div>

        <form id="formEdit" method="POST" enctype="multipart/form-data" class="overflow-y-auto p-5 space-y-3.5 text-xs">
            @csrf
            @method('PUT')

            <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                <span class="text-gray-500 font-semibold block text-[11px]">Nama Santri:</span>
                <strong id="editNamaSantri" class="text-gray-900 text-sm"></strong>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-800 uppercase mb-1">Tanggal Kejadian <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" id="editTanggal" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block font-bold text-gray-800 uppercase mb-1">Tingkat Pelanggaran <span class="text-red-500">*</span></label>
                    <select name="kategori" id="editKategori" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-amber-500">
                        <option value="ringan">Pelanggaran Ringan</option>
                        <option value="sedang">Pelanggaran Sedang</option>
                        <option value="berat">Pelanggaran Berat</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-800 uppercase mb-1">Nama / Bentuk Pelanggaran <span class="text-red-500">*</span></label>
                <input type="text" name="nama_pelanggaran" id="editNamaPelanggaran" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-amber-500">
            </div>

            <div>
                <label class="block font-bold text-gray-800 uppercase mb-1">Konsekuensi / Hukuman <span class="text-red-500">*</span></label>
                <input type="text" name="konsekuensi" id="editKonsekuensi" list="listKonsekuensi" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-bold text-amber-900 focus:ring-2 focus:ring-amber-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-800 uppercase mb-1">Status Pembinaan</label>
                    <select name="status_pembinaan" id="editStatusPembinaan" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-amber-500">
                        <option value="sedang_dijalani">Sedang Dijalani</option>
                        <option value="selesai">Selesai</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-gray-800 uppercase mb-1">Poin Pelanggaran</label>
                    <input type="number" name="poin" id="editPoin" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-mono focus:ring-2 focus:ring-amber-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-800 uppercase mb-1">Ustadz Penindak</label>
                    <input type="text" name="petugas_ustadz" id="editPetugas"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block font-bold text-gray-800 uppercase mb-1">Ubah Foto Bukti</label>
                    <input type="file" name="foto_bukti" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-800 uppercase mb-1">Catatan Tambahan</label>
                <textarea name="catatan" id="editCatatan" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-amber-500"></textarea>
            </div>

            <div class="pt-3 border-t border-gray-200 flex justify-end gap-2 shrink-0">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 text-white font-bold rounded-xl cursor-pointer shadow-sm hover:opacity-95"
                        style="background-color: #d97706 !important;">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function filterSantriPelanggaran(keyword) {
    const query = keyword.toLowerCase().trim();
    const select = document.getElementById('tambah_pelanggaran_santri_id');
    const clearBtn = document.getElementById('clearSantriPelanggaranBtn');
    const countSpan = document.getElementById('pelanggaranSearchCount');
    const options = select.getElementsByTagName('option');

    if (query.length > 0) {
        clearBtn.classList.remove('hidden');
    } else {
        clearBtn.classList.add('hidden');
    }

    let matchCount = 0;
    let firstMatch = null;

    for (let i = 1; i < options.length; i++) {
        const opt = options[i];
        const nama = opt.getAttribute('data-nama') || '';
        const nis = opt.getAttribute('data-nis') || '';
        const kelas = opt.getAttribute('data-kelas') || '';

        if (query === '' || nama.includes(query) || nis.includes(query) || kelas.includes(query)) {
            opt.style.display = '';
            opt.disabled = false;
            matchCount++;
            if (!firstMatch) {
                firstMatch = opt;
            }
        } else {
            opt.style.display = 'none';
            opt.disabled = true;
        }
    }

    if (query !== '') {
        countSpan.textContent = matchCount + ' ditemukan';
        if (firstMatch) {
            select.value = firstMatch.value;
        }
    } else {
        countSpan.textContent = (options.length - 1) + ' Santri';
    }
}

function clearSantriPelanggaranSearch() {
    const input = document.getElementById('searchSantriPelanggaranInput');
    if (input) {
        input.value = '';
        filterSantriPelanggaran('');
        input.focus();
    }
}

function openTambahModal() {
    clearSantriPelanggaranSearch();
    document.getElementById('modalTambah').classList.remove('hidden');
}
function closeTambahModal() {
    document.getElementById('modalTambah').classList.add('hidden');
}

function openEditModal(data) {
    document.getElementById('formEdit').action = '/admin/pelanggaran/' + data.id;
    document.getElementById('editNamaSantri').innerText = data.santri ? data.santri.nama_lengkap : '-';
    document.getElementById('editTanggal').value = data.tanggal ? data.tanggal.substring(0, 10) : '';
    document.getElementById('editKategori').value = data.kategori || 'sedang';
    document.getElementById('editNamaPelanggaran').value = data.nama_pelanggaran || '';
    document.getElementById('editKonsekuensi').value = data.konsekuensi || '';
    document.getElementById('editStatusPembinaan').value = data.status_pembinaan || 'sedang_dijalani';
    document.getElementById('editPoin').value = data.poin || 0;
    document.getElementById('editPetugas').value = data.petugas_ustadz || '';
    document.getElementById('editCatatan').value = data.catatan || '';
    document.getElementById('modalEdit').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('modalEdit').classList.add('hidden');
}
</script>
@endsection
