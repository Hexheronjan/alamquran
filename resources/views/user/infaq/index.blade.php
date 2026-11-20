<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
            <i class="fas fa-heart text-emerald-600"></i>
            {{ __('Infaq & Sedekah Online') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-10 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 shadow-sm">
                    <i class="fas fa-check-circle text-emerald-600 text-xl flex-shrink-0"></i>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-4 shadow-sm">
       -             <i class="fas fa-exclamation-circle text-rose-600 text-xl flex-shrink-0"></i>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Hero Banner --}}
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
                <div class="relative z-10 max-w-2xl">
                    <span class="inline-block bg-white/20 backdrop-blur-md text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider mb-3">
                        Lembaga Amil & Sedekah Pesantren
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white leading-tight">
                        Raih Keberkahan Melalui Infaq & Sedekah
                    </h1>
                    <p class="mt-2 text-emerald-100 text-sm leading-relaxed">
                        Salurkan bantuan terbaik Anda untuk mendukung operasional santri, sarana prasarana pesantren, dan beasiswa murid berprestasi.
                    </p>
                </div>
                <div class="absolute -right-10 -bottom-10 opacity-15 text-white pointer-events-none">
                    <i class="fas fa-hand-holding-heart text-[160px]"></i>
                </div>
            </div>

            {{-- Programs List --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-list-ul text-emerald-600"></i>
                        Program Donasi Aktif
                    </h3>
                    <span class="text-xs font-medium text-gray-500">{{ $programs->count() }} Program Berjalan</span>
                </div>

                @if($programs->isEmpty())
                    <div class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-sm">
                        <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mx-auto mb-3 text-2xl">
                            <i class="fas fa-heart-broken"></i>
                        </div>
                        <p class="text-gray-600 font-semibold">Belum Ada Program Donasi Aktif</p>
                        <p class="text-gray-400 text-xs mt-1">Silakan cek kembali secara berkala.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($programs as $program)
                            @php
                                $progress = $program->target_dana > 0
                                    ? min(100, round(($program->dana_terkumpul / $program->target_dana) * 100))
                                    : 0;
                            @endphp
                            <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition flex flex-col group">
                                {{-- Banner --}}
                                <div class="relative h-48 bg-gradient-to-br from-emerald-500 to-teal-700 overflow-hidden">
                                    @if($program->foto_banner)
                                        <img src="{{ Storage::url($program->foto_banner) }}"
                                             alt="{{ $program->nama_program }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-emerald-600 text-white\'><i class=\'fas fa-hand-holding-heart text-5xl\'></i></div>';">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-white/50 text-5xl">
                                            <i class="fas fa-hand-holding-heart"></i>
                                        </div>
                                    @endif
                                </div>

                                {{-- Details --}}
                                <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                                    <div>
                                        <h4 class="font-bold text-gray-800 text-base leading-snug group-hover:text-emerald-600 transition">
                                            {{ $program->nama_program }}
                                        </h4>
                                        <p class="text-xs text-gray-500 mt-2 line-clamp-3 leading-relaxed">
                                            {{ $program->deskripsi }}
                                        </p>
                                    </div>

                                    {{-- Progress Bar --}}
                                    <div class="space-y-2 pt-2 border-t border-gray-100">
                                        <div class="flex justify-between text-xs font-semibold">
                                            <span class="text-gray-500">Terkumpul</span>
                                            <span class="text-emerald-600">{{ $progress }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                            <div class="bg-emerald-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                                        </div>
                                        <div class="flex justify-between text-xs font-bold pt-1">
                                            <span class="text-emerald-700">Rp {{ number_format($program->dana_terkumpul ?? 0, 0, ',', '.') }}</span>
                                            <span class="text-gray-400">Target: Rp {{ number_format($program->target_dana ?? 0, 0, ',', '.') }}</span>
                                        </div>
                                    </div>

                                    {{-- Action Button --}}
                                    <button type="button"
                                            onclick="openDonasiModal({{ $program->id }}, '{{ addslashes($program->nama_program) }}')"
                                            class="w-full py-2.5 rounded-xl text-white font-bold text-sm shadow transition flex items-center justify-center gap-2 cursor-pointer"
                                            style="background-color: #16a34a !important; color: #ffffff !important;">
                                        <i class="fas fa-donate"></i> Salurkan Donasi
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Donation History --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                        <i class="fas fa-history text-emerald-600"></i> Riwayat Donasi Anda
                    </h3>
                    <span class="text-xs text-gray-400">{{ $myDonations->count() }} Transaksi</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-gray-700 font-bold text-xs uppercase">
                            <tr>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Program</th>
                                <th class="px-5 py-3">Nominal</th>
                                <th class="px-5 py-3">Metode</th>
                                <th class="px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($myDonations as $donasi)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-3.5 font-mono text-xs">{{ $donasi->created_at->format('d M Y H:i') }}</td>
                                    <td class="px-5 py-3.5 font-bold text-gray-800">{{ $donasi->program->nama_program ?? '-' }}</td>
                                    <td class="px-5 py-3.5 font-extrabold text-emerald-600">Rp {{ number_format($donasi->nominal, 0, ',', '.') }}</td>
                                    <td class="px-5 py-3.5 capitalize">
                                        @if($donasi->metode_pembayaran === 'potong_saldo')
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200">Potong Saldo</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">Transfer Bank</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if($donasi->status === 'berhasil')
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Berhasil</span>
                                        @elseif($donasi->status === 'menunggu_konfirmasi')
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Menunggu Konfirmasi</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">Ditolak</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-gray-400">
                                        Belum ada riwayat donasi dari akun ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- Modal Form Donasi --}}
    <div id="modalDonasi" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden animate-in fade-in my-auto max-h-[92vh] flex flex-col">
            <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between bg-emerald-600 text-white flex-shrink-0">
                <h3 class="text-sm font-bold flex items-center gap-2">
                    <i class="fas fa-hand-holding-heart"></i> Salurkan Donasi
                </h3>
                <button type="button" onclick="closeDonasiModal()" class="text-white/80 hover:text-white cursor-pointer">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <form action="{{ route('user.infaq.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col overflow-hidden">
                @csrf
                <input type="hidden" name="infaq_program_id" id="modalProgramId">

                <div class="p-4 sm:p-5 space-y-3 text-xs overflow-y-auto flex-1">
                    <div>
                        <label class="block text-gray-500 font-semibold mb-1 text-[11px]">Nama Program</label>
                        <div id="modalProgramTitle" class="font-bold text-gray-900 text-xs bg-gray-50 px-3 py-2 rounded-lg border border-gray-200"></div>
                    </div>

                    {{-- Nominal --}}
                    <div>
                        <label class="block text-gray-700 font-bold uppercase mb-1 text-[11px]">Nominal Donasi (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="nominal" id="modalNominal" min="1000" step="1000" required
                               placeholder="Contoh: 50000"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-bold text-emerald-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <div class="flex gap-1.5 mt-1.5">
                            <button type="button" onclick="setNominal(20000)" class="px-2.5 py-1 bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 font-bold rounded-md text-[11px] transition border">20rb</button>
                            <button type="button" onclick="setNominal(50000)" class="px-2.5 py-1 bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 font-bold rounded-md text-[11px] transition border">50rb</button>
                            <button type="button" onclick="setNominal(100000)" class="px-2.5 py-1 bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 font-bold rounded-md text-[11px] transition border">100rb</button>
                            <button type="button" onclick="setNominal(250000)" class="px-2.5 py-1 bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 font-bold rounded-md text-[11px] transition border">250rb</button>
                        </div>
                    </div>

                    {{-- Metode Pembayaran --}}
                    <div>
                        <label class="block text-gray-700 font-bold uppercase mb-1 text-[11px]">Metode Pembayaran <span class="text-red-500">*</span></label>
                        <select name="metode_pembayaran" id="modalMetode" onchange="toggleMetodeFields(this.value)" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500">
                            <option value="transfer">Transfer Bank / E-Wallet</option>
                            @if($santris->count() > 0)
                                <option value="potong_saldo">Potong Saldo Uang Saku Santri</option>
                            @endif
                        </select>
                    </div>

                    {{-- Select Santri (jika potong_saldo) --}}
                    @if($santris->count() > 0)
                        <div id="fieldSantri" class="hidden">
                            <label class="block text-gray-700 font-bold uppercase mb-1 text-[11px]">Pilih Santri (Potong Saldo) <span class="text-red-500">*</span></label>
                            <select name="santri_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500">
                                @foreach($santris as $s)
                                    <option value="{{ $s->id }}">
                                        {{ $s->nama_lengkap }} (Kelas {{ $s->kelas }}) - Saldo: Rp {{ number_format($s->saldo_uang_saku ?? 0, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Info Rekening & Bukti Transfer (jika transfer) --}}
                    <div id="fieldTransfer">
                        @if($rekening)
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-2.5 mb-2 text-blue-900 text-[11px] space-y-0.5">
                                <p class="font-bold">Rekening Transfer Donasi:</p>
                                <p><span class="font-bold">{{ $rekening->bank_deposit ?? $rekening->bank_name ?? 'Bank BSI' }}</span>: <span class="font-mono font-bold text-xs text-blue-700">{{ $rekening->nomor_rekening_deposit ?? $rekening->nomor_rekening ?? '-' }}</span></p>
                                <p>a.n. <span class="font-semibold">{{ $rekening->atas_nama_rekening ?? $rekening->atas_nama ?? 'Pesantren Alam Quran' }}</span></p>
                            </div>
                        @endif

                        <label class="block text-gray-700 font-bold uppercase mb-1 text-[11px]">Upload Bukti Transfer</label>
                        <input type="file" name="bukti_transfer" accept="image/*"
                               class="w-full text-[11px] text-gray-500 border border-gray-300 rounded-lg file:mr-2.5 file:py-1 file:px-2.5 file:rounded file:border-0 file:bg-emerald-50 file:text-emerald-700 file:font-bold">
                    </div>

                    {{-- Doa / Harapan --}}
                    <div>
                        <label class="block text-gray-700 font-bold uppercase mb-1 text-[11px]">Doa / Harapan (Opsional)</label>
                        <textarea name="doa_harapan" rows="2" placeholder="Tuliskan doa atau harapan Anda..."
                                  class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>
                </div>

                {{-- Submit & Cancel (Fixed footer) --}}
                <div class="p-3 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2 flex-shrink-0">
                    <button type="button" onclick="closeDonasiModal()"
                            class="px-4 py-1.5 bg-gray-700 text-white font-bold rounded-lg text-xs cursor-pointer"
                            style="background-color: #374151 !important; color: #ffffff !important;">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs shadow transition cursor-pointer"
                            style="background-color: #16a34a !important; color: #ffffff !important;">
                        Kirim Donasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openDonasiModal(programId, programTitle) {
            document.getElementById('modalProgramId').value = programId;
            document.getElementById('modalProgramTitle').innerText = programTitle;
            document.getElementById('modalDonasi').classList.remove('hidden');
        }

        function closeDonasiModal() {
            document.getElementById('modalDonasi').classList.add('hidden');
        }

        function setNominal(val) {
            document.getElementById('modalNominal').value = val;
        }

        function toggleMetodeFields(val) {
            const fieldSantri = document.getElementById('fieldSantri');
            const fieldTransfer = document.getElementById('fieldTransfer');

            if (val === 'potong_saldo') {
                if (fieldSantri) fieldSantri.classList.remove('hidden');
                if (fieldTransfer) fieldTransfer.classList.add('hidden');
            } else {
                if (fieldSantri) fieldSantri.classList.add('hidden');
                if (fieldTransfer) fieldTransfer.classList.remove('hidden');
            }
        }
    </script>
</x-app-layout>
