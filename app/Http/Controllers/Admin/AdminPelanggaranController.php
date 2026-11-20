<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PelanggaranSantri;
use App\Models\Santri;
use App\Services\AdminActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminPelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $query = PelanggaranSantri::with(['santri', 'admin']);

        if ($request->filled('santri_id')) {
            $query->where('santri_id', $request->santri_id);
        }

        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status_pembinaan', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_pelanggaran', 'like', "%{$search}%")
                  ->orWhere('konsekuensi', 'like', "%{$search}%")
                  ->orWhere('petugas_ustadz', 'like', "%{$search}%")
                  ->orWhereHas('santri', function ($sq) use ($search) {
                      $sq->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('nis', 'like', "%{$search}%");
                  });
            });
        }

        $pelanggarans = $query->orderBy('tanggal', 'desc')->paginate(15)->withQueryString();

        $santris = Santri::where('status', 'diterima')->orderBy('nama_lengkap', 'asc')->get();

        // Statistik
        $totalPelanggaran = PelanggaranSantri::count();
        $sedangDijalani = PelanggaranSantri::where('status_pembinaan', 'sedang_dijalani')->count();
        $selesaiPembinaan = PelanggaranSantri::where('status_pembinaan', 'selesai')->count();
        $sanksiBeratCount = PelanggaranSantri::where('kategori', 'berat')->count();

        $kategoriList = PelanggaranSantri::getKategoriList();
        $konsekuensiPresets = PelanggaranSantri::getKonsekuensiPresets();

        return view('admin.pelanggaran.index', compact(
            'pelanggarans',
            'santris',
            'totalPelanggaran',
            'sedangDijalani',
            'selesaiPembinaan',
            'sanksiBeratCount',
            'kategoriList',
            'konsekuensiPresets'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'santri_id'        => 'required|exists:santri,id',
            'tanggal'          => 'required|date',
            'kategori'         => 'required|in:ringan,sedang,berat',
            'nama_pelanggaran' => 'required|string|max:255',
            'konsekuensi'      => 'required|string|max:255',
            'status_pembinaan' => 'required|in:sedang_dijalani,selesai,dibatalkan',
            'poin'             => 'nullable|integer|min:0',
            'petugas_ustadz'   => 'nullable|string|max:150',
            'catatan'          => 'nullable|string|max:1000',
            'foto_bukti'       => 'nullable|image|max:3072',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto_bukti')) {
            $fotoPath = $request->file('foto_bukti')->store('pelanggaran', 'public');
        }

        $validated['foto_bukti'] = $fotoPath;
        $validated['poin'] = $validated['poin'] ?? 0;
        $validated['admin_id'] = auth('admin')->id();

        $pelanggaran = PelanggaranSantri::create($validated);

        AdminActivityLogger::log(
            'create_pelanggaran',
            "Mencatat pelanggaran santri: {$pelanggaran->santri->nama_lengkap} ({$pelanggaran->nama_pelanggaran} - Sanksi: {$pelanggaran->konsekuensi})"
        );

        return redirect()->route('admin.pelanggaran.index')
            ->with('success', 'Catatan pelanggaran dan konsekuensi santri berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $pelanggaran = PelanggaranSantri::findOrFail($id);

        $validated = $request->validate([
            'tanggal'          => 'required|date',
            'kategori'         => 'required|in:ringan,sedang,berat',
            'nama_pelanggaran' => 'required|string|max:255',
            'konsekuensi'      => 'required|string|max:255',
            'status_pembinaan' => 'required|in:sedang_dijalani,selesai,dibatalkan',
            'poin'             => 'nullable|integer|min:0',
            'petugas_ustadz'   => 'nullable|string|max:150',
            'catatan'          => 'nullable|string|max:1000',
            'foto_bukti'       => 'nullable|image|max:3072',
        ]);

        if ($request->hasFile('foto_bukti')) {
            if ($pelanggaran->foto_bukti && Storage::disk('public')->exists($pelanggaran->foto_bukti)) {
                Storage::disk('public')->delete($pelanggaran->foto_bukti);
            }
            $validated['foto_bukti'] = $request->file('foto_bukti')->store('pelanggaran', 'public');
        }

        $validated['poin'] = $validated['poin'] ?? 0;

        $pelanggaran->update($validated);

        AdminActivityLogger::log(
            'update_pelanggaran',
            "Memperbarui catatan pelanggaran santri: {$pelanggaran->santri->nama_lengkap}"
        );

        return redirect()->route('admin.pelanggaran.index')
            ->with('success', 'Catatan pelanggaran berhasil diperbarui.');
    }

    public function updateStatus(Request $request, $id)
    {
        $pelanggaran = PelanggaranSantri::findOrFail($id);

        $validated = $request->validate([
            'status_pembinaan' => 'required|in:sedang_dijalani,selesai,dibatalkan',
        ]);

        $pelanggaran->update($validated);

        return redirect()->back()
            ->with('success', "Status pembinaan berhasil diubah menjadi: " . ucfirst(str_replace('_', ' ', $validated['status_pembinaan'])));
    }

    public function destroy($id)
    {
        $pelanggaran = PelanggaranSantri::findOrFail($id);
        $namaSantri = $pelanggaran->santri->nama_lengkap ?? 'Santri';

        if ($pelanggaran->foto_bukti && Storage::disk('public')->exists($pelanggaran->foto_bukti)) {
            Storage::disk('public')->delete($pelanggaran->foto_bukti);
        }

        $pelanggaran->delete();

        AdminActivityLogger::log('delete_pelanggaran', "Menghapus catatan pelanggaran santri: {$namaSantri}");

        return redirect()->route('admin.pelanggaran.index')
            ->with('success', 'Catatan pelanggaran santri berhasil dihapus.');
    }
}
