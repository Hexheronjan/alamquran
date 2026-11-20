@extends('layouts.admin.app')

@section('title', 'Jadwal & Kegiatan Santri')

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

        {{-- Header & Action Button --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="ti ti-calendar-event text-emerald-600"></i> Manajemen Kegiatan Santri (24 Jam)
                </h1>
                <p class="text-gray-600 text-sm mt-1">
                    Atur jadwal kegiatan santri dari bangun tidur hingga istirahat malam per hari (Senin - Minggu) dan per kelas. Otomatis tampil di halaman utama akun wali.
                </p>
            </div>
            <div>
                <button type="button"
                        onclick="openModalTambah()"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl shadow-sm transition cursor-pointer"
                        style="background-color: #16a34a !important; color: #ffffff !important;">
                    <i class="ti ti-plus text-lg"></i>
                    + Tambah Kegiatan
                </button>
            </div>
        </div>

        {{-- Stat Cards / Filter Tabs --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('admin.kegiatan.index', ['tipe' => 'semua', 'hari' => $hari, 'kelas' => $kelas]) }}"
               class="bg-white rounded-xl p-4 shadow-sm border transition {{ $tipe === 'semua' ? 'border-purple-500 ring-2 ring-purple-200' : 'border-gray-200 hover:border-gray-300' }}">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span class="font-bold uppercase">Semua Kegiatan</span>
                    <i class="ti ti-list text-xl text-purple-600"></i>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ $totalRutin + $totalKhusus }}</div>
                <div class="text-[11px] text-gray-400 mt-0.5">Total jadwal terdaftar</div>
            </a>

            <a href="{{ route('admin.kegiatan.index', ['tipe' => 'rutin', 'hari' => $hari, 'kelas' => $kelas]) }}"
               class="bg-white rounded-xl p-4 shadow-sm border transition {{ $tipe === 'rutin' ? 'border-emerald-500 ring-2 ring-emerald-200' : 'border-gray-200 hover:border-gray-300' }}">
                <div class="flex items-center justify-between text-xs text-emerald-700 mb-1">
                    <span class="font-bold uppercase">Rutin Harian (24 Jam)</span>
                    <i class="ti ti-repeat text-xl text-emerald-600"></i>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ $totalRutin }}</div>
                <div class="text-[11px] text-gray-400 mt-0.5">Jadwal baku santri per hari/kelas</div>
            </a>

            <a href="{{ route('admin.kegiatan.index', ['tipe' => 'khusus', 'hari' => $hari, 'kelas' => $kelas]) }}"
               class="bg-white rounded-xl p-4 shadow-sm border transition {{ $tipe === 'khusus' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-200 hover:border-gray-300' }}">
                <div class="flex items-center justify-between text-xs text-blue-700 mb-1">
                    <span class="font-bold uppercase">Kegiatan Khusus / Insidental</span>
                    <i class="ti ti-star text-xl text-blue-600"></i>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ $totalKhusus }}</div>
                <div class="text-[11px] text-gray-400 mt-0.5">Agenda pada tanggal tertentu</div>
            </a>
        </div>

        {{-- Filter Bar Hari & Kelas --}}
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200">
            <form method="GET" action="{{ route('admin.kegiatan.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                <input type="hidden" name="tipe" value="{{ $tipe }}">

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Filter Hari:</label>
                    <select name="hari" onchange="this.form.submit()" class="w-full text-xs border border-gray-300 rounded-xl px-3 py-2 bg-gray-50 focus:bg-white focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="semua" {{ $hari === 'semua' ? 'selected' : '' }}>Semua Hari (Senin - Minggu)</option>
                        <option value="senin" {{ $hari === 'senin' ? 'selected' : '' }}>Senin</option>
                        <option value="selasa" {{ $hari === 'selasa' ? 'selected' : '' }}>Selasa</option>
                        <option value="rabu" {{ $hari === 'rabu' ? 'selected' : '' }}>Rabu</option>
                        <option value="kamis" {{ $hari === 'kamis' ? 'selected' : '' }}>Kamis</option>
                        <option value="jumat" {{ $hari === 'jumat' ? 'selected' : '' }}>Jumat</option>
                        <option value="sabtu" {{ $hari === 'sabtu' ? 'selected' : '' }}>Sabtu</option>
                        <option value="minggu" {{ $hari === 'minggu' ? 'selected' : '' }}>Minggu</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Filter Kelas:</label>
                    <select name="kelas" onchange="this.form.submit()" class="w-full text-xs border border-gray-300 rounded-xl px-3 py-2 bg-gray-50 focus:bg-white focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="semua" {{ $kelas === 'semua' ? 'selected' : '' }}>Semua Kelas (1, 2, 3)</option>
                        <option value="1" {{ $kelas === '1' ? 'selected' : '' }}>Kelas 1</option>
                        <option value="2" {{ $kelas === '2' ? 'selected' : '' }}>Kelas 2</option>
                        <option value="3" {{ $kelas === '3' ? 'selected' : '' }}>Kelas 3</option>
                    </select>
                </div>

                <div class="sm:col-span-2 flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                        <i class="ti ti-filter"></i> Terapkan Filter
                    </button>
                    @if($hari !== 'semua' || $kelas !== 'semua' || $tipe !== 'semua')
                        <a href="{{ route('admin.kegiatan.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-800">Daftar Jadwal Kegiatan Santri</h2>
                <span class="text-xs bg-gray-100 text-gray-700 px-3 py-1 rounded-full font-bold">
                    {{ $kegiatans->total() }} Data
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-gray-600 font-semibold text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3.5 w-12 text-center">No</th>
                            <th class="px-6 py-3.5">Waktu Pelaksanaan</th>
                            <th class="px-6 py-3.5">Nama Kegiatan</th>
                            <th class="px-6 py-3.5">Hari Berlaku</th>
                            <th class="px-6 py-3.5">Kelas</th>
                            <th class="px-6 py-3.5">Jenis Kegiatan</th>
                            <th class="px-6 py-3.5">Deskripsi</th>
                            <th class="px-6 py-3.5 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($kegiatans as $idx => $k)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-center text-gray-400 text-xs">
                                    {{ $kegiatans->firstItem() + $idx }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5 font-mono font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg w-max text-xs border border-emerald-200">
                                        <i class="ti ti-clock text-sm"></i>
                                        {{ \Carbon\Carbon::parse($k->waktu_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($k->waktu_selesai)->format('H:i') }} WIB
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 text-sm">{{ $k->nama_kegiatan }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($k->hari === 'semua' || empty($k->hari))
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                            <i class="ti ti-calendar text-xs"></i> Semua Hari
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200 capitalize">
                                            <i class="ti ti-calendar-event text-xs"></i> {{ $k->hari_label }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($k->kelas === 'semua' || empty($k->kelas))
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            Semua Kelas
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                            Kelas {{ $k->kelas }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($k->is_rutin_24jam)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                            <i class="ti ti-repeat text-xs"></i> Rutin 24 Jam
                                        </span>
                                    @else
                                        <div class="flex flex-col">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 w-max">
                                                <i class="ti ti-star text-xs"></i> Khusus
                                            </span>
                                            @if($k->tanggal_khusus)
                                                <span class="text-[11px] text-gray-500 mt-1 font-medium">
                                                    {{ \Carbon\Carbon::parse($k->tanggal_khusus)->translatedFormat('d M Y') }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-500 text-xs leading-relaxed max-w-xs">
                                    {{ $k->deskripsi ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button"
                                                onclick="openModalEdit({{ json_encode($k) }})"
                                                class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition cursor-pointer"
                                                title="Edit Kegiatan">
                                            <i class="ti ti-edit text-lg"></i>
                                        </button>
                                        <button type="button"
                                                onclick="confirmDelete({{ $k->id }}, '{{ addslashes($k->nama_kegiatan) }}')"
                                                class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer"
                                                title="Hapus Kegiatan">
                                            <i class="ti ti-trash text-lg"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                    <i class="ti ti-calendar-off text-4xl mb-2 block text-gray-300"></i>
                                    Belum ada data kegiatan santri yang sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($kegiatans->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $kegiatans->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

{{-- MODAL TAMBAH / EDIT KEGIATAN --}}
<div id="modalKegiatan" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs hidden p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 space-y-5 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 id="modalTitle" class="text-base font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-calendar-plus text-emerald-600 text-xl"></i> Tambah Kegiatan Baru
            </h3>
            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>

        <form id="formKegiatan" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">
                    Nama Kegiatan <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="nama_kegiatan"
                       id="inputNamaKegiatan"
                       required
                       placeholder="Misal: Bangun Tidur & Qiyamul Lail, Sholat Subuh & Muroja'ah, KBM Formal, dsb."
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">
                        Jam Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="time"
                           name="waktu_mulai"
                           id="inputWaktuMulai"
                           required
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">
                        Jam Selesai <span class="text-red-500">*</span>
                    </label>
                    <input type="time"
                           name="waktu_selesai"
                           id="inputWaktuSelesai"
                           required
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">
                    Sifat Kegiatan <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="border rounded-xl p-3 flex items-start gap-2.5 cursor-pointer hover:border-emerald-500 transition">
                        <input type="radio"
                               name="is_rutin_24jam"
                               value="1"
                               id="radioRutin"
                               checked
                               onchange="toggleTanggalKhusus(false)"
                               class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="font-bold text-xs text-gray-800 block">Rutin 24 Jam</span>
                            <span class="text-[11px] text-gray-500 leading-tight">Jadwal baku santri berulang</span>
                        </div>
                    </label>

                    <label class="border rounded-xl p-3 flex items-start gap-2.5 cursor-pointer hover:border-blue-500 transition">
                        <input type="radio"
                               name="is_rutin_24jam"
                               value="0"
                               id="radioKhusus"
                               onchange="toggleTanggalKhusus(true)"
                               class="mt-0.5 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="font-bold text-xs text-gray-800 block">Agenda Khusus</span>
                            <span class="text-[11px] text-gray-500 leading-tight">Pada tanggal tertentu</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                {{-- Field Berlaku Hari (Khusus Rutin 24 Jam) --}}
                <div id="groupHari">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">
                        Berlaku Hari <span class="text-red-500">*</span>
                    </label>
                    <select name="hari" id="inputHari" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="semua">Semua Hari (Senin - Minggu)</option>
                        <option value="senin">Senin</option>
                        <option value="selasa">Selasa</option>
                        <option value="rabu">Rabu</option>
                        <option value="kamis">Kamis</option>
                        <option value="jumat">Jumat</option>
                        <option value="sabtu">Sabtu</option>
                        <option value="minggu">Minggu</option>
                    </select>
                </div>

                {{-- Field Tanggal Khusus (Khusus Agenda Khusus) --}}
                <div id="groupTanggalKhusus" class="hidden">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">
                        Tanggal Kegiatan Khusus <span class="text-red-500">*</span>
                    </label>
                    <input type="date"
                           name="tanggal_khusus"
                           id="inputTanggalKhusus"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                {{-- Field Untuk Kelas (Berlaku untuk keduanya) --}}
                <div id="groupKelas">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">
                        Untuk Kelas <span class="text-red-500">*</span>
                    </label>
                    <select name="kelas" id="inputKelas" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="semua">Semua Kelas (1, 2, 3)</option>
                        <option value="1">Kelas 1</option>
                        <option value="2">Kelas 2</option>
                        <option value="3">Kelas 3</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">
                    Deskripsi / Keterangan (Opsional)
                </label>
                <textarea name="deskripsi"
                          id="inputDeskripsi"
                          rows="3"
                          placeholder="Penjelasan ringkas mengenai kegiatan ini..."
                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button"
                        onclick="closeModal()"
                        class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-100 text-sm font-semibold transition cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-md transition cursor-pointer"
                        style="background-color: #16a34a !important; color: #ffffff !important;">
                    Simpan Kegiatan
                </button>
            </div>
        </form>

    </div>
</div>

{{-- FORM DELETE HIDDEN --}}
<form id="formDelete" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
    function toggleTanggalKhusus(isKhusus) {
        const grpTanggal = document.getElementById('groupTanggalKhusus');
        const grpHari = document.getElementById('groupHari');
        const inpTanggal = document.getElementById('inputTanggalKhusus');
        const inpHari = document.getElementById('inputHari');

        if (isKhusus) {
            grpTanggal.classList.remove('hidden');
            grpHari.classList.add('hidden');
            inpTanggal.required = true;
            inpHari.required = false;
        } else {
            grpTanggal.classList.add('hidden');
            grpHari.classList.remove('hidden');
            inpTanggal.required = false;
            inpTanggal.value = '';
            inpHari.required = true;
        }
    }

    function openModalTambah() {
        document.getElementById('modalTitle').innerHTML = '<i class="ti ti-calendar-plus text-emerald-600 text-xl"></i> Tambah Kegiatan Baru';
        const form = document.getElementById('formKegiatan');
        form.action = "{{ route('admin.kegiatan.store') }}";
        document.getElementById('formMethod').value = 'POST';

        document.getElementById('inputNamaKegiatan').value = '';
        document.getElementById('inputWaktuMulai').value = '';
        document.getElementById('inputWaktuSelesai').value = '';
        document.getElementById('inputHari').value = 'semua';
        document.getElementById('inputKelas').value = 'semua';
        document.getElementById('inputDeskripsi').value = '';
        document.getElementById('radioRutin').checked = true;
        toggleTanggalKhusus(false);

        document.getElementById('modalKegiatan').classList.remove('hidden');
    }

    function openModalEdit(item) {
        document.getElementById('modalTitle').innerHTML = '<i class="ti ti-edit text-blue-600 text-xl"></i> Edit Kegiatan Santri';
        const form = document.getElementById('formKegiatan');
        form.action = "{{ url('/admin/kegiatan') }}/" + item.id;
        document.getElementById('formMethod').value = 'PUT';

        document.getElementById('inputNamaKegiatan').value = item.nama_kegiatan || '';
        document.getElementById('inputWaktuMulai').value = item.waktu_mulai ? item.waktu_mulai.substring(0, 5) : '';
        document.getElementById('inputWaktuSelesai').value = item.waktu_selesai ? item.waktu_selesai.substring(0, 5) : '';
        document.getElementById('inputHari').value = item.hari || 'semua';
        document.getElementById('inputKelas').value = item.kelas || 'semua';
        document.getElementById('inputDeskripsi').value = item.deskripsi || '';

        if (item.is_rutin_24jam) {
            document.getElementById('radioRutin').checked = true;
            toggleTanggalKhusus(false);
        } else {
            document.getElementById('radioKhusus').checked = true;
            toggleTanggalKhusus(true);
            if (item.tanggal_khusus) {
                document.getElementById('inputTanggalKhusus').value = item.tanggal_khusus.substring(0, 10);
            }
        }

        document.getElementById('modalKegiatan').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('modalKegiatan').classList.add('hidden');
    }

    function confirmDelete(id, name) {
        if (confirm("Apakah Anda yakin ingin menghapus kegiatan '" + name + "'?")) {
            const form = document.getElementById('formDelete');
            form.action = "{{ url('/admin/kegiatan') }}/" + id;
            form.submit();
        }
    }
</script>
@endpush

@endsection
