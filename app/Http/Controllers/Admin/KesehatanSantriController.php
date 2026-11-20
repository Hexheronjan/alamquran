<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSantri;
use App\Models\Santri;
use App\Models\SantriKesehatan;
use App\Services\AdminActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class KesehatanSantriController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'semua');
        $search = $request->get('search', '');

        $query = SantriKesehatan::with(['santri', 'petugas'])->latest('tanggal');

        if ($status !== 'semua') {
            $query->where('status_kesehatan', $status);
        }

        if ($search) {
            $query->whereHas('santri', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            })->orWhere('keluhan_sakit', 'like', "%{$search}%");
        }

        $catatanKesehatan = $query->paginate(20)->withQueryString();

        // Sync kelas santri & santri aktif untuk dropdown pilihan
        Santri::syncClassesForActiveYear();
        $santriList = Santri::aktif()
            ->orderBy('kelas')
            ->orderBy('nama_lengkap')
            ->get();

        // Counter statistik
        $totalSemua     = SantriKesehatan::count();
        $totalUks       = SantriKesehatan::where('status_kesehatan', 'istirahat_uks')->count();
        $totalPantauan  = SantriKesehatan::where('status_kesehatan', 'pantauan')->count();
        $totalRawatInap = SantriKesehatan::where('status_kesehatan', 'rawat_inap')->count();
        $totalDirumah   = SantriKesehatan::where('status_kesehatan', 'istirahat_dirumah')->count();
        $totalSembuh    = SantriKesehatan::where('status_kesehatan', 'sembuh')->count();

        // Santri yang sedang sakit >= 3 hari dan belum istirahat di rumah / belum sembuh
        $tigaHariLalu = now()->subDays(2)->startOfDay();
        $santriPerluJemput = SantriKesehatan::with(['santri.orangTua'])
            ->whereIn('status_kesehatan', ['istirahat_uks', 'rawat_inap', 'pantauan'])
            ->where('tanggal', '<=', $tigaHariLalu)
            ->latest('tanggal')
            ->get();

        return view('admin.kesehatan.index', compact(
            'catatanKesehatan', 'santriList', 'status', 'search',
            'totalSemua', 'totalUks', 'totalPantauan', 'totalRawatInap', 'totalDirumah', 'totalSembuh',
            'santriPerluJemput'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'santri_id'          => 'required|exists:santri,id',
            'tanggal'            => 'required|date',
            'keluhan_sakit'      => 'required|string|max:255',
            'tindakan_perawatan' => 'nullable|string',
            'obat_diberikan'     => 'nullable|string',
            'status_kesehatan'   => 'required|in:istirahat_uks,rawat_inap,sembuh,pantauan,istirahat_dirumah',
        ]);

        $catatan = SantriKesehatan::create([
            'santri_id'          => $request->santri_id,
            'tanggal'            => $request->tanggal,
            'keluhan_sakit'      => $request->keluhan_sakit,
            'tindakan_perawatan' => $request->tindakan_perawatan,
            'obat_diberikan'     => $request->obat_diberikan,
            'status_kesehatan'   => $request->status_kesehatan,
            'petugas_id'         => auth('admin')->id(),
        ]);

        $this->syncPresensiFromUks($request->santri_id, $request->tanggal, $request->status_kesehatan, $request->keluhan_sakit);

        $santri = Santri::with('orangTua')->find($request->santri_id);
        AdminActivityLogger::log('catat_kesehatan_santri', "Catat kesehatan santri '{$santri?->nama_lengkap}': {$request->keluhan_sakit}");
        NotificationService::sendSantriSakit(
            $santri,
            ucfirst(str_replace('_', ' ', $catatan->status_kesehatan)) . ': ' . $catatan->keluhan_sakit,
            $catatan->tindakan_perawatan,
            $catatan->obat_diberikan,
            (string) $catatan->id
        );

        return back()->with('success', "Catatan kesehatan untuk '{$santri?->nama_lengkap}' berhasil disimpan dan otomatis disinkronkan ke Presensi.");
    }

    public function update(Request $request, $id)
    {
        $catatan = SantriKesehatan::findOrFail($id);

        $request->validate([
            'santri_id'          => 'required|exists:santri,id',
            'tanggal'            => 'required|date',
            'keluhan_sakit'      => 'required|string|max:255',
            'tindakan_perawatan' => 'nullable|string',
            'obat_diberikan'     => 'nullable|string',
            'status_kesehatan'   => 'required|in:istirahat_uks,rawat_inap,sembuh,pantauan,istirahat_dirumah',
        ]);

        $catatan->update([
            'santri_id'          => $request->santri_id,
            'tanggal'            => $request->tanggal,
            'keluhan_sakit'      => $request->keluhan_sakit,
            'tindakan_perawatan' => $request->tindakan_perawatan,
            'obat_diberikan'     => $request->obat_diberikan,
            'status_kesehatan'   => $request->status_kesehatan,
        ]);

        $this->syncPresensiFromUks($request->santri_id, $request->tanggal, $request->status_kesehatan, $request->keluhan_sakit, $catatan->nama_penjemput);

        AdminActivityLogger::log('update_kesehatan_santri', "Update catatan kesehatan ID {$id}");

        return back()->with('success', "Catatan kesehatan dan sinkronisasi presensi berhasil diperbarui.");
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_kesehatan' => 'required|in:istirahat_uks,rawat_inap,sembuh,pantauan,istirahat_dirumah',
        ]);

        $catatan = SantriKesehatan::findOrFail($id);
        $catatan->update(['status_kesehatan' => $request->status_kesehatan]);

        $tanggalStr = $catatan->tanggal ? $catatan->tanggal->format('Y-m-d') : date('Y-m-d');
        $this->syncPresensiFromUks($catatan->santri_id, $tanggalStr, $request->status_kesehatan, $catatan->keluhan_sakit, $catatan->nama_penjemput);

        return back()->with('success', "Status kesehatan santri berhasil diubah menjadi: " . ucfirst(str_replace('_', ' ', $request->status_kesehatan)) . " dan presensi disesuaikan.");
    }

    public function jemputSantri(Request $request, $id)
    {
        $catatan = SantriKesehatan::with('santri.orangTua')->findOrFail($id);

        $request->validate([
            'tanggal_jemput'      => 'required|date',
            'nama_penjemput'      => 'required|string|max:255',
            'estimasi_kembali'    => 'nullable|date',
            'catatan_penjemputan' => 'nullable|string|max:500',
            'kirim_wa'            => 'nullable',
        ]);

        $catatan->update([
            'status_kesehatan'    => 'istirahat_dirumah',
            'tanggal_jemput'      => $request->tanggal_jemput,
            'nama_penjemput'      => $request->nama_penjemput,
            'estimasi_kembali'    => $request->estimasi_kembali,
            'catatan_penjemputan' => $request->catatan_penjemputan,
        ]);

        $tanggalStr = $catatan->tanggal ? $catatan->tanggal->format('Y-m-d') : date('Y-m-d');
        $this->syncPresensiFromUks(
            $catatan->santri_id,
            $tanggalStr,
            'istirahat_dirumah',
            $catatan->keluhan_sakit,
            $catatan->nama_penjemput
        );

        AdminActivityLogger::log('jemput_santri_sakit', "Santri '{$catatan->santri?->nama_lengkap}' dijemput untuk istirahat di rumah oleh '{$catatan->nama_penjemput}'.");

        if ($request->has('kirim_wa') && $request->kirim_wa) {
            NotificationService::sendJemputSantri($catatan->santri, $catatan, $catatan->lama_sakit);
        }

        return back()->with('success', "Santri '{$catatan->santri?->nama_lengkap}' berhasil diset Istirahat di Rumah (Dijemput oleh {$catatan->nama_penjemput}). Presensi telah otomatis diperbarui.");
    }

    public function destroy($id)
    {
        $catatan = SantriKesehatan::findOrFail($id);
        $santriId = $catatan->santri_id;
        $tanggal = $catatan->tanggal ? $catatan->tanggal->format('Y-m-d') : null;

        $catatan->delete();

        if ($santriId && $tanggal) {
            $absensi = AbsensiSantri::where('santri_id', $santriId)
                ->whereDate('tanggal', $tanggal)
                ->first();

            if ($absensi && str_contains((string) $absensi->catatan, '[UKS]')) {
                $absensi->delete();
            }
        }

        AdminActivityLogger::log('hapus_kesehatan_santri', "Hapus catatan kesehatan ID {$id}");

        return back()->with('success', "Catatan kesehatan berhasil dihapus dan presensi terkait disesuaikan.");
    }

    /**
     * Sinkronkan status Sakit dari UKS ke Presensi Santri (ID Santri + Tanggal)
     */
    private function syncPresensiFromUks($santriId, $tanggal, string $statusKesehatan, string $keluhanSakit = '', ?string $penjemput = null): void
    {
        $santri = Santri::find($santriId);
        if (!$santri) {
            return;
        }

        $tanggalStr = \Carbon\Carbon::parse($tanggal)->format('Y-m-d');
        $kelas = (string) ($santri->calculated_kelas ?? $santri->kelas ?? '1');

        if (in_array($statusKesehatan, ['istirahat_uks', 'rawat_inap', 'pantauan', 'istirahat_dirumah'], true)) {
            $existing = AbsensiSantri::where('santri_id', $santriId)
                ->whereDate('tanggal', $tanggalStr)
                ->first();

            $catatanText = $statusKesehatan === 'istirahat_dirumah'
                ? '[UKS] Istirahat di Rumah (Dijemput: ' . ($penjemput ?: 'Wali') . ')' . ($keluhanSakit ? ': ' . $keluhanSakit : '')
                : '[UKS] Status ' . ucfirst(str_replace('_', ' ', $statusKesehatan)) . ($keluhanSakit ? ': ' . $keluhanSakit : '');

            // Jangan menimpa jika presensi manual sudah diset sebagai 'izin'
            if (!$existing || $existing->status_kehadiran !== 'izin') {
                AbsensiSantri::updateOrCreate(
                    [
                        'santri_id' => $santriId,
                        'tanggal'   => $tanggalStr,
                    ],
                    [
                        'kelas'            => $kelas,
                        'status_kehadiran' => 'sakit',
                        'catatan'          => $catatanText,
                        'guru_id'          => auth('admin')->id(),
                    ]
                );
            }
        } elseif ($statusKesehatan === 'sembuh') {
            $existing = AbsensiSantri::where('santri_id', $santriId)
                ->whereDate('tanggal', $tanggalStr)
                ->first();

            if ($existing && $existing->status_kehadiran === 'sakit' && str_contains((string) $existing->catatan, '[UKS]')) {
                $existing->update([
                    'status_kehadiran' => 'hadir',
                    'catatan'          => '[UKS] Sudah sembuh',
                ]);
            }
        }
    }
}
