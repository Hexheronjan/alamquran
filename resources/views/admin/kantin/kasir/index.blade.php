@extends('layouts.admin.app')

@section('title', 'Kasir POS Kantin & Aqumart')

@section('content')
<div class="bg-gray-100 min-h-screen py-6 px-4 lg:px-8">

    {{-- Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-cash-register text-emerald-600"></i> Kasir Belanja Santri
            </h1>
            <p class="text-gray-600 text-xs mt-0.5">
                Pembayaran instan dengan memotong saldo uang saku santri &amp; notifikasi WA otomatis ke wali.
            </p>
        </div>

        {{-- Store Switcher — hanya tampil jika admin punya akses lebih dari 1 toko --}}
        @if(count($allowedTokos) > 1)
        <div class="inline-flex rounded-xl bg-white p-1 border border-gray-200 shadow-xs text-xs font-bold">
            @if(in_array('aqumart', $allowedTokos))
            <a href="{{ route('admin.kantin.kasir.index', ['toko' => 'aqumart']) }}"
               class="px-3.5 py-2 rounded-lg transition {{ $currentToko === 'aqumart' ? 'bg-amber-500 text-white shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                <i class="ti ti-shopping-cart mr-1"></i> Aqumart
            </a>
            @endif
            @if(in_array('kantin_1', $allowedTokos))
            <a href="{{ route('admin.kantin.kasir.index', ['toko' => 'kantin_1']) }}"
               class="px-3.5 py-2 rounded-lg transition {{ $currentToko === 'kantin_1' ? 'bg-emerald-600 text-white shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                <i class="ti ti-tools-kitchen-2 mr-1"></i> Kantin 1
            </a>
            @endif
            @if(in_array('kantin_2', $allowedTokos))
            <a href="{{ route('admin.kantin.kasir.index', ['toko' => 'kantin_2']) }}"
               class="px-3.5 py-2 rounded-lg transition {{ $currentToko === 'kantin_2' ? 'bg-blue-600 text-white shadow-xs' : 'text-gray-600 hover:text-gray-900' }}">
                <i class="ti ti-cup mr-1"></i> Kantin 2
            </a>
            @endif
        </div>
        @else
        {{-- Kasir satu toko: tampilkan badge toko saja --}}
        @php
            $tokoColors = ['aqumart'=>'amber','kantin_1'=>'emerald','kantin_2'=>'blue'];
            $tokoColor  = $tokoColors[$currentToko] ?? 'gray';
            $tokoLabels = ['aqumart'=>'Minimarket Aqumart','kantin_1'=>'Kantin 1','kantin_2'=>'Kantin 2'];
            $tokoLabel  = $tokoLabels[$currentToko] ?? $currentToko;
        @endphp
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-{{ $tokoColor }}-100 text-{{ $tokoColor }}-800 rounded-xl border border-{{ $tokoColor }}-200 text-xs font-bold">
            <i class="ti ti-building-store text-base"></i>
            {{ $tokoLabel }}
        </div>
        @endif
    </div>

    {{-- Grid Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- KOLOM KIRI --}}
        <div class="lg:col-span-7 space-y-5">

            {{-- STEP 1: Pilih Santri --}}
            <div id="boxStep1" class="bg-white rounded-2xl shadow-sm border-2 border-emerald-400 p-5 transition-all duration-300">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-6 h-6 rounded-full bg-emerald-600 text-white text-xs font-black flex items-center justify-center flex-shrink-0">1</span>
                        Pilih Santri Pembeli
                    </span>
                    <span class="text-[11px] text-gray-400">Ketik min. 2 huruf</span>
                </div>

                <div class="relative" id="wrapSearchSantri">
                    <input type="text" id="inputSearchSantri" autocomplete="off"
                           placeholder="Ketik nama santri atau NIS (contoh: Muhammad / AQ-)..."
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 font-medium">
                    <i class="ti ti-search absolute left-3.5 top-3 text-gray-400 text-lg"></i>
                    <div id="santriSearchResults" class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl shadow-xl border border-gray-200 hidden z-50 max-h-64 overflow-y-auto"></div>
                </div>

                {{-- Card santri terpilih --}}
                <div id="selectedSantriCard" class="hidden mt-4 p-4 rounded-xl border border-emerald-200 bg-emerald-50/60 flex flex-col sm:flex-row items-center gap-4">
                    <div class="w-16 h-16 rounded-xl bg-white border-2 border-emerald-400 overflow-hidden flex-shrink-0 shadow-xs">
                        <img id="santriFoto" src="" alt="Foto" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <div class="flex items-center justify-center sm:justify-start gap-2 flex-wrap">
                            <h3 id="santriNama" class="font-bold text-gray-900 text-base"></h3>
                            <span id="santriKelas" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-200 text-emerald-800"></span>
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5 font-mono">NIS: <span id="santriNis">-</span> | NISN: <span id="santriNisn">-</span></div>
                        <div class="text-xs text-gray-500 mt-1">Wali: <strong id="santriWali" class="text-gray-700"></strong> (<span id="santriWa"></span>)</div>
                    </div>
                    <div class="flex flex-col items-center gap-2">
                        <div class="bg-white px-4 py-2.5 rounded-xl border border-emerald-100 shadow-2xs text-center">
                            <span class="text-[11px] text-gray-400 uppercase font-bold block">Saldo Uang Saku</span>
                            <span id="santriSaldoText" class="text-lg font-black text-emerald-700 font-mono">Rp 0</span>
                        </div>
                        <button type="button" onclick="gantiSantri()"
                                class="text-[11px] text-red-500 hover:text-red-700 font-bold flex items-center gap-1 cursor-pointer">
                            <i class="ti ti-x text-sm"></i> Ganti Santri
                        </button>
                    </div>
                </div>
            </div>

            {{-- STEP 2: Pilih Produk --}}
            <div class="relative">
                {{-- Overlay: muncul jika belum pilih santri --}}
                <div id="overlayPilihSantri"
                     class="absolute inset-0 z-10 bg-white/90 backdrop-blur-[1px] rounded-2xl border-2 border-dashed border-gray-300 flex flex-col items-center justify-center gap-3">
                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center text-3xl text-gray-400">
                        <i class="ti ti-user-search"></i>
                    </div>
                    <div class="text-center px-4">
                        <p class="text-sm font-bold text-gray-600">Pilih santri terlebih dahulu</p>
                        <p class="text-xs text-gray-400 mt-0.5">Cari dan pilih nama santri di atas sebelum memilih produk.</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="w-6 h-6 rounded-full bg-gray-300 text-white text-xs font-black flex items-center justify-center flex-shrink-0" id="badgeStep2">2</span>
                            Pilih Produk &amp; Jumlah
                        </span>
                        <input type="text" id="filterProdukInput" placeholder="Cari produk..."
                               class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs w-40 focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div id="gridProduk" class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-[480px] overflow-y-auto pr-1">
                        @forelse($produks as $prod)
                            <div class="item-produk bg-gray-50 hover:bg-emerald-50 border border-gray-200 hover:border-emerald-300 rounded-xl p-3 flex flex-col justify-between cursor-pointer transition select-none group"
                                 onclick="openQtyModal({{ $prod->id }}, '{{ addslashes($prod->nama_produk) }}', {{ (int)$prod->harga_jual }}, {{ $prod->stok }})"
                                 data-name="{{ strtolower($prod->nama_produk) }}">
                                <div>
                                    <h4 class="font-bold text-gray-900 text-xs group-hover:text-emerald-700 leading-snug line-clamp-2">
                                        {{ $prod->nama_produk }}
                                    </h4>
                                    <span class="text-[11px] text-gray-400 mt-0.5 block">Stok: {{ $prod->stok }}</span>
                                </div>
                                <div class="mt-2.5 pt-2 border-t border-gray-100 flex items-center justify-between">
                                    <span class="font-bold text-emerald-700 font-mono text-xs">
                                        Rp {{ number_format($prod->harga_jual, 0, ',', '.') }}
                                    </span>
                                    <span class="w-6 h-6 rounded-full bg-emerald-600 group-hover:bg-emerald-700 text-white flex items-center justify-center text-sm shadow-xs">
                                        <i class="ti ti-plus"></i>
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-3 py-14 text-center text-gray-400 text-xs">
                                <i class="ti ti-package-off text-3xl mb-2 block"></i>
                                Tidak ada produk aktif dengan stok tersedia di toko ini.
                                <br>
                                <a href="{{ route('admin.kantin.produk.create', ['toko' => $currentToko]) }}"
                                   class="mt-3 inline-flex items-center gap-1 text-emerald-600 font-bold hover:underline">
                                    <i class="ti ti-plus"></i> Tambah Produk
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN: Keranjang & Checkout --}}
        <div class="lg:col-span-5">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 flex flex-col justify-between min-h-[580px] sticky top-6">
                <div>
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3">
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="ti ti-shopping-cart text-emerald-600 text-base"></i> Keranjang Belanja
                        </span>
                        <button type="button" onclick="clearCart()" class="text-xs text-red-500 hover:text-red-700 hover:underline cursor-pointer">Kosongkan</button>
                    </div>

                    <div id="cartItemsList" class="space-y-2 max-h-72 overflow-y-auto pr-1">
                        <div id="cartEmptyState" class="py-16 text-center text-gray-400 text-xs">
                            <i class="ti ti-shopping-cart-x text-3xl mb-2 block"></i>
                            Keranjang masih kosong.<br>
                            <span class="text-[11px]">Pilih santri → klik produk → konfirmasi jumlah.</span>
                        </div>
                    </div>
                </div>

                {{-- Ringkasan --}}
                <div class="border-t border-gray-100 pt-4 space-y-3">
                    <div class="space-y-1.5 text-xs">
                        <div class="flex items-center justify-between text-gray-500">
                            <span>Subtotal Belanja:</span>
                            <span id="labelTotalHarga" class="font-mono font-bold text-gray-900">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-500">
                            <span>Saldo Santri Saat Ini:</span>
                            <span id="labelSaldoAwal" class="font-mono font-bold text-gray-900">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-100 pt-1.5 font-bold">
                            <span class="text-gray-800">Proyeksi Sisa Saldo:</span>
                            <span id="labelSisaSaldo" class="font-mono text-emerald-700">Rp 0</span>
                        </div>
                    </div>

                    <div id="alertSaldoKurang" class="hidden p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 font-semibold flex items-center gap-2">
                        <i class="ti ti-alert-circle text-base flex-shrink-0"></i>
                        <span>Saldo santri tidak mencukupi untuk pembayaran ini!</span>
                    </div>

                    <button type="button" id="btnCheckout" onclick="processCheckout()" disabled
                            class="w-full py-3.5 bg-gray-300 text-gray-500 font-bold text-sm rounded-xl transition flex items-center justify-center gap-2 cursor-not-allowed">
                        <i class="ti ti-credit-card text-lg"></i>
                        <span>PROSES BAYAR (POTONG SALDO)</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- =============================== --}}
{{-- MODAL: Input Jumlah Beli        --}}
{{-- =============================== --}}
<div id="qtyModal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden">
        <div class="bg-emerald-600 px-5 py-4 flex items-start justify-between">
            <div>
                <h3 id="qtyModalNama" class="font-bold text-white text-sm leading-tight"></h3>
                <p class="text-emerald-100 text-[11px] mt-0.5">
                    Stok tersedia: <span id="qtyModalStok" class="font-bold text-white"></span>
                </p>
            </div>
            <button type="button" onclick="closeQtyModal()"
                    class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center cursor-pointer flex-shrink-0 ml-3">
                <i class="ti ti-x text-sm"></i>
            </button>
        </div>
        <div class="p-5 space-y-4">
            <div class="bg-emerald-50 rounded-xl p-3 text-center border border-emerald-100">
                <span class="text-[11px] text-gray-500 uppercase font-bold block">Harga Satuan</span>
                <span id="qtyModalHarga" class="text-2xl font-black text-emerald-700 font-mono"></span>
            </div>

            <div>
                <label class="text-xs font-bold text-gray-600 uppercase block mb-2 text-center">Jumlah yang Dibeli</label>
                <div class="flex items-center justify-center gap-4">
                    <button type="button" onclick="changeModalQty(-1)"
                            class="w-12 h-12 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 font-black text-xl flex items-center justify-center cursor-pointer transition border border-red-200">
                        <i class="ti ti-minus"></i>
                    </button>
                    <input type="number" id="qtyModalInput" value="1" min="1"
                           class="w-24 text-center text-3xl font-black font-mono border-2 border-emerald-400 rounded-xl py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <button type="button" onclick="changeModalQty(1)"
                            class="w-12 h-12 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-600 font-black text-xl flex items-center justify-center cursor-pointer transition border border-emerald-200">
                        <i class="ti ti-plus"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between bg-gray-50 rounded-xl p-3 border border-gray-100">
                <span class="text-xs text-gray-600 font-bold">Subtotal:</span>
                <span id="qtyModalSubtotal" class="text-lg font-black text-emerald-700 font-mono"></span>
            </div>

            <button type="button" onclick="confirmAddToCart()"
                    class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm transition flex items-center justify-center gap-2 cursor-pointer shadow-md">
                <i class="ti ti-shopping-cart-plus text-lg"></i> Masukkan ke Keranjang
            </button>
        </div>
    </div>
