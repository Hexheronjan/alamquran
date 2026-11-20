@extends('layouts.admin.app')

@section('title', 'Arsip Surat Masuk')

@section('content')
<div class="container-xl">

    {{-- Page Header --}}
    <div class="page-header d-print-none mb-4">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="ti ti-mail-down text-emerald-600 text-3xl"></i>
                    Arsip Surat Masuk
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Kelola semua surat masuk yang diterima pondok pesantren.
                    <span class="font-semibold text-emerald-600">{{ $suratMasuk->total() }} surat</span> tersimpan.
                </p>
            </div>
            <div class="mt-3 sm:mt-0">
                <button onclick="openModal()"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-5 py-2.5 rounded-lg shadow transition cursor-pointer"
                        style="background-color: #16a34a !important; color: #ffffff !important;">
                    <i class="ti ti-plus text-lg"></i>
                    Tambah Surat Masuk
                </button>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-4 shadow-sm">
            <i class="ti ti-circle-check text-green-500 text-xl flex-shrink-0"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 mb-4 shadow-sm">
            <i class="ti ti-alert-circle text-red-500 text-xl flex-shrink-0"></i>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Validation Errors (from modal form submission) --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-4">
            <div class="flex items-start gap-2">
                <i class="ti ti-alert-circle text-red-500 text-xl flex-shrink-0 mt-0.5"></i>
                <div>
                    <p class="text-sm font-semibold text-red-800 mb-1">Terdapat kesalahan pada formulir:</p>
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Search & Filter Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
        <form action="{{ route('admin.surat.masuk.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg pointer-events-none"></i>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nomor surat, asal instansi, perihal..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                >
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-800 text-white text-sm font-bold px-4 py-2.5 rounded-lg transition cursor-pointer"
                        style="background-color: #374151 !important; color: #ffffff !important;">
                    <i class="ti ti-search text-base"></i>
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.surat.masuk.index') }}"
                       class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2.5 rounded-lg transition">
                        <i class="ti ti-x text-base"></i>
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-8">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Surat</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tgl. Terima</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tgl. Surat</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Asal Instansi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Perihal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ringkasan</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">File</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Diarsipkan Oleh</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Hapus</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($suratMasuk as $index => $surat)
                        <tr class="hover:bg-emerald-50/40 transition-colors">

                            {{-- No --}}
                            <td class="px-4 py-3 text-sm text-gray-500 text-center">
                                {{ $suratMasuk->firstItem() + $index }}
                            </td>

                            {{-- Nomor Surat --}}
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 text-sm font-mono font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
                                    <i class="ti ti-file-import text-emerald-500 text-xs"></i>
                                    {{ $surat->nomor_surat }}
                                </span>
                            </td>

                            {{-- Tanggal Terima --}}
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <i class="ti ti-calendar-check text-emerald-400 text-base"></i>
                                    {{ \Carbon\Carbon::parse($surat->tanggal_terima)->translatedFormat('d M Y') }}
                                </div>
                            </td>

                            {{-- Tanggal Surat --}}
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <i class="ti ti-calendar text-gray-400 text-base"></i>
                                    {{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d M Y') }}
                                </div>
                            </td>

                            {{-- Asal Instansi --}}
                            <td class="px-4 py-3 text-sm text-gray-700 max-w-[160px]">
                                <div class="flex items-start gap-1.5">
                                    <i class="ti ti-building text-gray-400 text-base mt-0.5 flex-shrink-0"></i>
                                    <span class="truncate" title="{{ $surat->asal_instansi }}">{{ $surat->asal_instansi }}</span>
                                </div>
                            </td>

                            {{-- Perihal --}}
                            <td class="px-4 py-3 text-sm text-gray-700 max-w-[180px]">
                                <span class="line-clamp-2" title="{{ $surat->perihal }}">{{ $surat->perihal }}</span>
                            </td>

                            {{-- Ringkasan --}}
                            <td class="px-4 py-3 text-sm text-gray-500 max-w-[200px]">
                                <span class="line-clamp-2 text-xs" title="{{ $surat->ringkasan_isi }}">
                                    {{ $surat->ringkasan_isi ?? '—' }}
                                </span>
                            </td>

                            {{-- File --}}
                            <td class="px-4 py-3 text-center">
                                @if ($surat->file_arsip)
                                    <a href="{{ Storage::url($surat->file_arsip) }}"
                                       target="_blank"
                                       title="Unduh File Surat"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 hover:bg-blue-200 text-blue-700 transition">
                                        <i class="ti ti-download text-base"></i>
                                    </a>
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Diarsipkan Oleh --}}
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                        <i class="ti ti-user text-emerald-600 text-xs"></i>
                                    </div>
                                    <span>{{ $surat->creator->name ?? '-' }}</span>
                                </div>
                            </td>

                            {{-- Hapus --}}
                            <td class="px-4 py-3 text-center">
                                <form action="{{ route('admin.surat.masuk.destroy', $surat->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus surat masuk ini? Tindakan ini tidak dapat dibatalkan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            title="Hapus Surat"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 hover:bg-red-200 text-red-700 transition">
                                        <i class="ti ti-trash text-base"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                    <i class="ti ti-inbox-off text-5xl"></i>
                                    <p class="text-base font-medium">Belum ada surat masuk</p>
                                    @if(request('search'))
                                        <p class="text-sm">Tidak ada hasil untuk "<span class="font-semibold text-gray-600">{{ request('search') }}</span>"</p>
                                        <a href="{{ route('admin.surat.masuk.index') }}" class="text-sm text-emerald-600 hover:underline">Tampilkan semua surat</a>
                                    @else
                                        <button onclick="openModal()"
                                                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-4 py-2 rounded-lg transition mt-1 cursor-pointer"
                                                style="background-color: #16a34a !important; color: #ffffff !important;">
                                            <i class="ti ti-plus"></i> Tambah Surat Pertama
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination & Summary --}}
        @if ($suratMasuk->hasPages())
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-3 bg-gray-50 border-t border-gray-200">
                <p class="text-xs text-gray-500">
                    Menampilkan <span class="font-semibold text-gray-700">{{ $suratMasuk->firstItem() }}</span>
                    – <span class="font-semibold text-gray-700">{{ $suratMasuk->lastItem() }}</span>
                    dari <span class="font-semibold text-gray-700">{{ $suratMasuk->total() }}</span> surat
                </p>
                <div class="text-sm">
                    {{ $suratMasuk->appends(request()->query())->links() }}
                </div>
            </div>
        @else
            <div class="px-5 py-3 bg-gray-50 border-t border-gray-200">
                <p class="text-xs text-gray-500">
                    Total <span class="font-semibold text-gray-700">{{ $suratMasuk->total() }}</span> surat masuk
                </p>
            </div>
        @endif
    </div>

