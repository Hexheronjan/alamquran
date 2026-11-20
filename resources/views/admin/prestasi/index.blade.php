@extends('layouts.admin.app')

@section('title', 'Prestasi Santri')

@section('content')
<div class="bg-gray-100 min-h-screen py-6 px-4 lg:px-8">

    {{-- Alerts --}}
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
            <div>
                <h4 class="text-sm font-bold text-red-800">Perhatian!</h4>
                <ul class="text-xs text-red-700 list-disc list-inside mt-1 space-y-0.5">
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
                <i class="ti ti-trophy text-amber-500"></i> Dokumentasi Prestasi Santri
            </h1>
            <p class="text-xs text-gray-500 mt-1">Pencatatan rekam jejak juara & piagam penghargaan santri per tahun ajaran.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openTambahPrestasiModal()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white shadow-sm transition hover:opacity-95 cursor-pointer"
                    style="background-color: #d97706 !important;">
                <i class="ti ti-plus text-base"></i> Catat Prestasi Baru
            </button>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-200 flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl shrink-0">
                <i class="ti ti-trophy"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Prestasi</span>
                <span class="text-xl font-black text-gray-900">{{ number_format($totalPrestasi) }}</span>
                <span class="text-[10px] text-gray-500 block">Piala / Penghargaan</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-200 flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-2xl shrink-0">
                <i class="ti ti-book"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Prestasi Tahfidz</span>
                <span class="text-xl font-black text-teal-700">{{ number_format($prestasiTahfidz) }}</span>
                <span class="text-[10px] text-gray-500 block">MHQ & Hafalan</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-200 flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl shrink-0">
                <i class="ti ti-award"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Tingkat Nasional</span>
                <span class="text-xl font-black text-blue-700">{{ number_format($tingkatNasional) }}</span>
                <span class="text-[10px] text-gray-500 block">Nasional & Internasional</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-200 flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl shrink-0">
                <i class="ti ti-users"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Santri Juara</span>
                <span class="text-xl font-black text-purple-700">{{ number_format($santriBerprestasiCount) }}</span>
                <span class="text-[10px] text-gray-500 block">Santri Berprestasi</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="bg-white rounded-2xl p-5 shadow-xs border border-gray-200 mb-6">
        <form method="GET" action="{{ route('admin.prestasi.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-gray-600 mb-1">Tahun Ajaran</label>
                <select name="tahun_ajaran" class="w-full text-xs font-semibold rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                    <option value="all">Semua Tahun Ajaran</option>
                    @foreach($tahunAjaranList as $th)
                        <option value="{{ $th }}" {{ request('tahun_ajaran') === $th ? 'selected' : '' }}>{{ $th }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-600 mb-1">Kategori</label>
                <select name="kategori" class="w-full text-xs font-semibold rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                    <option value="all">Semua Kategori</option>
                    @foreach($kategoriList as $kKey => $kVal)
                        <option value="{{ $kKey }}" {{ request('kategori') === $kKey ? 'selected' : '' }}>{{ $kVal }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-600 mb-1">Tingkat</label>
                <select name="tingkat" class="w-full text-xs font-semibold rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                    <option value="all">Semua Tingkat</option>
                    @foreach($tingkatList as $tKey => $tVal)
                        <option value="{{ $tKey }}" {{ request('tingkat') === $tKey ? 'selected' : '' }}>{{ $tVal }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-600 mb-1">Cari (Santri / Lomba)</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama, NIS, atau lomba..."
                       class="w-full text-xs rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-amber-600 text-white font-bold rounded-xl text-xs hover:bg-amber-700 transition">
                    <i class="ti ti-filter"></i> Filter
                </button>
                <a href="{{ route('admin.prestasi.index') }}" class="px-3 py-2 bg-gray-100 text-gray-600 font-bold rounded-xl text-xs hover:bg-gray-200 transition" title="Reset">
                    <i class="ti ti-refresh"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-black text-sm text-gray-900 uppercase tracking-wider flex items-center gap-2">
                <i class="ti ti-list text-amber-600"></i> Rekap Prestasi Santri
            </h3>
            <span class="text-xs text-gray-500 font-semibold">Total {{ $prestasis->total() }} Data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-gray-50/80 text-gray-700 uppercase font-black text-[11px] tracking-wider border-b border-gray-200">
                    <tr>
                        <th class="py-3.5 px-4 text-center w-12">#</th>
                        <th class="py-3.5 px-4">Santri</th>
                        <th class="py-3.5 px-4">Prestasi & Kejuaraan</th>
                        <th class="py-3.5 px-4">Peringkat & Tingkat</th>
                        <th class="py-3.5 px-4">Kategori & TA</th>
                        <th class="py-3.5 px-4 text-center">Piagam</th>
                        <th class="py-3.5 px-4 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($prestasis as $index => $item)
                        <tr class="hover:bg-amber-50/30 transition">
                            <td class="py-3.5 px-4 text-center font-bold text-gray-400">
                                {{ $prestasis->firstItem() + $index }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-gray-900 text-sm">{{ $item->santri->nama_lengkap ?? '-' }}</div>
                                <div class="text-[11px] text-gray-500 font-medium">
                                    NIS: {{ $item->santri->nis ?? '-' }} • Kelas: {{ $item->santri->kelas ?? '-' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-black text-gray-900">{{ $item->nama_prestasi }}</div>
                                @if($item->penyelenggara)
                                    <div class="text-[11px] text-gray-500">Penyelenggara: {{ $item->penyelenggara }}</div>
                                @endif
                                @if($item->tanggal_perolehan)
                                    <div class="text-[10px] text-gray-400 mt-0.5">
                                        <i class="ti ti-calendar"></i> {{ $item->tanggal_perolehan->translatedFormat('d F Y') }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-black bg-amber-100 text-amber-900 border border-amber-300">
                                    {{ $item->peringkat }}
                                </span>
                                <div class="mt-1">
                                    <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 text-gray-700 uppercase">
                                        Tingkat {{ $item->getTingkatLabel() }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-800 border border-teal-200">
                                    {{ $item->getKategoriLabel() }}
                                </span>
                                <div class="text-[11px] text-gray-500 mt-1 font-semibold">TA {{ $item->tahun_ajaran }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($item->foto_piagam)
                                    <button type="button" onclick="previewPiagamModal('{{ asset('storage/' . $item->foto_piagam) }}', '{{ addslashes($item->nama_prestasi) }}')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-100 font-bold text-[11px] border border-teal-200">
                                        <i class="ti ti-photo"></i> Lihat
                                    </button>
                                @else
                                    <span class="text-gray-300 text-[11px] italic">Tidak ada foto</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" onclick="openEditPrestasiModal({{ json_encode($item) }})"
                                            class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center" title="Edit">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.prestasi.destroy', $item->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data prestasi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center" title="Hapus">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400">
                                <i class="ti ti-trophy-off text-4xl block mb-2 text-gray-300"></i>
                                Belum ada data prestasi yang tercatat dengan filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($prestasis->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $prestasis->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Tambah Prestasi -->
<div id="modalTambahPrestasi" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full overflow-hidden shadow-2xl animate-in fade-in zoom-in duration-200">
        <div class="p-5 text-white flex items-center justify-between" style="background-color: #d97706 !important;">
            <h3 class="font-black text-sm uppercase tracking-wider flex items-center gap-2">
                <i class="ti ti-trophy"></i> Catat Prestasi Santri Baru
            </h3>
            <button type="button" onclick="closeTambahPrestasiModal()" class="text-amber-100 hover:text-white p-1 rounded-lg">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.prestasi.store') }}" enctype="multipart/form-data" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-gray-700">Pilih Santri <span class="text-red-500">*</span></label>
                        <span id="tambahSantriSearchCount" class="text-[10px] text-gray-400 font-semibold">{{ $santris->count() }} Santri</span>
                    </div>

                    {{-- Input Pencarian Cepat Santri --}}
                    <div class="relative mb-2">
                        <i class="ti ti-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                        <input
                            type="text"
                            id="searchSantriTambahInput"
                            placeholder="Ketik nama atau NIS santri..."
                            oninput="filterSantriTambah(this.value)"
                            autocomplete="off"
                            class="w-full text-xs border border-amber-300 rounded-xl pl-8 pr-7 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 bg-amber-50/40 text-gray-800 placeholder-gray-400 font-medium"
                        />
                        <button type="button" onclick="clearSantriTambahSearch()" id="clearSantriTambahBtn" class="hidden absolute right-2.5 top-2 text-gray-400 hover:text-gray-600 text-xs p-0.5">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>

                    <select name="santri_id" id="tambah_santri_id" required class="w-full text-xs font-semibold rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                        <option value="">-- Pilih Santri --</option>
                        @foreach($santris as $s)
                            <option value="{{ $s->id }}" data-nama="{{ strtolower($s->nama_lengkap) }}" data-nis="{{ strtolower($s->nis ?? '') }}" data-kelas="{{ strtolower($s->kelas ?? '') }}">
                                {{ $s->nama_lengkap }} (NIS: {{ $s->nis ?? '-' }} / {{ $s->kelas ?? 'Kelas -' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tahun Ajaran <span class="text-red-500">*</span></label>
                    <input type="text" name="tahun_ajaran" value="{{ $tahunAjaranList[0] ?? date('Y') . '/' . (date('Y')+1) }}" required
                           placeholder="Contoh: 2026/2027" class="w-full text-xs rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Nama Prestasi / Kejuaraan <span class="text-red-500">*</span></label>
                <input type="text" name="nama_prestasi" required placeholder="Contoh: Juara 1 MHQ 10 Juz Tingkat Kabupaten Pasuruan"
                       class="w-full text-xs rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Peringkat <span class="text-red-500">*</span></label>
                    <input type="text" name="peringkat" required placeholder="Juara 1 / Harapan 2 / Medali Emas"
                           class="w-full text-xs rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="kategori" id="tambah_kategori" onchange="toggleKategoriLainnya(this.value, 'box_kategori_lainnya')" required class="w-full text-xs font-semibold rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                        @foreach($kategoriList as $kKey => $kVal)
                            <option value="{{ $kKey }}">{{ $kVal }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tingkat <span class="text-red-500">*</span></label>
                    <select name="tingkat" required class="w-full text-xs font-semibold rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                        @foreach($tingkatList as $tKey => $tVal)
                            <option value="{{ $tKey }}">{{ $tVal }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Input Kategori Lainnya (Otomatis Muncul jika Kategori = Lainnya) -->
            <div id="box_kategori_lainnya" class="hidden p-3 bg-amber-50/60 rounded-2xl border border-amber-200 animate-in fade-in duration-200">
                <label class="block text-xs font-bold text-amber-900 mb-1 flex items-center gap-1.5">
                    <i class="ti ti-pencil text-amber-600"></i> Sebutkan Nama Kategori Prestasi Lainnya: <span class="text-red-500">*</span>
                </label>
                <input type="text" name="kategori_lainnya" id="input_kategori_lainnya" placeholder="Contoh: Robotik & Otomasi / Pramuka / Desain Grafis / KIR"
                       class="w-full text-xs rounded-xl border-amber-300 bg-white focus:ring-amber-500 focus:border-amber-500">
                <p class="text-[11px] text-amber-800/80 mt-1">Nama kategori ini akan otomatis tersimpan dan ditampilkan pada portofolio prestasi santri.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Penyelenggara</label>
                    <input type="text" name="penyelenggara" placeholder="Contoh: Kemenag Jatim / Ponpes Sidogiri"
                           class="w-full text-xs rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Perolehan</label>
                    <input type="date" name="tanggal_perolehan" value="{{ date('Y-m-d') }}"
                           class="w-full text-xs rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Foto Piagam / Sertifikat / Medali (Maks 4MB)</label>
                <input type="file" name="foto_piagam" accept="image/*"
                       class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Catatan / Deskripsi Tambahan</label>
                <textarea name="deskripsi" rows="3" placeholder="Keterangan singkat mengenai prestasi ini..."
                          class="w-full text-xs rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeTambahPrestasiModal()" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-xl text-xs hover:bg-gray-200">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 text-white font-bold rounded-xl text-xs shadow-md transition hover:opacity-95"
                        style="background-color: #d97706 !important;">
                    <i class="ti ti-check"></i> Simpan Prestasi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Prestasi -->
<div id="modalEditPrestasi" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full overflow-hidden shadow-2xl animate-in fade-in zoom-in duration-200">
        <div class="p-5 text-white flex items-center justify-between" style="background-color: #0284c7 !important;">
            <h3 class="font-black text-sm uppercase tracking-wider flex items-center gap-2">
                <i class="ti ti-edit"></i> Edit Prestasi Santri
            </h3>
            <button type="button" onclick="closeEditPrestasiModal()" class="text-sky-100 hover:text-white p-1 rounded-lg">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>
        <form id="formEditPrestasi" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-gray-700">Pilih Santri <span class="text-red-500">*</span></label>
                        <span id="editSantriSearchCount" class="text-[10px] text-gray-400 font-semibold">{{ $santris->count() }} Santri</span>
                    </div>

                    {{-- Input Pencarian Cepat Santri Edit --}}
                    <div class="relative mb-2">
                        <i class="ti ti-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                        <input
                            type="text"
                            id="searchSantriEditInput"
                            placeholder="Ketik nama atau NIS santri..."
                            oninput="filterSantriEdit(this.value)"
                            autocomplete="off"
                            class="w-full text-xs border border-sky-300 rounded-xl pl-8 pr-7 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 bg-sky-50/40 text-gray-800 placeholder-gray-400 font-medium"
                        />
                        <button type="button" onclick="clearSantriEditSearch()" id="clearSantriEditBtn" class="hidden absolute right-2.5 top-2 text-gray-400 hover:text-gray-600 text-xs p-0.5">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>

                    <select name="santri_id" id="edit_santri_id" required class="w-full text-xs font-semibold rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
                        @foreach($santris as $s)
                            <option value="{{ $s->id }}" data-nama="{{ strtolower($s->nama_lengkap) }}" data-nis="{{ strtolower($s->nis ?? '') }}" data-kelas="{{ strtolower($s->kelas ?? '') }}">
                                {{ $s->nama_lengkap }} (NIS: {{ $s->nis ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tahun Ajaran <span class="text-red-500">*</span></label>
                    <input type="text" name="tahun_ajaran" id="edit_tahun_ajaran" required
                           class="w-full text-xs rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Nama Prestasi / Kejuaraan <span class="text-red-500">*</span></label>
                <input type="text" name="nama_prestasi" id="edit_nama_prestasi" required
                       class="w-full text-xs rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Peringkat <span class="text-red-500">*</span></label>
                    <input type="text" name="peringkat" id="edit_peringkat" required
                           class="w-full text-xs rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="kategori" id="edit_kategori" onchange="toggleKategoriLainnya(this.value, 'box_edit_kategori_lainnya')" required class="w-full text-xs font-semibold rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
                        @foreach($kategoriList as $kKey => $kVal)
                            <option value="{{ $kKey }}">{{ $kVal }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tingkat <span class="text-red-500">*</span></label>
                    <select name="tingkat" id="edit_tingkat" required class="w-full text-xs font-semibold rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
                        @foreach($tingkatList as $tKey => $tVal)
                            <option value="{{ $tKey }}">{{ $tVal }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Input Kategori Lainnya (Otomatis Muncul jika Kategori = Lainnya pada Edit) -->
            <div id="box_edit_kategori_lainnya" class="hidden p-3 bg-sky-50/60 rounded-2xl border border-sky-200 animate-in fade-in duration-200">
                <label class="block text-xs font-bold text-sky-900 mb-1 flex items-center gap-1.5">
                    <i class="ti ti-pencil text-sky-600"></i> Sebutkan Nama Kategori Prestasi Lainnya: <span class="text-red-500">*</span>
                </label>
                <input type="text" name="kategori_lainnya" id="edit_kategori_lainnya" placeholder="Contoh: Robotik & Otomasi / Pramuka / Desain Grafis / KIR"
                       class="w-full text-xs rounded-xl border-sky-300 bg-white focus:ring-sky-500 focus:border-sky-500">
                <p class="text-[11px] text-sky-800/80 mt-1">Nama kategori ini akan otomatis tersimpan dan ditampilkan pada portofolio prestasi santri.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Penyelenggara</label>
                    <input type="text" name="penyelenggara" id="edit_penyelenggara"
                           class="w-full text-xs rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Perolehan</label>
                    <input type="date" name="tanggal_perolehan" id="edit_tanggal_perolehan"
                           class="w-full text-xs rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Ganti Foto Piagam / Sertifikat (Opsional)</label>
                <input type="file" name="foto_piagam" accept="image/*"
                       class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Catatan / Deskripsi Tambahan</label>
                <textarea name="deskripsi" id="edit_deskripsi" rows="3"
                          class="w-full text-xs rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeEditPrestasiModal()" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-xl text-xs hover:bg-gray-200">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 text-white font-bold rounded-xl text-xs shadow-md transition hover:opacity-95"
                        style="background-color: #0284c7 !important;">
                    <i class="ti ti-check"></i> Perbarui Prestasi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Preview Piagam -->
<div id="modalPreviewPiagam" class="fixed inset-0 z-50 hidden bg-black/75 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full overflow-hidden shadow-2xl animate-in fade-in zoom-in duration-200">
        <div class="p-4 bg-gray-900 text-white flex items-center justify-between">
            <h3 id="previewPiagamTitle" class="font-bold text-xs truncate max-w-md">Piagam Penghargaan</h3>
            <button type="button" onclick="closePreviewPiagamModal()" class="text-gray-400 hover:text-white p-1">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>
        <div class="p-4 flex items-center justify-center bg-gray-100 max-h-[75vh] overflow-auto">
            <img id="previewPiagamImg" src="" alt="Piagam" class="max-h-[70vh] rounded-xl shadow-md object-contain">
        </div>
        <div class="p-3 bg-white border-t border-gray-100 text-right">
            <button type="button" onclick="closePreviewPiagamModal()" class="px-4 py-1.5 bg-gray-200 text-gray-800 text-xs font-bold rounded-xl hover:bg-gray-300">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    function filterSantriTambah(keyword) {
        const query = keyword.toLowerCase().trim();
        const select = document.getElementById('tambah_santri_id');
        const clearBtn = document.getElementById('clearSantriTambahBtn');
        const countSpan = document.getElementById('tambahSantriSearchCount');
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

    function clearSantriTambahSearch() {
        const input = document.getElementById('searchSantriTambahInput');
        if (input) {
            input.value = '';
            filterSantriTambah('');
            input.focus();
        }
    }

    function filterSantriEdit(keyword) {
        const query = keyword.toLowerCase().trim();
        const select = document.getElementById('edit_santri_id');
        const clearBtn = document.getElementById('clearSantriEditBtn');
        const countSpan = document.getElementById('editSantriSearchCount');
        const options = select.getElementsByTagName('option');

        if (query.length > 0) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }

        let matchCount = 0;
        let firstMatch = null;

        for (let i = 0; i < options.length; i++) {
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
            countSpan.textContent = options.length + ' Santri';
        }
    }

    function clearSantriEditSearch() {
        const input = document.getElementById('searchSantriEditInput');
        if (input) {
            input.value = '';
            filterSantriEdit('');
            input.focus();
        }
    }

    function toggleKategoriLainnya(val, targetBoxId) {
        const box = document.getElementById(targetBoxId);
        if (!box) return;
        if (val === 'lainnya') {
            box.classList.remove('hidden');
            const input = box.querySelector('input');
            if (input) input.focus();
        } else {
            box.classList.add('hidden');
        }
    }

    function openTambahPrestasiModal() {
        clearSantriTambahSearch();
        const katSelect = document.getElementById('tambah_kategori');
        if (katSelect) {
            katSelect.value = 'tahfidz';
            toggleKategoriLainnya('tahfidz', 'box_kategori_lainnya');
        }
        const inputLainnya = document.getElementById('input_kategori_lainnya');
        if (inputLainnya) inputLainnya.value = '';

        document.getElementById('modalTambahPrestasi').classList.remove('hidden');
    }
    function closeTambahPrestasiModal() {
        document.getElementById('modalTambahPrestasi').classList.add('hidden');
    }

    function openEditPrestasiModal(item) {
        clearSantriEditSearch();
        document.getElementById('formEditPrestasi').action = `/admin/prestasi/${item.id}`;
        document.getElementById('edit_santri_id').value = item.santri_id;
        document.getElementById('edit_tahun_ajaran').value = item.tahun_ajaran;
        document.getElementById('edit_nama_prestasi').value = item.nama_prestasi;
        document.getElementById('edit_peringkat').value = item.peringkat;

        // Check if category is standard or custom
        const standardCategories = ['tahfidz', 'akademik', 'bahasa', 'seni_kaligrafi', 'pidato_dai', 'olahraga'];
        if (standardCategories.includes(item.kategori)) {
            document.getElementById('edit_kategori').value = item.kategori;
            document.getElementById('box_edit_kategori_lainnya').classList.add('hidden');
            document.getElementById('edit_kategori_lainnya').value = '';
        } else {
            document.getElementById('edit_kategori').value = 'lainnya';
            document.getElementById('box_edit_kategori_lainnya').classList.remove('hidden');
            document.getElementById('edit_kategori_lainnya').value = (item.kategori === 'lainnya' ? '' : item.kategori);
        }

        document.getElementById('edit_tingkat').value = item.tingkat;
        document.getElementById('edit_penyelenggara').value = item.penyelenggara || '';
        document.getElementById('edit_tanggal_perolehan').value = item.tanggal_perolehan ? item.tanggal_perolehan.substring(0, 10) : '';
        document.getElementById('edit_deskripsi').value = item.deskripsi || '';

        document.getElementById('modalEditPrestasi').classList.remove('hidden');
    }
    function closeEditPrestasiModal() {
        document.getElementById('modalEditPrestasi').classList.add('hidden');
    }

    function previewPiagamModal(src, title) {
        document.getElementById('previewPiagamImg').src = src;
        document.getElementById('previewPiagamTitle').innerText = title;
        document.getElementById('modalPreviewPiagam').classList.remove('hidden');
    }
    function closePreviewPiagamModal() {
        document.getElementById('modalPreviewPiagam').classList.add('hidden');
    }
</script>
@endsection