</div>

{{-- MODAL: Struk Transaksi --}}
<div id="receiptModal" class="fixed inset-0 z-50 hidden bg-black/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden text-center p-6 space-y-4">
        <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto text-3xl">
            <i class="ti ti-check"></i>
        </div>
        <div>
            <h3 class="text-lg font-bold text-gray-900">Transaksi Berhasil!</h3>
            <p class="text-xs text-gray-500 mt-0.5">Saldo uang saku santri telah dipotong secara otomatis.</p>
        </div>
        <div class="bg-gray-50 p-4 rounded-xl text-left text-xs space-y-1.5 border border-gray-100">
            <div class="flex justify-between text-gray-500">
                <span>No. Transaksi:</span>
                <span id="recNoTrans" class="font-mono font-bold text-gray-900"></span>
            </div>
            <div class="flex justify-between text-gray-500">
                <span>Santri:</span>
                <span id="recNamaSantri" class="font-bold text-gray-900"></span>
            </div>
            <div class="flex justify-between text-gray-500">
                <span>Sisa Saldo:</span>
                <span id="recSisaSaldo" class="font-mono font-bold text-emerald-700"></span>
            </div>
            <div class="pt-2 border-t border-gray-100 flex items-center gap-1 text-[11px] text-emerald-700">
                <i class="ti ti-brand-whatsapp text-sm text-green-600"></i>
                Notifikasi WhatsApp otomatis dikirim ke wali santri.
            </div>
        </div>
        <button type="button" onclick="closeReceiptModal()"
                class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm transition cursor-pointer">
            Transaksi Baru
        </button>
    </div>
