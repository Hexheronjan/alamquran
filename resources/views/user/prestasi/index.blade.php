<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-black text-xl text-gray-800 leading-tight flex items-center gap-2">
                    <i class="ti ti-trophy text-amber-500"></i> Papan Prestasi & Penghargaan Santri
                </h2>
                <p class="text-xs text-gray-500 mt-1">Galeri rekam jejak juara, piagam penghargaan, dan pencapaian santri Pondok Pesantren Alam Qur'an.</p>
            </div>
            @if($mySantris->isNotEmpty())
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold">
                    <i class="ti ti-user-star text-amber-600"></i>
                    <span>{{ $mySantris->count() }} Ananda Terdaftar</span>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Banner Galeri Prestasi Bersama -->
            <div class="rounded-3xl p-6 text-white shadow-lg relative overflow-hidden"
                 style="background: linear-gradient(135deg, #d97706 0%, #b45309 50%, #78350f 100%) !important; color: #ffffff !important;">
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl font-black shrink-0"
                             style="background: rgba(255, 255, 255, 0.2) !important; color: #fef3c7 !important; border: 1px solid rgba(255, 255, 255, 0.3) !important;">
                            <i class="ti ti-trophy"></i>
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold mb-1"
                                  style="background: rgba(255, 255, 255, 0.2) !important; color: #fef3c7 !important; border: 1px solid rgba(255, 255, 255, 0.3) !important;">
                                <i class="ti ti-sparkles"></i> Galeri Prestasi Publik Seluruh Santri
                            </span>
                            <h3 class="text-xl font-black tracking-tight" style="color: #ffffff !important; font-size: 1.5rem !important; line-height: 2rem !important; margin: 0 !important; font-weight: 900 !important; text-shadow: 0 1px 3px rgba(0,0,0,0.3) !important;">
                                Pondok Pesantren Alam Qur'an
                            </h3>
                            <p class="text-xs mt-0.5 font-medium" style="color: #fef3c7 !important;">
                                Dokumentasi resmi rekam jejak juara dan penghargaan membanggakan para santri.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 shrink-0 flex-wrap">
                        <div class="px-5 py-3 rounded-2xl text-center"
                             style="background: rgba(255, 255, 255, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.25) !important;">
                            <div class="text-2xl font-black" style="color: #ffffff !important;">{{ $totalPrestasi }}</div>
                            <div class="text-[10px] uppercase font-bold" style="color: #fde68a !important;">Total Prestasi</div>
                        </div>
                        <div class="px-5 py-3 rounded-2xl text-center"
                             style="background: rgba(255, 255, 255, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.25) !important;">
                            <div class="text-2xl font-black" style="color: #fef08a !important;">{{ $tingkatNasional }}</div>
                            <div class="text-[10px] uppercase font-bold" style="color: #fde68a !important;">Nasional / Inter</div>
                        </div>
                        <div class="px-5 py-3 rounded-2xl text-center"
                             style="background: rgba(255, 255, 255, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.25) !important;">
                            <div class="text-2xl font-black" style="color: #fef08a !important;">{{ $totalSantriJuara }}</div>
                            <div class="text-[10px] uppercase font-bold" style="color: #fde68a !important;">Santri Juara</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-100 space-y-3">
                {{-- Filter Khusus Ananda Saya jika wali memiliki anak --}}
                @if($mySantris->isNotEmpty())
                    <div class="flex items-center gap-2 flex-wrap pb-3 border-b border-gray-100">
                        <span class="text-xs font-bold text-gray-500 mr-1 flex items-center gap-1.5">
                            <i class="ti ti-filter text-amber-500"></i> Lihat Santri:
                        </span>
                        <a href="{{ route('user.prestasi.index', array_merge(request()->except('santri_id'), ['santri_id' => 'all'])) }}"
                           class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ (!request('santri_id') || request('santri_id') === 'all') ? 'bg-amber-500 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Semua Santri (Publik)
                        </a>
                        @foreach($mySantris as $ms)
                            <a href="{{ route('user.prestasi.index', array_merge(request()->except('santri_id'), ['santri_id' => $ms->id])) }}"
                               class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ request('santri_id') == $ms->id ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100' }}">
                                <i class="ti ti-star-filled text-[10px]"></i> Ananda: {{ $ms->nama_lengkap }}
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Filter Tahun Ajaran & Kategori --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 flex-wrap">
                    @if(count($tahunAjaranList) > 0)
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-bold text-gray-500">Tahun Ajaran:</span>
                            <a href="{{ route('user.prestasi.index', array_merge(request()->except('tahun_ajaran'), ['tahun_ajaran' => 'all'])) }}"
                               class="px-3 py-1 rounded-lg text-xs font-bold transition {{ (!request('tahun_ajaran') || request('tahun_ajaran') === 'all') ? 'bg-amber-500 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                Semua TA
                            </a>
                            @foreach($tahunAjaranList as $th)
                                <a href="{{ route('user.prestasi.index', array_merge(request()->except('tahun_ajaran'), ['tahun_ajaran' => $th])) }}"
                                   class="px-3 py-1 rounded-lg text-xs font-bold transition {{ request('tahun_ajaran') === $th ? 'bg-amber-500 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                    {{ $th }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    {{-- Filter Kategori --}}
                    <form method="GET" action="{{ route('user.prestasi.index') }}" class="inline-flex items-center gap-2">
                        @if(request('santri_id')) <input type="hidden" name="santri_id" value="{{ request('santri_id') }}"> @endif
                        @if(request('tahun_ajaran')) <input type="hidden" name="tahun_ajaran" value="{{ request('tahun_ajaran') }}"> @endif
                        <select name="kategori" onchange="this.form.submit()" class="text-xs font-bold border border-gray-300 rounded-xl px-3 py-1.5 bg-gray-50 text-gray-700 focus:ring-amber-500">
                            <option value="all">Semua Kategori</option>
                            @foreach($kategoriList as $kKey => $kVal)
                                <option value="{{ $kKey }}" {{ request('kategori') === $kKey ? 'selected' : '' }}>{{ $kVal }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            <!-- Galeri Card Grid Prestasi -->
            @if($prestasis->isEmpty())
                <div class="bg-white rounded-3xl p-12 text-center shadow-xs border border-gray-100">
                    <div class="w-20 h-20 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mx-auto mb-4 text-4xl">
                        <i class="ti ti-trophy"></i>
                    </div>
                    <h4 class="font-black text-base text-gray-900">Belum Ada Rekam Prestasi</h4>
                    <p class="text-xs text-gray-500 max-w-md mx-auto mt-1 leading-relaxed">
                        Belum ada data prestasi santri yang dicatat dengan kriteria filter ini.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                     style="display: grid !important; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)) !important; gap: 1.5rem !important;">
                    @foreach($prestasis as $item)
                        @php
                            $isMyChild = $mySantris->contains('id', $item->santri_id);
                        @endphp
                        <div class="bg-white rounded-3xl shadow-xs border border-gray-100 overflow-hidden flex flex-col hover:shadow-lg transition {{ $isMyChild ? 'ring-2 ring-emerald-500' : '' }}">
                            @if($item->foto_piagam)
                                <div class="cursor-pointer overflow-hidden group relative"
                                     style="height: 220px !important; width: 100% !important; background-color: #f8fafc !important; position: relative !important; overflow: hidden !important; display: flex !important; align-items: center !important; justify-content: center !important;"
                                     onclick="previewPiagamModal('{{ asset('storage/' . $item->foto_piagam) }}', '{{ addslashes($item->nama_prestasi) }}')">
                                    <img src="{{ asset('storage/' . $item->foto_piagam) }}" alt="{{ $item->nama_prestasi }}"
                                         style="width: 100% !important; height: 100% !important; max-height: 220px !important; object-fit: contain !important; display: block !important;">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white gap-2 font-bold text-xs" style="transition: all 0.2s;">
                                        <i class="ti ti-zoom-in text-lg"></i> Perbesar Piagam
                                    </div>
                                    <div class="absolute top-3 left-3" style="position: absolute !important; top: 12px !important; left: 12px !important;">
                                        <span class="px-3 py-1 rounded-full text-xs font-black shadow-md" style="background-color: #f59e0b !important; color: #ffffff !important;">
                                            {{ $item->peringkat }}
                                        </span>
                                    </div>
                                    @if($isMyChild)
                                        <div class="absolute top-3 right-3" style="position: absolute !important; top: 12px !important; right: 12px !important;">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black shadow-md flex items-center gap-1" style="background-color: #059669 !important; color: #ffffff !important;">
                                                <i class="ti ti-star-filled text-[10px]"></i> Ananda Anda
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="flex items-center justify-center relative p-4"
                                     style="height: 140px !important; background: linear-gradient(135deg, #fef3c7 0%, #ffedd5 100%) !important; position: relative !important;">
                                    <i class="ti ti-trophy text-5xl text-amber-400"></i>
                                    <div class="absolute top-3 left-3" style="position: absolute !important; top: 12px !important; left: 12px !important;">
                                        <span class="px-3 py-1 rounded-full text-xs font-black shadow-xs" style="background-color: #f59e0b !important; color: #ffffff !important;">
                                            {{ $item->peringkat }}
                                        </span>
                                    </div>
                                    @if($isMyChild)
                                        <div class="absolute top-3 right-3" style="position: absolute !important; top: 12px !important; right: 12px !important;">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black shadow-md flex items-center gap-1" style="background-color: #059669 !important; color: #ffffff !important;">
                                                <i class="ti ti-star-filled text-[10px]"></i> Ananda Anda
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="p-5 flex-1 flex flex-col justify-between">
                                <div>
                                    <!-- Nama Santri & Kelas -->
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-lg bg-teal-100 text-teal-800 flex items-center justify-center text-xs font-black shrink-0">
                                                {{ strtoupper(substr($item->santri->nama_lengkap ?? 'S', 0, 1)) }}
                                            </div>
                                            <div>
                                                <h5 class="text-xs font-black text-gray-900 leading-tight flex items-center gap-1.5">
                                                    {{ $item->santri->nama_lengkap ?? 'Santri' }}
                                                </h5>
                                                <span class="text-[10px] text-gray-500 font-semibold block">
                                                    {{ $item->santri->kelas ?? 'Santri' }} • NIS: {{ $item->santri->nis ?? '-' }}
                                                </span>
                                            </div>
                                        </div>
                                        <span class="text-[11px] font-bold text-gray-400">
                                            TA {{ $item->tahun_ajaran }}
                                        </span>
                                    </div>

                                    <!-- Badges Kategori & Tingkat -->
                                    <div class="flex items-center gap-1.5 mb-2.5 flex-wrap">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                            {{ $item->getKategoriLabel() }}
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200 uppercase">
                                            {{ $item->getTingkatLabel() }}
                                        </span>
                                    </div>

                                    <!-- Nama Prestasi -->
                                    <h4 class="font-black text-sm text-gray-900 leading-snug">{{ $item->nama_prestasi }}</h4>

                                    @if($item->penyelenggara)
                                        <div class="text-xs text-gray-500 mt-2 flex items-center gap-1.5">
                                            <i class="ti ti-building text-amber-600"></i>
                                            <span>{{ $item->penyelenggara }}</span>
                                        </div>
                                    @endif

                                    @if($item->tanggal_perolehan)
                                        <div class="text-xs text-gray-400 mt-1 flex items-center gap-1.5">
                                            <i class="ti ti-calendar text-gray-400"></i>
                                            <span>{{ $item->tanggal_perolehan->translatedFormat('d F Y') }}</span>
                                        </div>
                                    @endif

                                    @if($item->deskripsi)
                                        <p class="text-xs text-gray-600 mt-3 p-3 bg-gray-50 rounded-xl border border-gray-100 leading-relaxed whitespace-pre-line">
                                            {{ $item->deskripsi }}
                                        </p>
                                    @endif
                                </div>

                                @if($item->foto_piagam)
                                    <div class="pt-4 mt-4 border-t border-gray-100">
                                        <button type="button" onclick="previewPiagamModal('{{ asset('storage/' . $item->foto_piagam) }}', '{{ addslashes($item->nama_prestasi) }}')"
                                                class="w-full py-2 px-3 rounded-xl bg-amber-50 text-amber-700 font-bold text-xs hover:bg-amber-100 flex items-center justify-center gap-2 border border-amber-200 transition">
                                            <i class="ti ti-photo"></i> Lihat Piagam Penghargaan
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>

    <!-- Modal Zoom Piagam -->
    <div id="modalPreviewPiagam" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-2xl w-full overflow-hidden shadow-2xl animate-in fade-in zoom-in duration-200">
            <div class="p-4 bg-gray-900 text-white flex items-center justify-between">
                <h4 id="previewPiagamTitle" class="font-bold text-xs truncate max-w-md">Piagam Penghargaan</h4>
                <button type="button" onclick="closePreviewPiagamModal()" class="text-gray-400 hover:text-white p-1">
                    <i class="ti ti-x text-lg"></i>
                </button>
            </div>
            <div class="p-4 flex items-center justify-center bg-gray-100 max-h-[75vh] overflow-auto">
                <img id="previewPiagamImg" src="" alt="Piagam" class="max-h-[70vh] rounded-xl shadow-md object-contain">
            </div>
            <div class="p-3 bg-white border-t border-gray-100 text-right">
                <button type="button" onclick="closePreviewPiagamModal()" class="px-4 py-1.5 bg-gray-200 text-gray-800 text-xs font-bold rounded-xl hover:bg-gray-300">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function previewPiagamModal(src, title) {
            document.getElementById('previewPiagamImg').src = src;
            document.getElementById('previewPiagamTitle').innerText = title;
            document.getElementById('modalPreviewPiagam').classList.remove('hidden');
        }
        function closePreviewPiagamModal() {
            document.getElementById('modalPreviewPiagam').classList.add('hidden');
        }
    </script>
</x-app-layout>
