<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 flex items-center gap-2">
            <i class="fas fa-lightbulb text-indigo-600"></i> Saran & Masukan Pengembangan Aplikasi
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 pb-20 lg:pb-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="flex items-center gap-3 bg-green-50 border border-green-300 text-green-800 rounded-xl p-4 shadow-xs">
                    <i class="fas fa-check-circle text-green-600 text-xl flex-shrink-0"></i>
                    <span class="text-xs sm:text-sm font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="flex items-start gap-3 bg-red-50 border border-red-300 text-red-800 rounded-xl p-4 shadow-xs text-xs">
                    <i class="fas fa-exclamation-triangle text-red-600 text-lg flex-shrink-0 mt-0.5"></i>
                    <div>
                        <strong class="block mb-1 font-bold">Mohon periksa isian formulir:</strong>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Banner Ajakan Berkontribusi -->
            <div class="p-5 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-950 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                <div class="space-y-1">
                    <h3 class="font-bold text-sm flex items-center gap-2 text-indigo-900">
                        <i class="fas fa-comments text-indigo-600 text-base"></i> Suara & Aspirasi Bapak/Ibu Wali Santri
                    </h3>
                    <p class="text-xs text-indigo-800/80 leading-relaxed max-w-xl">
                        Aplikasi Pesantren Tahfizh Alam Qur'an terus berkembang. Punya usulan fitur baru, keluhan tampilan, atau kendala pemakaian? Silakan sampaikan melalui formulir di bawah ini.
                    </p>
                </div>
                <div class="shrink-0">
                    <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-white text-indigo-700 shadow-xs border border-indigo-200 inline-flex items-center gap-1.5">
                        <i class="fas fa-heart text-rose-500"></i> Terbuka & Transparan
                    </span>
                </div>
            </div>

            <!-- Form Kirim Saran -->
            <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-200 space-y-4">
                <h3 class="font-bold text-sm text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-3">
                    <i class="fas fa-paper-plane text-indigo-600"></i> Kirim Masukan Baru
                </h3>

                <form action="{{ route('user.saran.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                    @csrf

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1 flex items-center gap-1.5">
                            <i class="fas fa-tags text-indigo-500"></i> Kategori Masukan <span class="text-red-500">*</span>
                        </label>
                        <select name="kategori" required class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                            @foreach($kategoriList as $k => $lbl)
                                <option value="{{ $k }}" {{ old('kategori') === $k ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Judul / Pokok Masukan <span class="text-red-500">*</span></label>
                        <input type="text" name="judul" value="{{ old('judul') }}" placeholder="Contoh: Usul fitur notifikasi WA saat santri setor hafalan" required
                               class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Uraian Detail Masukan / Kendala <span class="text-red-500">*</span></label>
                        <textarea name="pesan" rows="4" placeholder="Ceritakan ide Anda secara jelas atau ceritakan kendala yang Anda alami..." required
                                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500">{{ old('pesan') }}</textarea>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Lampiran Tangkapan Layar / Gambar (Opsional)</label>
                        <input type="file" name="lampiran" accept="image/*"
                               class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                        <span class="text-[11px] text-gray-400 mt-1 block">Format: JPG, PNG maksimal 3 MB.</span>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 font-bold rounded-xl text-xs shadow-md transition cursor-pointer"
                                style="background-color: #4f46e5 !important; color: #ffffff !important;">
                            <i class="fas fa-paper-plane" style="color: #ffffff !important;"></i> Kirim Masukan Sekarang
                        </button>
                    </div>
                </form>
            </div>

            <!-- Riwayat Saran Anda -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-history text-indigo-600"></i> Riwayat Masukan & Tanggapan Pengembang
                    </h3>
                    <span class="text-xs text-gray-500 font-semibold">{{ $sarans->count() }} Masukan</span>
                </div>

                @if($sarans->isEmpty())
                    <div class="p-10 text-center text-gray-400 text-xs">
                        <i class="fas fa-inbox text-3xl text-gray-300 mb-2 block"></i>
                        Anda belum pernah mengirimkan saran atau masukan pengembangan.
                    </div>
                @else
                    <div class="divide-y divide-gray-100 text-xs">
                        @foreach($sarans as $s)
                            <div class="p-5 hover:bg-gray-50/80 transition space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 inline-flex items-center gap-1.5">
                                            <i class="{{ \App\Models\SaranMasukan::getKategoriIcon($s->kategori, 'fa') }}"></i>
                                            {{ $kategoriList[$s->kategori] ?? ucfirst($s->kategori) }}
                                        </span>
                                        <h4 class="font-bold text-gray-900 text-sm mt-1.5">{{ $s->judul }}</h4>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] text-gray-400">{{ $s->created_at ? $s->created_at->translatedFormat('d M Y, H:i') : '-' }}</span>
                                        @if($s->status === 'diterapkan')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                ✓ Diterapkan
                                            </span>
                                        @elseif($s->status === 'sedang_diproses')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800">
                                                Sedang Diproses
                                            </span>
                                        @elseif($s->status === 'diarsipkan')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">
                                                Diarsipkan
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                Menunggu Review
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <p class="text-xs text-gray-700 leading-relaxed whitespace-pre-line">{{ $s->pesan }}</p>

                                @if($s->lampiran)
                                    <div>
                                        <a href="{{ Storage::url($s->lampiran) }}" target="_blank"
                                           class="inline-flex items-center gap-1 text-[11px] text-blue-600 hover:underline font-bold">
                                            <i class="fas fa-image"></i> Lihat Lampiran Gambar
                                        </a>
                                    </div>
                                @endif

                                @if($s->tanggapan_admin)
                                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 space-y-1">
                                        <div class="font-bold text-emerald-900 flex items-center gap-1.5 text-xs">
                                            <i class="fas fa-reply text-emerald-600"></i> Tanggapan Resmi Pengembang / Pondok:
                                        </div>
                                        <p class="text-xs text-emerald-800 leading-relaxed italic">"{{ $s->tanggapan_admin }}"</p>
                                        <span class="text-[10px] text-emerald-600 block mt-1">Dibalas {{ $s->responded_at ? $s->responded_at->translatedFormat('d M Y, H:i') : '' }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
