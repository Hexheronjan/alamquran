@extends('layouts.admin.app')

@section('title', 'Saran & Masukan Pengembangan')

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

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <i class="ti ti-message-2-share text-indigo-600"></i> Saran & Masukan Pengembangan Aplikasi
            </h1>
            <p class="text-xs text-gray-500 mt-1">Daftar aspirasi, masukan fitur, dan kendala sistem dari wali santri serta staf pondok.</p>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span class="font-bold uppercase">Total Masukan</span>
                <i class="ti ti-messages text-xl text-blue-600"></i>
            </div>
            <div class="text-2xl font-black text-gray-900 font-mono">{{ number_format($totalSaran) }}</div>
            <div class="text-[11px] text-gray-400 mt-0.5">Semua pesan masuk</div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span class="font-bold uppercase">Perlu Ditinjau</span>
                <i class="ti ti-clock-pause text-xl text-amber-600"></i>
            </div>
            <div class="text-2xl font-black text-amber-700 font-mono">{{ number_format($menungguReview) }}</div>
            <div class="text-[11px] text-amber-600 font-semibold mt-0.5">Menunggu respon admin</div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span class="font-bold uppercase">Sedang Diproses</span>
                <i class="ti ti-loader text-xl text-indigo-600"></i>
            </div>
            <div class="text-2xl font-black text-indigo-700 font-mono">{{ number_format($sedangDiproses) }}</div>
            <div class="text-[11px] text-indigo-600 font-semibold mt-0.5">Dalam tahap tindak lanjut</div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span class="font-bold uppercase">Telah Diterapkan</span>
                <i class="ti ti-circle-check text-xl text-emerald-600"></i>
            </div>
            <div class="text-2xl font-black text-emerald-700 font-mono">{{ number_format($diterapkan) }}</div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-0.5">Fitur/ide berhasil rilis</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl shadow-xs border border-gray-200 p-4 mb-6 text-xs">
        <form method="GET" action="{{ route('admin.saran.index') }}" class="flex flex-wrap items-center gap-3">
            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Status</label>
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                    <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                    @foreach($statusList as $s => $lbl)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-1">Kategori</label>
                <select name="kategori" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                    <option value="all" {{ request('kategori') === 'all' || !request('kategori') ? 'selected' : '' }}>Semua Kategori</option>
                    @foreach($kategoriList as $k => $lbl)
                        <option value="{{ $k }}" {{ request('kategori') === $k ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 min-w-[220px]">
                <label class="block font-bold text-gray-700 uppercase mb-1">Cari Saran / Pengirim</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik kata kunci judul, pesan, atau nama..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="pt-4 flex gap-1.5">
                <button type="submit" class="px-4 py-2 rounded-lg font-bold text-xs cursor-pointer text-white shadow-xs"
                        style="background-color: #4f46e5 !important;">
                    <i class="ti ti-filter"></i> Filter
                </button>
                <a href="{{ route('admin.saran.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Saran & Masukan -->
    <div class="bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-inbox text-indigo-600"></i> Kotak Masuk Aspirasi
            </h2>
            <span class="text-xs bg-gray-100 text-gray-700 px-3 py-1 rounded-full font-bold">
                {{ $sarans->total() }} Pesan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Pengirim</th>
                        <th class="px-4 py-3.5 text-center">Kategori</th>
                        <th class="px-6 py-3.5">Judul & Isi Masukan</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($sarans as $s)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-4 whitespace-nowrap text-gray-600 font-medium">
                                {{ $s->created_at ? $s->created_at->translatedFormat('d M Y, H:i') : '-' }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-bold text-gray-900">{{ $s->nama_pengirim }}</div>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase {{ $s->tipe_pengirim === 'wali_santri' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                    {{ $s->tipe_pengirim === 'wali_santri' ? 'Wali Santri' : 'Staf / Admin' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-indigo-50 text-indigo-700 border border-indigo-200 inline-flex items-center gap-1">
                                    <i class="{{ \App\Models\SaranMasukan::getKategoriIcon($s->kategori, 'ti') }}"></i>
                                    {{ $kategoriList[$s->kategori] ?? ucfirst($s->kategori) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 text-sm mb-1">{{ $s->judul }}</div>
                                <p class="text-xs text-gray-600 leading-relaxed whitespace-pre-line">{{ $s->pesan }}</p>
                                
                                @if($s->lampiran)
                                    <div class="mt-2">
                                        <a href="{{ Storage::url($s->lampiran) }}" target="_blank"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 transition">
                                            <i class="ti ti-photo"></i> Lihat Lampiran Gambar
                                        </a>
                                    </div>
                                @endif

                                @if($s->tanggapan_admin)
                                    <div class="mt-2.5 p-3 rounded-xl bg-gray-50 border border-gray-200 text-xs">
                                        <div class="font-bold text-gray-700 flex items-center gap-1.5 mb-0.5">
                                            <i class="ti ti-corner-down-right text-indigo-600"></i> Tanggapan Pengembang / Admin:
                                        </div>
                                        <p class="text-gray-600 italic">"{{ $s->tanggapan_admin }}"</p>
                                        <span class="text-[10px] text-gray-400 mt-1 block">Oleh {{ $s->responder->name ?? 'Admin' }} • {{ $s->responded_at ? $s->responded_at->diffForHumans() : '' }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                @if($s->status === 'diterapkan')
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        ✓ Diterapkan
                                    </span>
                                @elseif($s->status === 'sedang_diproses')
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-indigo-100 text-indigo-800 border border-indigo-200">
                                        Sedang Diproses
                                    </span>
                                @elseif($s->status === 'diarsipkan')
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-gray-100 text-gray-600">
                                        Diarsipkan
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-amber-100 text-amber-800 border border-amber-200">
                                        Menunggu Review
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button"
                                            onclick='openTanggapiModal({{ json_encode($s) }})'
                                            class="px-2.5 py-1.5 rounded-lg text-xs font-bold text-white shadow-xs cursor-pointer hover:opacity-95"
                                            style="background-color: #4f46e5 !important;"
                                            title="Tanggapi Saran & Update Status">
                                        <i class="ti ti-edit"></i> Tanggapi
                                    </button>

                                    <form action="{{ route('admin.saran.destroy', $s->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus masukan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-xs font-bold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 transition cursor-pointer" title="Hapus">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <i class="ti ti-message-dots text-4xl text-gray-300 mb-2 block"></i>
                                Belum ada saran dan masukan pada filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sarans->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $sarans->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Tanggapi Saran -->
<div id="modalTanggapi" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-5 py-3.5 border-b border-indigo-700 flex items-center justify-between text-white shrink-0" style="background-color: #4f46e5;">
            <div class="flex items-center gap-2">
                <i class="ti ti-message-dots text-lg"></i>
                <h3 class="text-sm font-bold">Tanggapi Saran & Masukan</h3>
            </div>
            <button type="button" onclick="closeTanggapiModal()" class="cursor-pointer p-1 text-white/80 hover:text-white transition">
                <i class="ti ti-x text-base"></i>
            </button>
        </div>

        <form id="formTanggapi" method="POST" class="overflow-y-auto p-5 space-y-3.5 text-xs">
            @csrf
            @method('PATCH')

            <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 space-y-1">
                <span class="text-[11px] font-bold text-indigo-700 uppercase" id="modalKategori"></span>
                <h4 class="font-bold text-gray-900 text-sm" id="modalJudul"></h4>
                <p class="text-xs text-gray-600 line-clamp-3" id="modalPesan"></p>
            </div>

            <div>
                <label class="block font-bold text-gray-800 uppercase mb-1">Status Penanganan <span class="text-red-500">*</span></label>
                <select name="status" id="inputStatus" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                    <option value="menunggu_review">Menunggu Review</option>
                    <option value="sedang_diproses">Sedang Diproses</option>
                    <option value="diterapkan">Selesai / Diterapkan ke Sistem</option>
                    <option value="diarsipkan">Diarsipkan</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-gray-800 uppercase mb-1">Tulis Tanggapan Resmi untuk Pengirim</label>
                <textarea name="tanggapan_admin" id="inputTanggapan" rows="4" placeholder="Tuliskan tanggapan, konfirmasi pengerjaan, atau terima kasih..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500"></textarea>
                <span class="text-[11px] text-gray-400 mt-1 block">Tanggapan ini akan dapat dibaca langsung oleh wali santri/staf yang mengirimkan saran.</span>
            </div>

            <div class="pt-3 border-t border-gray-200 flex justify-end gap-2 shrink-0">
                <button type="button" onclick="closeTanggapiModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 text-white font-bold rounded-xl cursor-pointer shadow-sm hover:opacity-95"
                        style="background-color: #4f46e5 !important;">
                    Simpan Tanggapan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openTanggapiModal(data) {
    document.getElementById('formTanggapi').action = '/admin/saran/' + data.id + '/status';
    document.getElementById('modalKategori').innerText = data.kategori || 'Saran';
    document.getElementById('modalJudul').innerText = data.judul || '';
    document.getElementById('modalPesan').innerText = data.pesan || '';
    document.getElementById('inputStatus').value = data.status || 'menunggu_review';
    document.getElementById('inputTanggapan').value = data.tanggapan_admin || '';
    document.getElementById('modalTanggapi').classList.remove('hidden');
}
function closeTanggapiModal() {
    document.getElementById('modalTanggapi').classList.add('hidden');
}
</script>
@endsection
