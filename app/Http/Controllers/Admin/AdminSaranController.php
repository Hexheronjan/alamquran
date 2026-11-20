<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaranMasukan;
use App\Services\AdminActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminSaranController extends Controller
{
    public function index(Request $request)
    {
        $query = SaranMasukan::with(['user', 'admin', 'responder']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('pesan', 'like', "%{$search}%")
                  ->orWhere('nama_pengirim', 'like', "%{$search}%");
            });
        }

        $sarans = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $totalSaran = SaranMasukan::count();
        $menungguReview = SaranMasukan::where('status', 'menunggu_review')->count();
        $sedangDiproses = SaranMasukan::where('status', 'sedang_diproses')->count();
        $diterapkan = SaranMasukan::where('status', 'diterapkan')->count();

        $kategoriList = SaranMasukan::getKategoriList();
        $statusList = SaranMasukan::getStatusList();

        return view('admin.saran.index', compact(
            'sarans',
            'totalSaran',
            'menungguReview',
            'sedangDiproses',
            'diterapkan',
            'kategoriList',
            'statusList'
        ));
    }

    public function update(Request $request, $id)
    {
        $saran = SaranMasukan::findOrFail($id);

        $validated = $request->validate([
            'status'          => 'required|in:menunggu_review,sedang_diproses,diterapkan,diarsipkan',
            'tanggapan_admin' => 'nullable|string|max:1000',
        ]);

        $saran->update([
            'status'                => $validated['status'],
            'tanggapan_admin'       => $validated['tanggapan_admin'] ?? $saran->tanggapan_admin,
            'responded_by_admin_id' => auth('admin')->id(),
            'responded_at'          => now(),
        ]);

        AdminActivityLogger::log(
            'update_saran',
            "Memperbarui status saran/masukan: \"{$saran->judul}\" menjadi {$validated['status']}"
        );

        return redirect()->route('admin.saran.index')
            ->with('success', 'Tanggapan dan status saran masukan berhasil diperbarui.');
    }

    public function updateStatus(Request $request, $id)
    {
        return $this->update($request, $id);
    }

    public function destroy($id)
    {
        $saran = SaranMasukan::findOrFail($id);

        if ($saran->lampiran && Storage::disk('public')->exists($saran->lampiran)) {
            Storage::disk('public')->delete($saran->lampiran);
        }

        $saran->delete();

        AdminActivityLogger::log('delete_saran', "Menghapus saran/masukan ID: {$id}");

        return redirect()->route('admin.saran.index')
            ->with('success', 'Saran masukan berhasil dihapus.');
    }
}