</div>

<script>
let selectedSantri = null;
let cart = [];
const currentToko = "{{ $currentToko }}";

// =======================
// STEP 1 — Santri Search
// =======================
const inputSearch  = document.getElementById('inputSearchSantri');
const searchResult = document.getElementById('santriSearchResults');
let debounce = null;

inputSearch.addEventListener('input', function () {
    clearTimeout(debounce);
    const q = this.value.trim();
    if (q.length < 2) { searchResult.innerHTML = ''; searchResult.classList.add('hidden'); return; }
    debounce = setTimeout(() => {
        fetch("{{ route('admin.kantin.kasir.search_santri') }}?q=" + encodeURIComponent(q))
            .then(r => r.json())
            .then(data => {
                if (!data.length) {
                    searchResult.innerHTML = '<div class="p-3 text-xs text-gray-400 text-center">Santri tidak ditemukan.</div>';
                } else {
                    searchResult.innerHTML = data.map(s => `
                        <div class="p-2.5 hover:bg-emerald-50 border-b border-gray-100 last:border-b-0 cursor-pointer flex items-center gap-3 transition"
                             onclick='selectSantri(${JSON.stringify(s)})'>
                            <img src="${s.foto_url}" class="w-9 h-9 rounded-lg object-cover bg-gray-100 flex-shrink-0 border">
                            <div class="flex-1 text-left min-w-0">
                                <div class="font-bold text-xs text-gray-900 truncate">${s.nama_lengkap} <span class="text-emerald-700 font-normal">(${s.kelas})</span></div>
                                <div class="text-[11px] text-gray-500 font-mono">Saldo: ${s.saldo_formatted}</div>
                            </div>
                        </div>
                    `).join('');
                }
                searchResult.classList.remove('hidden');
            })
            .catch(() => {});
    }, 280);
});

