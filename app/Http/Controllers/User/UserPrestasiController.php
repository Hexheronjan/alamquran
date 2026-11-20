<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\SantriPrestasi;
use Illuminate\Http\Request;

class UserPrestasiController extends Controller
{
    public function index(Request $request)
    {
        // Anak milik akun wali yang sedang login (untuk penanda khusus / filter cepat)
        $mySantris = Santri::where('user_id', auth()->id())
            ->where('status', 'diterima')
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        $query = SantriPrestasi::with(['santri', 'admin']);

        // Filter per santri jika dipilih
        if ($request->filled('santri_id') && $request->santri_id !== 'all') {
            $query->where('santri_id', $request->santri_id);
        }

        // Filter per tahun ajaran
        if ($request->filled('tahun_ajaran') && $request->tahun_ajaran !== 'all') {
            $query->where('tahun_ajaran', $request->tahun_ajaran);
        }

        // Filter per kategori
        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('kategori', $request->kategori);
        }

        $prestasis = $query->orderBy('tanggal_perolehan', 'desc')->get();

        // Daftar tahun ajaran untuk filter
        $tahunAjaranList = SantriPrestasi::select('tahun_ajaran')
            ->distinct()
            ->orderByDesc('tahun_ajaran')
            ->pluck('tahun_ajaran')
            ->toArray();

        // Daftar seluruh santri yang memiliki rekam prestasi (untuk dropdown)
        $santriJuaraList = Santri::whereIn('id', SantriPrestasi::select('santri_id')->distinct())
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        $totalPrestasi = SantriPrestasi::count();
        $tingkatNasional = SantriPrestasi::whereIn('tingkat', ['nasional', 'internasional'])->count();
        $totalSantriJuara = SantriPrestasi::distinct('santri_id')->count('santri_id');

        $kategoriList = SantriPrestasi::getKategoriList();

        return view('user.prestasi.index', compact(
            'mySantris',
            'santriJuaraList',
            'prestasis',
            'tahunAjaranList',
            'totalPrestasi',
            'tingkatNasional',
            'totalSantriJuara',
            'kategoriList'
        ));
    }
}
