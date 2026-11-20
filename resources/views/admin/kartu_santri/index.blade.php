@extends('layouts.admin.app')

@section('title', 'Manajemen & Cetak Kartu Santri')

@section('content')
<div class="bg-gray-100 min-h-screen py-6 px-4 lg:px-8" x-data="kartuSantriApp()">

    <!-- Header Section -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2.5">
                <i class="ti ti-id-badge text-emerald-600 text-3xl"></i>
                <span>Manajemen & Cetak Kartu Santri</span>
            </h1>
            <p class="text-gray-600 text-xs mt-1">
                Kelola foto santri dan cetak kartu santri digital resmi sesuai template. Foto yang diunggah otomatis tersinkronisasi ke cetakan kartu.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.santri.cetakKartuMassal', request()->query()) }}" target="_blank"
               class="px-4 py-2.5 rounded-xl font-bold text-xs shadow-sm flex items-center gap-1.5 transition text-white hover:opacity-90"
               style="background-color: #16a34a !important;">
                <i class="ti ti-printer text-base"></i> Cetak Massal (Sesuai Filter)
            </a>
            <a href="{{ route('admin.santri.index') }}"
               class="px-4 py-2.5 rounded-xl font-bold text-xs shadow-sm flex items-center gap-1.5 transition bg-white border border-gray-300 text-gray-700 hover:bg-gray-50">
                <i class="ti ti-users text-base"></i> Kelola Data Santri
            </a>
        </div>
    </div>

    <!-- Alert Success / Error -->
    @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-6 rounded-r-xl shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="ti ti-circle-check text-emerald-600 text-xl"></i>
                <p class="text-xs font-bold text-emerald-900">{{ session('success') }}</p>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 text-sm font-bold">&times;</button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 mb-6 rounded-r-xl shadow-sm">
            <div class="flex items-start gap-3">
                <i class="ti ti-alert-triangle text-rose-600 text-xl mt-0.5"></i>
                <div>
                    <h3 class="text-xs font-bold text-rose-900">Gagal mengunggah foto:</h3>
                    <ul class="text-xs text-rose-800 mt-1 list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Statistic Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span class="font-bold uppercase tracking-wider">Total Santri Aktif</span>
                <i class="ti ti-users text-xl text-blue-600"></i>
            </div>
            <div class="text-3xl font-black text-gray-900 font-mono">{{ $totalSantri }}</div>
            <div class="text-[11px] text-gray-400 mt-1">Santri berstatus aktif / diterima</div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span class="font-bold uppercase tracking-wider">Sudah Ada Foto</span>
                <i class="ti ti-camera-check text-xl text-emerald-600"></i>
            </div>
            <div class="text-3xl font-black text-emerald-700 font-mono">{{ $totalDenganFoto }}</div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-1">
                {{ $totalSantri > 0 ? round(($totalDenganFoto / $totalSantri) * 100) : 0 }}% memiliki foto custom
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span class="font-bold uppercase tracking-wider">Belum Ada Foto Khusus</span>
                <i class="ti ti-camera-off text-xl text-amber-600"></i>
            </div>
            <div class="text-3xl font-black text-amber-700 font-mono">{{ $totalTanpaFoto }}</div>
            <div class="text-[11px] text-amber-600 mt-1">Menggunakan inisial/avatar standar</div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 mb-6 text-xs">
        <form method="GET" action="{{ route('admin.kartu_santri.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div class="lg:col-span-2">
                <label class="block font-bold text-gray-700 uppercase mb-1">Cari Santri</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Ketik nama santri, NIS, atau NISN..."
                           class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500">
                    <i class="ti ti-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Jenjang Kelas</label>
                <select name="kelas" onchange="this.form.submit()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-emerald-500">
                    <option value="all" {{ request('kelas') == 'all' || !request('kelas') ? 'selected' : '' }}>Semua Kelas</option>
                    <option value="1" {{ request('kelas') == '1' ? 'selected' : '' }}>Kelas 1</option>
                    <option value="2" {{ request('kelas') == '2' ? 'selected' : '' }}>Kelas 2</option>
                    <option value="3" {{ request('kelas') == '3' ? 'selected' : '' }}>Kelas 3</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Status Foto</label>
                <select name="status_foto" onchange="this.form.submit()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-emerald-500">
                    <option value="" {{ !request('status_foto') ? 'selected' : '' }}>Semua Status</option>
                    <option value="ada" {{ request('status_foto') == 'ada' ? 'selected' : '' }}>Sudah Ada Foto</option>
                    <option value="belum" {{ request('status_foto') == 'belum' ? 'selected' : '' }}>Belum Ada Foto</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 py-2 rounded-lg font-bold text-xs text-white transition hover:opacity-90 flex items-center justify-center gap-1.5 cursor-pointer"
                        style="background-color: #0d9488 !important;">
                    <i class="ti ti-filter text-sm"></i> Filter
                </button>
                <a href="{{ route('admin.kartu_santri.index') }}"
                   class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold transition flex items-center justify-center text-xs">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Santri Cards Grid Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-list-details text-teal-600"></i> Daftar Santri ({{ $santris->total() }} Data)
            </h2>
            <span class="text-xs text-gray-500 font-medium">
                Klik tombol <strong>"Ganti Foto"</strong> untuk memperbarui foto kartu secara langsung.
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase font-bold border-b border-gray-200 text-[11px]">
                    <tr>
                        <th class="px-6 py-3.5 text-center" style="width: 80px;">Foto</th>
                        <th class="px-6 py-3.5">Nama Santri & Identitas</th>
                        <th class="px-6 py-3.5 text-center">Kelas</th>
                        <th class="px-6 py-3.5 text-center">Tahun Ajaran / Masuk</th>
                        <th class="px-6 py-3.5 text-center">Status Foto</th>
                        <th class="px-6 py-3.5 text-center" style="width: 240px;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($santris as $s)
                        <tr class="hover:bg-gray-50/80 transition">
                            <!-- Foto Thumbnail -->
                            <td class="px-6 py-4 text-center">
                                <div class="relative inline-block">
                                    <img src="{{ $s->foto_url }}"
                                         alt="{{ $s->nama_lengkap }}"
                                         class="w-12 h-12 rounded-full object-cover border-2 border-emerald-500 shadow-sm bg-gray-100"
                                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($s->nama_lengkap) }}&background=16a34a&color=fff&size=48'">
                                    @if($s->foto_santri)
                                        <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px]" title="Foto Kustom">
                                            <i class="ti ti-check"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Nama & Identitas -->
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 text-sm leading-snug">{{ $s->nama_lengkap }}</div>
                                <div class="text-[11px] text-gray-500 mt-1 flex flex-wrap items-center gap-3 font-mono">
                                    <span>NIS: <strong class="text-gray-800">{{ $s->nis ?? '-' }}</strong></span>
                                    <span>NISN: <strong class="text-gray-800">{{ $s->nisn ?? '-' }}</strong></span>
                                </div>
                            </td>

                            <!-- Jenjang Kelas -->
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-md bg-teal-50 text-teal-700 border border-teal-200">
                                    {{ $s->kelas_label }}
                                </span>
                            </td>

                            <!-- Tahun Ajaran -->
                            <td class="px-6 py-4 text-center font-mono text-gray-700">
                                {{ $s->tahun_ajaran ?? ($s->angkatan->nama_angkatan ?? ($s->tahun_masuk ? 'Masuk ' . $s->tahun_masuk : '-')) }}
                            </td>

                            <!-- Status Foto -->
                            <td class="px-6 py-4 text-center">
                                @if($s->foto_santri)
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="ti ti-circle-check"></i> Foto Custom
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full font-medium text-[10px] bg-gray-100 text-gray-500 border border-gray-200">
                                        Avatar Standar
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <!-- Tombol Ganti Foto -->
                                    <button type="button"
                                            @click="openModal({{ $s->id }}, '{{ addslashes($s->nama_lengkap) }}', '{{ $s->foto_url }}')"
                                            class="px-3 py-1.5 font-bold rounded-lg transition inline-flex items-center gap-1 cursor-pointer text-xs shadow-xs"
                                            style="background-color: #0284c7 !important; color: #ffffff !important;"
                                            title="Upload / Ganti Foto Kartu">
                                        <i class="ti ti-camera text-sm"></i> Ganti Foto
                                    </button>

                                    <!-- Tombol Cetak Kartu -->
                                    <a href="{{ route('admin.santri.cetakKartu', $s->id) }}" target="_blank"
                                       class="px-3 py-1.5 font-bold rounded-lg transition inline-flex items-center gap-1 text-xs shadow-xs"
                                       style="background-color: #16a34a !important; color: #ffffff !important;"
                                       title="Lihat & Cetak Kartu Santri">
                                        <i class="ti ti-printer text-sm"></i> Cetak
                                    </a>

                                    @if($s->foto_santri)
                                        <!-- Tombol Reset / Hapus Foto Custom -->
                                        <form action="{{ route('admin.kartu_santri.delete_foto', $s->id) }}" method="POST"
                                              class="inline"
                                              onsubmit="return confirm('Hapus foto kustom ananda {{ addslashes($s->nama_lengkap) }} dan kembalikan ke avatar standar?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-1.5 font-bold rounded-lg transition inline-flex items-center justify-center text-xs text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 cursor-pointer"
                                                    title="Hapus foto custom">
                                                <i class="ti ti-trash text-sm"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <i class="ti ti-users-off text-4xl block mb-2 text-gray-300"></i>
                                <span class="text-sm font-semibold text-gray-600">Tidak ada data santri yang sesuai filter.</span>
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

    <!-- MODAL GANTI FOTO SANTRI -->
    <div x-show="isModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
        <div @click.away="isModalOpen = false"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all border border-gray-100">

            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between"
                 style="background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%); color: #ffffff;">
                <h3 class="font-bold text-sm flex items-center gap-2">
                    <i class="ti ti-camera text-lg"></i>
                    <span>Ganti Foto Kartu Santri</span>
                </h3>
                <button type="button" @click="isModalOpen = false" class="text-white hover:text-gray-200 text-lg font-bold">&times;</button>
            </div>

            <form :action="'{{ url('/admin/kartu-santri') }}/' + modalSantriId + '/foto'"
                  method="POST"
                  enctype="multipart/form-data"
                  class="p-6 space-y-4">
                @csrf

                <!-- Nama Santri Info -->
                <div class="text-center">
                    <div class="relative inline-block mb-2">
                        <img :src="previewUrl || modalCurrentFoto"
                             alt="Preview Foto"
                             class="w-28 h-28 rounded-full object-cover border-4 border-emerald-500 shadow-md bg-gray-100 mx-auto">
                    </div>
                    <h4 class="font-bold text-gray-900 text-base" x-text="modalSantriNama"></h4>
                    <p class="text-[11px] text-gray-500 mt-0.5">Foto ini akan langsung digunakan pada Kartu Santri fisik & digital.</p>
                </div>

                <!-- Input File -->
                <div>
                    <label class="block font-bold text-gray-700 text-xs uppercase mb-1.5">Pilih Foto Baru</label>
                    <input type="file"
                           name="foto"
                           accept="image/jpeg,image/png,image/jpg,image/webp"
                           @change="handleFileChange($event)"
                           required
                           class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-300 rounded-lg cursor-pointer">
                    <span class="text-[11px] text-gray-400 mt-1 block">Format: JPG, PNG, atau WEBP. Maksimal 3 MB. Disarankan foto portrait tegak.</span>
                </div>

                <!-- Buttons -->
                <div class="pt-2 flex items-center justify-end gap-2 border-t border-gray-100">
                    <button type="button" @click="isModalOpen = false"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 font-bold text-xs text-white rounded-lg shadow-sm transition hover:opacity-90 flex items-center gap-1.5 cursor-pointer"
                            style="background-color: #16a34a !important;">
                        <i class="ti ti-device-floppy text-sm"></i> Simpan & Perbarui Foto
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function kartuSantriApp() {
    return {
        isModalOpen: false,
        modalSantriId: null,
        modalSantriNama: '',
        modalCurrentFoto: '',
        previewUrl: null,

        openModal(id, nama, fotoUrl) {
            this.modalSantriId = id;
            this.modalSantriNama = nama;
            this.modalCurrentFoto = fotoUrl;
            this.previewUrl = null;
            this.isModalOpen = true;
        },

        handleFileChange(event) {
            const file = event.target.files[0];
            if (file) {
                this.previewUrl = URL.createObjectURL(file);
            }
        }
    };
}
</script>
@endsection
