<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\SantriPrestasi;
use App\Services\AdminActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminPrestasiController extends Controller
{
    public function index(Request $request)
    {
        $query = SantriPrestasi::with(['santri', 'admin']);

        if ($request->filled('santri_id')) {
            $query->where('santri_id', $request->santri_id);
        }

        if ($request->filled('tahun_ajaran') && $request->tahun_ajaran !== 'all') {
            $query->where('tahun_ajaran', $request->tahun_ajaran);
        }

        if ($request->filled('kategori') && $request->kategori !== 'all') {
            if ($request->kategori === 'lainnya') {
                $standardKeys = ['tahfidz', 'akademik', 'bahasa', 'seni_kaligrafi', 'pidato_dai', 'olahraga'];
                $query->where(function ($q) use ($standardKeys) {
                    $q->where('kategori', 'lainnya')
                      ->orWhereNotIn('kategori', $standardKeys);
                });
            } else {
                $query->where('kategori', $request->kategori);
            }
        }

        if ($request->filled('tingkat') && $request->tingkat !== 'all') {
            $query->where('tingkat', $request->tingkat);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_prestasi', 'like', "%{$search}%")
                  ->orWhere('penyelenggara', 'like', "%{$search}%")
                  ->orWhere('peringkat', 'like', "%{$search}%")
                  ->orWhereHas('santri', function ($sq) use ($search) {
                      $sq->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('nis', 'like', "%{$search}%");
                  });
            });
        }

        $prestasis = $query->orderBy('tanggal_perolehan', 'desc')->paginate(15)->withQueryString();

        $santris = Santri::where('status', 'diterima')->orderBy('nama_lengkap', 'asc')->get();

        $tahunAjaranList = SantriPrestasi::select('tahun_ajaran')
            ->distinct()
            ->orderByDesc('tahun_ajaran')
            ->pluck('tahun_ajaran')
            ->toArray();

        $currentYear = date('n') >= 7 ? date('Y') : date('Y') - 1;
        $defaultTahunAjaran = $currentYear . '/' . ($currentYear + 1);
        if (!in_array($defaultTahunAjaran, $tahunAjaranList)) {
            array_unshift($tahunAjaranList, $defaultTahunAjaran);
        }

        // Statistik
        $totalPrestasi = SantriPrestasi::count();
        $prestasiTahfidz = SantriPrestasi::where('kategori', 'tahfidz')->count();
        $tingkatNasional = SantriPrestasi::whereIn('tingkat', ['nasional', 'internasional'])->count();
        $santriBerprestasiCount = SantriPrestasi::distinct('santri_id')->count('santri_id');

        $kategoriList = SantriPrestasi::getKategoriList();
        $tingkatList = SantriPrestasi::getTingkatList();

        return view('admin.prestasi.index', compact(
            'prestasis',
            'santris',
            'tahunAjaranList',
            'totalPrestasi',
            'prestasiTahfidz',
            'tingkatNasional',
            'santriBerprestasiCount',
            'kategoriList',
            'tingkatList'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'santri_id'         => 'required|exists:santri,id',
            'tahun_ajaran'      => 'required|string|max:20',
            'nama_prestasi'     => 'required|string|max:255',
            'kategori'          => 'required|string|max:50',
            'kategori_lainnya'  => 'nullable|string|max:50',
            'tingkat'           => 'required|string|max:50',
            'peringkat'         => 'required|string|max:50',
            'penyelenggara'     => 'nullable|string|max:200',
            'tanggal_perolehan' => 'nullable|date',
            'deskripsi'         => 'nullable|string|max:1000',
            'foto_piagam'       => 'nullable|image|max:4096',
        ]);

        if ($validated['kategori'] === 'lainnya' && $request->filled('kategori_lainnya')) {
            $validated['kategori'] = trim($request->kategori_lainnya);
        }
        unset($validated['kategori_lainnya']);

        $fotoPath = null;
        if ($request->hasFile('foto_piagam')) {
            $fotoPath = $request->file('foto_piagam')->store('prestasi', 'public');
        }

        $validated['foto_piagam'] = $fotoPath;
        $validated['admin_id']    = auth('admin')->id();

        $prestasi = SantriPrestasi::create($validated);

        AdminActivityLogger::log(
            'create_prestasi',
            "Mencatat prestasi santri: {$prestasi->santri->nama_lengkap} - {$prestasi->nama_prestasi} ({$prestasi->peringkat})"
        );

        return redirect()->route('admin.prestasi.index')
            ->with('success', 'Data prestasi santri berhasil didokumentasikan.');
    }

    public function update(Request $request, $id)
    {
        $prestasi = SantriPrestasi::findOrFail($id);

        $validated = $request->validate([
            'tahun_ajaran'      => 'required|string|max:20',
            'nama_prestasi'     => 'required|string|max:255',
            'kategori'          => 'required|string|max:50',
            'kategori_lainnya'  => 'nullable|string|max:50',
            'tingkat'           => 'required|string|max:50',
            'peringkat'         => 'required|string|max:50',
            'penyelenggara'     => 'nullable|string|max:200',
            'tanggal_perolehan' => 'nullable|date',
            'deskripsi'         => 'nullable|string|max:1000',
            'foto_piagam'       => 'nullable|image|max:4096',
        ]);

        if ($validated['kategori'] === 'lainnya' && $request->filled('kategori_lainnya')) {
            $validated['kategori'] = trim($request->kategori_lainnya);
        }
        unset($validated['kategori_lainnya']);

        if ($request->hasFile('foto_piagam')) {
            if ($prestasi->foto_piagam && Storage::disk('public')->exists($prestasi->foto_piagam)) {
                Storage::disk('public')->delete($prestasi->foto_piagam);
            }
            $validated['foto_piagam'] = $request->file('foto_piagam')->store('prestasi', 'public');
        }

        $prestasi->update($validated);

        AdminActivityLogger::log(
            'update_prestasi',
            "Memperbarui data prestasi santri: {$prestasi->santri->nama_lengkap} - {$prestasi->nama_prestasi}"
        );

        return redirect()->route('admin.prestasi.index')
            ->with('success', 'Data prestasi santri berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $prestasi = SantriPrestasi::findOrFail($id);
        $namaSantri = $prestasi->santri->nama_lengkap ?? 'Santri';

        if ($prestasi->foto_piagam && Storage::disk('public')->exists($prestasi->foto_piagam)) {
            Storage::disk('public')->delete($prestasi->foto_piagam);
        }

        $prestasi->delete();

        AdminActivityLogger::log('delete_prestasi', "Menghapus data prestasi santri: {$namaSantri}");

        return redirect()->route('admin.prestasi.index')
            ->with('success', 'Data prestasi santri berhasil dihapus.');
    }
}
