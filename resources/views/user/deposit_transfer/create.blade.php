<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Titip Uang Saku melalui Transfer Bank</h2></x-slot>

    <div class="py-6 sm:py-8 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 pb-20 lg:pb-8">
            @if(session('success'))
                <div class="rounded-xl bg-green-50 border border-green-200 p-4 text-sm font-medium text-green-800 flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-600 text-lg"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <div class="rounded-xl bg-white p-4 sm:p-6 shadow-sm border border-gray-100">
                <h1 class="text-lg sm:text-xl font-bold text-gray-800">Ajukan Deposit Uang Saku</h1>
                <p class="mt-1 text-xs sm:text-sm text-gray-600">Transfer terlebih dahulu ke rekening berikut, kemudian unggah bukti transfer.</p>

                <div class="my-5 rounded-xl border border-teal-200 bg-teal-50/70 p-4 text-sm text-teal-950 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="font-bold text-teal-950 flex items-center gap-2">
                            <i class="fas fa-university text-teal-600"></i> Rekening Tujuan Ustadz Pembimbing
                        </p>
                        <span id="labelKelasBadge" class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-teal-200 text-teal-800">
                            Kelas
                        </span>
                    </div>
                    <div class="mt-2 text-xs text-gray-600">
                        Ustadz Pembimbing: <strong id="valUstadz" class="text-gray-900">-</strong>
                    </div>
                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs sm:text-sm">
                        <div class="bg-white/80 p-2.5 rounded-lg border border-teal-100">
                            <span class="text-teal-600 text-xs block font-semibold uppercase">Bank</span>
                            <span id="valBank" class="font-bold text-gray-800 text-sm">-</span>
                        </div>
                        <div class="bg-white/80 p-2.5 rounded-lg border border-teal-100">
                            <span class="text-teal-600 text-xs block font-semibold uppercase">Nomor Rekening</span>
                            <span id="valNorek" class="font-bold text-teal-700 text-sm font-mono tracking-wider">-</span>
                        </div>
                        <div class="bg-white/80 p-2.5 rounded-lg border border-teal-100 sm:col-span-1">
                            <span class="text-teal-600 text-xs block font-semibold uppercase">Atas Nama</span>
                            <span id="valAn" class="font-bold text-gray-800 text-sm">-</span>
                        </div>
                    </div>
                    <p id="valCatatan" class="mt-2 text-xs italic text-teal-800 hidden"></p>
                </div>

                @if($santris->isEmpty())
                    <p class="rounded-xl bg-yellow-50 border border-yellow-200 p-4 text-sm text-yellow-800 flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                        <span>Deposit tersedia setelah ada santri dengan status diterima.</span>
                    </p>
                @else
                    <form method="POST" action="{{ route('user.deposit_transfer.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="mb-1 block text-sm font-bold text-gray-700">Pilih Santri</label>
                            <select name="santri_id" id="santriSelect" required class="w-full rounded-xl border-gray-300 focus:border-teal-500 focus:ring-teal-500 text-sm">
                                @foreach($santris as $santri)
                                    @php
                                        $rekK = $rekeningKelas->get($santri->kelas);
                                        $bankName = $rekK?->bank_name ?? ($rekening?->bank_deposit ?? 'BSI');
                                        $noRek = $rekK?->nomor_rekening ?? ($rekening?->nomor_rekening_deposit ?? '-');
                                        $atasNama = $rekK?->atas_nama ?? ($rekening?->atas_nama_rekening ?? 'Pesantren Alam Quran');
                                        $ustadz = $rekK?->nama_ustadz ?? 'Ustadz Pembina Kelas ' . $santri->kelas;
                                        $catatan = $rekK?->catatan ?? '';
                                    @endphp
                                    <option value="{{ $santri->id }}"
                                            data-kelas="Kelas {{ $santri->kelas }}"
                                            data-bank="{{ $bankName }}"
                                            data-norek="{{ $noRek }}"
                                            data-an="{{ $atasNama }}"
                                            data-ustadz="{{ $ustadz }}"
                                            data-catatan="{{ $catatan }}"
                                            @selected(old('santri_id') == $santri->id)>
                                        {{ $santri->nama_lengkap }} (Kelas {{ $santri->kelas }})
                                    </option>
                                @endforeach
                            </select>
                            @error('santri_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-bold text-gray-700">Nominal Deposit (Rp)</label>
                            <input type="number" name="jumlah" value="{{ old('jumlah') }}" min="1000" step="1000" required class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Contoh: 100000">
                            @error('jumlah')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-bold text-gray-700">Bukti Transfer</label>
                            <input type="file" name="bukti_transfer" required accept=".jpg,.jpeg,.png,.pdf" class="w-full rounded-xl border border-gray-300 p-2 text-sm bg-gray-50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @error('bukti_transfer')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            <p class="mt-1 text-xs text-gray-500">JPG, PNG, atau PDF. Maksimal 5 MB.</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-bold text-gray-700">Catatan (opsional)</label>
                            <textarea name="catatan_wali" rows="3" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Contoh: Transfer dari rekening BSI wali">{{ old('catatan_wali') }}</textarea>
                        </div>

                        <button type="submit" class="w-full sm:w-auto rounded-xl bg-blue-600 px-6 py-3 font-bold text-white hover:bg-blue-700 transition shadow-sm flex items-center justify-center gap-2 text-sm">
                            <i class="fas fa-paper-plane"></i> Kirim Pengajuan Deposit
                        </button>
                    </form>
                @endif
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-100">
                <div class="border-b border-gray-100 px-4 sm:px-6 py-4 font-bold text-gray-800 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-history text-blue-600"></i> Riwayat Pengajuan Deposit
                    </span>
                    <span class="text-xs font-normal text-gray-400 block sm:hidden">
                        <i class="fas fa-arrows-left-right mr-1"></i>Geser tabel
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-600 font-bold border-b border-gray-100">
                            <tr>
                                <th class="px-4 sm:px-5 py-3.5 whitespace-nowrap">Tanggal</th>
                                <th class="px-4 sm:px-5 py-3.5 whitespace-nowrap">Santri</th>
                                <th class="px-4 sm:px-5 py-3.5 whitespace-nowrap">Nominal</th>
                                <th class="px-4 sm:px-5 py-3.5 whitespace-nowrap">Status</th>
                                <th class="px-4 sm:px-5 py-3.5 whitespace-nowrap">Catatan Admin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($depositTerbaru as $item)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="px-4 sm:px-5 py-3.5 whitespace-nowrap text-gray-600 text-xs font-medium">
                                        {{ $item->created_at->format('d M Y H:i') }}
                                    </td>
                                    <td class="px-4 sm:px-5 py-3.5 whitespace-nowrap font-medium text-gray-800">
                                        {{ $item->santri->nama_lengkap }}
                                    </td>
                                    <td class="px-4 sm:px-5 py-3.5 whitespace-nowrap font-bold text-blue-600">
                                        Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 sm:px-5 py-3.5 whitespace-nowrap">
                                        @if($item->status === 'disetujui' || $item->status === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle"></i> Disetujui
                                            </span>
                                        @elseif($item->status === 'ditolak' || $item->status === 'rejected')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle"></i> Ditolak
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock"></i> {{ ucwords(str_replace('_', ' ', $item->status)) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 sm:px-5 py-3.5 text-gray-600 text-xs min-w-[140px]">
                                        {{ $item->catatan_admin ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-gray-500 text-xs sm:text-sm">
                                        Belum ada pengajuan deposit.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($depositTerbaru->hasPages())
                    <div class="p-4 border-t border-gray-100">{{ $depositTerbaru->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('santriSelect');
            function updateBankInfo() {
                if (!select) return;
                const opt = select.options[select.selectedIndex];
                if (!opt) return;

                document.getElementById('labelKelasBadge').innerText = opt.dataset.kelas || 'Santri';
                document.getElementById('valUstadz').innerText = opt.dataset.ustadz || '-';
                document.getElementById('valBank').innerText = opt.dataset.bank || '-';
                document.getElementById('valNorek').innerText = opt.dataset.norek || '-';
                document.getElementById('valAn').innerText = opt.dataset.an || '-';

                const catatanEl = document.getElementById('valCatatan');
                if (opt.dataset.catatan && opt.dataset.catatan.trim() !== '') {
                    catatanEl.innerText = 'Catatan: ' + opt.dataset.catatan;
                    catatanEl.classList.remove('hidden');
                } else {
                    catatanEl.classList.add('hidden');
                }
            }

            if (select) {
                select.addEventListener('change', updateBankInfo);
                updateBankInfo();
            }
        });
    </script>
</x-app-layout>
