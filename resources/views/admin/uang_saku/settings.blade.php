@extends('layouts.admin.app')

@section('title', 'Pengaturan Notifikasi WA Uang Saku')

@section('content')
<div class="bg-gray-100 min-h-screen">
    <div class="w-full py-6 px-4 lg:px-8">

        <div class="mb-6">
            <a href="{{ route('admin.uang_saku.index') }}" class="text-teal-600 hover:text-teal-800 font-semibold mb-3 inline-flex items-center text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Uang Saku
            </a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">Pengaturan Notifikasi WhatsApp</h1>
            <p class="text-gray-500 text-sm mt-1">Konfigurasi notifikasi otomatis ke wali santri saat saldo uang saku mendekati batas minimal.</p>
        </div>

        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-4 rounded-lg mb-6 shadow-sm flex items-center gap-2 font-semibold">
            <i class="fas fa-check-circle text-green-600 text-lg"></i>
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-4 rounded-lg mb-6 shadow-sm">
            <div class="font-bold flex items-center gap-2 mb-1"><i class="fas fa-exclamation-circle text-red-600"></i> Terjadi Kesalahan:</div>
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div class="max-w-3xl mx-auto space-y-6">

            <!-- Info Card -->
            <div class="bg-teal-50 border border-teal-200 rounded-2xl p-5 flex gap-4">
                <div class="w-10 h-10 bg-teal-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fab fa-whatsapp text-teal-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-teal-800 text-sm mb-1">Cara Kerja Notifikasi</h3>
                    <ul class="text-xs text-teal-700 space-y-1 list-disc list-inside">
                        <li>Sistem otomatis mengecek saldo setelah setiap transaksi pengeluaran</li>
                        <li>Jika saldo ≤ batas minimal, notifikasi WA dikirim ke nomor Ayah/Wali santri</li>
                        <li>Notifikasi hanya dikirim <strong>1 kali</strong> selama saldo masih di bawah batas</li>
                        <li>Setelah top up dan saldo kembali normal, status reset otomatis</li>
                        <li>Pengiriman menggunakan <strong>Background Queue</strong> agar transaksi tetap cepat</li>
                    </ul>
                </div>
            </div>

            <!-- Settings Form -->
            <div class="bg-white rounded-2xl shadow p-6 border border-gray-200">
                <h2 class="text-base font-bold text-gray-800 mb-5 border-b pb-3 flex items-center gap-2">
                    <i class="fas fa-cog text-teal-600"></i> Konfigurasi Notifikasi
                </h2>

                <form action="{{ route('admin.uang_saku.updateSettings') }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Status Aktif -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200">
                        <div>
                            <p class="font-bold text-gray-800 text-sm">Status Notifikasi</p>
                            <p class="text-xs text-gray-500 mt-0.5">Aktifkan atau nonaktifkan pengiriman notifikasi WA</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span id="toggleLabel" class="text-sm font-semibold {{ $settings->is_active ? 'text-green-600' : 'text-gray-400' }}">
                                {{ $settings->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            <button type="button" id="toggleBtn" onclick="toggleStatus()"
                                class="relative inline-flex h-7 w-14 items-center rounded-full transition-colors duration-300 border-0 cursor-pointer
                                {{ $settings->is_active ? 'bg-teal-500' : 'bg-gray-300' }}">
                                <span id="toggleDot" class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-300
                                    {{ $settings->is_active ? 'translate-x-8' : 'translate-x-1' }}"></span>
                            </button>
                            <input type="hidden" name="is_active" id="is_active_input" value="{{ $settings->is_active ? '1' : '0' }}">
                        </div>
                    </div>

                    <!-- Batas Minimal Saldo -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">
                            Batas Minimal Saldo (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div>
                            <input type="number" name="batas_minimal_saldo" required min="0" step="1000"
                                value="{{ old('batas_minimal_saldo', $settings->batas_minimal_saldo) }}"
                                placeholder="50000"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Notifikasi WA akan dikirim saat saldo santri ≤ nominal ini. Default: Rp 50.000</p>
                    </div>

                    <!-- Template Pesan -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5">
                            Template Pesan WhatsApp <span class="text-red-500">*</span>
                        </label>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3 text-xs text-blue-700">
                            <strong>Variabel tersedia:</strong>
                            <span class="font-mono bg-blue-100 px-1.5 py-0.5 rounded ml-1">[NAMA_SANTRI]</span>
                            <span class="font-mono bg-blue-100 px-1.5 py-0.5 rounded ml-1">[KELAS]</span>
                            <span class="font-mono bg-blue-100 px-1.5 py-0.5 rounded ml-1">[SALDO]</span>
                            <span class="font-mono bg-blue-100 px-1.5 py-0.5 rounded ml-1">[BATAS_MINIMAL]</span>
                        </div>
                        <textarea name="template_pesan" required rows="10"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 font-mono">{{ old('template_pesan', $settings->template_pesan) }}</textarea>
                    </div>

                    <div class="flex gap-3 pt-2 border-t border-gray-100">
                        <button type="submit" style="background-color: #0d9488; color: #ffffff;"
                            class="px-6 py-2.5 rounded-lg text-sm font-bold shadow hover:opacity-90 transition flex items-center gap-2 border-0 cursor-pointer">
                            <i class="fas fa-save"></i> Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
function toggleStatus() {
    const input = document.getElementById('is_active_input');
    const btn = document.getElementById('toggleBtn');
    const dot = document.getElementById('toggleDot');
    const label = document.getElementById('toggleLabel');

    const isActive = input.value === '1';
    if (isActive) {
        input.value = '0';
        btn.classList.remove('bg-teal-500');
        btn.classList.add('bg-gray-300');
        dot.classList.remove('translate-x-8');
        dot.classList.add('translate-x-1');
        label.textContent = 'Nonaktif';
        label.classList.remove('text-green-600');
        label.classList.add('text-gray-400');
    } else {
        input.value = '1';
        btn.classList.remove('bg-gray-300');
        btn.classList.add('bg-teal-500');
        dot.classList.remove('translate-x-1');
        dot.classList.add('translate-x-8');
        label.textContent = 'Aktif';
        label.classList.remove('text-gray-400');
        label.classList.add('text-green-600');
    }
}
</script>
@endsection
