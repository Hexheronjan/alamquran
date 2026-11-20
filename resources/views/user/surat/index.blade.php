<x-app-layout>
    <x-slot name="title">Surat & Dokumen</x-slot>


    <div class="max-w-5xl mx-auto px-4 py-6 space-y-6">

        {{-- Header --}}
        <div class="flex items-center gap-3">
            <div class="bg-green-100 text-green-600 rounded-xl p-3">
                <i class="ti ti-mail text-2xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-800">Surat & Dokumen dari Pondok</h1>
                <p class="text-sm text-gray-500">Pesantren Tahfizh Alam Qur'an</p>
            </div>
        </div>

        {{-- Info Box --}}
        <div class="flex items-start gap-3 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <i class="ti ti-info-circle text-blue-500 text-xl mt-0.5 shrink-0"></i>
            <p class="text-sm text-blue-700">
                Halaman ini menampilkan surat resmi dan dokumen yang diterbitkan oleh Pesantren Tahfizh Alam Qur'an.
            </p>
        </div>

        {{-- Surat List --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- Desktop Table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 text-xs uppercase tracking-wide">
                            <th class="px-4 py-3 text-left">No. Surat</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Perihal</th>
                            <th class="px-4 py-3 text-left">Ditujukan</th>
                            <th class="px-4 py-3 text-left">Penandatangan</th>
                            <th class="px-4 py-3 text-center">Isi Surat</th>
                            <th class="px-4 py-3 text-center">Lampiran Berkas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($surats as $surat)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $surat->nomor_surat }}</td>
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                    {{ $surat->tanggal_surat->translatedFormat('d F Y') }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-800">{{ $surat->perihal }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $surat->tujuan_surat }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $surat->penandatangan }}</td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <a href="{{ route('user.surat.show', $surat->id) }}"
                                       class="inline-flex items-center gap-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-bold px-3 py-1.5 rounded-lg transition-colors shadow-2xs">
                                        <i class="ti ti-eye text-sm"></i>
                                        Baca Surat
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @if ($surat->file_lampiran)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($surat->file_lampiran) }}"
                                           target="_blank"
                                           class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors shadow-2xs">
                                            <i class="ti ti-paperclip text-sm"></i>
                                            Unduh Berkas
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Tidak ada</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3 text-gray-400">
                                        <i class="ti ti-mail-off text-5xl"></i>
                                        <p class="text-base font-medium">Belum ada surat yang diterbitkan</p>
                                        <p class="text-sm">Surat resmi dari pesantren akan muncul di sini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="md:hidden divide-y divide-gray-100">
                @forelse ($surats as $surat)
                    <div class="p-4 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <span class="font-semibold text-gray-800 text-sm leading-snug">{{ $surat->perihal }}</span>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <a href="{{ route('user.surat.show', $surat->id) }}"
                                   class="inline-flex items-center gap-1 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-bold px-2.5 py-1.5 rounded-lg transition-colors">
                                    <i class="ti ti-eye text-sm"></i>
                                    Baca
                                </a>
                                @if ($surat->file_lampiran)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($surat->file_lampiran) }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg transition-colors">
                                        <i class="ti ti-paperclip text-sm"></i>
                                        Berkas
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-gray-500">
                            <div>
                                <span class="text-gray-400">No. Surat</span>
                                <p class="font-mono text-gray-600">{{ $surat->nomor_surat }}</p>
                            </div>
                            <div>
                                <span class="text-gray-400">Tanggal</span>
                                <p class="text-gray-600">{{ $surat->tanggal_surat->translatedFormat('d F Y') }}</p>
                            </div>
                            <div>
                                <span class="text-gray-400">Ditujukan</span>
                                <p class="text-gray-600">{{ $surat->tujuan_surat }}</p>
                            </div>
                            <div>
                                <span class="text-gray-400">Penandatangan</span>
                                <p class="text-gray-600">{{ $surat->penandatangan }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-16 flex flex-col items-center gap-3 text-gray-400 px-4 text-center">
                        <i class="ti ti-mail-off text-5xl"></i>
                        <p class="text-base font-medium">Belum ada surat yang diterbitkan</p>
                        <p class="text-sm">Surat resmi dari pesantren akan muncul di sini.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($surats->hasPages())
                <div class="px-4 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $surats->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
