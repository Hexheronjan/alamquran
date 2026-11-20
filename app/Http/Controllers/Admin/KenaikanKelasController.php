<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Services\AcademicPromotionService;
use Illuminate\Http\Request;

class KenaikanKelasController extends Controller
{
    public function index(Request $request)
    {
        $selectedKelas = $request->get('kelas', 'all');
        $currentYear   = (int) date('Y');

        $counts = [
            '1'          => Santri::where('status', 'diterima')->where('status_santri', 'aktif')->where('kelas', '1')->count(),
            '1_eligible' => Santri::where('status', 'diterima')->where('status_santri', 'aktif')->where('kelas', '1')->where(function ($q) use ($currentYear) {
                $q->whereNull('tahun_masuk')->orWhere('tahun_masuk', '<=', (string) $currentYear);
            })->count(),
            '1_baru'     => Santri::where('status', 'diterima')->where('status_santri', 'aktif')->where('kelas', '1')->where('tahun_masuk', '>', (string) $currentYear)->count(),
            '2'          => Santri::where('status', 'diterima')->where('status_santri', 'aktif')->where('kelas', '2')->count(),
            '3'          => Santri::where('status', 'diterima')->where('status_santri', 'aktif')->where('kelas', '3')->count(),
            'alumni'     => Santri::where('status', 'diterima')->where(function ($q) {
                $q->where('kelas', 'alumni')->orWhere('status_santri', 'lulus');
            })->count(),
        ];

        $query = Santri::with(['orangTua', 'angkatan'])
            ->where('status', 'diterima')
            ->where('status_santri', 'aktif');

        if ($selectedKelas !== 'all') {
            $query->where('kelas', $selectedKelas);
        } else {
            $query->whereIn('kelas', ['1', '2', '3']);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $santriList = $query->orderBy('kelas', 'desc')
            ->orderBy('nama_lengkap', 'asc')
            ->paginate(25)
            ->withQueryString();

        return view('admin.santri.kenaikan_kelas', compact('santriList', 'counts', 'selectedKelas', 'currentYear'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'tahun_lulus'  => ['nullable', 'digits:4'],
            'excluded_ids' => ['nullable', 'array'],
        ]);

        $excludedIds = $request->input('excluded_ids', []);
        $tahunLulus  = $request->input('tahun_lulus') ?: date('Y');

        $result = AcademicPromotionService::promoteAll($excludedIds, $tahunLulus);

        $msg = "Proses kenaikan kelas berhasil! {$result['kelas_1_ke_2']} santri naik ke Kelas 2, {$result['kelas_2_ke_3']} santri naik ke Kelas 3, dan {$result['kelas_3_alumni']} santri lulus menjadi Alumni.";
        if (!empty($result['tetap_kelas_1'])) {
            $msg .= " ({$result['tetap_kelas_1']} santri baru tetap di Kelas 1).";
        }

        return redirect()->route('admin.santri.kenaikan_kelas')
            ->with('success', $msg);
    }

    public function updateIndividual(Request $request, $id)
    {
        $request->validate([
            'kelas'       => ['required', 'in:1,2,3,alumni'],
            'tahun_lulus' => ['nullable', 'digits:4'],
        ]);

        AcademicPromotionService::updateIndividualClass($id, $request->kelas, $request->tahun_lulus);

        return back()->with('success', 'Jenjang kelas santri berhasil diperbarui.');
    }
}
