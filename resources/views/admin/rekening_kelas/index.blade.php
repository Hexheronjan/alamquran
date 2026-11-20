@extends('layouts.admin.app')

@section('title', 'Rekening Ustadz Per Kelas')

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

        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="ti ti-building-bank text-teal-600"></i> Rekening Ustadz Pembimbing Per Kelas
                </h1>
                <p class="text-gray-600 text-sm mt-1">Atur rekening tujuan transfer deposit uang saku santri sesuai jenjang kelas masing-masing (Kelas 1, 2, 3).</p>
            </div>
            <div>
                <a href="{{ route('admin.deposit_transfer.index') }}" class="px-4 py-2.5 bg-emerald-600 text-white rounded-lg font-bold text-sm hover:bg-emerald-700 transition shadow-sm flex items-center gap-2 cursor-pointer" style="background-color: #16a34a !important; color: #ffffff !important;">
                    <i class="ti ti-checkup-list text-base"></i> Verifikasi Deposit Transfer
                </a>
            </div>
        </div>

        <!-- Info Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 text-xs text-blue-800 flex items-start gap-3">
            <i class="ti ti-info-circle text-xl text-blue-600 flex-shrink-0 mt-0.5"></i>
            <div>
                <span class="font-bold text-blue-900">Alur Deposit Uang Saku Multi-Rekening:</span>
                <ol class="list-decimal list-inside mt-1 space-y-0.5 text-blue-800">
                    <li>Wali santri memilih santri di portal &rarr; sistem otomatis menampilkan rekening ustadz kelas santri tersebut.</li>
                    <li>Wali transfer dan unggah bukti transfer.</li>
                    <li>Ustadz pembimbing kelas memeriksa mutasi rekening bank dan menyetujui deposit di aplikasi.</li>
                    <li>Saldo otomatis bertambah ke akun santri dan siap dibelanjakan di Kantin atau Aqumart.</li>
                </ol>
            </div>
        </div>

        <!-- Grid Cards 3 Kelas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach(['1' => 'Kelas 1', '2' => 'Kelas 2', '3' => 'Kelas 3'] as $kelasKey => $kelasLabel)
                @php
                    $rek = $rekeningKelas->firstWhere('kelas', $kelasKey);
                @endphp
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <!-- Card Header -->
                        <div class="p-5 border-b border-gray-100 flex items-center justify-between {{ $kelasKey == '1' ? 'bg-teal-50/50' : ($kelasKey == '2' ? 'bg-blue-50/50' : 'bg-amber-50/50') }}">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-white {{ $kelasKey == '1' ? 'bg-teal-600' : ($kelasKey == '2' ? 'bg-blue-600' : 'bg-amber-600') }}">
                                    {{ $kelasKey }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 text-sm">{{ $kelasLabel }}</h3>
                                    <span class="text-[11px] {{ $rek && $rek->is_active ? 'text-green-600 font-semibold' : 'text-gray-400' }}">
                                        {{ $rek && $rek->is_active ? '● Rekening Aktif' : 'Belum Dikonfigurasi' }}
                                    </span>
                                </div>
                            </div>
                            <button type="button" onclick="openEditModal('{{ $kelasKey }}', '{{ $kelasLabel }}', {{ json_encode($rek) }})"
                                    class="p-2 text-gray-600 hover:text-teal-600 hover:bg-white rounded-lg transition cursor-pointer" title="Edit Rekening">
                                <i class="ti ti-edit text-lg"></i>
                            </button>
                        </div>

                        <!-- Card Body -->
                        <div class="p-5 space-y-3.5 text-xs">
                            @if($rek)
                                <div>
                                    <span class="text-gray-400 block mb-0.5">Ustadz Pembimbing:</span>
                                    <div class="font-bold text-gray-900 text-sm">{{ $rek->nama_ustadz }}</div>
                                    @if($rek->no_wa_ustadz)
                                        <div class="text-gray-500 flex items-center gap-1 mt-0.5">
                                            <i class="ti ti-brand-whatsapp text-green-600"></i> {{ $rek->no_wa_ustadz }}
                                        </div>
                                    @endif
                                </div>

                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                                    <span class="text-gray-400 block mb-0.5">Bank & Nomor Rekening:</span>
                                    <div class="font-black text-gray-900 text-sm tracking-wide">{{ $rek->bank_name }}</div>
                                    <div class="font-mono text-base font-bold text-teal-700 tracking-wider mt-0.5">{{ $rek->nomor_rekening }}</div>
                                    <div class="text-gray-600 mt-1">a.n. <span class="font-semibold">{{ $rek->atas_nama }}</span></div>
                                </div>

                                @if($rek->catatan)
                                    <div>
                                        <span class="text-gray-400 block mb-0.5">Catatan Transfer:</span>
                                        <div class="text-gray-600 italic bg-amber-50/60 p-2.5 rounded-lg border border-amber-100 text-[11px]">
                                            {{ $rek->catatan }}
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="py-8 text-center text-gray-400">
                                    <i class="ti ti-building-bank text-3xl mb-2 block text-gray-300"></i>
                                    Belum ada data rekening ustadz untuk {{ $kelasLabel }}.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Card Footer Button -->
                    <div class="p-4 border-t border-gray-100 bg-gray-50">
                        <button type="button" onclick="openEditModal('{{ $kelasKey }}', '{{ $kelasLabel }}', {{ json_encode($rek) }})"
                                class="w-full py-2 bg-white border border-gray-300 hover:border-teal-500 hover:text-teal-600 font-semibold text-xs text-gray-700 rounded-lg transition shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="ti ti-pencil text-sm"></i> {{ $rek ? 'Ubah Rekening Ustadz' : 'Atur Rekening Ustadz' }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>

<!-- Modal Atur Rekening Ustadz -->
<div id="rekeningModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden animate-in fade-in">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-building-bank text-teal-600"></i>
                <span>Atur Rekening Ustadz <span id="modalKelasLabel"></span></span>
            </h3>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>

        <form action="{{ route('admin.rekening_kelas.store') }}" method="POST">
            @csrf
            <input type="hidden" name="kelas" id="modalKelasInput">

            <div class="p-5 space-y-3.5 text-xs">
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Nama Ustadz Pembimbing <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_ustadz" id="modalNamaUstadz" required
                           placeholder="Contoh: Ustadz Ahmad Fauzi, S.Pd.I"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Nama Bank <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_name" id="modalBankName" required
                               placeholder="Contoh: BSI, BCA, BRI"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Nomor Rekening <span class="text-red-500">*</span></label>
                        <input type="text" name="nomor_rekening" id="modalNomorRekening" required
                               placeholder="Contoh: 7123456789"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono font-bold focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Atas Nama Rekening <span class="text-red-500">*</span></label>
                        <input type="text" name="atas_nama" id="modalAtasNama" required
                               placeholder="Contoh: Ahmad Fauzi"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">No. WhatsApp Ustadz</label>
                        <input type="text" name="no_wa_ustadz" id="modalNoWaUstadz"
                               placeholder="Contoh: 081234567890"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Hubungkan ke Akun Admin Ustadz (Opsional)</label>
                    <select name="admin_id" id="modalAdminId" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                        <option value="">-- Pilih Akun Admin Ustadz --</option>
                        @foreach($adminUstadzList as $adm)
                            <option value="{{ $adm->id }}">{{ $adm->name }} ({{ $adm->username }})</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-500 mt-1">Ustadz yang dipilih akan memiliki hak verifikasi deposit uang saku kelas ini.</p>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Catatan Tambahan untuk Wali</label>
                    <textarea name="catatan" id="modalCatatan" rows="2"
                              placeholder="Misal: Mohon tambahkan kode unik 3 digit terakhir NIS santri saat transfer..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" id="modalIsActive" value="1" checked
                           class="w-4 h-4 text-teal-600 rounded border-gray-300 focus:ring-teal-500">
                    <label for="modalIsActive" class="font-semibold text-gray-700">Aktifkan rekening ini di portal wali santri</label>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-700 text-white font-bold rounded-lg cursor-pointer" style="background-color: #374151 !important; color: #ffffff !important;">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition shadow-sm cursor-pointer" style="background-color: #16a34a !important; color: #ffffff !important;">
                        Simpan Rekening
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(kelas, label, data) {
        document.getElementById('modalKelasInput').value = kelas;
        document.getElementById('modalKelasLabel').innerText = label;

        if (data) {
            document.getElementById('modalNamaUstadz').value = data.nama_ustadz || '';
            document.getElementById('modalBankName').value = data.bank_name || '';
            document.getElementById('modalNomorRekening').value = data.nomor_rekening || '';
            document.getElementById('modalAtasNama').value = data.atas_nama || '';
            document.getElementById('modalNoWaUstadz').value = data.no_wa_ustadz || '';
            document.getElementById('modalAdminId').value = data.admin_id || '';
            document.getElementById('modalCatatan').value = data.catatan || '';
            document.getElementById('modalIsActive').checked = data.is_active ? true : false;
        } else {
            document.getElementById('modalNamaUstadz').value = '';
            document.getElementById('modalBankName').value = 'BSI';
            document.getElementById('modalNomorRekening').value = '';
            document.getElementById('modalAtasNama').value = '';
            document.getElementById('modalNoWaUstadz').value = '';
            document.getElementById('modalAdminId').value = '';
            document.getElementById('modalCatatan').value = '';
            document.getElementById('modalIsActive').checked = true;
        }

        document.getElementById('rekeningModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('rekeningModal').classList.add('hidden');
    }
</script>
@endsection
