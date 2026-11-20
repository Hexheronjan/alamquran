<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\KalenderAkademik;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserKalenderController extends Controller
{
    public function index(Request $request)
    {
        $currentYear = date('n') >= 7 ? date('Y') : date('Y') - 1;
        $defaultTahunAjaran = $currentYear . '/' . ($currentYear + 1);

        $selectedTahunAjaran = $request->get('tahun_ajaran', $defaultTahunAjaran);

        // Ambil data Hari Libur Nasional & Peringatan Penting sebagai Overlay Informasi Kalender (Tanpa Masuk DB)
        $startYear = (int) explode('/', $selectedTahunAjaran)[0];
        $masterNasional = KalenderAkademik::getMasterHariNasional($startYear);
        $hariNasionalByDate = [];
        foreach ($masterNasional as $item) {
            $tglMulai = Carbon::parse($item['tanggal_mulai']);
            $tglSelesai = Carbon::parse($item['tanggal_selesai'] ?? $item['tanggal_mulai']);
            while ($tglMulai->lte($tglSelesai)) {
                $dStr = $tglMulai->format('Y-m-d');
                $hariNasionalByDate[$dStr][] = $item;
                $tglMulai->addDay();
            }
        }

        $events = KalenderAkademik::where('tahun_ajaran', $selectedTahunAjaran)
            ->where('is_public', true)
            ->orderBy('tanggal_mulai', 'asc')
            ->get();

        $tahunAjaranList = KalenderAkademik::where('is_public', true)
            ->select('tahun_ajaran')
            ->distinct()
            ->orderByDesc('tahun_ajaran')
            ->pluck('tahun_ajaran')
            ->toArray();

        if (!in_array($defaultTahunAjaran, $tahunAjaranList)) {
            array_unshift($tahunAjaranList, $defaultTahunAjaran);
        }

        $kategoriList = KalenderAkademik::getKategoriList();

        // 12 Bulan Akademik dari Juli s.d. Juni
        $monthsAcademic = [];
        for ($m = 7; $m <= 12; $m++) {
            $monthsAcademic[] = [
                'month' => $m,
                'year'  => $startYear,
                'label' => Carbon::createFromDate($startYear, $m, 1)->translatedFormat('F Y'),
            ];
        }
        for ($m = 1; $m <= 6; $m++) {
            $monthsAcademic[] = [
                'month' => $m,
                'year'  => $startYear + 1,
                'label' => Carbon::createFromDate($startYear + 1, $m, 1)->translatedFormat('F Y'),
            ];
        }

        return view('user.kalender.index', compact(
            'events',
            'hariNasionalByDate',
            'selectedTahunAjaran',
            'tahunAjaranList',
            'kategoriList',
            'monthsAcademic'
        ));
    }
}
