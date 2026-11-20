<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Services\AdminActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KartuSantriController extends Controller
{
    public function index(Request $request)
    {
        $query = Santri::aktif()->with(['orangTua', 'angkatan']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kelas') && $request->kelas !== 'all') {
            $query->where('kelas', $request->kelas);
        }

        if ($request->filled('tahun_masuk') && $request->tahun_masuk !== 'all') {
            $query->where('tahun_masuk', $request->tahun_masuk);
        }

        if ($request->filled('status_foto')) {
            if ($request->status_foto === 'ada') {
                $query->whereNotNull('foto_santri');
            } elseif ($request->status_foto === 'belum') {
                $query->whereNull('foto_santri');
            }
        }

        $santris = $query->orderBy('kelas')->orderBy('nama_lengkap')->paginate(20)->withQueryString();

        // Statistics
        $totalSantri = Santri::aktif()->count();
        $totalDenganFoto = Santri::aktif()->whereNotNull('foto_santri')->count();
        $totalTanpaFoto = $totalSantri - $totalDenganFoto;

        $tahunList = Santri::aktif()
            ->whereNotNull('tahun_masuk')
            ->distinct()
            ->orderBy('tahun_masuk', 'desc')
            ->pluck('tahun_masuk');

        return view('admin.kartu_santri.index', compact(
            'santris',
            'totalSantri',
            'totalDenganFoto',
            'totalTanpaFoto',
            'tahunList'
        ));
    }

    public function updateFoto(Request $request, Santri $santri)
    {
        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
        ], [
            'foto.required' => 'Pilih file foto terlebih dahulu.',
            'foto.image'    => 'File harus berupa gambar.',
            'foto.mimes'    => 'Format gambar harus JPG, PNG, atau WEBP.',
            'foto.max'      => 'Ukuran foto maksimal 3 MB.',
        ]);

        if ($request->hasFile('foto')) {
            // Hapus file lama jika ada
            if ($santri->foto_santri && Storage::disk('public')->exists($santri->foto_santri)) {
                Storage::disk('public')->delete($santri->foto_santri);
            }

            $file = $request->file('foto');
            $fileName = 'santri_' . $santri->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('santri/foto', $fileName, 'public');

            $santri->update(['foto_santri' => $path]);

            AdminActivityLogger::log(
                'update_foto_santri',
                "Memperbarui foto kartu santri ananda {$santri->nama_lengkap}",
                'Santri',
                $santri->id
            );
        }

        return back()->with('success', "Foto kartu santri ananda {$santri->nama_lengkap} berhasil diperbarui. Cetakan kartu otomatis mengikuti foto baru ini.");
    }

    public function deleteFoto(Santri $santri)
    {
        if ($santri->foto_santri && Storage::disk('public')->exists($santri->foto_santri)) {
            Storage::disk('public')->delete($santri->foto_santri);
        }

        $santri->update(['foto_santri' => null]);

        AdminActivityLogger::log(
            'delete_foto_santri',
            "Menghapus foto kartu santri ananda {$santri->nama_lengkap}",
            'Santri',
            $santri->id
        );

        return back()->with('success', "Foto kartu santri ananda {$santri->nama_lengkap} berhasil dihapus dan dikembalikan ke avatar standar.");
    }
}
