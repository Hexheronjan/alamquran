<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <i class="ti ti-qrcode text-2xl text-green-700"></i>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Perizinan & Kehadiran Wali
            </h2>
        </div>
    </x-slot>

    <style>
        @media print { .no-print { display: none; } }
    </style>

    <div class="py-6 no-print">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Session Alerts --}}
            @if(session('success'))
                <div class="flex items-center gap-3 bg-green-50 border border-green-300 text-green-800 px-4 py-3 rounded-lg">
                    <i class="ti ti-circle-check text-xl"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-lg">
                    <i class="ti ti-alert-circle text-xl"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- 1. Active Izin Alert --}}
            @if($activeIzin)
                @php
                    $terlambat = $activeIzin->batas_jam_kembali && \Carbon\Carbon::parse($activeIzin->batas_jam_kembali)->isPast();
                @endphp
                <div class="border-2 {{ $terlambat ? 'border-red-500 bg-red-50' : 'border-orange-400 bg-orange-50' }} rounded-xl p-5">
                    <div class="flex items-start gap-3">
                        <i class="ti ti-alert-triangle text-2xl {{ $terlambat ? 'text-red-600' : 'text-orange-500' }} mt-0.5"></i>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap mb-2">
                                <h3 class="font-bold text-lg {{ $terlambat ? 'text-red-700' : 'text-orange-700' }}">Izin Aktif!</h3>
                                @if($terlambat)
                                    <span class="inline-flex items-center gap-1 bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                        <i class="ti ti-clock-x"></i> TERLAMBAT KEMBALI
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-orange-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                        <i class="ti ti-walk"></i> Sedang di Luar
                                    </span>
                                @endif
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="text-gray-500">Santri:</span>
                                    <span class="font-semibold text-gray-800 ml-1">{{ $activeIzin->santri->nama_lengkap ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Jenis Izin:</span>
                                    <span class="font-semibold text-gray-800 ml-1 capitalize">{{ str_replace('_', ' ', $activeIzin->jenis_izin) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Waktu Keluar:</span>
                                    <span class="font-semibold text-gray-800 ml-1">
                                        {{ $activeIzin->waktu_keluar ? \Carbon\Carbon::parse($activeIzin->waktu_keluar)->format('d/m/Y H:i') : '-' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Batas Kembali:</span>
                                    <span class="font-semibold {{ $terlambat ? 'text-red-700' : 'text-gray-800' }} ml-1">
                                        {{ $activeIzin->batas_jam_kembali ? \Carbon\Carbon::parse($activeIzin->batas_jam_kembali)->format('d/m/Y H:i') : '-' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Status:</span>
                                    <span class="font-semibold text-gray-800 ml-1 capitalize">{{ $activeIzin->status }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 2. QR Code Section --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-4" style="background-color:#16a34a">
                    <i class="ti ti-qrcode text-xl text-white"></i>
                    <h3 class="font-semibold text-white text-base">QR Code Wali</h3>
                </div>
                <div class="p-5 flex flex-col items-center gap-3 text-center">
                    <img
                        src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($qrToken) }}"
                        onerror="this.src='https://quickchart.io/qr?size=250&text={{ urlencode($qrToken) }}'"
                        alt="QR Code"
                        class="mx-auto rounded-lg border border-gray-200 shadow-sm"
                        width="200"
                        height="200"
                    >
                    <p class="font-mono text-xs text-gray-500 break-all max-w-xs">{{ $qrToken }}</p>
                    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-2 max-w-sm">
                        <i class="ti ti-info-circle text-base shrink-0"></i>
                        <span>Tunjukkan QR Code ini kepada petugas saat mengunjungi pesantren</span>
                    </div>
                </div>
            </div>

            {{-- 3. Form Pengajuan Izin --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-100">
                    <i class="ti ti-door-exit text-xl text-gray-600"></i>
                    <h3 class="font-semibold text-gray-800 text-base">Ajukan Izin Santri</h3>
                </div>
                <div class="p-5">
                    <form method="POST" action="{{ route('user.perizinan.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Santri</label>
                            <select name="santri_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                                <option value="" disabled selected>-- Pilih Santri --</option>
                                @foreach($santris as $s)
                                    <option value="{{ $s->id }}" {{ old('santri_id') == $s->id ? 'selected' : '' }}>
                                        {{ $s->nama_lengkap }} (Kelas {{ $s->kelas }})
                                    </option>
                                @endforeach
                            </select>
                            @error('santri_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Izin</label>
                            <select name="jenis_izin" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                                <option value="" disabled selected>-- Pilih Jenis Izin --</option>
                                <option value="pulang" {{ old('jenis_izin') == 'pulang' ? 'selected' : '' }}>Pulang ke Rumah</option>
                                <option value="keluar_sementara" {{ old('jenis_izin') == 'keluar_sementara' ? 'selected' : '' }}>Keluar Sementara</option>
                            </select>
                            @error('jenis_izin')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tujuan / Keperluan</label>
                            <input type="text" name="tujuan" required value="{{ old('tujuan') }}" placeholder="Tujuan/Keperluan"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                            @error('tujuan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Penjemput</label>
                            <input type="text" name="nama_penjemput" required value="{{ old('nama_penjemput') }}" placeholder="Nama Penjemput"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                            @error('nama_penjemput')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No WhatsApp Penjemput</label>
                            <input type="text" name="no_wa_penjemput" required value="{{ old('no_wa_penjemput') }}" placeholder="No WhatsApp Penjemput"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                            @error('no_wa_penjemput')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Batas Kembali <span class="text-gray-400 font-normal">(opsional)</span>
                            </label>
                            <input type="datetime-local" name="batas_jam_kembali" value="{{ old('batas_jam_kembali') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                            @error('batas_jam_kembali')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 pt-2">
                            <button type="submit"
                                class="inline-flex items-center gap-2 text-white text-sm font-semibold px-5 py-2.5 rounded-lg hover:opacity-90 transition"
                                style="background-color:#16a34a">
                                <i class="ti ti-send"></i>
                                Ajukan Izin
                            </button>
                            <p class="text-xs text-gray-500 flex items-center gap-1">
                                <i class="ti ti-info-circle"></i>
                                Pengajuan akan diproses petugas saat wali tiba di pesantren
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 4. Riwayat Kehadiran Ahad Pagi --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-100">
                    <i class="ti ti-calendar-check text-xl text-gray-600"></i>
                    <h3 class="font-semibold text-gray-800 text-base">Riwayat Kehadiran Ahad Pagi</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Jam Kedatangan</th>
                                <th class="px-4 py-3 text-left">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($kehadirans as $hadir)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-gray-800">
                                        {{ \Carbon\Carbon::parse($hadir->tanggal)->translatedFormat('d F Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ \Carbon\Carbon::parse($hadir->created_at)->format('H:i') }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">{{ $hadir->catatan ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-gray-400">
                                        <i class="ti ti-calendar-off text-3xl block mb-1"></i>
                                        Belum ada data kehadiran
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($kehadirans->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100">{{ $kehadirans->links() }}</div>
                @endif
            </div>

            {{-- 5. Riwayat Perizinan --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-100">
                    <i class="ti ti-clipboard-list text-xl text-gray-600"></i>
                    <h3 class="font-semibold text-gray-800 text-base">Riwayat Perizinan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[700px]">
                        <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Santri</th>
                                <th class="px-4 py-3 text-left">Jenis Izin</th>
                                <th class="px-4 py-3 text-left">Tujuan</th>
                                <th class="px-4 py-3 text-left">Penjemput</th>
                                <th class="px-4 py-3 text-left">Waktu Keluar</th>
                                <th class="px-4 py-3 text-left">Batas Kembali</th>
                                <th class="px-4 py-3 text-left">Waktu Kembali</th>
                                <th class="px-4 py-3 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($perizinans as $izin)
                                @php
                                    $isTerlambat = $izin->status === 'keluar'
                                        && $izin->batas_jam_kembali
                                        && \Carbon\Carbon::parse($izin->batas_jam_kembali)->isPast();
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 font-medium text-gray-800 whitespace-nowrap">{{ $izin->santri->nama_lengkap ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-700 capitalize whitespace-nowrap">{{ str_replace('_', ' ', $izin->jenis_izin) }}</td>
                                    <td class="px-4 py-3 text-gray-600 max-w-[160px] truncate" title="{{ $izin->tujuan }}">{{ $izin->tujuan ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                        <div>{{ $izin->nama_penjemput ?? '-' }}</div>
                                        @if($izin->no_wa_penjemput)
                                            <div class="text-xs text-gray-400">{{ $izin->no_wa_penjemput }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                        {{ $izin->waktu_keluar ? \Carbon\Carbon::parse($izin->waktu_keluar)->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap {{ $isTerlambat ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                        {{ $izin->batas_jam_kembali ? \Carbon\Carbon::parse($izin->batas_jam_kembali)->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                        {{ $izin->waktu_kembali ? \Carbon\Carbon::parse($izin->waktu_kembali)->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($isTerlambat)
                                            <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                                                <i class="ti ti-clock-x"></i> Terlambat
                                            </span>
                                        @elseif($izin->status === 'pengajuan')
                                            <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 text-xs font-semibold px-2 py-0.5 rounded-full">
                                                <i class="ti ti-clock"></i> Pengajuan
                                            </span>
                                        @elseif($izin->status === 'keluar')
                                            <span class="inline-flex items-center gap-1 bg-orange-100 text-orange-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                                                <i class="ti ti-walk"></i> Keluar
                                            </span>
                                        @elseif($izin->status === 'kembali')
                                            <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                                                <i class="ti ti-circle-check"></i> Kembali
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-500 text-xs font-semibold px-2 py-0.5 rounded-full capitalize">
                                                {{ $izin->status }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-400">
                                        <i class="ti ti-file-off text-3xl block mb-1"></i>
                                        Belum ada riwayat perizinan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($perizinans->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100">{{ $perizinans->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
