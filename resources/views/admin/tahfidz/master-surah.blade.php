@extends('layouts.admin.app')

@section('title', 'Master Data Surah Al-Qur\'an')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <div class="flex items-center gap-2 text-emerald-600 font-semibold text-sm mb-1">
                <i class="ti ti-book text-lg"></i> Modul Tahfidz
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Master Data Surah Al-Qur'an</h1>
            <p class="text-gray-500 text-sm mt-1">Daftar 114 surah Al-Qur'an untuk acuan setoran hafalan santri.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.tahfidz.index') }}" class="px-4 py-2.5 bg-gray-700 hover:bg-gray-800 text-white rounded-lg font-bold text-sm transition flex items-center gap-2" style="background-color: #374151 !important; color: #ffffff !important;">
                <i class="ti ti-arrow-left"></i> Kembali ke Setoran
            </a>
            <button onclick="document.getElementById('modalTambahSurah').classList.remove('hidden')" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-sm transition shadow-sm flex items-center gap-2 cursor-pointer" style="background-color: #16a34a !important; color: #ffffff !important;">
                <i class="ti ti-plus"></i> + Tambah Surah Baru
            </button>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3">
            <i class="ti ti-circle-check text-xl"></i>
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Main Table Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                <i class="ti ti-list text-emerald-600"></i> Daftar Surah Al-Qur'an
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 font-bold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">No. Surah</th>
                        <th class="px-6 py-3">Nama Latin</th>
                        <th class="px-6 py-3 text-right">Nama Arab</th>
                        <th class="px-6 py-3">Arti / Makna</th>
                        <th class="px-6 py-3 text-center">Jumlah Ayat</th>
                        <th class="px-6 py-3 text-center">Tempat Turun</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($surahs as $surah)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-bold text-gray-800">
                                <span class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-700 inline-flex items-center justify-center text-xs border border-emerald-200">
                                    {{ $surah->nomor_surah }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-800">
                                {{ $surah->nama_latin }}
                            </td>
                            <td class="px-6 py-4 text-right text-xl font-serif text-emerald-900 font-bold">
                                {{ $surah->nama_arab }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $surah->arti }}
                            </td>
                            <td class="px-6 py-4 text-center font-medium">
                                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-md text-xs font-semibold">
                                    {{ $surah->jumlah_ayat }} Ayat
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold {{ strtolower($surah->tempat_turun) === 'makkiyah' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                    {{ ucfirst($surah->tempat_turun) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" 
                                            data-id="{{ $surah->id }}"
                                            data-nomor="{{ $surah->nomor_surah }}"
                                            data-ayat="{{ $surah->jumlah_ayat }}"
                                            data-latin="{{ $surah->nama_latin }}"
                                            data-arab="{{ $surah->nama_arab }}"
                                            data-arti="{{ $surah->arti }}"
                                            data-turun="{{ $surah->tempat_turun }}"
                                            onclick="openEditSurahModal(this)"
                                            class="px-2.5 py-1.5 bg-amber-50 text-amber-700 hover:bg-amber-100 rounded-lg text-xs font-bold border border-amber-200 transition inline-flex items-center gap-1 cursor-pointer"
                                            title="Edit Surah">
                                        <i class="ti ti-pencil"></i> Edit
                                    </button>
                                    <form action="{{ url('/admin/tahfidz/master-surah/' . $surah->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus surah {{ $surah->nama_latin }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="px-2.5 py-1.5 bg-red-50 text-red-700 hover:bg-red-100 rounded-lg text-xs font-bold border border-red-200 transition inline-flex items-center gap-1 cursor-pointer"
                                                title="Hapus Surah">
                                            <i class="ti ti-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                <i class="ti ti-book-off text-4xl mb-2 block text-gray-300"></i>
                                Belum ada data surah tersimpan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($surahs->hasPages())
            <div class="p-5 border-t border-gray-100">
                {{ $surahs->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modal Tambah Surah --}}
<div id="modalTambahSurah" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="ti ti-plus text-emerald-600"></i> Tambah Data Surah Baru
            </h3>
            <button onclick="document.getElementById('modalTambahSurah').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>

        <form action="{{ route('admin.tahfidz.store_surah') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nomor Surah</label>
                    <input type="number" name="nomor_surah" required min="1" max="114" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Jumlah Ayat</label>
                    <input type="number" name="jumlah_ayat" required min="1" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Latin (e.g. Al-Fatihah)</label>
                <input type="text" name="nama_latin" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Arab (e.g. الفاتحة)</label>
                <input type="text" name="nama_arab" required class="w-full px-3 py-2 border rounded-lg text-sm text-right font-serif focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Arti / Terjemahan</label>
                <input type="text" name="arti" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tempat Turun</label>
                <select name="tempat_turun" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="Makkiyah">Makkiyah</option>
                    <option value="Madaniyah">Madaniyah</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modalTambahSurah').classList.add('hidden')" class="px-4 py-2 bg-gray-700 text-white rounded-lg text-sm font-bold" style="background-color: #374151 !important; color: #ffffff !important;">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 cursor-pointer" style="background-color: #16a34a !important; color: #ffffff !important;">
                    Simpan Surah
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Surah --}}
<div id="modalEditSurah" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="ti ti-pencil text-amber-600"></i> Edit Data Surah
            </h3>
            <button onclick="document.getElementById('modalEditSurah').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>

        <form id="formEditSurah" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nomor Surah</label>
                    <input type="number" id="edit_nomor_surah" name="nomor_surah" required min="1" max="114" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Jumlah Ayat</label>
                    <input type="number" id="edit_jumlah_ayat" name="jumlah_ayat" required min="1" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Latin (e.g. Al-Fatihah)</label>
                <input type="text" id="edit_nama_latin" name="nama_latin" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Arab (e.g. الفاتحة)</label>
                <input type="text" id="edit_nama_arab" name="nama_arab" required class="w-full px-3 py-2 border rounded-lg text-sm text-right font-serif focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Arti / Terjemahan</label>
                <input type="text" id="edit_arti" name="arti" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tempat Turun</label>
                <select id="edit_tempat_turun" name="tempat_turun" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="Makkiyah">Makkiyah</option>
                    <option value="Madaniyah">Madaniyah</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modalEditSurah').classList.add('hidden')" class="px-4 py-2 bg-gray-700 text-white rounded-lg text-sm font-bold" style="background-color: #374151 !important; color: #ffffff !important;">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-bold hover:bg-amber-700 cursor-pointer" style="background-color: #d97706 !important; color: #ffffff !important;">
                    Update Surah
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditSurahModal(btn) {
    const id = btn.getAttribute('data-id');
    const form = document.getElementById('formEditSurah');
    form.action = "{{ url('/admin/tahfidz/master-surah') }}/" + id;
    document.getElementById('edit_nomor_surah').value = btn.getAttribute('data-nomor');
    document.getElementById('edit_jumlah_ayat').value = btn.getAttribute('data-ayat');
    document.getElementById('edit_nama_latin').value = btn.getAttribute('data-latin');
    document.getElementById('edit_nama_arab').value = btn.getAttribute('data-arab');
    document.getElementById('edit_arti').value = btn.getAttribute('data-arti');
    document.getElementById('edit_tempat_turun').value = btn.getAttribute('data-turun');
    document.getElementById('modalEditSurah').classList.remove('hidden');
}
</script>
@endsection