document.addEventListener('click', function (e) {
    if (!searchResult.contains(e.target) && e.target !== inputSearch) {
        searchResult.classList.add('hidden');
    }
});

function selectSantri(s) {
    selectedSantri = s;
    searchResult.classList.add('hidden');
    inputSearch.value = '';

    document.getElementById('santriFoto').src    = s.foto_url;
    document.getElementById('santriNama').innerText  = s.nama_lengkap;
    document.getElementById('santriKelas').innerText = s.kelas;
    document.getElementById('santriNis').innerText   = s.nis;
    document.getElementById('santriNisn').innerText  = s.nisn;
    document.getElementById('santriWali').innerText  = s.nama_wali;
    document.getElementById('santriWa').innerText    = s.no_wa_wali;
    document.getElementById('santriSaldoText').innerText = s.saldo_formatted;

    document.getElementById('selectedSantriCard').classList.remove('hidden');
    document.getElementById('wrapSearchSantri').classList.add('hidden');

    // Hilangkan overlay produk
    document.getElementById('overlayPilihSantri').style.display = 'none';
    // Aktifkan badge step 2
    document.getElementById('badgeStep2').classList.replace('bg-gray-300', 'bg-emerald-600');
    // Ubah border step 1
    document.getElementById('boxStep1').classList.replace('border-emerald-400', 'border-emerald-200');

    updateSummary();
}

