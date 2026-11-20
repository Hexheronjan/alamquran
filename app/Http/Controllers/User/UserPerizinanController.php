<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\KehadiranWali;
use App\Models\PerizinanSantri;
use App\Models\Santri;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserPerizinanController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $qrToken = $user->ensureQrToken();

        $santris = Santri::where('user_id', $user->id)
            ->where('status', 'diterima')
            ->get();

        $santriIds = $santris->pluck('id');

        // Riwayat Kehadiran Ahad Pagi
        $kehadirans = KehadiranWali::where('user_id', $user->id)
            ->latest('tanggal')
            ->paginate(10, ['*'], 'kehadiran_page');

        // Riwayat Izin Santri Keluar / Pulang
        $perizinans = PerizinanSantri::with('santri')
            ->whereIn('santri_id', $santriIds)
            ->latest('waktu_keluar')
            ->paginate(10, ['*'], 'izin_page');

        $activeIzin = PerizinanSantri::with('santri')
            ->whereIn('santri_id', $santriIds)
            ->where('status', 'keluar')
            ->first();

        return view('user.perizinan.index', compact('user', 'qrToken', 'santris', 'kehadirans', 'perizinans', 'activeIzin'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'santri_id'         => 'required|exists:santri,id',
            'jenis_izin'        => 'required|string|in:pulang,keluar_sementara,keluar_sebentar,bawa_pulang,keperluan_khusus',
            'tujuan'            => 'required|string|max:255',
            'nama_penjemput'    => 'required|string|max:100',
            'no_wa_penjemput'   => 'required|string|max:20',
            'batas_jam_kembali' => 'nullable|date',
        ]);

        // Pastikan santri milik user ini
        $santri = Santri::where('id', $request->santri_id)
            ->where('user_id', $user->id)
            ->where('status', 'diterima')
            ->firstOrFail();

        // Cek apakah santri sudah ada izin aktif
        $izinAktif = PerizinanSantri::where('santri_id', $santri->id)
            ->where('status', 'keluar')
            ->first();

        if ($izinAktif) {
            return back()->with('error', 'Santri ' . $santri->nama_lengkap . ' masih memiliki izin yang aktif dan belum kembali.');
        }

        $batasJamKembali = null;
        if ($request->filled('batas_jam_kembali')) {
            try {
                $batasJamKembali = Carbon::parse($request->batas_jam_kembali)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                $batasJamKembali = $request->batas_jam_kembali;
            }
        }

        $izin = PerizinanSantri::create([
            'santri_id'         => $santri->id,
            'user_id'           => $user->id,
            'jenis_izin'        => $request->jenis_izin,
            'tujuan'            => $request->tujuan,
            'nama_penjemput'    => $request->nama_penjemput,
            'no_wa_penjemput'   => $request->no_wa_penjemput,
            'waktu_keluar'      => now(),
            'batas_jam_kembali' => $batasJamKembali,
            'status'            => 'pengajuan',
        ]);

        NotificationService::send([
            'recipient_number' => $user->no_wa,
            'recipient_name' => $user->name,
            'user_id' => $user->id,
            'santri_id' => $santri->id,
            'modul' => 'Perizinan',
            'reference_id' => 'IZIN-PENGAJUAN-' . $izin->id,
            'message' => "Assalamu'alaikum. Pengajuan izin {$santri->nama_lengkap} telah diterima dan sedang menunggu persetujuan petugas pondok.",
        ]);

        return back()->with('success', 'Pengajuan izin untuk ' . $santri->nama_lengkap . ' telah dikirim. Menunggu petugas untuk proses.');
    }
}
