<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DepositTransferManual;
use App\Models\RekeningKelas;
use App\Models\Santri;
use App\Models\UangSakuSetting;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class DepositTransferController extends Controller
{
    public function create()
    {
        $santris = Santri::where('user_id', auth()->id())
            ->where('status', 'diterima')
            ->orderBy('nama_lengkap')
            ->get();

        $depositTerbaru = DepositTransferManual::where('user_id', auth()->id())
            ->with(['santri', 'rekeningKelas'])
            ->latest()
            ->paginate(10);

        $rekening = UangSakuSetting::first();
        $rekeningKelas = RekeningKelas::active()->get()->keyBy('kelas');

        return view('user.deposit_transfer.create', compact('santris', 'depositTerbaru', 'rekening', 'rekeningKelas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'santri_id'      => ['required', 'integer'],
            'jumlah'         => ['required', 'numeric', 'min:1000'],
            'bukti_transfer' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'catatan_wali'   => ['nullable', 'string', 'max:500'],
        ], [
            'santri_id.required'     => 'Pilih santri untuk deposit ini.',
            'jumlah.min'              => 'Nominal deposit minimal Rp 1.000.',
            'bukti_transfer.required' => 'Bukti transfer wajib diunggah.',
            'bukti_transfer.mimes'    => 'Bukti transfer harus berupa JPG, JPEG, PNG, atau PDF.',
            'bukti_transfer.max'      => 'Ukuran bukti transfer maksimal 5 MB.',
        ]);

        $santri = Santri::where('id', $validated['santri_id'])
            ->where('user_id', auth()->id())
            ->where('status', 'diterima')
            ->firstOrFail();

        $kelasClean = preg_replace('/[^0-9]/', '', (string) ($santri->calculated_kelas ?: $santri->kelas));
        $rekeningKelas = RekeningKelas::where('is_active', true)
            ->where(function ($q) use ($santri, $kelasClean) {
                $q->where('kelas', $santri->kelas)
                  ->orWhere('kelas', $kelasClean)
                  ->orWhere('kelas', 'Kelas ' . $kelasClean);
            })->first();

        $path = $request->file('bukti_transfer')->store('deposit-transfer', 'public');

        $deposit = DepositTransferManual::create([
            'user_id'           => auth()->id(),
            'santri_id'         => $santri->id,
            'rekening_kelas_id' => $rekeningKelas?->id,
            'jumlah'            => $validated['jumlah'],
            'bukti_transfer'    => $path,
            'status'            => 'menunggu_verifikasi',
            'catatan_wali'      => $validated['catatan_wali'] ?? null,
        ]);

        // Kirim notifikasi WhatsApp otomatis ke Nomor WhatsApp Ustadz Pembimbing kelas
        try {
            if ($rekeningKelas && !empty($rekeningKelas->no_wa_ustadz)) {
                NotificationService::sendDepositKonfirmasiKeUstadz($santri, $deposit, $rekeningKelas);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal kirim notif WA deposit ke ustadz: ' . $e->getMessage());
        }

        return redirect()->route('user.deposit_transfer.create')
            ->with('success', 'Pengajuan deposit uang saku berhasil dikirim dan sedang menunggu verifikasi ustadz pembimbing kelas.');
    }
}
