@extends('layouts.admin.app')

@section('title', 'Input Absensi Harian')

@section('content')
<div class="p-6 space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="ti ti-pencil-plus text-green-600 text-3xl"></i>
                Input Absensi Kelas {{ $kelas }} &mdash; {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Tandai status kehadiran setiap santri pada hari ini.
            </p>
        </div>
        <a href="{{ route('admin.absensi.index') }}"
           class="inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-800 text-white text-sm font-bold px-4 py-2 rounded-lg transition"
           style="background-color: #374151 !important; color: #ffffff !important;">
            <i class="ti ti-arrow-left"></i>
            Kembali
        </a>
    </div>

    {{-- Alert Session --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3">
            <i class="ti ti-circle-check text-green-500 text-xl shrink-0"></i>
            <span class="text-sm">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3">
            <i class="ti ti-alert-circle text-red-500 text-xl shrink-0"></i>
            <span class="text-sm">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Banner: Status Absensi --}}
    @php
        $totalSantri   = count($santriList);
        $totalTerabsen = collect($santriList)->filter(fn($s) => isset($existingAbsensi[$s->id]))->count();
        $semuaSudah    = $totalSantri > 0 && $totalTerabsen === $totalSantri;
    @endphp
    @if($semuaSudah)
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-xl px-5 py-4 shadow-sm">
            <i class="ti ti-circle-check-filled text-emerald-500 text-2xl shrink-0"></i>
            <div class="flex-1">
                <p class="font-bold text-sm">Absensi sudah lengkap tersimpan!</p>
                <p class="text-xs text-emerald-600 mt-0.5">
                    Semua {{ $totalSantri }} santri Kelas {{ $kelas }} pada tanggal
                    <strong>{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</strong>
                    sudah diabsen. Jika ada kesalahan data, silakan klik tombol <strong>Edit</strong> pada baris santri yang bersangkutan, lalu simpan kembali.
                </p>
            </div>
        </div>
    @elseif($totalTerabsen > 0)
        <div class="flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl px-5 py-4 shadow-sm">
            <i class="ti ti-info-circle text-blue-500 text-2xl shrink-0"></i>
            <p class="text-sm">
                <strong>{{ $totalTerabsen }} dari {{ $totalSantri }} santri</strong> sudah terabsen.
                Lengkapi santri yang belum diabsen, atau klik <strong>Edit</strong> untuk mengubah yang sudah tercatat.
            </p>
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('admin.absensi.input') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</label>
                <input type="date"
                       name="tanggal"
                       value="{{ \Carbon\Carbon::parse($tanggal)->format('Y-m-d') }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Kelas</label>
                <select name="kelas"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="1" {{ $kelas == '1' ? 'selected' : '' }}>Kelas 1</option>
                    <option value="2" {{ $kelas == '2' ? 'selected' : '' }}>Kelas 2</option>
                    <option value="3" {{ $kelas == '3' ? 'selected' : '' }}>Kelas 3</option>
                </select>
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-bold px-4 py-2 rounded-lg transition"
                    style="background-color: #374151 !important; color: #ffffff !important;">
                <i class="ti ti-refresh"></i>
                Ganti Kelas / Tanggal
            </button>
        </form>
    </div>

    {{-- Input Form --}}
    <form method="POST" action="{{ route('admin.absensi.storeSantri') }}" id="formAbsensi">
        @csrf
        <input type="hidden" name="tanggal" value="{{ \Carbon\Carbon::parse($tanggal)->format('Y-m-d') }}">
        <input type="hidden" name="kelas" value="{{ $kelas }}">

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

            {{-- Toolbar --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 py-3 bg-gray-50 border-b border-gray-200">
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <i class="ti ti-users text-green-600 text-lg"></i>
                    <span>Total: <strong class="text-gray-800">{{ $totalSantri }}</strong> santri</span>
                    @if($totalTerabsen > 0)
                        <span class="text-xs text-emerald-700 font-bold bg-emerald-100 border border-emerald-200 px-2 py-0.5 rounded-full">
                            {{ $totalTerabsen }} Terabsen
                        </span>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button"
                            onclick="markAllStatus('hadir')"
                            class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition cursor-pointer"
                            style="background-color: #16a34a !important; color: #ffffff !important;">
                        <i class="ti ti-circle-check"></i>
                        Semua Hadir
                    </button>
                    <button type="button"
                            onclick="markAllStatus('alpa')"
                            class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition cursor-pointer"
                            style="background-color: #dc2626 !important; color: #ffffff !important;">
                        <i class="ti ti-x"></i>
                        Semua Alpa
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition cursor-pointer"
                            style="background-color: #16a34a !important; color: #ffffff !important;">
                        <i class="ti ti-device-floppy"></i>
                        Simpan Semua
                    </button>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-10">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Santri</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-green-600 uppercase tracking-wider">
                                <span class="inline-flex items-center gap-1"><i class="ti ti-circle-check"></i> Hadir</span>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-yellow-600 uppercase tracking-wider">
                                <span class="inline-flex items-center gap-1"><i class="ti ti-heart-broken"></i> Sakit</span>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-blue-600 uppercase tracking-wider">
                                <span class="inline-flex items-center gap-1"><i class="ti ti-file-description"></i> Izin</span>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-red-600 uppercase tracking-wider">
                                <span class="inline-flex items-center gap-1"><i class="ti ti-x"></i> Alpa</span>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider min-w-[160px]">Catatan</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-28">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="santriTableBody">
                        @forelse($santriList as $index => $santri)
                            @php
                                $existing       = $existingAbsensi[$santri->id] ?? null;
                                $existingStatus = $existing?->status_kehadiran ?? null;
                                $existingCatatan = $existing?->catatan ?? '';
                                $sudahAbsen     = !is_null($existingStatus);

                                $badgeConfig = [
                                    'hadir' => ['bg-emerald-100 text-emerald-800 border-emerald-300', 'ti-circle-check', 'Hadir'],
                                    'sakit' => ['bg-yellow-100 text-yellow-800 border-yellow-300',  'ti-heartbeat',    'Sakit'],
                                    'izin'  => ['bg-blue-100 text-blue-800 border-blue-300',          'ti-id',           'Izin'],
                                    'alpa'  => ['bg-red-100 text-red-800 border-red-300',             'ti-x',            'Alpa'],
                                ];
                                $badge = $badgeConfig[$existingStatus] ?? null;
                            @endphp

                            <tr class="hover:bg-gray-50 transition status-row {{ $sudahAbsen ? 'row-locked' : '' }}"
                                id="row-{{ $index }}"
                                data-locked="{{ $sudahAbsen ? 'true' : 'false' }}">

                                {{-- No --}}
                                <td class="px-4 py-3 text-gray-400 text-xs">{{ $index + 1 }}</td>

                                {{-- Nama --}}
                                <td class="px-4 py-3">
                                    <input type="hidden" name="absensi[{{ $index }}][santri_id]" value="{{ $santri->id }}">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center shrink-0 text-green-700 font-bold text-xs uppercase">
                                            {{ substr($santri->nama_lengkap ?? $santri->name ?? '?', 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-800">{{ $santri->nama_lengkap ?? $santri->name ?? '-' }}</p>
                                            <p class="text-xs text-gray-400">{{ $santri->nis ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Radio: Hadir --}}
                                <td class="px-4 py-3 text-center">
                                    <label class="cursor-pointer inline-flex items-center justify-center">
                                        <input type="radio"
                                               name="absensi[{{ $index }}][status]"
                                               value="hadir"
                                               class="sr-only status-radio"
                                               data-row="{{ $index }}"
                                               data-status="hadir"
                                               {{ $existingStatus === 'hadir' ? 'checked' : '' }}
                                               onchange="highlightRow({{ $index }}, 'hadir')"
                                               {{ $sudahAbsen ? 'disabled' : '' }}>
                                        <span class="w-8 h-8 rounded-full border-2 flex items-center justify-center transition
                                            radio-display-hadir-{{ $index }}
                                            {{ $existingStatus === 'hadir' ? 'border-green-500 bg-green-500 text-white' : 'border-gray-300 text-gray-300 hover:border-green-400 hover:text-green-400' }}
                                            {{ $sudahAbsen && $existingStatus !== 'hadir' ? 'opacity-30' : '' }}">
                                            <i class="ti ti-check text-sm"></i>
                                        </span>
                                    </label>
                                </td>

                                {{-- Radio: Sakit --}}
                                <td class="px-4 py-3 text-center">
                                    <label class="cursor-pointer inline-flex items-center justify-center">
                                        <input type="radio"
                                               name="absensi[{{ $index }}][status]"
                                               value="sakit"
                                               class="sr-only status-radio"
                                               data-row="{{ $index }}"
                                               data-status="sakit"
                                               {{ $existingStatus === 'sakit' ? 'checked' : '' }}
                                               onchange="highlightRow({{ $index }}, 'sakit')"
                                               {{ $sudahAbsen ? 'disabled' : '' }}>
                                        <span class="w-8 h-8 rounded-full border-2 flex items-center justify-center transition
                                            radio-display-sakit-{{ $index }}
                                            {{ $existingStatus === 'sakit' ? 'border-yellow-500 bg-yellow-500 text-white' : 'border-gray-300 text-gray-300 hover:border-yellow-400 hover:text-yellow-400' }}
                                            {{ $sudahAbsen && $existingStatus !== 'sakit' ? 'opacity-30' : '' }}">
                                            <i class="ti ti-heartbeat text-sm"></i>
                                        </span>
                                    </label>
                                </td>

                                {{-- Radio: Izin --}}
                                <td class="px-4 py-3 text-center">
                                    <label class="cursor-pointer inline-flex items-center justify-center">
                                        <input type="radio"
                                               name="absensi[{{ $index }}][status]"
                                               value="izin"
                                               class="sr-only status-radio"
                                               data-row="{{ $index }}"
                                               data-status="izin"
                                               {{ $existingStatus === 'izin' ? 'checked' : '' }}
                                               onchange="highlightRow({{ $index }}, 'izin')"
                                               {{ $sudahAbsen ? 'disabled' : '' }}>
                                        <span class="w-8 h-8 rounded-full border-2 flex items-center justify-center transition
                                            radio-display-izin-{{ $index }}
                                            {{ $existingStatus === 'izin' ? 'border-blue-500 bg-blue-500 text-white' : 'border-gray-300 text-gray-300 hover:border-blue-400 hover:text-blue-400' }}
                                            {{ $sudahAbsen && $existingStatus !== 'izin' ? 'opacity-30' : '' }}">
                                            <i class="ti ti-id text-sm"></i>
                                        </span>
                                    </label>
                                </td>

                                {{-- Radio: Alpa --}}
                                <td class="px-4 py-3 text-center">
                                    <label class="cursor-pointer inline-flex items-center justify-center">
                                        <input type="radio"
                                               name="absensi[{{ $index }}][status]"
                                               value="alpa"
                                               class="sr-only status-radio"
                                               data-row="{{ $index }}"
                                               data-status="alpa"
                                               {{ $existingStatus === 'alpa' ? 'checked' : '' }}
                                               onchange="highlightRow({{ $index }}, 'alpa')"
                                               {{ $sudahAbsen ? 'disabled' : '' }}>
                                        <span class="w-8 h-8 rounded-full border-2 flex items-center justify-center transition
                                            radio-display-alpa-{{ $index }}
                                            {{ $existingStatus === 'alpa' ? 'border-red-500 bg-red-500 text-white' : 'border-gray-300 text-gray-300 hover:border-red-400 hover:text-red-400' }}
                                            {{ $sudahAbsen && $existingStatus !== 'alpa' ? 'opacity-30' : '' }}">
                                            <i class="ti ti-x text-sm"></i>
                                        </span>
                                    </label>
                                </td>

                                {{-- Catatan --}}
                                <td class="px-4 py-3">
                                    <input type="text"
                                           name="absensi[{{ $index }}][catatan]"
                                           value="{{ $existingCatatan }}"
                                           placeholder="Catatan (opsional)"
                                           {{ $sudahAbsen ? 'readonly' : '' }}
                                           class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm text-gray-700 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition
                                               {{ $sudahAbsen ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : '' }}">
                                </td>

                                {{-- Kolom Keterangan / Tombol Edit --}}
                                <td class="px-4 py-3 text-center">
                                    @if($sudahAbsen && $badge)
                                        <div id="badge-{{ $index }}" class="flex flex-col items-center gap-1">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold border {{ $badge[0] }}">
                                                <i class="ti {{ $badge[1] }}"></i>
                                                {{ $badge[2] }}
                                            </span>
                                            <button type="button"
                                                    onclick="unlockRow({{ $index }})"
                                                    class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-2.5 py-0.5 rounded-md transition cursor-pointer">
                                                <i class="ti ti-edit text-xs"></i> Edit
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-[11px] text-gray-400 italic bg-gray-50 border border-gray-200 px-2 py-0.5 rounded-full">Belum Diabsen</span>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center gap-2">
                                        <i class="ti ti-users-off text-4xl text-gray-300"></i>
                                        <span class="text-sm">Tidak ada santri di kelas ini</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Bottom Submit Bar --}}
            @if(count($santriList) > 0)
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 py-3 bg-gray-50 border-t border-gray-200">
                <p class="text-sm text-gray-500 flex items-center gap-1">
                    <i class="ti ti-info-circle text-blue-400"></i>
                    Santri yang berstatus <strong>Sudah Terabsen</strong> terkunci otomatis. Klik tombol <strong>Edit</strong> untuk membuka dan mengubah data.
                </p>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm px-6 py-2.5 rounded-lg shadow transition cursor-pointer"
                        style="background-color: #16a34a !important; color: #ffffff !important;">
                    <i class="ti ti-device-floppy text-lg"></i>
                    Simpan Semua Absensi
                </button>
            </div>
            @endif

        </div>
    </form>

</div>

@push('scripts')
<script>
    const statusColors = {
        hadir: {
            active:   'border-green-500 bg-green-500 text-white',
            inactive: 'border-gray-300 text-gray-300 hover:border-green-400 hover:text-green-400',
            row:      'bg-green-50/70',
        },
        sakit: {
            active:   'border-yellow-500 bg-yellow-500 text-white',
            inactive: 'border-gray-300 text-gray-300 hover:border-yellow-400 hover:text-yellow-400',
            row:      'bg-yellow-50/70',
        },
        izin: {
            active:   'border-blue-500 bg-blue-500 text-white',
            inactive: 'border-gray-300 text-gray-300 hover:border-blue-400 hover:text-blue-400',
            row:      'bg-blue-50/70',
        },
        alpa: {
            active:   'border-red-500 bg-red-500 text-white',
            inactive: 'border-gray-300 text-gray-300 hover:border-red-400 hover:text-red-400',
            row:      'bg-red-50/70',
        },
    };

    const allStatuses  = ['hadir', 'sakit', 'izin', 'alpa'];
    const allRowColors = ['bg-green-50/70', 'bg-yellow-50/70', 'bg-blue-50/70', 'bg-red-50/70'];

    function highlightRow(rowIndex, selectedStatus) {
        const row = document.getElementById('row-' + rowIndex);
        if (!row) return;
        allRowColors.forEach(c => row.classList.remove(c));
        if (statusColors[selectedStatus]) row.classList.add(statusColors[selectedStatus].row);

        allStatuses.forEach(status => {
            const display = row.querySelector('.radio-display-' + status + '-' + rowIndex);
            if (!display) return;
            Object.values(statusColors).forEach(cs => {
                cs.active.split(' ').forEach(c => display.classList.remove(c));
                cs.inactive.split(' ').forEach(c => display.classList.remove(c));
            });
            display.classList.remove('opacity-30');
            const cs = statusColors[status];
            if (status === selectedStatus) {
                cs.active.split(' ').forEach(c => display.classList.add(c));
            } else {
                cs.inactive.split(' ').forEach(c => display.classList.add(c));
            }
        });
    }

    function markAllStatus(status) {
        document.querySelectorAll('input[type="radio"][value="' + status + '"]').forEach(radio => {
            if (radio.disabled) return;
            radio.checked = true;
            highlightRow(parseInt(radio.getAttribute('data-row')), status);
        });
    }

    /**
     * Buka kunci baris untuk diedit
     */
    function unlockRow(rowIndex) {
        const row   = document.getElementById('row-' + rowIndex);
        const badge = document.getElementById('badge-' + rowIndex);
        if (!row) return;

        // Buka disable radio
        row.querySelectorAll('input[type="radio"]').forEach(r => {
            r.disabled = false;
        });

        // Buka readonly catatan
        const catatanInput = row.querySelector('input[type="text"]');
        if (catatanInput) {
            catatanInput.removeAttribute('readonly');
            catatanInput.classList.remove('bg-gray-50', 'text-gray-500', 'cursor-not-allowed');
            catatanInput.focus();
        }

        // Kembalikan opacity radio
        allStatuses.forEach(s => {
            const d = row.querySelector('.radio-display-' + s + '-' + rowIndex);
            if (d) d.classList.remove('opacity-30');
        });

        // Ubah badge status menjadi indikator "Mode Edit"
        if (badge) {
            badge.innerHTML = `
                <span class="text-[10px] text-amber-800 bg-amber-100 border border-amber-300 px-2 py-0.5 rounded-full font-bold inline-flex items-center gap-1">
                    <i class="ti ti-pencil text-xs"></i> Mengedit
                </span>
                <button type="button" onclick="cancelEdit(${rowIndex})"
                        class="text-[10px] text-gray-500 hover:text-red-600 underline cursor-pointer mt-0.5">
                    Batal
                </button>
            `;
        }

        row.dataset.locked = 'false';
    }

    function cancelEdit(rowIndex) {
        location.reload();
    }

    // Pastikan semua input di-enable sesaat sebelum form di-submit
    // agar data dari baris yang terkunci maupun diedit tetap terkirim lengkap ke server
    document.getElementById('formAbsensi')?.addEventListener('submit', function () {
        this.querySelectorAll('input[type="radio"]').forEach(r => {
            r.disabled = false;
        });
        this.querySelectorAll('input[type="text"]').forEach(t => {
            t.readOnly = false;
        });
    });

    // Inisialisasi highlight untuk baris yang sudah ada nilainya
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.status-radio:checked').forEach(radio => {
            const rowIndex = parseInt(radio.getAttribute('data-row'));
            const status   = radio.getAttribute('data-status');
            highlightRow(rowIndex, status);
        });
    });
</script>
@endpush

@endsection
