<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DepositTransferManual;
use App\Models\UangSaku;
use App\Models\UangSakuSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DepositTransferController extends Controller
{
    public function index(Request $request)
    {
        $currentAdmin = auth('admin')->user();
        $query = DepositTransferManual::with(['user', 'santri.orangTua', 'rekeningKelas'])->latest();

        // Filter otomatis jika admin adalah ustadz pembina kelas tertentu
        if ($currentAdmin && $currentAdmin->assigned_kelas) {
            $query->whereHas('santri', function ($q) use ($currentAdmin) {
                $q->where('kelas', $currentAdmin->assigned_kelas);
            });
        }

        if ($request->filled('kelas')) {
            $query->whereHas('santri', function ($q) use ($request) {
                $q->where('kelas', $request->kelas);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $deposit = $query->paginate(15)->withQueryString();
        $rekening = UangSakuSetting::first();

        return view('admin.deposit_transfer.index', compact('deposit', 'rekening'));
    }

    public function updateRekening(Request $request)
    {
        $validated = $request->validate([
            'bank_deposit' => ['required', 'string', 'max:100'],
            'nomor_rekening_deposit' => ['required', 'string', 'max:50'],
            'atas_nama_rekening' => ['required', 'string', 'max:150'],
        ]);

        $settings = UangSakuSetting::firstOrCreate([], [
            'batas_minimal_saldo' => 50000,
            'is_active' => true,
            'template_pesan' => 'Notifikasi saldo uang saku.',
        ]);

        $settings->update($validated);

        return back()->with('success', 'Rekening tujuan pesantren berhasil diperbarui.');
    }

    public function showProof(DepositTransferManual $deposit)
    {
        abort_unless(Storage::disk('public')->exists($deposit->bukti_transfer), 404, 'Bukti transfer tidak ditemukan.');

        return Storage::disk('public')->response($deposit->bukti_transfer);
    }

    public function approve(Request $request, DepositTransferManual $deposit)
    {
        $request->validate(['catatan_admin' => ['nullable', 'string', 'max:500']]);

        DB::transaction(function () use ($request, $deposit) {
            $deposit = DepositTransferManual::lockForUpdate()->findOrFail($deposit->id);

            if ($deposit->status !== 'menunggu_verifikasi') {
                abort(422, 'Deposit ini sudah diproses.');
            }

            $santri = $deposit->santri()->with('orangTua')->lockForUpdate()->firstOrFail();

            UangSaku::create([
                'santri_id' => $santri->id,
                'jenis' => 'masuk',
                'jumlah' => $deposit->jumlah,
                'saldo_akhir' => 0,
                'keterangan' => 'Deposit transfer manual #' . $deposit->id,
                'kelas' => $santri->kelas,
                'tahun_ajaran' => $santri->tahun_ajaran,
                'admin_id' => auth('admin')->id(),
            ]);

            $saldoBaru = UangSaku::recalculateBalance($santri->id);
            $settings = UangSakuSetting::first();
            if ($settings && $saldoBaru > $settings->batas_minimal_saldo && $santri->wa_saldo_warning_sent) {
                $santri->update(['wa_saldo_warning_sent' => false]);
            }

            $deposit->update([
                'status' => 'disetujui',
                'catatan_admin' => $request->catatan_admin,
                'admin_id' => auth('admin')->id(),
                'diverifikasi_pada' => now(),
            ]);

            \App\Services\AdminActivityLogger::log(
                'approve_deposit',
                "Menyetujui deposit transfer #{$deposit->id} santri '{$santri->nama_lengkap}' sebesar Rp " . number_format($deposit->jumlah, 0, ',', '.'),
                'DepositTransferManual',
                $deposit->id
            );

            // Kirim notifikasi WA ke Orang Tua
            try {
                $noWaOrtu = $santri->orangTua?->no_wa_ayah;
                if ($noWaOrtu) {
                    $pesan = "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\n"
                           . "Alhamdulillah, deposit uang saku untuk ananda *{$santri->nama_lengkap}* (Kelas {$santri->kelas}) telah dikonfirmasi dan disetujui oleh ustadz pembimbing.\n\n"
                           . "💵 Nominal Masuk: Rp " . number_format($deposit->jumlah, 0, ',', '.') . "\n"
                           . "💰 Saldo Sekarang: Rp " . number_format($saldoBaru, 0, ',', '.') . "\n\n"
                           . "Jazakumullah khairan katsiran atas kepercayaannya kepada Pesantren Tahfizh Alam Qur'an.";

                    \App\Services\NotificationService::send([
                        'recipient_number' => $noWaOrtu,
                        'recipient_name' => $santri->orangTua?->nama_ayah,
                        'user_id' => $santri->user_id,
                        'santri_id' => $santri->id,
                        'modul' => 'Uang Saku',
                        'message' => $pesan,
                        'reference_id' => 'DEPOSIT-DITERIMA-' . $deposit->id,
                        'sync' => true,
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::warning('Gagal kirim notif WA deposit approved: ' . $e->getMessage());
            }
        });

        return back()->with('success', 'Deposit disetujui. Saldo uang saku santri telah ditambahkan dan notifikasi WA terkirim.');
    }

    public function reject(Request $request, DepositTransferManual $deposit)
    {
        $validated = $request->validate([
            'catatan_admin' => ['required', 'string', 'max:500'],
        ], ['catatan_admin.required' => 'Alasan penolakan wajib diisi.']);

        if ($deposit->status !== 'menunggu_verifikasi') {
            return back()->withErrors(['deposit' => 'Deposit ini sudah diproses.']);
        }

        $deposit->update([
            'status' => 'ditolak',
            'catatan_admin' => $validated['catatan_admin'],
            'admin_id' => auth('admin')->id(),
            'diverifikasi_pada' => now(),
        ]);

        \App\Services\AdminActivityLogger::log(
            'reject_deposit',
            "Menolak deposit transfer #{$deposit->id} santri '{$deposit->santri->nama_lengkap}': {$validated['catatan_admin']}",
            'DepositTransferManual',
            $deposit->id
        );

        $deposit->load('santri.orangTua');
        \App\Services\NotificationService::send([
            'recipient_number' => $deposit->santri?->orangTua?->no_wa_ayah ?: $deposit->user?->no_wa,
            'recipient_name' => $deposit->santri?->orangTua?->nama_ayah ?: $deposit->user?->name,
            'user_id' => $deposit->user_id,
            'santri_id' => $deposit->santri_id,
            'modul' => 'Uang Saku',
            'reference_id' => 'DEPOSIT-DITOLAK-' . $deposit->id,
            'message' => "Assalamu'alaikum. Bukti deposit uang saku {$deposit->santri?->nama_lengkap} belum dapat diverifikasi. Alasan: {$validated['catatan_admin']}. Silakan unggah bukti transfer kembali.",
            'sync' => true,
        ]);

        return back()->with('success', 'Deposit ditolak dan saldo tidak berubah.');
    }

    public function destroy(DepositTransferManual $deposit)
    {
        $buktiTransfer = $deposit->bukti_transfer;
        $santriName = $deposit->santri?->nama_lengkap ?? 'Santri';
        $depositId = $deposit->id;

        DB::transaction(function () use ($deposit) {
            if ($deposit->status === 'disetujui') {
                UangSaku::where('santri_id', $deposit->santri_id)
                    ->where('keterangan', 'Deposit transfer manual #' . $deposit->id)
                    ->delete();

                UangSaku::recalculateBalance($deposit->santri_id);
            }

            $deposit->delete();
        });

        if ($buktiTransfer) {
            Storage::disk('public')->delete($buktiTransfer);
        }

        \App\Services\AdminActivityLogger::log(
            'delete_deposit',
            "Menghapus data deposit transfer #{$depositId} santri '{$santriName}'",
            'DepositTransferManual',
            $depositId
        );

        return back()->with('success', 'Data deposit berhasil dihapus. Saldo santri telah disesuaikan bila deposit sebelumnya disetujui.');
    }
}