function gantiSantri() {
    selectedSantri = null;
    document.getElementById('selectedSantriCard').classList.add('hidden');
    document.getElementById('wrapSearchSantri').classList.remove('hidden');
    document.getElementById('overlayPilihSantri').style.display = 'flex';
    document.getElementById('badgeStep2').classList.replace('bg-emerald-600', 'bg-gray-300');
    document.getElementById('boxStep1').classList.replace('border-emerald-200', 'border-emerald-400');
    inputSearch.focus();
    updateSummary();
}

// =======================
// STEP 2 — Filter Produk
// =======================
document.getElementById('filterProdukInput').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.item-produk').forEach(el => {
        el.style.display = el.dataset.name.includes(q) ? '' : 'none';
    });
});

// =======================
// MODAL: Input Qty
// =======================
let _mId = null, _mNama = '', _mHarga = 0, _mStok = 0;

function openQtyModal(id, nama, harga, stok) {
    if (!selectedSantri) {
        // Animasi shake box santri
        const box = document.getElementById('boxStep1');
        box.classList.add('ring-2', 'ring-red-400', 'ring-offset-2');
        inputSearch.focus();
        setTimeout(() => box.classList.remove('ring-2','ring-red-400','ring-offset-2'), 1500);
        return;
    }
    _mId = id; _mNama = nama; _mHarga = harga; _mStok = stok;

    const existing = cart.find(i => i.id === id);
    const alreadyQty = existing ? existing.qty : 0;
    const maxLeft = stok - alreadyQty;

    if (maxLeft <= 0) {
        alert(`Stok "${nama}" sudah habis atau sudah maksimal di keranjang.`);
        return;
    }

    document.getElementById('qtyModalNama').innerText = nama;
    document.getElementById('qtyModalStok').innerText = `${stok}${alreadyQty > 0 ? ' (di keranjang: '+alreadyQty+')' : ''}`;
    document.getElementById('qtyModalHarga').innerText = 'Rp ' + harga.toLocaleString('id-ID');
    const inp = document.getElementById('qtyModalInput');
    inp.value = 1;
    inp.max   = maxLeft;
    refreshSubtotal();
    document.getElementById('qtyModal').classList.remove('hidden');
    setTimeout(() => inp.select(), 50);
}

