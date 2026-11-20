<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSantri;
use App\Models\Santri;
use Illuminate\Http\Request;

class UserAbsensiController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $santris = Santri::where('user_id', $user->id)
            ->where('status', 'diterima')
            ->get();

        $selectedSantriId = $request->get('santri_id', $santris->first()?->id);
        $activeSantri = $santris->firstWhere('id', $selectedSantriId);

        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $absensiList = AbsensiSantri::with('guru')
            ->where('santri_id', $selectedSantriId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderByDesc('tanggal')
            ->get();

        $rekap = [
            'hadir' => $absensiList->where('status_kehadiran', 'hadir')->count(),
            'sakit' => $absensiList->where('status_kehadiran', 'sakit')->count(),
            'izin'  => $absensiList->where('status_kehadiran', 'izin')->count(),
            'alpa'  => $absensiList->where('status_kehadiran', 'alpa')->count(),
            'total' => $absensiList->count(),
        ];

        // Bulan list untuk dropdown
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return view('user.absensi.index', compact(
            'santris', 'activeSantri', 'selectedSantriId',
            'absensiList', 'rekap', 'bulan', 'tahun', 'bulanList'
        ));
    }
}
