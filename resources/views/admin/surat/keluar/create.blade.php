@extends('layouts.admin.app')

@section('title', 'Buat Surat Keluar Baru')

@section('content')
<div class="container-xl max-w-4xl">

    {{-- Page Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.surat.keluar.index') }}"
           class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 transition">
            <i class="ti ti-arrow-left text-lg"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="ti ti-mail-forward text-blue-600 text-3xl"></i>
                Buat Surat Keluar Baru
            </h2>
            <p class="text-sm text-gray-500 mt-0.5">Isi formulir berikut untuk membuat surat keluar resmi</p>
        </div>
    </div>

    {{-- Nomor Format Info --}}
    <div class="flex items-start gap-3 bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 mb-6">
        <i class="ti ti-info-circle text-blue-500 text-xl flex-shrink-0 mt-0.5"></i>
        <div class="text-sm text-blue-800">
            <span class="font-semibold">Format Nomor Surat:</span>
            Sistem akan otomatis men-generate nomor surat dengan format
            <code class="bg-blue-100 px-1.5 py-0.5 rounded font-mono text-xs font-bold">NNN/PS-AL/SK/MM/YYYY</code>
            (contoh: <code class="bg-blue-100 px-1.5 py-0.5 rounded font-mono text-xs font-bold">{{ $nomorBerikut }}</code>).
            Anda dapat mengubahnya secara manual jika diperlukan.
        </div>
    </div>

    {{-- Form Card --}}
    <form action="{{ route('admin.surat.keluar.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @csrf

        <div class="p-6 space-y-6">

            {{-- Row: Nomor Surat & Tanggal --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Nomor Surat --}}
                <div>
                    <label for="nomor_surat" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Nomor Surat <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="ti ti-hash absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-base pointer-events-none"></i>
                        <input
                            type="text"
                            id="nomor_surat"
                            name="nomor_surat"
                            value="{{ old('nomor_surat', $nomorBerikut) }}"
                            required
                            class="w-full pl-9 pr-4 py-2.5 border @error('nomor_surat') border-red-400 bg-red-50 @else border-gray-300 @enderror rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                    @error('nomor_surat')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="ti ti-alert-circle text-sm"></i> {{ $message }}
                        </p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400">Nomor di atas ter-generate otomatis. Edit jika perlu.</p>
                </div>

                {{-- Tanggal Surat --}}
                <div>
                    <label for="tanggal_surat" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Tanggal Surat <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="ti ti-calendar-event absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-base pointer-events-none"></i>
                        <input
                            type="date"
                            id="tanggal_surat"
                            name="tanggal_surat"
                            value="{{ old('tanggal_surat', date('Y-m-d')) }}"
                            required
                            class="w-full pl-9 pr-4 py-2.5 border @error('tanggal_surat') border-red-400 bg-red-50 @else border-gray-300 @enderror rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                    @error('tanggal_surat')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="ti ti-alert-circle text-sm"></i> {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            {{-- Tujuan Surat --}}
            <div>
                <label for="tujuan_surat" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Tujuan / Kepada <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <i class="ti ti-building-bank absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-base pointer-events-none"></i>
                    <input
                        type="text"
                        id="tujuan_surat"
                        name="tujuan_surat"
                        value="{{ old('tujuan_surat') }}"
                        required
                        placeholder="Contoh: Kepala Dinas Pendidikan Kabupaten..."
                        class="w-full pl-9 pr-4 py-2.5 border @error('tujuan_surat') border-red-400 bg-red-50 @else border-gray-300 @enderror rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
                @error('tujuan_surat')
                    <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                        <i class="ti ti-alert-circle text-sm"></i> {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Perihal --}}
            <div>
                <label for="perihal" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Perihal <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <i class="ti ti-tag absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-base pointer-events-none"></i>
                    <input
                        type="text"
                        id="perihal"
                        name="perihal"
                        value="{{ old('perihal') }}"
                        required
                        placeholder="Perihal / judul singkat surat"
                        class="w-full pl-9 pr-4 py-2.5 border @error('perihal') border-red-400 bg-red-50 @else border-gray-300 @enderror rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
                @error('perihal')
                    <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                        <i class="ti ti-alert-circle text-sm"></i> {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Isi Surat --}}
            <div>
                <label for="isi_surat" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Isi Surat <span class="text-red-500">*</span>
                </label>
                <textarea
                    id="isi_surat"
                    name="isi_surat"
                    rows="12"
                    required
                    placeholder="Tuliskan isi surat di sini. Contoh:&#10;&#10;Assalamu'alaikum Warahmatullahi Wabarakatuh,&#10;&#10;Dengan hormat, bersama surat ini kami sampaikan..."
                    class="w-full px-4 py-3 border @error('isi_surat') border-red-400 bg-red-50 @else border-gray-300 @enderror rounded-lg text-sm leading-relaxed focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-sans resize-y"
                >{{ old('isi_surat') }}</textarea>
                @error('isi_surat')
                    <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                        <i class="ti ti-alert-circle text-sm"></i> {{ $message }}
                    </p>
                @enderror
                <p class="mt-1 text-xs text-gray-400">Tulis isi surat secara lengkap dan formal.</p>
            </div>

            {{-- Row: Penandatangan & Lampiran --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Penandatangan --}}
                <div>
                    <label for="penandatangan" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Penandatangan <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="ti ti-pencil-check absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-base pointer-events-none"></i>
                        <input
                            type="text"
                            id="penandatangan"
                            name="penandatangan"
                            value="{{ old('penandatangan', 'Pimpinan Pesantren') }}"
                            required
                            placeholder="Nama / Jabatan penandatangan"
                            class="w-full pl-9 pr-4 py-2.5 border @error('penandatangan') border-red-400 bg-red-50 @else border-gray-300 @enderror rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                    @error('penandatangan')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="ti ti-alert-circle text-sm"></i> {{ $message }}
                        </p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400">Default: Pimpinan Pesantren</p>
                </div>

                {{-- Lampiran --}}
                <div>
                    <label for="file_lampiran" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        File Lampiran
                        <span class="text-gray-400 font-normal">(Opsional)</span>
                    </label>
                    <div class="border-2 border-dashed @error('file_lampiran') border-red-400 bg-red-50 @else border-gray-300 @enderror rounded-lg p-4 text-center hover:border-blue-400 hover:bg-blue-50 transition-colors cursor-pointer"
                         onclick="document.getElementById('file_lampiran').click()">
                        <input
                            type="file"
                            id="file_lampiran"
                            name="file_lampiran"
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="hidden"
                            onchange="updateFileLabel(this)"
                        >
                        <i class="ti ti-upload text-3xl text-gray-300 mb-1 block"></i>
                        <p id="file_label" class="text-sm text-gray-500">Klik untuk pilih file</p>
                        <p class="text-xs text-gray-400 mt-0.5">PDF, JPG, PNG — Maks. 5MB</p>
                    </div>
                    @error('file_lampiran')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <i class="ti ti-alert-circle text-sm"></i> {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

        </div>

        {{-- Form Footer --}}
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200">
            <a href="{{ route('admin.surat.keluar.index') }}"
               class="inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-800 text-white text-sm font-bold px-5 py-2.5 rounded-lg transition"
               style="background-color: #374151 !important; color: #ffffff !important;">
                <i class="ti ti-x text-base"></i>
                Batal
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-6 py-2.5 rounded-lg shadow transition cursor-pointer"
                    style="background-color: #16a34a !important; color: #ffffff !important;">
                <i class="ti ti-send text-base"></i>
                Simpan Surat
            </button>
        </div>
    </form>

</div>

@push('scripts')
<script>
    function updateFileLabel(input) {
        const label = document.getElementById('file_label');
        if (input.files && input.files.length > 0) {
            const file = input.files[0];
            const sizeMB = (file.size / 1024 / 1024).toFixed(2);
            label.textContent = `${file.name} (${sizeMB} MB)`;
            label.classList.add('text-blue-600', 'font-medium');
            label.classList.remove('text-gray-500');
        } else {
            label.textContent = 'Klik untuk pilih file';
            label.classList.remove('text-blue-600', 'font-medium');
            label.classList.add('text-gray-500');
        }
    }
</script>
@endpush
@endsection
