<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 flex items-center gap-2">
            <i class="fas fa-shield-alt text-rose-600"></i> Catatan Kedisiplinan & Pembinaan Santri
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 pb-20 lg:pb-8">

            {{-- Tab Pemilih Anak (Jika punya lebih dari 1 anak) --}}
            @if($santris->count() > 1)
                <div class="bg-white rounded-2xl p-2 shadow-xs border border-gray-200 flex items-center gap-2 overflow-x-auto">
                    <span class="text-xs font-bold text-gray-500 uppercase px-3 whitespace-nowrap">Pilih Ananda:</span>
                    @foreach($santris as $s)
                        <a href="{{ route('user.pelanggaran.index', ['santri_id' => $s->id]) }}"
                           class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $selectedSantriId === $s->id ? 'bg-rose-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ $s->nama_lengkap }} ({{ $s->kelas_label ?? 'Kelas -' }})
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Kartu Ringkasan Status Ananda --}}
            <div class="bg-white rounded-2xl p-5 shadow-xs border border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">{{ $activeSantri->nama_lengkap }}</h3>
                        <p class="text-xs text-gray-500">{{ $activeSantri->kelas_label ?? 'Kelas -' }} • NIS: {{ $activeSantri->nis ?? '-' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if($sedangDijalani > 0)
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            <i class="fas fa-exclamation-circle text-amber-600 mr-1"></i> Sedang Dalam Pembinaan ({{ $sedangDijalani }})
                        </span>
                    @else
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            <i class="fas fa-check-circle text-emerald-600 mr-1"></i> Disiplin & Bebas Sanksi
                        </span>
                    @endif
                </div>
            </div>

            {{-- Edukasi & Pengantar --}}
            <div class="p-4 rounded-xl bg-blue-50/70 border border-blue-200 text-xs text-blue-900 leading-relaxed flex items-start gap-3">
                <i class="fas fa-info-circle text-blue-600 text-base shrink-0 mt-0.5"></i>
                <div>
                    <strong>Pendidikan & Kedisiplinan Pesantren:</strong>
                    Setiap konsekuensi/sanksi yang diberikan semata-mata bersifat mendidik, melatih tanggung jawab, dan menjaga marwah akhlak santri di lingkungan pesantren.
                </div>
            </div>

            {{-- Daftar Riwayat Pelanggaran & Pembinaan --}}
            <div class="bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-history text-rose-600"></i> Riwayat Pembinaan & Konsekuensi
                    </h3>
                    <span class="text-xs text-gray-500 font-semibold">{{ $pelanggarans->count() }} Catatan</span>
                </div>

                @if($pelanggarans->isEmpty())
                    <div class="p-12 text-center text-gray-400 text-xs">
                        <i class="fas fa-medal text-4xl text-amber-400 mb-3 block"></i>
                        <h4 class="font-bold text-gray-700 text-sm">Alhamdulillah, Belum Ada Catatan Pelanggaran</h4>
                        <p class="text-gray-400 mt-1">Ananda senantiasa mematuhi tata tertib dan adab santri di Pesantren Tahfizh Alam Qur'an.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($pelanggarans as $p)
                            <div class="p-5 hover:bg-gray-50/80 transition space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $p->kategori === 'berat' ? 'bg-red-50 text-red-700 border-red-200' : ($p->kategori === 'sedang' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-blue-50 text-blue-700 border-blue-200') }}">
                                            {{ strtoupper($p->kategori) }}
                                        </span>
                                        <span class="text-xs font-bold text-gray-900">{{ $p->nama_pelanggaran }}</span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] text-gray-400">{{ $p->tanggal ? $p->tanggal->translatedFormat('d F Y') : '-' }}</span>
                                        @if($p->status_pembinaan === 'selesai')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                ✓ Selesai
                                            </span>
                                        @elseif($p->status_pembinaan === 'dibatalkan')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">
                                                Dibatalkan
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                Sedang Dijalani
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="p-3 bg-rose-50/60 border border-rose-200 rounded-xl flex items-center justify-between text-xs">
                                    <div>
                                        <span class="text-[11px] text-gray-500 block">Tindakan / Konsekuensi Disiplin:</span>
                                        <strong class="text-rose-800 font-bold text-sm">{{ $p->konsekuensi }}</strong>
                                    </div>
                                    @if($p->petugas_ustadz)
                                        <div class="text-right text-[11px] text-gray-500">
                                            <span>Pembina:</span>
                                            <strong class="text-gray-700 block">{{ $p->petugas_ustadz }}</strong>
                                        </div>
                                    @endif
                                </div>

                                @if($p->catatan)
                                    <p class="text-xs text-gray-600 bg-gray-50 p-3 rounded-lg border border-gray-100 italic">
                                        "{{ $p->catatan }}"
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
