@extends('layouts.admin.app')

@section('title', 'Tempat Sampah Santri')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center text-red-600 shadow-sm">
                        <i class="ti ti-trash text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Tempat Sampah Santri</h1>
                        <p class="text-sm text-gray-500">Kelola santri yang dipindahkan ke tempat sampah. Data dapat dipulihkan atau dihapus permanen.</p>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.santri.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 transition shadow-sm">
                    <i class="ti ti-arrow-left"></i> Kembali ke Data Santri
                </a>
                @if($trashedSantri->total() > 0)
                    <form method="POST" action="{{ route('admin.santri.restore_all') }}" onsubmit="return confirm('Apakah Anda yakin ingin memulihkan SELURUH data santri di tempat sampah ini?')">
                        @csrf
                        <button type="submit" 
                                style="background-color: #059669; color: #ffffff;"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold hover:opacity-90 transition shadow-sm border-0 cursor-pointer">
                            <i class="ti ti-rotate-clockwise"></i> Pulihkan Semua
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.santri.empty_trash') }}" onsubmit="return confirm('PERINGATAN KERAS!\n\nApakah Anda benar-benar yakin ingin MENGOSONGKAN Tempat Sampah?\n\nSemua data santri di tempat sampah ini akan DIMUSNAHKAN SECARA PERMANEN dan TIDAK BISA DIKEMBALIKAN!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                style="background-color: #dc2626; color: #ffffff;"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold hover:opacity-90 transition shadow-sm border-0 cursor-pointer">
                            <i class="ti ti-trash-x"></i> Kosongkan Tempat Sampah
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Alert Notifikasi -->
        @if(session('success'))
            <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 flex items-center gap-3">
                <i class="ti ti-circle-check text-xl text-emerald-600"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-800 flex items-center gap-3">
                <i class="ti ti-alert-triangle text-xl text-red-600"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Card Container -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Search & Filter Bar -->
            <div class="p-4 sm:p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <form method="GET" action="{{ route('admin.santri.trash') }}" class="flex-1 max-w-md">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="ti ti-search text-lg"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Cari nama santri, NISN, NIK, atau nama ayah..." 
                               class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition">
                    </div>
                </form>
                <div class="text-xs text-gray-500 font-semibold">
                    Total di Tempat Sampah: <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-bold">{{ $trashedSantri->total() }} Santri</span>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-700">
                    <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Nama Santri</th>
                            <th class="py-3 px-4">Kelas & Angkatan</th>
                            <th class="py-3 px-4">Wali Santri</th>
                            <th class="py-3 px-4">Tanggal Dihapus</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($trashedSantri as $index => $item)
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="py-3 px-4 text-center font-medium text-gray-400">
                                {{ $trashedSantri->firstItem() + $index }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-gray-900">{{ $item->nama_lengkap }}</div>
                                <div class="text-xs text-gray-500 font-mono">NISN: {{ $item->nisn ?: '-' }} · NIK: {{ $item->nik ?: '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-teal-50 text-teal-700 border border-teal-200">
                                    {{ $item->kelas_sekarang ?: 'Kelas ' . $item->kelas }}
                                </span>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $item->angkatan ? $item->angkatan->nama_angkatan : ($item->tahun_masuk ? 'Tahun ' . $item->tahun_masuk : '-') }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-800">{{ $item->orangTua?->nama_ayah ?: '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $item->orangTua?->no_wa_ayah ?: '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-xs text-red-600 font-medium">
                                <i class="ti ti-clock mr-1"></i>
                                {{ $item->deleted_at ? $item->deleted_at->translatedFormat('d M Y, H:i') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <!-- Tombol Pulihkan (Restore) -->
                                    <form method="POST" action="{{ route('admin.santri.restore', $item->id) }}"
                                          onsubmit="return confirm('Pulihkan data santri {{ $item->nama_lengkap }} kembali ke data aktif?')">
                                        @csrf
                                        <button type="submit" 
                                                style="background-color: #059669; color: #ffffff;"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold hover:opacity-90 transition shadow-sm border-0 cursor-pointer"
                                                title="Pulihkan Santri ke Data Aktif">
                                            <i class="ti ti-rotate-clockwise text-sm"></i> Pulihkan
                                        </button>
                                    </form>

                                    <!-- Tombol Hapus Permanen (Force Delete) -->
                                    <form method="POST" action="{{ route('admin.santri.force_delete', $item->id) }}"
                                          onsubmit="return confirm('PERINGATAN!\n\nApakah Anda benar-benar yakin ingin menghapus santri {{ $item->nama_lengkap }} secara PERMANEN?\n\nData yang dihapus permanen TIDAK BISA dipulihkan kembali!')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                style="background-color: #dc2626; color: #ffffff;"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold hover:opacity-90 transition shadow-sm border-0 cursor-pointer"
                                                title="Hapus Permanen dari Database">
                                            <i class="ti ti-trash-x text-sm"></i> Hapus Permanen
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center text-gray-400">
                                <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-500 mx-auto flex items-center justify-center mb-3">
                                    <i class="ti ti-check text-3xl"></i>
                                </div>
                                <h3 class="text-base font-bold text-gray-700">Tempat Sampah Bersih</h3>
                                <p class="text-xs text-gray-500 mt-1">Tidak ada data santri yang sedang berada di Tempat Sampah.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($trashedSantri->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $trashedSantri->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
