@extends('layouts.admin.app')

@section('title', 'Kenaikan Kelas & Kelulusan')

@section('content')
<div class="bg-gray-100 min-h-screen">
    <div class="w-full py-6 px-4 lg:px-8">

        <!-- Alerts -->
        @if(session('success'))
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
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="ti ti-stairs-up text-teal-600"></i> Kenaikan Kelas & Kelulusan Santri
                </h1>
                <p class="text-gray-600 text-sm mt-1">Kelola kenaikan jenjang tahunan otomatis: Kelas 1 &rarr; Kelas 2 &rarr; Kelas 3 &rarr; Alumni.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.alumni.index') }}" class="px-4 py-2.5 bg-gray-700 hover:bg-gray-800 text-white rounded-lg font-bold text-sm transition shadow-sm flex items-center gap-2" style="background-color: #374151 !important; color: #ffffff !important;">
                    <i class="ti ti-school text-base"></i> Buka Menu Alumni
                </a>
                <button type="button" onclick="openPromoteModal()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-sm transition shadow-sm flex items-center gap-2 cursor-pointer" style="background-color: #16a34a !important; color: #ffffff !important;">
                    <i class="ti ti-player-play text-base"></i> + Proses Kenaikan Serentak
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <!-- Kelas 1 -->
            <a href="{{ route('admin.santri.kenaikan_kelas', ['kelas' => '1']) }}" class="bg-white rounded-xl p-5 shadow-sm border {{ $selectedKelas === '1' ? 'border-teal-500 ring-2 ring-teal-200' : 'border-gray-200 hover:border-gray-300' }} transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-teal-700 uppercase bg-teal-50 px-2.5 py-1 rounded">Kelas 1</span>
                    <i class="ti ti-users text-xl text-teal-600"></i>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ number_format($counts['1']) }}</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $counts['1_eligible'] }} siap naik ke Kelas 2
                    @if($counts['1_baru'] > 0)
                        &bull; <span class="text-teal-600 font-semibold">{{ $counts['1_baru'] }} santri baru</span>
                    @endif
                </div>
            </a>

            <!-- Kelas 2 -->
            <a href="{{ route('admin.santri.kenaikan_kelas', ['kelas' => '2']) }}" class="bg-white rounded-xl p-5 shadow-sm border {{ $selectedKelas === '2' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-200 hover:border-gray-300' }} transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-blue-700 uppercase bg-blue-50 px-2.5 py-1 rounded">Kelas 2</span>
                    <i class="ti ti-users text-xl text-blue-600"></i>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ number_format($counts['2']) }}</div>
                <div class="text-xs text-gray-500 mt-1">Santri Madya &rarr; Naik ke Kelas 3</div>
            </a>

            <!-- Kelas 3 -->
            <a href="{{ route('admin.santri.kenaikan_kelas', ['kelas' => '3']) }}" class="bg-white rounded-xl p-5 shadow-sm border {{ $selectedKelas === '3' ? 'border-amber-500 ring-2 ring-amber-200' : 'border-gray-200 hover:border-gray-300' }} transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-amber-700 uppercase bg-amber-50 px-2.5 py-1 rounded">Kelas 3</span>
                    <i class="ti ti-certificate text-xl text-amber-600"></i>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ number_format($counts['3']) }}</div>
                <div class="text-xs text-gray-500 mt-1">Santri Akhir &rarr; Menjadi Alumni</div>
            </a>

            <!-- Total Alumni -->
            <a href="{{ route('admin.alumni.index') }}" class="bg-white rounded-xl p-5 shadow-sm border border-gray-200 hover:border-purple-300 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-purple-700 uppercase bg-purple-50 px-2.5 py-1 rounded">Buku Alumni</span>
                    <i class="ti ti-school text-xl text-purple-600"></i>
                </div>
                <div class="text-2xl font-black text-gray-900">{{ number_format($counts['alumni']) }}</div>
                <div class="text-xs text-gray-500 mt-1">Total Santri Lulus</div>
            </a>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" action="{{ route('admin.santri.kenaikan_kelas') }}" class="flex flex-col md:flex-row gap-3">
                <div class="flex-1 relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari santri berdasarkan nama, NISN, atau NIS..."
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm">
                    <i class="ti ti-search absolute left-3.5 top-3 text-gray-400 text-lg"></i>
                </div>
                <div class="w-full md:w-56">
                    <select name="kelas" onchange="this.form.submit()" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                        <option value="all" {{ $selectedKelas === 'all' ? 'selected' : '' }}>Semua Kelas (1, 2, 3)</option>
                        <option value="1" {{ $selectedKelas === '1' ? 'selected' : '' }}>Khusus Kelas 1 ({{ $counts['1'] }})</option>
                        <option value="2" {{ $selectedKelas === '2' ? 'selected' : '' }}>Khusus Kelas 2 ({{ $counts['2'] }})</option>
                        <option value="3" {{ $selectedKelas === '3' ? 'selected' : '' }}>Khusus Kelas 3 ({{ $counts['3'] }})</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-bold transition flex items-center gap-2 cursor-pointer" style="background-color: #16a34a !important; color: #ffffff !important;">
                        <i class="ti ti-search text-base"></i> Cari
                    </button>
                    <a href="{{ route('admin.santri.kenaikan_kelas') }}" class="px-4 py-2.5 bg-gray-700 hover:bg-gray-800 text-white rounded-lg transition text-sm font-bold flex items-center gap-1" style="background-color: #374151 !important; color: #ffffff !important;">
                        <i class="ti ti-refresh text-base"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table Data Santri -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-bold text-gray-900">Daftar Santri Aktif</h2>
                    <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full font-medium">
                        {{ $santriList->total() }} Santri
                    </span>
                </div>
                <p class="text-xs text-gray-500">Gunakan kolom aksi untuk mengubah kelas santri secara perorangan.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-gray-600 font-semibold text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3.5">Santri</th>
                            <th class="px-6 py-3.5">NIS / NISN</th>
                            <th class="px-6 py-3.5 text-center">Kelas Saat Ini</th>
                            <th class="px-6 py-3.5">Wali / No. WA</th>
                            <th class="px-6 py-3.5 text-center">Proyeksi Kenaikan</th>
                            <th class="px-6 py-3.5 text-center">Aksi Mandiri</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($santriList as $santri)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $santri->nama_lengkap }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        {{ $santri->tahun_masuk ? 'Masuk: ' . $santri->tahun_masuk : ($santri->tahun_ajaran ? 'TA: ' . $santri->tahun_ajaran : '') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-mono text-gray-700">NIS: <span class="font-semibold">{{ $santri->nis ?? '-' }}</span></div>
                                    <div class="text-xs font-mono text-gray-500">NISN: {{ $santri->nisn ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($santri->kelas == '1')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-teal-100 text-teal-800">
                                            Kelas 1
                                        </span>
                                    @elseif($santri->kelas == '2')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                            Kelas 2
                                        </span>
                                    @elseif($santri->kelas == '3')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                            Kelas 3
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                                            {{ ucfirst($santri->kelas) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-medium text-gray-800">{{ $santri->orangTua?->nama_ayah ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                                        <i class="ti ti-brand-whatsapp text-green-600"></i> {{ $santri->orangTua?->no_wa_ayah ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($santri->tahun_masuk && (int)$santri->tahun_masuk > ($currentYear ?? (int)date('Y')))
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-700 border border-teal-200">
                                            <i class="ti ti-sparkles text-xs"></i> Santri Baru (Tetap Kelas 1)
                                        </span>
                                    @elseif($santri->kelas == '1')
                                        <span class="text-xs font-semibold text-blue-600 flex items-center justify-center gap-1">
                                            &rarr; Naik ke Kelas 2
                                        </span>
                                    @elseif($santri->kelas == '2')
                                        <span class="text-xs font-semibold text-amber-600 flex items-center justify-center gap-1">
                                            &rarr; Naik ke Kelas 3
                                        </span>
                                    @elseif($santri->kelas == '3')
                                        <span class="text-xs font-semibold text-purple-600 flex items-center justify-center gap-1">
                                            &rarr; Lulus ke Alumni ({{ date('Y') }})
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button type="button" onclick="openEditIndividualModal({{ $santri->id }}, '{{ addslashes($santri->nama_lengkap) }}', '{{ $santri->kelas }}')"
                                            class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded-md transition inline-flex items-center gap-1 cursor-pointer">
                                        <i class="ti ti-edit text-sm"></i> Ubah Kelas
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <i class="ti ti-user-x text-3xl text-gray-400 mb-2 block"></i>
                                    Tidak ada data santri pada filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($santriList->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $santriList->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

<!-- Modal Kenaikan Serentak -->
<div id="promoteModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden animate-in fade-in">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 font-bold">
                    <i class="ti ti-stairs-up text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Proses Kenaikan Kelas Serentak</h3>
                    <p class="text-xs text-gray-500">Pergantian tahun ajaran seluruh santri aktif</p>
                </div>
            </div>
            <button type="button" onclick="closePromoteModal()" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <i class="ti ti-x text-2xl"></i>
            </button>
        </div>

        <form action="{{ route('admin.santri.kenaikan_kelas.process') }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-800 space-y-2">
                    <div class="font-bold flex items-center gap-1.5 text-amber-900">
                        <i class="ti ti-alert-triangle text-base"></i> Informasi Perubahan:
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-amber-800">
                        <li>Santri <strong>Kelas 1</strong> ({{ $counts['1_eligible'] }} santri) akan naik ke <strong>Kelas 2</strong>.</li>
                        @if($counts['1_baru'] > 0)
                            <li class="text-teal-800 font-semibold">&bull; {{ $counts['1_baru'] }} santri baru angkatan mendatang akan <strong>tetap di Kelas 1</strong>.</li>
                        @endif
                        <li>Santri <strong>Kelas 2</strong> ({{ $counts['2'] }} santri) akan naik ke <strong>Kelas 3</strong>.</li>
                        <li>Santri <strong>Kelas 3</strong> ({{ $counts['3'] }} santri) akan <strong>Lulus menjadi Alumni</strong>.</li>
                    </ul>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                        Tahun Kelulusan Alumni (Kelas 3)
                    </label>
                    <input type="number" name="tahun_lulus" value="{{ date('Y') }}" min="2000" max="2099" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 font-bold text-gray-800">
                    <p class="text-[11px] text-gray-500 mt-1">Tahun ini akan tercatat di profil santri kelas 3 yang lulus.</p>
                </div>

                <div class="pt-2 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closePromoteModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-100 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-sm transition flex items-center gap-2 cursor-pointer shadow-sm" style="background-color: #16a34a !important; color: #ffffff !important;">
                        <i class="ti ti-check text-base"></i> Ya, Jalankan Kenaikan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Individual -->
<div id="editIndividualModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden animate-in fade-in">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900">Ubah Jenjang Kelas Santri</h3>
            <button type="button" onclick="closeEditIndividualModal()" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>

        <form id="formEditIndividual" method="POST">
            @csrf
            @method('PUT')
            <div class="p-5 space-y-4">
                <div>
                    <span class="text-xs text-gray-500">Nama Santri:</span>
                    <h4 id="modalSantriName" class="font-bold text-gray-900 text-sm"></h4>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Pilih Jenjang Kelas</label>
                    <select name="kelas" id="modalSelectKelas" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                        <option value="1">Kelas 1</option>
                        <option value="2">Kelas 2</option>
                        <option value="3">Kelas 3</option>
                        <option value="alumni">Alumni (Lulus)</option>
                    </select>
                </div>

                <div id="tahunLulusWrapper" class="hidden">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tahun Lulus</label>
                    <input type="number" name="tahun_lulus" id="modalTahunLulus" value="{{ date('Y') }}" min="2000" max="2099"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                </div>

                <div class="pt-2 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeEditIndividualModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-100 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg text-sm transition cursor-pointer shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openPromoteModal() {
        document.getElementById('promoteModal').classList.remove('hidden');
    }
    function closePromoteModal() {
        document.getElementById('promoteModal').classList.add('hidden');
    }

    function openEditIndividualModal(id, name, kelas) {
        document.getElementById('modalSantriName').innerText = name;
        document.getElementById('modalSelectKelas').value = kelas;
        const form = document.getElementById('formEditIndividual');
        form.action = "{{ url('/admin/santri/kenaikan-kelas') }}/" + id;

        toggleTahunLulusInput(kelas);
        document.getElementById('editIndividualModal').classList.remove('hidden');
    }
    function closeEditIndividualModal() {
        document.getElementById('editIndividualModal').classList.add('hidden');
    }

    document.getElementById('modalSelectKelas').addEventListener('change', function() {
        toggleTahunLulusInput(this.value);
    });

    function toggleTahunLulusInput(kelas) {
        const wrapper = document.getElementById('tahunLulusWrapper');
        if (kelas === 'alumni') {
            wrapper.classList.remove('hidden');
        } else {
            wrapper.classList.add('hidden');
        }
    }
</script>
@endsection