function closeQtyModal() {
    document.getElementById('qtyModal').classList.add('hidden');
    _mId = null;
}

function changeModalQty(d) {
    const inp = document.getElementById('qtyModalInput');
    let v = parseInt(inp.value) + d;
    const mx = parseInt(inp.max) || _mStok;
    if (v < 1) v = 1;
    if (v > mx) { v = mx; }
    inp.value = v;
    refreshSubtotal();
}

function refreshSubtotal() {
    const qty = Math.max(1, parseInt(document.getElementById('qtyModalInput').value) || 1);
    document.getElementById('qtyModalSubtotal').innerText = 'Rp ' + (_mHarga * qty).toLocaleString('id-ID');
}

document.getElementById('qtyModalInput').addEventListener('input', refreshSubtotal);
document.getElementById('qtyModalInput').addEventListener('keydown', e => {
    if (e.key === 'Enter') confirmAddToCart();
    if (e.key === 'Escape') closeQtyModal();
});
document.getElementById('qtyModal').addEventListener('click', function (e) {
    if (e.target === this) closeQtyModal();
});

function confirmAddToCart() {
    const qty = parseInt(document.getElementById('qtyModalInput').value);
    const max = parseInt(document.getElementById('qtyModalInput').max) || _mStok;
    if (!qty || qty < 1 || qty > max) { alert('Jumlah tidak valid.'); return; }
    const existing = cart.find(i => i.id === _mId);
    if (existing) {
        existing.qty += qty;
    } else {
        cart.push({ id: _mId, nama: _mNama, harga: _mHarga, qty, stok: _mStok });
    }
    closeQtyModal();
    renderCart();
}

// =======================
// CART
// =======================
function changeQty(id, d) {
    const item = cart.find(i => i.id === id);
    if (!item) return;
    item.qty += d;
    if (item.qty <= 0) cart = cart.filter(i => i.id !== id);
    else if (item.qty > item.stok) { item.qty = item.stok; }
    renderCart();
}

function clearCart() { cart = []; renderCart(); }

function renderCart() {
    const c = document.getElementById('cartItemsList');
    if (!cart.length) {
        c.innerHTML = `<div id="cartEmptyState" class="py-16 text-center text-gray-400 text-xs">
            <i class="ti ti-shopping-cart-x text-3xl mb-2 block"></i>
            Keranjang masih kosong.<br><span class="text-[11px]">Pilih santri → klik produk → konfirmasi jumlah.</span>
        </div>`;
    } else {
        c.innerHTML = cart.map(item => `
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 flex items-center gap-3 text-xs">
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-gray-900 truncate">${item.nama}</div>
                    <div class="text-[11px] text-gray-500 font-mono">Rp ${item.harga.toLocaleString('id-ID')} × ${item.qty}</div>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <div class="inline-flex items-center bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <button type="button" onclick="changeQty(${item.id},-1)" class="px-2 py-1 text-red-500 hover:bg-red-50 cursor-pointer font-bold">−</button>
                        <span class="px-2 py-1 font-bold font-mono text-gray-800 min-w-[2rem] text-center">${item.qty}</span>
                        <button type="button" onclick="changeQty(${item.id},1)"  class="px-2 py-1 text-emerald-600 hover:bg-emerald-50 cursor-pointer font-bold">+</button>
                    </div>
                    <span class="font-bold text-gray-900 font-mono text-right w-20">${(item.harga*item.qty).toLocaleString('id-ID')}</span>
                </div>
            </div>
        `).join('');
    }
    updateSummary();
}