</div>

{{-- ============================================================ --}}
{{-- MODAL: Tambah Surat Masuk --}}
{{-- ============================================================ --}}
<div id="modal-surat-masuk"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden"
     role="dialog" aria-modal="true" aria-labelledby="modal-title">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>

    {{-- Modal Panel --}}
    <div class="relative z-10 w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden">

        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-6 py-4 bg-emerald-600">
            <h3 id="modal-title" class="text-lg font-bold text-white flex items-center gap-2">
                <i class="ti ti-mail-down text-xl"></i>
                Tambah Surat Masuk
            </h3>
            <button onclick="closeModal()"
                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/20 hover:bg-white/30 text-white transition">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>

        {{-- Modal Form --}}
        <form action="{{ route('admin.surat.masuk.store') }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf

            <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">

                {{-- Row: Nomor Surat & Asal Instansi --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    {{-- Nomor Surat --}}
                    <div>
                        <label for="m_nomor_surat" class="block text-sm font-semibold text-gray-700 mb-1">
                            Nomor Surat <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="ti ti-hash absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                            <input
                                type="text"
                                id="m_nomor_surat"
                                name="nomor_surat"
                                value="{{ old('nomor_surat') }}"
                                required
                                placeholder="Nomor surat yang tertera"
                                class="w-full pl-9 pr-3 py-2.5 border @error('nomor_surat') border-red-400 @else border-gray-300 @enderror rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                        </div>
                        @error('nomor_surat')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Asal Instansi --}}
                    <div>
                        <label for="m_asal_instansi" class="block text-sm font-semibold text-gray-700 mb-1">
                            Asal Instansi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="ti ti-building absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                            <input
                                type="text"
                                id="m_asal_instansi"
                                name="asal_instansi"
                                value="{{ old('asal_instansi') }}"
                                required
                                placeholder="Nama instansi/lembaga pengirim"
                                class="w-full pl-9 pr-3 py-2.5 border @error('asal_instansi') border-red-400 @else border-gray-300 @enderror rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                        </div>
                        @error('asal_instansi')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Row: Tanggal Terima & Tanggal Surat --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    {{-- Tanggal Terima --}}
                    <div>
                        <label for="m_tanggal_terima" class="block text-sm font-semibold text-gray-700 mb-1">
                            Tanggal Diterima <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="ti ti-calendar-check absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                            <input
                                type="date"
                                id="m_tanggal_terima"
                                name="tanggal_terima"
                                value="{{ old('tanggal_terima', date('Y-m-d')) }}"
                                required
                                class="w-full pl-9 pr-3 py-2.5 border @error('tanggal_terima') border-red-400 @else border-gray-300 @enderror rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                        </div>
                        @error('tanggal_terima')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Surat --}}
                    <div>
                        <label for="m_tanggal_surat" class="block text-sm font-semibold text-gray-700 mb-1">
                            Tanggal Surat <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="ti ti-calendar absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                            <input
                                type="date"
                                id="m_tanggal_surat"
                                name="tanggal_surat"
                                value="{{ old('tanggal_surat') }}"
                                required
                                class="w-full pl-9 pr-3 py-2.5 border @error('tanggal_surat') border-red-400 @else border-gray-300 @enderror rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                        </div>
                        @error('tanggal_surat')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Perihal --}}
                <div>
                    <label for="m_perihal" class="block text-sm font-semibold text-gray-700 mb-1">
                        Perihal <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="ti ti-tag absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                        <input
                            type="text"
                            id="m_perihal"
                            name="perihal"
                            value="{{ old('perihal') }}"
                            required
                            placeholder="Perihal / pokok surat"
                            class="w-full pl-9 pr-3 py-2.5 border @error('perihal') border-red-400 @else border-gray-300 @enderror rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        >
                    </div>
                    @error('perihal')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Ringkasan Isi --}}
                <div>
                    <label for="m_ringkasan_isi" class="block text-sm font-semibold text-gray-700 mb-1">
                        Ringkasan Isi
                        <span class="text-gray-400 font-normal">(Opsional)</span>
                    </label>
                    <textarea
                        id="m_ringkasan_isi"
                        name="ringkasan_isi"
                        rows="4"
                        placeholder="Tuliskan ringkasan singkat isi surat untuk memudahkan pencarian..."
                        class="w-full px-3 py-2.5 border @error('ringkasan_isi') border-red-400 @else border-gray-300 @enderror rounded-lg text-sm leading-relaxed focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"
                    >{{ old('ringkasan_isi') }}</textarea>
                    @error('ringkasan_isi')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- File Arsip --}}
                <div>
                    <label for="m_file_arsip" class="block text-sm font-semibold text-gray-700 mb-1">
                        File Scan / Dokumen
                        <span class="text-gray-400 font-normal">(Opsional)</span>
                    </label>
                    <div class="border-2 border-dashed @error('file_arsip') border-red-400 bg-red-50 @else border-gray-300 @enderror rounded-lg p-4 text-center hover:border-emerald-400 hover:bg-emerald-50 transition-colors cursor-pointer"
                         onclick="document.getElementById('m_file_arsip').click()">
                        <input
                            type="file"
                            id="m_file_arsip"
                            name="file_arsip"
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="hidden"
                            onchange="updateModalFileLabel(this)"
                        >
                        <i class="ti ti-cloud-upload text-3xl text-gray-300 mb-1 block"></i>
                        <p id="modal_file_label" class="text-sm text-gray-500">Klik untuk pilih file</p>
                        <p class="text-xs text-gray-400 mt-0.5">PDF, JPG, PNG — Maks. 5MB</p>
                    </div>
                    @error('file_arsip')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="ti ti-alert-circle text-sm"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200">
                <button type="button"
                        onclick="closeModal()"
                        class="inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-800 text-white text-sm font-bold px-5 py-2.5 rounded-lg transition cursor-pointer"
                        style="background-color: #374151 !important; color: #ffffff !important;">
                    <i class="ti ti-x text-base"></i>
                    Batal
                </button>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-6 py-2.5 rounded-lg shadow transition cursor-pointer"
                        style="background-color: #16a34a !important; color: #ffffff !important;">
                    <i class="ti ti-archive text-base"></i>
                    Simpan Arsip
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const modal = document.getElementById('modal-surat-masuk');

    function openModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });

    // Re-open modal on validation error so user sees feedback
    @if ($errors->any())
        document.addEventListener('DOMContentLoaded', openModal);
    @endif

    function updateModalFileLabel(input) {
        const label = document.getElementById('modal_file_label');
        if (input.files && input.files.length > 0) {
            const file = input.files[0];
            const sizeMB = (file.size / 1024 / 1024).toFixed(2);
            label.textContent = `${file.name} (${sizeMB} MB)`;
            label.classList.add('text-emerald-600', 'font-medium');
            label.classList.remove('text-gray-500');
        } else {
            label.textContent = 'Klik untuk pilih file';
            label.classList.remove('text-emerald-600', 'font-medium');
            label.classList.add('text-gray-500');
        }
    }
</script>
@endpush
@endsection
