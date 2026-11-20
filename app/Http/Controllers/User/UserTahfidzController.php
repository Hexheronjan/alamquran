<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\TahfidzSetoran;
use Illuminate\Http\Request;

class UserTahfidzController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $santris = Santri::where('user_id', $user->id)
            ->where('status', 'diterima')
            ->get();

        $selectedSantriId = $request->get('santri_id', $santris->first()?->id);
        $activeSantri = $santris->firstWhere('id', $selectedSantriId);

        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $query = TahfidzSetoran::with(['surah', 'ustadz'])
            ->where('santri_id', $selectedSantriId);

        if ($bulan && $tahun) {
            $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
        }

        $setorans = $query->orderByDesc('tanggal')->orderByDesc('id')->paginate(20);

        // Statistik all-time
        $allSetoran = TahfidzSetoran::where('santri_id', $selectedSantriId)->get();
        $stats = [
            'total'         => $allSetoran->count(),
            'ziyadah'       => $allSetoran->where('jenis_setoran', 'ziyadah')->count(),
            'murajaah'      => $allSetoran->where('jenis_setoran', 'murajaah')->count(),
            'ujian_juziyah' => $allSetoran->where('jenis_setoran', 'ujian_juziyah')->count(),
            'total_ayat'    => $allSetoran->where('jenis_setoran', 'ziyadah')->sum(function ($s) {
                return max(0, ($s->ayat_akhir - $s->ayat_awal) + 1);
            }),
        ];

        return view('user.tahfidz.index', compact(
            'santris', 'activeSantri', 'selectedSantriId',
            'setorans', 'stats', 'bulan', 'tahun'
        ));
    }
}
