@extends('layouts.admin.app')

@section('title', 'Data Alumni')

@section('content')
<div class="bg-gray-100 min-h-screen">
    <div class="w-full py-6 px-4 lg:px-8">

        <!-- Alerts -->
        @if(session('import_errors'))
            <div class="mb-5 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <i class="ti ti-alert-triangle text-2xl text-red-600 flex-shrink-0"></i>
                    <div>
                        <h4 class="text-sm font-bold text-red-800">Laporan Hasil Import Excel Alumni</h4>
                        <p class="text-sm text-red-700">{{ session('success') }}</p>
                    </div>
                </div>
                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full text-xs text-left text-red-900 border border-red-200 bg-white rounded-lg">
                        <thead class="bg-red-100 font-bold uppercase text-red-800">
                            <tr>
                                <th class="px-3 py-2 border-b">Baris Excel</th>
                                <th class="px-3 py-2 border-b">Nama Alumni</th>
                                <th class="px-3 py-2 border-b">Detail Error / Alasan Gagal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-red-100">
                            @foreach(session('import_errors') as $err)
                                <tr>
                                    <td class="px-3 py-2 font-mono font-semibold text-red-700">Baris {{ $err['row'] ?? '-' }}</td>
                                    <td class="px-3 py-2 font-medium">{{ $err['nama'] ?? '-' }}</td>
                                    <td class="px-3 py-2 text-red-600 font-mono">{{ $err['error'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif(session('success'))
            <div class="mb-5 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg shadow-sm flex items-start gap-3">
                <i class="ti ti-circle-check text-2xl text-green-600 flex-shrink-0 mt-0.5"></i>
                <div>
                    <h4 class="text-sm font-bold text-green-800">Berhasil!</h4>
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm flex items-start gap-3">
                <i class="ti ti-alert-circle text-2xl text-red-600 flex-shrink-0 mt-0.5"></i>
                <div>
                    <h4 class="text-sm font-bold text-red-800">Perhatian</h4>
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Header -->
        <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="ti ti-school text-purple-600"></i> Buku Induk Alumni
                </h1>
                <p class="text-gray-600 text-sm mt-1">Data lulusan santri pondok pesantren dan integrasi akun wali di menu Pengguna.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.alumni.export', request()->query()) }}" class="px-3.5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-xs transition shadow-sm flex items-center gap-1.5" style="background-color: #2563eb !important; color: #ffffff !important;">
                    <i class="ti ti-file-export text-base"></i> Export Data (XLSX)
                </a>
                <a href="{{ route('admin.alumni.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs transition shadow-sm flex items-center gap-1.5" style="background-color: #16a34a !important; color: #ffffff !important;">
                    <i class="ti ti-plus text-base"></i> + Tambah Alumni Baru
                </a>
            </div>
        </div>

        <!-- Filter & Search Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" action="{{ route('admin.alumni.index') }}" class="flex flex-col md:flex-row gap-3">
                <div class="flex-1 relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari alumni berdasarkan nama, NISN, NIS, nama ayah, atau no WA..."
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm">
                    <i class="ti ti-search absolute left-3.5 top-3 text-gray-400 text-lg"></i>
                </div>
                <div class="w-full md:w-52">
                    <select name="tahun_lulus" onchange="this.form.submit()" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                        <option value="">Semua Tahun Lulus</option>
                        @foreach($tahunLulusList as $tahun)
                            <option value="{{ $tahun }}" {{ request('tahun_lulus') == $tahun ? 'selected' : '' }}>
                                Lulusan {{ $tahun }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-bold transition flex items-center gap-1.5 cursor-pointer" style="background-color: #16a34a !important; color: #ffffff !important;">
                        <i class="ti ti-search text-base"></i> Filter
                    </button>
                    <a href="{{ route('admin.alumni.index') }}" class="px-4 py-2.5 bg-gray-700 hover:bg-gray-800 text-white rounded-lg transition text-sm font-bold flex items-center gap-1" style="background-color: #374151 !important; color: #ffffff !important;">
                        <i class="ti ti-refresh text-base"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table Data Alumni -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-bold text-gray-900">Daftar Data Alumni</h2>
                    <span class="text-xs bg-purple-100 text-purple-700 px-2.5 py-0.5 rounded-full font-bold">
                        {{ $alumniList->total() }} Santri Lulus
                    </span>
                </div>
                <div class="text-xs text-gray-500 flex items-center gap-1.5">
                    <i class="ti ti-info-circle text-purple-600"></i>
                    <span>Akun orang tua alumni otomatis aktif di menu <strong>Pengguna</strong>.</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-gray-600 font-semibold text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3.5">Nama Alumni</th>
                            <th class="px-6 py-3.5">NIS / NISN</th>
                            <th class="px-6 py-3.5 text-center">Tahun Masuk / Lulus</th>
                            <th class="px-6 py-3.5">Data Orang Tua / Wali</th>
                            <th class="px-6 py-3.5 text-center">Akun Pengguna</th>
                            <th class="px-6 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($alumniList as $alumni)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $alumni->nama_lengkap }}</div>
                                    @if($alumni->tempat_lahir || $alumni->tanggal_lahir)
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            {{ $alumni->tempat_lahir ? $alumni->tempat_lahir . ', ' : '' }}
                                            {{ $alumni->tanggal_lahir ? $alumni->tanggal_lahir->translatedFormat('d F Y') : '' }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-mono text-gray-700">NIS: <span class="font-semibold">{{ $alumni->nis ?? '-' }}</span></div>
                                    <div class="text-xs font-mono text-gray-500">NISN: {{ $alumni->nisn ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                                        Lulus {{ $alumni->tahun_lulus ?? '-' }}
                                    </span>
                                    <div class="text-[11px] text-gray-500 mt-1">
                                        Masuk: {{ $alumni->tahun_masuk ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-semibold text-gray-800">{{ $alumni->orangTua?->nama_ayah ?? 'Tidak terdata' }}</div>
                                    @if($alumni->orangTua?->no_wa_ayah && $alumni->orangTua?->no_wa_ayah !== '-')
                                        <a href="https://wa.me/{{ $alumni->orangTua->no_wa_ayah }}" target="_blank"
                                           class="text-xs text-green-600 hover:text-green-700 font-medium inline-flex items-center gap-1 mt-0.5">
                                            <i class="ti ti-brand-whatsapp text-sm"></i> {{ $alumni->orangTua->no_wa_ayah }}
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($alumni->user)
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
                                            <i class="ti ti-check text-xs"></i> Terhubung
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-400 bg-gray-50 px-2 py-0.5 rounded">
                                            Belum Ada
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('admin.alumni.edit', $alumni->id) }}"
                                           class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition" title="Edit Alumni">
                                            <i class="ti ti-edit text-lg"></i>
                                        </a>
                                        @if(auth('admin')->user()?->isSuperAdmin())
                                        <button type="button" onclick="confirmDelete({{ $alumni->id }}, '{{ addslashes($alumni->nama_lengkap) }}')"
                                                class="p-1.5 text-red-600 hover:bg-red-50 rounded transition cursor-pointer" title="Hapus Alumni (Khusus Super Admin)">
                                            <i class="ti ti-trash text-lg"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <i class="ti ti-school-off text-3xl text-gray-400 mb-2 block"></i>
                                    Belum ada data alumni yang sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($alumniList->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $alumniList->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

<!-- Modal Import Excel / CSV -->
<div id="importModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden animate-in fade-in">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 font-bold">
                    <i class="ti ti-file-spreadsheet text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Import Massal Alumni (Excel / CSV)</h3>
                    <p class="text-xs text-gray-500">Unggah file spreadsheet XLSX/CSV alumni pondok</p>
                </div>
            </div>
            <button type="button" onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <i class="ti ti-x text-2xl"></i>
            </button>
        </div>

        <form action="{{ route('admin.alumni.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="p-6 space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-3.5 text-xs text-blue-800 space-y-1">
                    <div class="font-bold flex items-center gap-1 text-blue-900">
                        <i class="ti ti-bulb text-sm"></i> Petunjuk Import:
                    </div>
                    <p>1. Gunakan format template resmi Excel yang sudah disediakan.</p>
                    <p>2. Nomor WhatsApp wali akan otomatis dibuatkan akun di menu <strong>Pengguna</strong>.</p>
                    <p>3. Format tanggal lahir: <code>YYYY-MM-DD</code> (contoh: <code>2008-05-14</code>).</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Pilih File Excel (.xlsx / .csv)</label>
                    <input type="file" name="file_excel" accept=".xlsx,.xls,.csv" required
                           class="w-full text-xs text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 border border-gray-300 rounded-lg cursor-pointer">
                </div>

                <div class="pt-2 border-t border-gray-100 flex items-center justify-between">
                    <a href="{{ route('admin.alumni.downloadTemplate') }}" class="text-xs text-purple-600 font-semibold hover:underline flex items-center gap-1">
                        <i class="ti ti-download text-sm"></i> Download Template XLSX
                    </a>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="closeImportModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-xs text-gray-700 hover:bg-gray-100 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs transition flex items-center gap-1.5 cursor-pointer shadow-sm" style="background-color: #16a34a !important; color: #ffffff !important;">
                            <i class="ti ti-upload text-sm"></i> Mulai Import Data
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Delete Confirmation -->
<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    function openImportModal() {
        document.getElementById('importModal').classList.remove('hidden');
    }
    function closeImportModal() {
        document.getElementById('importModal').classList.add('hidden');
    }

    function confirmDelete(id, name) {
        if (confirm("Apakah Anda yakin ingin menghapus data alumni '" + name + "'? Tindakan ini tidak dapat dibatalkan.")) {
            const form = document.getElementById('deleteForm');
            form.action = "{{ url('/admin/alumni') }}/" + id;
            form.submit();
        }
    }
</script>
@endsection
