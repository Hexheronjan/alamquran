<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\SppSetting;
use App\Models\DaftarUlangSetting;
use App\Models\DaftarUlangTagihan;
use App\Models\DaftarUlangPembayaran;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserDaftarUlangController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil semua santri berstatus diterima yang dimiliki wali ini
        $santris = Santri::where('user_id', auth()->id())
            ->where('status', 'diterima')
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        if ($santris->isEmpty()) {
            return redirect()->route('dashboard')
                ->with('info', 'Belum ada data santri yang diterima pada akun Anda.');
        }

        $santriIds = $santris->pluck('id');
        $selectedSantriId = (int) $request->get('santri_id', $santriIds->first());

        $activeSantri = $santris->firstWhere('id', $selectedSantriId) ?? $santris->first();

        // 2. Ambil seluruh tagihan daftar ulang santri (akumulasi tahunan jika naik kelas & masih mengangsur)
        $tagihans = DaftarUlangTagihan::with(['pembayarans', 'addons'])
            ->where('santri_id', $activeSantri->id)
            ->where('is_ready', true)
            ->orderBy('tahun_angkatan', 'asc')
            ->get();

        if ($tagihans->isEmpty()) {
            $tagihan = DaftarUlangTagihan::with('addons')->where('santri_id', $activeSantri->id)->first();
            $totalKewajiban = (float) ($tagihan?->total_tagihan ?? ($tagihan?->nominal_biaya ?? 0));
            $totalTerbayar  = (float) ($tagihan?->nominal_terbayar ?? 0);
            $totalSisa      = (float) ($tagihan?->sisa_tagihan ?? 0);
            $isAccumulated  = false;
        } else {
            // Tagihan target bayar: ambil tagihan tertua yang belum lunas (FIFO)
            $tagihan = $tagihans->firstWhere('status', '!=', 'lunas') ?? $tagihans->last();
            $totalKewajiban = (float) $tagihans->sum(fn($t) => $t->total_tagihan);
            $totalTerbayar  = (float) $tagihans->sum('nominal_terbayar');
            $totalSisa      = (float) $tagihans->sum('sisa_tagihan');
            $isAccumulated  = $tagihans->count() > 1;
        }

        $rekInfo = $activeSantri->info_rekening_spp;
        $sppSetting = SppSetting::getActive();
        $daftarUlangSetting = DaftarUlangSetting::getActive();
        $isPaymentOpen = $daftarUlangSetting->isOpen();

        return view('user.daftar_ulang.index', compact(
            'santris',
            'activeSantri',
            'selectedSantriId',
            'tagihan',
            'tagihans',
            'totalKewajiban',
            'totalTerbayar',
            'totalSisa',
            'isAccumulated',
            'rekInfo',
            'sppSetting',
            'daftarUlangSetting',
            'isPaymentOpen'
        ));
    }

    public function konfirmasiBayar(Request $request, $id)
    {
        // 1. Cek status buka/tutup pembayaran
        $daftarUlangSetting = DaftarUlangSetting::getActive();
        if (!$daftarUlangSetting->isOpen()) {
            return back()->with('error', $daftarUlangSetting->pesan_tutup ?: 'Pembayaran daftar ulang saat ini belum dibuka.');
        }

        $validated = $request->validate([
            'jumlah_bayar' => ['required', 'numeric', 'min:1000'],
            'metode_bayar' => ['required', 'string', 'in:transfer'],
            'bukti_bayar'  => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'catatan'      => ['nullable', 'string', 'max:500'],
        ], [
            'jumlah_bayar.required' => 'Nominal pembayaran wajib diisi.',
            'jumlah_bayar.min'      => 'Nominal pembayaran minimal Rp 1.000.',
            'bukti_bayar.required'  => 'Bukti transfer pembayaran wajib diunggah.',
            'bukti_bayar.mimes'     => 'Bukti transfer harus berupa format JPG, JPEG, PNG, atau PDF.',
            'bukti_bayar.max'       => 'Ukuran file bukti transfer maksimal 5 MB.',
        ]);

        $tagihan = DaftarUlangTagihan::whereHas('santri', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);

        // Ambil semua tagihan santri ini untuk mengecek total sisa akumulasi
        $allTagihans = DaftarUlangTagihan::where('santri_id', $tagihan->santri_id)
            ->where('is_ready', true)
            ->orderBy('tahun_angkatan', 'asc')
            ->get();

        $totalSisa = $allTagihans->isNotEmpty() ? (float) $allTagihans->sum('sisa_tagihan') : (float) $tagihan->sisa_tagihan;

        if ($totalSisa <= 0) {
            return back()->with('error', 'Seluruh tagihan biaya daftar ulang ananda sudah lunas.');
        }

        if ($tagihan->pembayarans()->where('status', 'menunggu_konfirmasi')->exists()) {
            return back()->with('error', 'Bukti pembayaran sebelumnya masih menunggu verifikasi bendahara pondok.');
        }

        $jumlahBayar = (float) $validated['jumlah_bayar'];
        if ($jumlahBayar > $totalSisa) {
            return back()->with('error', 'Nominal transfer melebihi total sisa tagihan (Maksimal: Rp ' . number_format($totalSisa, 0, ',', '.') . ').');
        }

        // Tentukan target tagihan: targetkan tagihan tertua yang belum lunas
        $targetTagihan = $allTagihans->firstWhere('status', '!=', 'lunas') ?? $tagihan;

        // Upload bukti transfer
        $file = $request->file('bukti_bayar');
        $filename = 'daftar_ulang_' . $targetTagihan->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('bukti_daftar_ulang', $filename, 'public');

        $pembayaran = DaftarUlangPembayaran::create([
            'daftar_ulang_tagihan_id' => $targetTagihan->id,
            'santri_id'               => $targetTagihan->santri_id,
            'jumlah_bayar'            => $jumlahBayar,
            'metode_bayar'            => 'transfer',
            'bukti_bayar'             => $path,
            'catatan'                 => $request->catatan,
            'status'                  => 'menunggu_konfirmasi',
            'submitted_by_user_id'    => auth()->id(),
        ]);

        // Notifikasi otomatis ke nomor WA Bendahara
        $sppSetting = SppSetting::getActive();
        if ($sppSetting && !empty($sppSetting->no_wa_bendahara)) {
            try {
                NotificationService::sendDaftarUlangKonfirmasiKeBendahara(
                    $targetTagihan->santri,
                    $targetTagihan,
                    $pembayaran,
                    $sppSetting->no_wa_bendahara
                );
            } catch (\Throwable $e) {
                \Log::warning('Gagal kirim notif daftar ulang ke bendahara: ' . $e->getMessage());
            }
        }

        return redirect()->route('user.daftar_ulang.index', ['santri_id' => $targetTagihan->santri_id])
            ->with('success', 'Bukti transfer angsuran berhasil dikirimkan. Notifikasi telah diteruskan ke bendahara pondok untuk diverifikasi.');
    }

    public function cetakInvoice($id)
    {
        $tagihan = DaftarUlangTagihan::whereHas('santri', function ($q) {
            $q->where('user_id', auth()->id());
        })->with(['santri.orangTua', 'addons', 'pembayarans.verifiedBy'])->findOrFail($id);

        $setting = \App\Models\InvoiceSetting::getSetting();
        $fileName = 'Invoice_Daftar_Ulang_' . \Illuminate\Support\Str::slug($tagihan->santri->nama_lengkap) . '_' . $tagihan->tahun_angkatan . '.pdf';

        if (request('download') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice_daftar_ulang', [
                'tagihan' => $tagihan,
                'setting' => $setting,
                'isPdf' => true,
            ])->setPaper('a4', 'portrait');
            return $pdf->download($fileName);
        }

        return view('pdf.invoice_daftar_ulang', [
            'tagihan' => $tagihan,
            'setting' => $setting,
            'fileName' => $fileName,
            'isPdf' => false,
        ]);
    }
}
