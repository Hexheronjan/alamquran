@extends('layouts.admin.app')

@section('title', 'Scanner QR Code')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <div class="flex items-center gap-2 text-cyan-600 font-semibold text-sm mb-1">
                <i class="ti ti-door-exit text-lg"></i> Perizinan & Kehadiran Santri
            </div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="ti ti-qrcode text-cyan-600"></i> Scanner QR Code
            </h1>
            <p class="text-gray-500 text-sm mt-1">Scan QR Code Kartu Wali Santri untuk Presensi Ahad Pagi dan Perizinan Keluar/Kembali Santri.</p>
        </div>
        <div>
            <a href="{{ route('admin.perizinan.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-sm transition flex items-center gap-2">
                <i class="ti ti-arrow-left"></i> Kembali ke Dashboard Perizinan
            </a>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('scan_success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3">
            <i class="ti ti-circle-check text-2xl text-emerald-600"></i>
            <span class="font-medium text-sm">{{ session('scan_success') }}</span>
        </div>
    @endif
    @if(session('scan_error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center gap-3">
            <i class="ti ti-alert-circle text-2xl text-rose-600"></i>
            <span class="font-medium text-sm">{{ session('scan_error') }}</span>
        </div>
    @endif

    {{-- Main 2-Column Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Left Panel: Scanner & Manual Form --}}
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                
                {{-- Mode/Tab Selector --}}
                <div class="p-4 bg-gray-50 border-b border-gray-100">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Pilih Mode Presensi / Perizinan:</label>
                    <div class="grid grid-cols-3 gap-2" id="modeSelector">
                        <button type="button" data-mode="presensi_ahad" class="btn-mode active flex items-center justify-center gap-2 py-2.5 px-3 rounded-lg text-xs font-bold transition bg-emerald-600 text-white shadow-sm">
                            <i class="ti ti-calendar-check text-base"></i> Ahad Pagi
                        </button>
                        <button type="button" data-mode="izin_keluar" class="btn-mode flex items-center justify-center gap-2 py-2.5 px-3 rounded-lg text-xs font-bold transition bg-white text-gray-600 hover:bg-gray-100 border border-gray-200">
                            <i class="ti ti-door-exit text-base text-rose-500"></i> Izin Keluar
                        </button>
                        <button type="button" data-mode="izin_kembali" class="btn-mode flex items-center justify-center gap-2 py-2.5 px-3 rounded-lg text-xs font-bold transition bg-white text-gray-600 hover:bg-gray-100 border border-gray-200">
                            <i class="ti ti-door-enter text-base text-blue-500"></i> Izin Kembali
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    
                    {{-- Status Indicator Badge --}}
                    <div class="text-center">
                        <span id="activeModeBadge" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-100 text-emerald-800 text-sm font-bold shadow-sm">
                            <i class="ti ti-calendar-check"></i> <span id="activeModeText">Mode: Presensi Ahad Pagi</span>
                        </span>
                    </div>

                    {{-- Camera View Box --}}
                    <div class="relative bg-gray-950 rounded-2xl overflow-hidden aspect-video flex items-center justify-center border border-gray-800 shadow-inner">
                        <video id="qrVideo" class="w-full h-full object-cover" autoplay playsinline muted></video>
                        <canvas id="qrCanvas" class="hidden"></canvas>

                        {{-- Scanner Overlay Frame --}}
                        <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                            <div class="w-56 h-56 border-2 border-white/40 rounded-2xl relative">
                                <div class="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-emerald-400 rounded-tl-lg"></div>
                                <div class="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-emerald-400 rounded-tr-lg"></div>
                                <div class="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-emerald-400 rounded-bl-lg"></div>
                                <div class="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-emerald-400 rounded-br-lg"></div>
                                {{-- Scan Animation Line --}}
                                <div class="w-full h-1 bg-gradient-to-r from-transparent via-emerald-400 to-transparent absolute top-0 animate-bounce shadow-lg"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Camera Toggle Buttons --}}
                    <div class="flex items-center justify-center gap-3">
                        <button type="button" id="btnStartCamera" class="px-5 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white rounded-xl font-bold text-sm transition shadow-sm flex items-center gap-2">
                            <i class="ti ti-camera text-lg"></i> Aktifkan Kamera Scanner
                        </button>
                        <button type="button" id="btnStopCamera" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-sm transition shadow-sm flex items-center gap-2 hidden">
                            <i class="ti ti-camera-off text-lg"></i> Matikan Kamera
                        </button>
                    </div>

                    <div id="scanStatus" class="text-center text-xs text-gray-500 font-medium">
                        <i class="ti ti-info-circle"></i> Klik "Aktifkan Kamera" untuk memindai QR Code secara langsung.
                    </div>

                    <hr class="border-gray-100">

                    {{-- Manual Input Form --}}
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-200/80 space-y-4">
                        <h4 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                            <i class="ti ti-keyboard text-cyan-600"></i> Input Token Manual (Alternatif Tanpa Kamera)
                        </h4>
                        
                        <form id="manualScanForm" action="{{ route('admin.perizinan.scan') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="aksi" id="manualAksiInput" value="presensi_ahad">

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Token QR Code / Wali</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                        <i class="ti ti-qrcode text-lg"></i>
                                    </span>
                                    <input type="text" name="qr_token" required placeholder="Contoh: WALI-9F81E73A" class="w-full pl-10 pr-4 py-2.5 border rounded-lg text-sm font-mono uppercase focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Catatan (Opsional)</label>
                                <textarea name="catatan" rows="2" placeholder="Catatan perizinan atau penjemputan..." class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:outline-none"></textarea>
                            </div>

                            <button type="submit" class="w-full py-2.5 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-sm font-bold transition flex items-center justify-center gap-2">
                                <i class="ti ti-check"></i> Proses Token QR Code Manual
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        {{-- Right Panel: Results & Information --}}
        <div class="lg:col-span-5 space-y-6">

            {{-- Live Result Card --}}
            <div id="liveResultCard" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hidden">
                <div id="liveResultContent"></div>
            </div>

            {{-- Flash Session Result Card --}}
            @if(session('scan_result'))
                @php $res = session('scan_result'); @endphp
                <div class="bg-white rounded-2xl shadow-sm border {{ ($res['status'] ?? '') === 'success' ? 'border-emerald-200 bg-emerald-50/40' : 'border-rose-200 bg-rose-50/40' }} p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full {{ ($res['status'] ?? '') === 'success' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center text-2xl font-bold">
                            <i class="ti ti-{{ ($res['status'] ?? '') === 'success' ? 'circle-check' : 'alert-circle' }}"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-base {{ ($res['status'] ?? '') === 'success' ? 'text-emerald-900' : 'text-rose-900' }}">
                                {{ ($res['status'] ?? '') === 'success' ? 'Scan Berhasil' : 'Scan Gagal' }}
                            </h3>
                            <p class="text-xs text-gray-500">{{ $res['pesan'] ?? '' }}</p>
                        </div>
                    </div>

                    @if(($res['status'] ?? '') === 'success')
                        <div class="bg-white rounded-xl p-4 border border-emerald-100 space-y-2 text-sm">
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-gray-500">Nama Wali:</span>
                                <span class="font-bold text-gray-800">{{ $res['nama_wali'] ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-gray-500">Santri:</span>
                                <span class="font-bold text-gray-800">{{ $res['nama_santri'] ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-gray-500">Kelas:</span>
                                <span class="font-bold text-cyan-600">Kelas {{ $res['kelas'] ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Aksi:</span>
                                <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800">{{ $res['aksi_label'] ?? '-' }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Guide Information Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2 border-b pb-3">
                    <i class="ti ti-help-circle text-cyan-600 text-lg"></i> Panduan Penggunaan QR Code
                </h3>
                
                <div class="space-y-3 text-xs text-gray-600">
                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100 space-y-1">
                        <div class="font-bold text-emerald-800 flex items-center gap-1.5">
                            <i class="ti ti-calendar-check"></i> Presensi Ahad Pagi
                        </div>
                        <p class="text-gray-600">Wali Santri menunjukkan QR Code dari aplikasi seluler mereka saat tiba di komplek pesantren pada acara kajian/kunjungan Ahad Pagi.</p>
                    </div>

                    <div class="p-3 bg-rose-50 rounded-xl border border-rose-100 space-y-1">
                        <div class="font-bold text-rose-800 flex items-center gap-1.5">
                            <i class="ti ti-door-exit"></i> Izin Keluar Santri
                        </div>
                        <p class="text-gray-600">Petunjuk izin penjemputan santri keluar komplek pondok. Sistem mencatat jam keluar & batas kepulangan santri.</p>
                    </div>

                    <div class="p-3 bg-blue-50 rounded-xl border border-blue-100 space-y-1">
                        <div class="font-bold text-blue-800 flex items-center gap-1.5">
                            <i class="ti ti-door-enter"></i> Izin Kembali Santri
                        </div>
                        <p class="text-gray-600">Pindai QR saat santri kembali ke pondok. Sistem memverifikasi ketepatan waktu dan menandai keterlambatan jika melebihi jadwal.</p>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let stream = null;
    let animFrameId = null;
    let scanning = false;
    let currentMode = 'presensi_ahad';
    let lastScannedToken = null;

    const video = document.getElementById('qrVideo');
    const canvas = document.getElementById('qrCanvas');
    const ctx = canvas.getContext('2d');
    const btnStart = document.getElementById('btnStartCamera');
    const btnStop = document.getElementById('btnStopCamera');
    const scanStatus = document.getElementById('scanStatus');
    const manualAksiInput = document.getElementById('manualAksiInput');
    const activeModeBadge = document.getElementById('activeModeBadge');
    const activeModeText = document.getElementById('activeModeText');
    const liveResultCard = document.getElementById('liveResultCard');
    const liveResultContent = document.getElementById('liveResultContent');

    const modesConfig = {
        presensi_ahad: { title: 'Mode: Presensi Ahad Pagi', bg: 'bg-emerald-100', text: 'text-emerald-800', btnBg: 'bg-emerald-600' },
        izin_keluar:   { title: 'Mode: Izin Keluar Santri', bg: 'bg-rose-100', text: 'text-rose-800', btnBg: 'bg-rose-600' },
        izin_kembali:  { title: 'Mode: Izin Kembali Santri', bg: 'bg-blue-100', text: 'text-blue-800', btnBg: 'bg-blue-600' }
    };

    // Mode selection handler
    document.querySelectorAll('#modeSelector .btn-mode').forEach(btn => {
        btn.addEventListener('click', function () {
            currentMode = this.dataset.mode;
            manualAksiInput.value = currentMode;

            document.querySelectorAll('#modeSelector .btn-mode').forEach(b => {
                b.className = 'btn-mode flex items-center justify-center gap-2 py-2.5 px-3 rounded-lg text-xs font-bold transition bg-white text-gray-600 hover:bg-gray-100 border border-gray-200';
            });

            const cfg = modesConfig[currentMode];
            this.className = `btn-mode active flex items-center justify-center gap-2 py-2.5 px-3 rounded-lg text-xs font-bold transition ${cfg.btnBg} text-white shadow-sm`;

            activeModeBadge.className = `inline-flex items-center gap-2 px-4 py-2 rounded-full ${cfg.bg} ${cfg.text} text-sm font-bold shadow-sm`;
            activeModeText.textContent = cfg.title;
        });
    });

    // Camera control
    btnStart.addEventListener('click', async function () {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' } }
            });
            video.srcObject = stream;
            video.play();
            scanning = true;
            btnStart.classList.add('hidden');
            btnStop.classList.remove('hidden');
            scanStatus.innerHTML = '<span class="text-emerald-600 font-bold"><i class="ti ti-radar"></i> Kamera aktif. Arahkan ke QR Code Wali.</span>';
            requestAnimationFrame(tick);
        } catch (e) {
            alert('Tidak dapat mengakses kamera: ' + e.message);
        }
    });

    btnStop.addEventListener('click', function () {
        scanning = false;
        if (stream) stream.getTracks().forEach(t => t.stop());
        video.srcObject = null;
        btnStart.classList.remove('hidden');
        btnStop.classList.add('hidden');
        scanStatus.innerHTML = '<i class="ti ti-info-circle"></i> Kamera dimatikan.';
    });

    function tick() {
        if (!scanning) return;
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.height = video.videoHeight;
            canvas.width = video.videoWidth;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height);

            if (code && code.data && code.data !== lastScannedToken) {
                lastScannedToken = code.data;
                processQrToken(code.data);
                setTimeout(() => { lastScannedToken = null; }, 3000);
            }
        }
        requestAnimationFrame(tick);
    }

    // Form manual submit interceptor
    const manualForm = document.getElementById('manualScanForm');
    if (manualForm) {
        manualForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const tokenInput = this.querySelector('input[name="qr_token"]');
            const catatanInput = this.querySelector('textarea[name="catatan"]');
            const btnSubmit = this.querySelector('button[type="submit"]');

            if (!tokenInput || !tokenInput.value.trim()) return;

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="ti ti-loader animate-spin"></i> Memproses...';

            processQrToken(tokenInput.value.trim(), catatanInput ? catatanInput.value : '', () => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="ti ti-check"></i> Proses Token QR Code Manual';
            });
        });
    }

    function processQrToken(token, catatan = '', callback = null) {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('qr_token', token);
        formData.append('aksi', currentMode);
        if (catatan) formData.append('catatan', catatan);

        fetch('{{ route("admin.perizinan.scan") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            liveResultCard.classList.remove('hidden');
            liveResultCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (data.success || data.status === 'success') {
                liveResultContent.innerHTML = `
                    <div class="text-center space-y-3">
                        <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-3xl font-bold">
                            <i class="ti ti-circle-check"></i>
                        </div>
                        <h3 class="font-bold text-lg text-emerald-900">Scan Berhasil!</h3>
                        <p class="text-xs text-gray-600">${data.message || data.pesan || ''}</p>
                        <div class="bg-gray-50 p-4 rounded-xl text-left space-y-2 text-xs border border-gray-200">
                            <div class="flex justify-between"><span class="text-gray-500">Wali:</span><span class="font-bold text-gray-800">${data.nama_wali || data.data?.wali || '-'}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Santri:</span><span class="font-bold text-gray-800">${data.nama_santri || data.data?.santri || '-'}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Kelas:</span><span class="font-bold text-cyan-600">${data.kelas || data.data?.kelas || '-'}</span></div>
                        </div>
                    </div>
                `;
            } else {
                liveResultContent.innerHTML = `
                    <div class="text-center space-y-3">
                        <div class="w-14 h-14 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto text-3xl font-bold">
                            <i class="ti ti-alert-circle"></i>
                        </div>
                        <h3 class="font-bold text-lg text-rose-900">Scan Gagal</h3>
                        <p class="text-xs text-rose-600">${data.message || data.pesan || 'Token QR tidak valid.'}</p>
                    </div>
                `;
            }
        })
        .catch(err => {
            liveResultCard.classList.remove('hidden');
            liveResultContent.innerHTML = `
                <div class="text-center space-y-3">
                    <div class="w-14 h-14 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto text-3xl font-bold">
                        <i class="ti ti-alert-circle"></i>
                    </div>
                    <h3 class="font-bold text-lg text-rose-900">Terjadi Kesalahan</h3>
                    <p class="text-xs text-rose-600">${err.message || 'Gagal menghubungi server.'}</p>
                </div>
            `;
        })
        .finally(() => {
            if (callback) callback();
        });
    }
});
</script>
@endpush