function updateSummary() {
    const total = cart.reduce((s,i) => s + i.harga * i.qty, 0);
    const saldo = selectedSantri ? selectedSantri.saldo : 0;
    const sisa  = saldo - total;

    document.getElementById('labelTotalHarga').innerText = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('labelSaldoAwal').innerText  = 'Rp ' + saldo.toLocaleString('id-ID');
    document.getElementById('labelSisaSaldo').innerText  = 'Rp ' + sisa.toLocaleString('id-ID');

    const sisaEl = document.getElementById('labelSisaSaldo');
    const alertEl = document.getElementById('alertSaldoKurang');
    const btn = document.getElementById('btnCheckout');

    if (sisa < 0) {
        sisaEl.classList.add('text-red-600'); sisaEl.classList.remove('text-emerald-700');
        alertEl.classList.remove('hidden');
    } else {
        sisaEl.classList.remove('text-red-600'); sisaEl.classList.add('text-emerald-700');
        alertEl.classList.add('hidden');
    }

    const canPay = selectedSantri && cart.length > 0 && sisa >= 0;
    btn.disabled = !canPay;
    if (canPay) {
        btn.classList.remove('bg-gray-300','text-gray-500','cursor-not-allowed');
        btn.classList.add('bg-emerald-600','hover:bg-emerald-700','text-white','cursor-pointer');
    } else {
        btn.classList.add('bg-gray-300','text-gray-500','cursor-not-allowed');
        btn.classList.remove('bg-emerald-600','hover:bg-emerald-700','text-white','cursor-pointer');
    }
}

// =======================
// CHECKOUT
// =======================
function processCheckout() {
    if (!selectedSantri || !cart.length) return;
    const total = cart.reduce((s,i) => s + i.harga * i.qty, 0);
    if (!confirm(`Proses pembayaran Rp ${total.toLocaleString('id-ID')} untuk santri "${selectedSantri.nama_lengkap}"?\n\nSaldo akan dipotong otomatis.`)) return;

    const btn = document.getElementById('btnCheckout');
    btn.disabled = true;
    btn.innerHTML = '<i class="ti ti-loader-2 animate-spin text-lg"></i> Memproses...';

    fetch("{{ route('admin.kantin.kasir.checkout') }}", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            santri_id: selectedSantri.id,
            toko: currentToko,
            items: cart.map(i => ({ produk_id: i.id, qty: i.qty }))
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('recNoTrans').innerText    = '#' + data.no_transaksi;
            document.getElementById('recNamaSantri').innerText = selectedSantri.nama_lengkap + ' (' + selectedSantri.kelas + ')';
            document.getElementById('recSisaSaldo').innerText  = data.sisa_saldo_formatted;
            document.getElementById('receiptModal').classList.remove('hidden');
            cart = []; renderCart();
        } else {
            alert(data.message || 'Gagal memproses transaksi.');
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-credit-card text-lg"></i><span>PROSES BAYAR (POTONG SALDO)</span>';
        }
    })
    .catch(() => {
        alert('Terjadi kesalahan jaringan.');
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-credit-card text-lg"></i><span>PROSES BAYAR (POTONG SALDO)</span>';
    });
}

function closeReceiptModal() {
    document.getElementById('receiptModal').classList.add('hidden');
    window.location.reload();
}
</script>
@endsection
