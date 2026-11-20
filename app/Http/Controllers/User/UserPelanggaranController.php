<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PelanggaranSantri;
use App\Models\Santri;
use Illuminate\Http\Request;

class UserPelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $santris = Santri::where('user_id', auth()->id())
            ->where('status', 'diterima')
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        if ($santris->isEmpty()) {
            return redirect()->route('user.dashboard')
                ->with('info', 'Belum ada data santri aktif pada akun Anda.');
        }

        $santriIds = $santris->pluck('id');
        $selectedSantriId = (int) $request->get('santri_id', $santriIds->first());
        $activeSantri = $santris->firstWhere('id', $selectedSantriId) ?? $santris->first();

        $pelanggarans = PelanggaranSantri::where('santri_id', $activeSantri->id)
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalPelanggaran = $pelanggarans->count();
        $sedangDijalani = $pelanggarans->where('status_pembinaan', 'sedang_dijalani')->count();
        $selesai = $pelanggarans->where('status_pembinaan', 'selesai')->count();

        return view('user.pelanggaran.index', compact(
            'santris',
            'activeSantri',
            'selectedSantriId',
            'pelanggarans',
            'totalPelanggaran',
            'sedangDijalani',
            'selesai'
        ));
    }
}
