@extends('layouts.admin.app')

@section('title', 'Broadcast WhatsApp')

@section('content')
<div class="bg-gray-100 min-h-screen py-6 px-4 lg:px-8 space-y-6">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-r-xl shadow-sm flex items-start gap-3">
            <i class="ti ti-circle-check text-2xl text-green-600 flex-shrink-0 mt-0.5"></i>
            <div>
                <h4 class="text-sm font-bold text-green-800">Berhasil!</h4>
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm flex items-start gap-3">
            <i class="ti ti-alert-circle text-2xl text-red-600 flex-shrink-0 mt-0.5"></i>
            <div>
                <h4 class="text-sm font-bold text-red-800">Perhatian</h4>
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                <i class="ti ti-message-circle text-blue-600"></i> Komunikasi & Layanan
            </div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-brand-whatsapp text-green-600"></i> Broadcast WhatsApp
            </h1>
            <p class="text-gray-600 text-xs mt-0.5">Kirim pesan pengumuman massal ke nomor WhatsApp resmi wali santri.</p>
        </div>
    </div>

    <!-- Warning Banner -->
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3 text-amber-900 shadow-xs">
        <i class="ti ti-alert-triangle text-2xl text-amber-600 flex-shrink-0 mt-0.5"></i>
        <div class="text-xs">
            <h4 class="font-bold text-amber-950 text-sm mb-0.5">Perhatian Penggunaan Fitur Broadcast</h4>
            <p class="text-amber-800 leading-relaxed">
                Gunakan fitur ini dengan bijak. Pastikan pesan berisi informasi penting, santun, dan relevan bagi penerima untuk menjaga kenyamanan komunikasi wali santri.
            </p>
        </div>
    </div>

    <!-- Metric Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 font-bold uppercase mb-1">
                <span>Total Wali Santri</span>
                <i class="ti ti-users text-xl text-blue-600"></i>
            </div>
            <div class="text-3xl font-black text-blue-600 font-mono">
                {{ $totalWali ?? 0 }}
            </div>
            <div class="text-[11px] text-gray-400 mt-1">Wali terdaftar di sistem</div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between text-xs text-gray-500 font-bold uppercase mb-1">
                <span>Total Santri Aktif</span>
                <i class="ti ti-school text-xl text-green-600"></i>
            </div>
            <div class="text-3xl font-black text-green-600 font-mono">
                {{ $totalSantri ?? 0 }}
            </div>
            <div class="text-[11px] text-gray-400 mt-1">Santri berstatus aktif</div>
        </div>
    </div>

    <!-- Main Grid Content -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left: Form Broadcast (8 Columns) -->
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                    <i class="ti ti-send text-xl text-blue-600"></i>
                    <h3 class="font-bold text-gray-900 text-base">Form Broadcast Pesan</h3>
                </div>

                <div class="p-6">
                    <form action="{{ route('admin.broadcast.send') }}" method="POST" id="broadcastForm" class="space-y-5">
                        @csrf

                        <!-- Target Penerima -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1" for="target">
                                Target Penerima <span class="text-red-500">*</span>
                            </label>
                            <select id="target" name="target" required
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="">-- Pilih Target Penerima --</option>
                                <option value="semua_wali" {{ old('target') == 'semua_wali' ? 'selected' : '' }}>
                                    Semua Wali Santri
                                </option>
                                <option value="per_kelas" {{ old('target') == 'per_kelas' ? 'selected' : '' }}>
                                    Per Kelas Specific
                                </option>
                                <option value="per_angkatan" {{ old('target') == 'per_angkatan' ? 'selected' : '' }}>Per Angkatan</option>
                                <option value="alumni" {{ old('target') == 'alumni' ? 'selected' : '' }}>Wali Alumni</option>
                            </select>
                            @error('target')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kelas Select (conditional) -->
                        <div id="kelasWrapper" style="display: none;">
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1" for="kelas">
                                Pilih Kelas <span class="text-red-500">*</span>
                            </label>
                            <select id="kelas" name="kelas"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="">-- Pilih Kelas --</option>
                                @if(isset($kelasOptions) && count($kelasOptions))
                                    @foreach($kelasOptions as $k)
                                        <option value="{{ $k }}" {{ old('kelas') == $k ? 'selected' : '' }}>
                                            Kelas {{ $k }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="1">Kelas 1</option>
                                    <option value="2">Kelas 2</option>
                                    <option value="3">Kelas 3</option>
                                @endif
                            </select>
                            @error('kelas')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="angkatanWrapper" style="display: none;">
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1" for="angkatan_id">Pilih Angkatan</label>
                            <select id="angkatan_id" name="angkatan_id" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-xs font-semibold">
                                <option value="">-- Pilih Angkatan --</option>
                                @foreach($angkatanOptions as $angkatan)<option value="{{ $angkatan->id }}">{{ $angkatan->nama_angkatan }} ({{ $angkatan->tahun_angkatan }})</option>@endforeach
                            </select>
                        </div>

                        <!-- Preview Penerima Button & Box -->
                        <div>
                            <button type="button" id="btnPreview"
                                    class="px-4 py-2 border border-blue-600 text-blue-600 hover:bg-blue-50 font-bold rounded-xl text-xs transition inline-flex items-center gap-1.5 cursor-pointer">
                                <i class="ti ti-eye"></i> Preview Penerima
                            </button>

                            <div id="previewResult" class="mt-3 hidden">
                                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-xs text-blue-900">
                                    <div class="font-bold text-sm mb-1 text-blue-950 flex items-center gap-1.5">
                                        <i class="ti ti-users text-blue-600"></i> <span id="previewCount">0 penerima ditemukan</span>
                                    </div>
                                    <div id="previewNames" class="text-gray-600 text-xs leading-relaxed"></div>
                                </div>
                            </div>
                            <div id="previewError" class="mt-2 text-red-500 font-semibold text-xs hidden"></div>
                        </div>

                        <!-- Pesan Textarea -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1" for="pesan">
                                Isi Pesan WhatsApp <span class="text-red-500">*</span>
                            </label>
                            <textarea id="pesan" name="pesan" rows="8" minlength="10" maxlength="2000" required
                                      placeholder="Tulis pesan broadcast di sini..."
                                      class="w-full border border-gray-300 rounded-xl px-4 py-3 text-xs leading-relaxed font-sans focus:ring-2 focus:ring-blue-500 outline-none">{{ old('pesan') }}</textarea>
                            @error('pesan')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <div class="flex justify-between items-center text-[11px] text-gray-400 mt-1">
                                <span>Minimal 10 karakter, maksimal 2000 karakter.</span>
                                <span class="font-mono font-bold text-gray-600"><span id="charCount">0</span>/2000</span>
                            </div>
                        </div>

                        <!-- Dynamic Variables Hint Box -->
                        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-xs">
                            <h4 class="font-bold text-blue-900 text-xs uppercase mb-2 flex items-center gap-1.5">
                                <i class="ti ti-variable text-blue-600"></i> Variabel Dinamis Pesan
                            </h4>
                            <p class="text-gray-600 mb-2.5 text-xs">
                                Klik tombol di bawah untuk menyisipkan variabel otomatis. Sistem akan menggantinya dengan nama penerima saat pesan terkirim:
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="insertVar px-3 py-1.5 bg-white border border-blue-300 text-blue-700 hover:bg-blue-100 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                                        data-var="{nama_wali}">
                                    <i class="ti ti-user"></i> {nama_wali}
                                </button>
                                <button type="button" class="insertVar px-3 py-1.5 bg-white border border-blue-300 text-blue-700 hover:bg-blue-100 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                                        data-var="{nama_santri}">
                                    <i class="ti ti-school"></i> {nama_santri}
                                </button>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                            <a href="{{ route('admin.broadcast.index') }}"
                               class="px-5 py-2.5 rounded-xl font-bold text-xs shadow-xs transition cursor-pointer"
                               style="background-color: #4b5563 !important; color: #ffffff !important;">
                                <i class="ti ti-refresh"></i> Reset
                            </a>
                            <button type="submit" id="btnSubmit"
                                    class="px-6 py-2.5 rounded-xl font-bold text-xs shadow-md transition cursor-pointer flex items-center gap-2"
                                    style="background-color: #16a34a !important; color: #ffffff !important;"
                                    onclick="return confirm('Apakah Anda yakin ingin mengirim pesan broadcast ini?')">
                                <i class="ti ti-brand-whatsapp text-base"></i> Kirim Broadcast WA
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Template Sidebar Cards (4 Columns) -->
        <div class="lg:col-span-4 space-y-6">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                        <i class="ti ti-template text-amber-500"></i> Template Pesan Cepat
                    </h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">Klik untuk mengisi teks otomatis</p>
                </div>

                <div class="p-4 space-y-3">
                    <!-- Template 1: Info SPP -->
                    <div class="template-card border border-gray-200 hover:border-blue-400 rounded-xl p-3.5 hover:shadow-sm transition cursor-pointer bg-gray-50/50 hover:bg-blue-50/30"
                         data-template="Assalamu'alaikum Wr. Wb.

Yth. Bapak/Ibu {nama_wali},

Kami ingin menginformasikan bahwa tagihan SPP putra/putri Anda, *{nama_santri}*, untuk bulan ini sudah tersedia.

Mohon dapat melakukan pembayaran secara penuh atau cicilan melalui Virtual Account portal wali santri.

Terima kasih atas perhatian dan kerjasamanya.

Wassalamu'alaikum Wr. Wb.
*Admin Pesantren Tahfizh Alam Qur'an*">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0 font-bold">
                                <i class="ti ti-cash"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-gray-800 text-xs">Info SPP Bulanan</h4>
                                <p class="text-[11px] text-gray-500 mt-0.5 line-clamp-2">Pengingat tagihan SPP bulanan santri</p>
                            </div>
                        </div>
                        <button type="button" class="use-template-btn mt-2.5 w-full py-1.5 px-3 bg-white border border-amber-300 text-amber-800 hover:bg-amber-100 rounded-lg text-xs font-bold transition text-center cursor-pointer">
                            Gunakan Template
                        </button>
                    </div>

                    <!-- Template 2: Pengumuman Umum -->
                    <div class="template-card border border-gray-200 hover:border-blue-400 rounded-xl p-3.5 hover:shadow-sm transition cursor-pointer bg-gray-50/50 hover:bg-blue-50/30"
                         data-template="Assalamu'alaikum Wr. Wb.

Yth. Bapak/Ibu {nama_wali},

Bersama ini kami sampaikan pengumuman penting dari Pesantren Tahfizh Alam Qur'an untuk ananda *{nama_santri}*:

[Tulis isi pengumuman di sini...]

Harap diperhatikan dan disebarluaskan kepada pihak yang berkepentingan.

Terima kasih atas perhatian dan kerjasamanya.

Wassalamu'alaikum Wr. Wb.
*Pengasuh Pesantren Tahfizh Alam Qur'an*">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center flex-shrink-0 font-bold">
                                <i class="ti ti-speakerphone"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-gray-800 text-xs">Pengumuman Resmi</h4>
                                <p class="text-[11px] text-gray-500 mt-0.5 line-clamp-2">Template pengumuman resmi pesantren</p>
                            </div>
                        </div>
                        <button type="button" class="use-template-btn mt-2.5 w-full py-1.5 px-3 bg-white border border-blue-300 text-blue-800 hover:bg-blue-100 rounded-lg text-xs font-bold transition text-center cursor-pointer">
                            Gunakan Template
                        </button>
                    </div>

                    <!-- Template 3: Undangan Kegiatan -->
                    <div class="template-card border border-gray-200 hover:border-blue-400 rounded-xl p-3.5 hover:shadow-sm transition cursor-pointer bg-gray-50/50 hover:bg-blue-50/30"
                         data-template="Assalamu'alaikum Wr. Wb.

Yth. Bapak/Ibu {nama_wali},

Dengan hormat, kami mengundang Bapak/Ibu untuk hadir dalam kegiatan yang akan diselenggarakan oleh Pesantren:

📅 Hari/Tanggal : [Isi Tanggal]
⏰ Waktu : [Isi Waktu]
📍 Tempat : Aula Pesantren Tahfizh Alam Qur'an
📋 Acara : [Isi Nama Kegiatan]

Kehadiran wali dari ananda *{nama_santri}* sangat kami harapkan.

Atas perhatian dan kehadirannya, kami ucapkan terima kasih.

Wassalamu'alaikum Wr. Wb.
*Panitia Kegiatan Pesantren Tahfizh Alam Qur'an*">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-green-100 text-green-700 flex items-center justify-center flex-shrink-0 font-bold">
                                <i class="ti ti-calendar-event"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-gray-800 text-xs">Undangan Acara</h4>
                                <p class="text-[11px] text-gray-500 mt-0.5 line-clamp-2">Template undangan pertemuan/kegiatan</p>
                            </div>
                        </div>
                        <button type="button" class="use-template-btn mt-2.5 w-full py-1.5 px-3 bg-white border border-green-300 text-green-800 hover:bg-green-100 rounded-lg text-xs font-bold transition text-center cursor-pointer">
                            Gunakan Template
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tips Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-xs text-blue-900 space-y-2">
                <h4 class="font-bold text-xs uppercase flex items-center gap-1.5 text-blue-950">
                    <i class="ti ti-bulb text-blue-600"></i> Tips Broadcast WhatsApp
                </h4>
                <ul class="space-y-1 text-gray-600 text-[11px] leading-relaxed">
                    <li class="flex items-start gap-1.5"><i class="ti ti-check text-green-600 shrink-0 mt-0.5"></i> Gunakan bahasa yang sopan dan jelas</li>
                    <li class="flex items-start gap-1.5"><i class="ti ti-check text-green-600 shrink-0 mt-0.5"></i> Cek preview penerima sebelum mengirim</li>
                    <li class="flex items-start gap-1.5"><i class="ti ti-check text-green-600 shrink-0 mt-0.5"></i> Variabel {nama_wali} & {nama_santri} diisi otomatis oleh sistem</li>
                </ul>
            </div>

        </div>

    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const targetSelect = document.getElementById('target');
    const kelasWrapper = document.getElementById('kelasWrapper');
    const kelasSelect  = document.getElementById('kelas');
    const angkatanWrapper = document.getElementById('angkatanWrapper');
    const angkatanSelect  = document.getElementById('angkatan_id');
    const pesanArea    = document.getElementById('pesan');
    const charCount    = document.getElementById('charCount');
    const btnPreview   = document.getElementById('btnPreview');

    // --- Toggle kelas select ---
    function toggleKelas() {
        if (targetSelect.value === 'per_kelas') {
            kelasWrapper.style.display = 'block';
            kelasSelect.setAttribute('required', 'required');
        } else {
            kelasWrapper.style.display = 'none';
            kelasSelect.removeAttribute('required');
        }

        if (targetSelect.value === 'per_angkatan') {
            angkatanWrapper.style.display = 'block';
            angkatanSelect.setAttribute('required', 'required');
        } else {
            angkatanWrapper.style.display = 'none';
            angkatanSelect.removeAttribute('required');
        }
    }
    targetSelect.addEventListener('change', toggleKelas);
    toggleKelas();

    // --- Character counter ---
    function updateCharCount() {
        charCount.textContent = pesanArea.value.length;
    }
    pesanArea.addEventListener('input', updateCharCount);
    updateCharCount();

    // --- Insert variable into textarea ---
    document.querySelectorAll('.insertVar').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const varText = this.dataset.var;
            const start   = pesanArea.selectionStart;
            const end     = pesanArea.selectionEnd;
            const val     = pesanArea.value;
            pesanArea.value = val.substring(0, start) + varText + val.substring(end);
            pesanArea.selectionStart = pesanArea.selectionEnd = start + varText.length;
            pesanArea.focus();
            updateCharCount();
        });
    });

    // --- Template quick-fill ---
    document.querySelectorAll('.use-template-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const card = this.closest('.template-card');
            const tmpl = card.dataset.template;
            pesanArea.value = tmpl;
            updateCharCount();
            pesanArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
            pesanArea.focus();
        });
    });

    document.querySelectorAll('.template-card').forEach(function (card) {
        card.addEventListener('click', function (e) {
            if (!e.target.closest('.use-template-btn')) {
                const tmpl = this.dataset.template;
                pesanArea.value = tmpl;
                updateCharCount();
                pesanArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                pesanArea.focus();
            }
        });
    });

    // --- Preview Penerima (AJAX) ---
    btnPreview.addEventListener('click', function () {
        const target = targetSelect.value;
        const kelas  = kelasSelect.value;
        const angkatanId = angkatanSelect.value;
        const previewResult = document.getElementById('previewResult');
        const previewError  = document.getElementById('previewError');
        const previewCount  = document.getElementById('previewCount');
        const previewNames  = document.getElementById('previewNames');

        previewResult.classList.add('hidden');
        previewError.classList.add('hidden');

        if (!target) {
            previewError.textContent = 'Pilih target penerima terlebih dahulu.';
            previewError.classList.remove('hidden');
            return;
        }
        if (target === 'per_kelas' && !kelas) {
            previewError.textContent = 'Pilih kelas terlebih dahulu.';
            previewError.classList.remove('hidden');
            return;
        }
        if (target === 'per_angkatan' && !angkatanId) {
            previewError.textContent = 'Pilih angkatan terlebih dahulu.';
            previewError.classList.remove('hidden');
            return;
        }

        btnPreview.disabled = true;
        btnPreview.innerHTML = '<i class="ti ti-loader animate-spin"></i> Memuat...';

        const params = new URLSearchParams({ target });
        if (kelas) params.append('kelas', kelas);
        if (angkatanId) params.append('angkatan_id', angkatanId);

        fetch(`{{ route('admin.broadcast.previewPenerima') }}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => {
            if (!r.ok) throw new Error('Gagal memuat data.');
            return r.json();
        })
        .then(data => {
            const count   = data.count ?? 0;
            const samples = data.samples ?? [];

            previewCount.textContent = `${count} penerima ditemukan`;

            if (samples.length > 0) {
                previewNames.innerHTML = '<span class="font-bold text-gray-700">Contoh penerima:</span> ' +
                    samples.map(n => `<span class="inline-block bg-white border border-gray-200 px-2 py-0.5 rounded text-[11px] font-semibold text-gray-700 me-1 mb-1">${n}</span>`).join('') +
                    (count > samples.length ? `<span class="text-gray-400 font-semibold text-[11px]">... dan ${count - samples.length} lainnya</span>` : '');
            } else {
                previewNames.innerHTML = '<span class="text-gray-400">Tidak ada penerima ditemukan.</span>';
            }

            previewResult.classList.remove('hidden');
        })
        .catch(err => {
            previewError.textContent = err.message || 'Terjadi kesalahan saat memuat preview.';
            previewError.classList.remove('hidden');
        })
        .finally(() => {
            btnPreview.disabled = false;
            btnPreview.innerHTML = '<i class="ti ti-eye"></i> Preview Penerima';
        });
    });

});
</script>
@endpush
@endsection
