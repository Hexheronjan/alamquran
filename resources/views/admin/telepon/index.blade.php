@extends('layouts.admin.app')

@section('title', 'Jadwal Sambungan Telepon Santri')

@section('content')
<div class="bg-gray-100 min-h-screen py-6 px-4 lg:px-8">

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="mb-5 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-start gap-3">
            <i class="ti ti-circle-check text-2xl text-emerald-600 shrink-0 mt-0.5"></i>
            <div>
                <h4 class="text-sm font-bold text-emerald-800">Berhasil!</h4>
                <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs flex items-start gap-3">
            <i class="ti ti-alert-triangle text-2xl text-rose-600 shrink-0 mt-0.5"></i>
            <div>
                <h4 class="text-sm font-bold text-rose-800">Peringatan!</h4>
                <p class="text-xs text-rose-700 mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if(session('info'))
        <div class="mb-5 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-xl shadow-xs flex items-start gap-3">
            <i class="ti ti-info-circle text-2xl text-blue-600 shrink-0 mt-0.5"></i>
            <div>
                <h4 class="text-sm font-bold text-blue-800">Informasi</h4>
                <p class="text-xs text-blue-700 mt-0.5">{{ session('info') }}</p>
            </div>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-md">
                    <i class="ti ti-phone-call text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Jadwal Sambungan Telepon Santri</h1>
                    <p class="text-xs sm:text-sm text-gray-500">Atur jadwal telepon ananda, kirim pengingat WhatsApp ke wali santri, dan catat riwayat panggilan.</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <form action="{{ route('admin.telepon.broadcast_today') }}" method="POST" onsubmit="return confirm('Kirimkan notifikasi WhatsApp pengingat untuk seluruh jadwal telepon santri yang berlangsung hari ini?');">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2.5 text-white rounded-xl text-xs font-bold shadow-sm transition hover:opacity-90" style="background-color: #15803d !important; color: #ffffff !important;">
                    <i class="ti ti-brand-whatsapp text-base"></i>
                    <span>Broadcast Pengingat Hari Ini</span>
                </button>
            </form>

            <button type="button" onclick="openModal('modalBulk')" class="inline-flex items-center gap-2 px-3.5 py-2.5 text-white rounded-xl text-xs font-bold shadow-sm transition hover:opacity-90" style="background-color: #4f46e5 !important; color: #ffffff !important;">
                <i class="ti ti-bolt text-base"></i>
                <span>Generate Massal (Per Kelas)</span>
            </button>

            <button type="button" onclick="openModal('modalCreate')" class="inline-flex items-center gap-2 px-4 py-2.5 text-white rounded-xl text-xs font-bold shadow-sm transition hover:opacity-90" style="background-color: #059669 !important; color: #ffffff !important;">
                <i class="ti ti-plus text-base"></i>
                <span>Tambah Jadwal</span>
            </button>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 mb-6">
        <div class="bg-white rounded-xl p-3.5 border border-gray-200/80 shadow-xs">
            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total Jadwal</span>
            <div class="text-xl font-bold text-gray-900 mt-1">{{ number_format($totalJadwal) }}</div>
        </div>
        <div class="bg-white rounded-xl p-3.5 border border-blue-200 bg-blue-50/30 shadow-xs">
            <span class="text-[11px] font-semibold text-blue-700 uppercase tracking-wider">Jadwal Hari Ini</span>
            <div class="text-xl font-bold text-blue-800 mt-1">{{ number_format($totalHariIni) }}</div>
        </div>
        <div class="bg-white rounded-xl p-3.5 border border-amber-200 bg-amber-50/30 shadow-xs">
            <span class="text-[11px] font-semibold text-amber-700 uppercase tracking-wider">Menunggu Giliran</span>
            <div class="text-xl font-bold text-amber-800 mt-1">{{ number_format($totalMenunggu) }}</div>
        </div>
        <div class="bg-white rounded-xl p-3.5 border border-emerald-200 bg-emerald-50/30 shadow-xs">
            <span class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider">Terhubung</span>
            <div class="text-xl font-bold text-emerald-800 mt-1">{{ number_format($totalTerhubung) }}</div>
        </div>
        <div class="bg-white rounded-xl p-3.5 border border-rose-200 bg-rose-50/30 shadow-xs">
            <span class="text-[11px] font-semibold text-rose-700 uppercase tracking-wider">Tidak Terangkat</span>
            <div class="text-xl font-bold text-rose-800 mt-1">{{ number_format($totalTidakTerangkat) }}</div>
        </div>
        <div class="bg-white rounded-xl p-3.5 border border-purple-200 bg-purple-50/30 shadow-xs">
            <span class="text-[11px] font-semibold text-purple-700 uppercase tracking-wider">Jadwal Ulang</span>
            <div class="text-xl font-bold text-purple-800 mt-1">{{ number_format($totalJadwalUlang) }}</div>
        </div>
    </div>

    {{-- Filter & Search Card --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-4 sm:p-5 mb-6">
        <form method="GET" action="{{ route('admin.telepon.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            {{-- Search --}}
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-gray-700 mb-1">Cari Santri / Ustadz / No. HP</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama santri, ustadz, posko..." class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                    <i class="ti ti-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                </div>
            </div>

            {{-- Filter Status --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Status Panggilan</label>
                <select name="status" class="w-full py-2 px-3 text-xs rounded-xl border border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="all">Semua Status</option>
                    @foreach($statusList as $key => $label)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Kelas --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Filter Kelas</label>
                <select name="kelas" class="w-full py-2 px-3 text-xs rounded-xl border border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="all">Semua Kelas</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k }}" {{ request('kelas') == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Tanggal & Action --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal</label>
                <div class="flex items-center gap-1.5">
                    <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="w-full py-2 px-2 text-xs rounded-xl border border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                    <button type="submit" class="px-3 py-2 text-white rounded-xl text-xs font-bold transition hover:opacity-90" style="background-color: #059669 !important; color: #ffffff !important;">
                        <i class="ti ti-filter"></i>
                    </button>
                    @if(request()->hasAny(['search', 'status', 'kelas', 'tanggal']))
                        <a href="{{ route('admin.telepon.index') }}" class="px-2.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold transition" title="Reset Filter">
                            <i class="ti ti-refresh"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Main Table Card --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm sm:text-base font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-list text-emerald-600"></i>
                Daftar Jadwal Sambungan Telepon
                <span class="text-xs font-normal text-gray-500">({{ $jadwals->total() }} jadwal ditemukan)</span>
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-700">
                <thead class="bg-gray-50/80 text-gray-600 font-bold uppercase text-[11px] border-b border-gray-200">
                    <tr>
                        <th class="py-3 px-3.5 text-center w-12">No</th>
                        <th class="py-3 px-4">Santri & Kelas</th>
                        <th class="py-3 px-4">Waktu & Durasi</th>
                        <th class="py-3 px-4">Ustadz & No. Penelepon</th>
                        <th class="py-3 px-4 text-center">Status Telepon</th>
                        <th class="py-3 px-4 text-center">Pengingat WA</th>
                        <th class="py-3 px-4">Topik & Catatan</th>
                        <th class="py-3 px-4 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($jadwals as $index => $item)
                        <tr class="hover:bg-gray-50/80 transition {{ $item->is_today ? 'bg-emerald-50/20' : '' }}">
                            <td class="py-3.5 px-3.5 text-center font-medium text-gray-400">
                                {{ $jadwals->firstItem() + $index }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-gray-900 text-sm">
                                    {{ $item->santri?->nama_lengkap ?? 'Santri Tidak Ditemukan' }}
                                </div>
                                <div class="flex items-center gap-2 text-[11px] text-gray-500 mt-0.5">
                                    <span class="bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded font-semibold border border-emerald-200/60">
                                        Kelas {{ $item->santri?->kelas ?? '-' }}
                                    </span>
                                    <span>NIS: {{ $item->santri?->nis ?? '-' }}</span>
                                </div>
                                @php
                                    $rawWaliWa = $item->santri?->orangTua?->no_wa_ayah ?: $item->santri?->user?->no_wa ?: null;
                                    $waliWaClean = \App\Services\WhatsappService::formatPhoneNumber($rawWaliWa);
                                    $waliNama = $item->santri?->orangTua?->nama_ayah ?: $item->santri?->user?->name ?: 'Wali Santri';
                                @endphp
                                <div class="text-[11px] text-gray-600 mt-1 flex items-center gap-1.5 flex-wrap">
                                    <span>Wali: <strong class="text-gray-800">{{ $waliNama }}</strong></span>
                                    @if($waliWaClean)
                                        <a href="https://wa.me/{{ $waliWaClean }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 hover:text-emerald-800 border border-emerald-300 font-mono text-[11px] font-bold transition shadow-2xs group"
                                           title="Klik untuk langsung chat atau telepon via WhatsApp ke {{ $waliNama }}">
                                            <i class="ti ti-brand-whatsapp text-emerald-600 text-sm group-hover:scale-110 transition-transform"></i>
                                            <span>{{ $rawWaliWa }}</span>
                                            <i class="ti ti-external-link text-[10px] text-emerald-500"></i>
                                        </a>
                                    @else
                                        <span class="text-gray-400">({{ $rawWaliWa ?: 'Belum ada No WA' }})</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-semibold text-gray-900 flex items-center gap-1.5">
                                    <i class="ti ti-calendar text-gray-400 text-sm"></i>
                                    {{ $item->formatted_tanggal }}
                                </div>
                                <div class="text-xs font-bold text-emerald-700 mt-0.5 flex items-center gap-1.5">
                                    <i class="ti ti-clock text-emerald-600 text-sm"></i>
                                    {{ $item->formatted_jam }}
                                </div>
                                <div class="text-[11px] text-gray-500 mt-0.5">
                                    Durasi: {{ $item->durasi_menit }} Menit
                                    @if($item->is_today)
                                        <span class="ms-1 bg-rose-500 text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full">HARI INI</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-gray-800 flex items-center gap-1.5">
                                    <i class="ti ti-user-check text-indigo-600 text-sm"></i>
                                    {{ $item->ustadz_pendamping }}
                                </div>
                                @php
                                    $poskoClean = \App\Services\WhatsappService::formatPhoneNumber($item->no_hp_posko);
                                @endphp
                                @if($poskoClean)
                                    <a href="https://wa.me/{{ $poskoClean }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="text-xs font-mono text-gray-600 hover:text-emerald-700 hover:underline mt-0.5 flex items-center gap-1.5"
                                       title="Klik untuk hubungi No Posko via WhatsApp">
                                        <i class="ti ti-phone text-emerald-600 text-sm"></i>
                                        <span>{{ $item->no_hp_posko }}</span>
                                    </a>
                                @else
                                    <div class="text-xs font-mono text-gray-600 mt-0.5 flex items-center gap-1.5">
                                        <i class="ti ti-phone text-emerald-600 text-sm"></i>
                                        <span>{{ $item->no_hp_posko }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                {!! $item->status_badge !!}
                                @if($item->waktu_dihubungi)
                                    <div class="text-[10px] text-gray-400 mt-1">
                                        {{ $item->waktu_dihubungi->format('d/m/Y H:i') }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($item->wa_reminder_sent)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-800 border border-green-200" title="Terkirim pada {{ $item->wa_reminder_sent_at?->format('d M H:i') }}">
                                        <i class="ti ti-check text-green-600"></i> Terkirim
                                    </span>
                                    <div class="text-[10px] text-gray-400 mt-0.5">
                                        {{ $item->wa_reminder_sent_at?->format('H:i') }} WIB
                                    </div>
                                @elseif($item->wa_reminder_status === 'gagal')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800 border border-rose-200" title="{{ $item->wa_reminder_error }}">
                                        <i class="ti ti-x text-rose-600"></i> Gagal
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                        <i class="ti ti-clock text-gray-400"></i> Belum
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                @if($item->topik)
                                    <div class="font-semibold text-gray-800 text-[11px]">
                                        Topik: {{ $item->topik }}
                                    </div>
                                @endif
                                @if($item->catatan_panggilan)
                                    <div class="text-xs text-gray-600 mt-0.5 italic bg-gray-50 p-1.5 rounded border border-gray-200/60">
                                        "{{ Str::limit($item->catatan_panggilan, 80) }}"
                                    </div>
                                @elseif($item->keterangan)
                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                        {{ Str::limit($item->keterangan, 60) }}
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Tombol Update Status & Catatan Panggilan --}}
                                    <button type="button"
                                        onclick="openModalStatus({{ $item->id }}, '{{ addslashes($item->santri?->nama_lengkap ?? '') }}', '{{ $item->status }}', '{{ addslashes($item->catatan_panggilan ?? '') }}', '{{ addslashes($waliNama) }}', '{{ $waliWaClean ?? '' }}', '{{ $rawWaliWa ?? '' }}')"
                                        class="p-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition"
                                        title="Catat Hasil Panggilan & Hubungi">
                                        <i class="ti ti-notes text-base"></i>
                                    </button>

                                    {{-- Tombol Kirim / Kirim Ulang WA --}}
                                    <form action="{{ route('admin.telepon.send_reminder', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Kirim notifikasi pengingat WhatsApp ke wali ananda {{ $item->santri?->nama_lengkap }} sekarang?');">
                                        @csrf
                                        <button type="submit"
                                            class="p-1.5 rounded-lg bg-green-50 hover:bg-green-100 text-green-700 transition"
                                            title="Kirim / Ulangi Pengingat WhatsApp">
                                            <i class="ti ti-brand-whatsapp text-base"></i>
                                        </button>
                                    </form>

                                    {{-- Tombol Edit --}}
                                    <button type="button"
                                        onclick="openModalEdit({{ json_encode($item) }})"
                                        class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 transition"
                                        title="Edit Jadwal">
                                        <i class="ti ti-edit text-base"></i>
                                    </button>

                                    {{-- Tombol Hapus --}}
                                    <form action="{{ route('admin.telepon.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus jadwal telepon ananda {{ $item->santri?->nama_lengkap }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition" title="Hapus Jadwal">
                                            <i class="ti ti-trash text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-400">
                                <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3 text-gray-300">
                                    <i class="ti ti-phone-off text-3xl"></i>
                                </div>
                                <p class="text-sm font-semibold text-gray-600">Belum ada jadwal sambungan telepon yang sesuai.</p>
                                <p class="text-xs text-gray-400 mt-1">Gunakan tombol "Tambah Jadwal" atau "Generate Massal (Per Kelas)" di atas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jadwals->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $jadwals->links() }}
            </div>
        @endif
    </div>

</div>

{{-- MODAL TAMBAH JADWAL (SINGLE) --}}
<div id="modalCreate" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl relative my-8">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-phone-plus text-emerald-600 text-lg"></i>
                Tambah Jadwal Sambungan Telepon
            </h3>
            <button type="button" onclick="closeModal('modalCreate')" class="text-gray-400 hover:text-gray-600">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>

        <form action="{{ route('admin.telepon.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Pilih Santri <span class="text-rose-500">*</span></label>
                <select name="santri_id" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">-- Pilih Santri --</option>
                    @foreach($santris as $s)
                        <option value="{{ $s->id }}">{{ $s->nama_lengkap }} (Kelas {{ $s->kelas }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_telepon" value="{{ date('Y-m-d') }}" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Jam Mulai <span class="text-rose-500">*</span></label>
                    <input type="time" name="jam_mulai" value="16:00" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Jam Selesai <span class="text-rose-500">*</span></label>
                    <input type="time" name="jam_selesai" value="16:15" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Durasi (Menit)</label>
                    <input type="number" name="durasi_menit" value="15" min="5" max="120" class="w-full text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Ustadz Pendamping <span class="text-rose-500">*</span></label>
                    <input type="text" name="ustadz_pendamping" placeholder="Contoh: Ustadz Ahmad" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nomor HP Penelepon (Posko) <span class="text-rose-500">*</span></label>
                    <input type="text" name="no_hp_posko" placeholder="08xxxxxxxxxx" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Topik Pembicaraan (Opsional)</label>
                <input type="text" name="topik" placeholder="Contoh: Kabar Mingguan & Perkembangan Tahfidz" class="w-full text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Tambahan untuk Wali (Opsional)</label>
                <textarea name="keterangan" rows="2" placeholder="Catatan atau pesan khusus kepada wali..." class="w-full text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="kirim_wa_sekarang" value="1" class="rounded text-emerald-600 focus:ring-emerald-500">
                    <span class="text-xs font-semibold text-gray-700">Langsung kirim notifikasi pengingat WhatsApp ke wali saat disimpan</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeModal('modalCreate')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-white rounded-xl text-xs font-bold shadow-sm transition hover:opacity-90" style="background-color: #059669 !important; color: #ffffff !important;">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL GENERATE MASSAL (BULK PER KELAS) --}}
<div id="modalBulk" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl relative my-8">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
            <div>
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="ti ti-bolt text-indigo-600 text-lg"></i>
                    Generate Jadwal Massal Per Kelas
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Sistem akan membagi giliran jam secara berurutan untuk semua santri di kelas yang dipilih.</p>
            </div>
            <button type="button" onclick="closeModal('modalBulk')" class="text-gray-400 hover:text-gray-600">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>

        <form action="{{ route('admin.telepon.store_bulk') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Pilih Kelas Santri <span class="text-rose-500">*</span></label>
                <select name="kelas" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k }}">Kelas {{ $k }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Sesi <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_telepon" value="{{ date('Y-m-d') }}" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Jam Mulai Sesi <span class="text-rose-500">*</span></label>
                    <input type="time" name="jam_mulai" value="16:00" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Durasi / Santri <span class="text-rose-500">*</span></label>
                    <select name="durasi_menit" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="10">10 Menit</option>
                        <option value="15" selected>15 Menit</option>
                        <option value="20">20 Menit</option>
                        <option value="30">30 Menit</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Ustadz Pendamping <span class="text-rose-500">*</span></label>
                    <input type="text" name="ustadz_pendamping" placeholder="Contoh: Ustadz Ahmad" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nomor HP Penelepon (Posko) <span class="text-rose-500">*</span></label>
                    <input type="text" name="no_hp_posko" placeholder="08xxxxxxxxxx" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Topik Pembicaraan (Opsional)</label>
                <input type="text" name="topik" placeholder="Contoh: Sambungan Telepon Rutin Mingguan" class="w-full text-xs rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeModal('modalBulk')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-white rounded-xl text-xs font-bold shadow-sm transition hover:opacity-90" style="background-color: #4f46e5 !important; color: #ffffff !important;">Generate Semua Santri</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL CATAT STATUS HASIL PANGGILAN --}}
<div id="modalStatus" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative my-8">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-notes text-indigo-600 text-lg"></i>
                Catat Hasil Sambungan Telepon
            </h3>
            <button type="button" onclick="closeModal('modalStatus')" class="text-gray-400 hover:text-gray-600">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>

        <form id="formStatus" method="POST" class="space-y-4">
            @csrf
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                <div>
                    <span class="text-[11px] text-gray-500 block">Nama Santri:</span>
                    <p id="statusSantriNama" class="font-bold text-gray-900 text-sm mt-0.5">-</p>
                </div>
                <div class="text-right">
                    <span class="text-[11px] text-gray-500 block">Wali Santri:</span>
                    <p id="statusWaliNama" class="font-bold text-gray-800 text-xs mt-0.5">-</p>
                </div>
            </div>

            {{-- Box Shortcut Kontak WA Wali --}}
            <div id="statusWaliWaContainer" class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <i class="ti ti-brand-whatsapp text-xl"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-semibold text-emerald-900">WhatsApp Wali Santri</div>
                        <div id="statusWaliNo" class="text-xs font-mono text-emerald-700 font-bold">-</div>
                    </div>
                </div>
                <a id="statusWaliWaBtn" href="#" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-white rounded-xl text-xs font-bold shadow-xs hover:opacity-90 transition shrink-0"
                   style="background-color: #059669 !important; color: #ffffff !important;">
                    <i class="ti ti-brand-whatsapp text-sm"></i>
                    <span>Chat / Panggil WA</span>
                </a>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Status Panggilan <span class="text-rose-500">*</span></label>
                <select name="status" id="statusSelect" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="terhubung">✓ Berhasil Terhubung</option>
                    <option value="tidak_terangkat">✗ Tidak Terangkat / Sibuk</option>
                    <option value="dijadwalkan_ulang">↻ Dijadwalkan Ulang</option>
                    <option value="menunggu">⌛ Menunggu Giliran</option>
                    <option value="dibatalkan">⊘ Dibatalkan</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Panggilan dari Ustadz (Opsional)</label>
                <textarea name="catatan_panggilan" id="catatanPanggilan" rows="3" placeholder="Contoh: Alhamdulillah ananda sudah bicara dengan orang tua, kondisi sehat dan bahagia..." class="w-full text-xs rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeModal('modalStatus')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-white rounded-xl text-xs font-bold shadow-sm transition hover:opacity-90" style="background-color: #4f46e5 !important; color: #ffffff !important;">Simpan Hasil Panggilan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT JADWAL --}}
<div id="modalEdit" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl relative my-8">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-edit text-amber-600 text-lg"></i>
                Edit Jadwal Sambungan Telepon
            </h3>
            <button type="button" onclick="closeModal('modalEdit')" class="text-gray-400 hover:text-gray-600">
                <i class="ti ti-x text-lg"></i>
            </button>
        </div>

        <form id="formEdit" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Santri</label>
                <select name="santri_id" id="editSantriId" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                    @foreach($santris as $s)
                        <option value="{{ $s->id }}">{{ $s->nama_lengkap }} (Kelas {{ $s->kelas }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal</label>
                    <input type="date" name="tanggal_telepon" id="editTanggal" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Jam Mulai</label>
                    <input type="time" name="jam_mulai" id="editJamMulai" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Jam Selesai</label>
                    <input type="time" name="jam_selesai" id="editJamSelesai" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Ustadz Pendamping</label>
                    <input type="text" name="ustadz_pendamping" id="editUstadz" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nomor HP Penelepon (Posko)</label>
                    <input type="text" name="no_hp_posko" id="editNoPosko" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Topik Pembicaraan</label>
                <input type="text" name="topik" id="editTopik" class="w-full text-xs rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Tambahan untuk Wali</label>
                <textarea name="keterangan" id="editKeterangan" rows="2" class="w-full text-xs rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeModal('modalEdit')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-white rounded-xl text-xs font-bold shadow-sm transition hover:opacity-90" style="background-color: #d97706 !important; color: #ffffff !important;">Perbarui Jadwal</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    function openModalStatus(id, namaSantri, status, catatan, namaWali, waliWaClean, rawWaliWa) {
        document.getElementById('formStatus').action = '/admin/jadwal-telepon/' + id + '/status';
        document.getElementById('statusSantriNama').innerText = namaSantri || '-';
        document.getElementById('statusSelect').value = status;
        document.getElementById('catatanPanggilan').value = catatan || '';

        const waliNamaEl = document.getElementById('statusWaliNama');
        const waliNoEl = document.getElementById('statusWaliNo');
        const waContainer = document.getElementById('statusWaliWaContainer');
        const waBtn = document.getElementById('statusWaliWaBtn');

        if (waliNamaEl) waliNamaEl.innerText = namaWali || '-';
        if (waliNoEl) waliNoEl.innerText = rawWaliWa || '-';

        if (waContainer && waBtn) {
            if (waliWaClean) {
                waBtn.href = 'https://wa.me/' + waliWaClean;
                waContainer.classList.remove('hidden');
            } else {
                waContainer.classList.add('hidden');
            }
        }

        openModal('modalStatus');
    }

    function openModalEdit(data) {
        document.getElementById('formEdit').action = '/admin/jadwal-telepon/' + data.id;
        document.getElementById('editSantriId').value = data.santri_id;
        document.getElementById('editTanggal').value = data.tanggal_telepon_raw || (data.tanggal_telepon ? data.tanggal_telepon.substring(0, 10) : '');
        document.getElementById('editJamMulai').value = data.jam_mulai ? data.jam_mulai.substring(0, 5) : '';
        document.getElementById('editJamSelesai').value = data.jam_selesai ? data.jam_selesai.substring(0, 5) : '';
        document.getElementById('editUstadz').value = data.ustadz_pendamping || '';
        document.getElementById('editNoPosko').value = data.no_hp_posko || '';
        document.getElementById('editTopik').value = data.topik || '';
        document.getElementById('editKeterangan').value = data.keterangan || '';
        openModal('modalEdit');
    }
</script>
@endsection
