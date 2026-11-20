<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\User;
use App\Models\Angkatan;
use App\Services\AdminActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    public function index()
    {
        $totalWali    = User::whereHas('santri', fn($q) => $q->where('status_santri', 'aktif'))->count();
        $totalSantri  = Santri::where('status', 'diterima')->where('status_santri', 'aktif')->count();
        $kelasOptions = ['1', '2', '3'];

        $angkatanOptions = Angkatan::orderByDesc('tahun_angkatan')->get();
        return view('admin.broadcast.index', compact('totalWali', 'totalSantri', 'kelasOptions', 'angkatanOptions'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'target'  => 'required|in:semua_wali,per_kelas,per_angkatan,alumni',
            'kelas'   => 'nullable|string',
            'angkatan_id' => 'nullable|required_if:target,per_angkatan|exists:angkatan,id',
            'pesan'   => 'required|string|min:10|max:2000',
        ]);

        $pesan  = $request->pesan;
        $target = $request->target;

        // Bangun query penerima
        $query = User::whereHas('santri', fn($q) => $q->where('status_santri', 'aktif')->where('status', 'diterima'));

        if ($target === 'per_kelas' && $request->kelas) {
            $query->whereHas('santri', fn($q) => $q->where('kelas', $request->kelas));
        }
        if ($target === 'per_angkatan') {
            $query->whereHas('santri', fn($q) => $q->where('angkatan_id', $request->angkatan_id));
        }
        if ($target === 'alumni') {
            $query = User::whereHas('santri', fn($q) => $q->where('status_santri', 'lulus'));
        }

        $penerima = $query->whereNotNull('no_wa')->get();

        if ($penerima->isEmpty()) {
            return back()->with('error', 'Tidak ada penerima yang ditemukan dengan kriteria ini.');
        }

        $berhasil = 0;
        $gagal    = 0;

        foreach ($penerima as $user) {
            try {
                $pesanPersonal = str_replace(
                    ['{nama_wali}', '{nama_santri}'],
                    [$user->name, $user->santri->first()?->nama_lengkap ?? '-'],
                    $pesan
                );
                $message = NotificationService::send([
                    'recipient_number' => $user->no_wa,
                    'recipient_name' => $user->name,
                    'user_id' => $user->id,
                    'santri_id' => $user->santri->first()?->id,
                    'modul' => 'Broadcast',
                    'message' => $pesanPersonal,
                    'reference_id' => 'BROADCAST-' . now()->format('YmdHis') . '-' . $user->id,
                ]);
                $message ? $berhasil++ : $gagal++;
            } catch (\Exception $e) {
                $gagal++;
            }
        }

        $targetLabel = match ($target) {
            'per_kelas'     => "Kelas {$request->kelas}",
            'per_angkatan'  => 'Angkatan ' . optional(Angkatan::find($request->angkatan_id))->tahun_angkatan,
            'alumni'        => 'Alumni',
            default         => 'Semua Wali',
        };

        AdminActivityLogger::log('broadcast_wa', "Broadcast WA ke {$targetLabel}: {$berhasil} berhasil, {$gagal} gagal");

        return back()->with('success', "Broadcast selesai! ✅ {$berhasil} pesan terkirim, ❌ {$gagal} gagal dari total " . $penerima->count() . " penerima.");
    }

    public function previewPenerima(Request $request)
    {
        $target = $request->target;
        $kelas  = $request->kelas;

        $query = User::with(['santri' => fn($q) => $q->where('status_santri', 'aktif')])
            ->whereHas('santri', fn($q) => $q->where('status_santri', 'aktif')->where('status', 'diterima'));

        if ($target === 'per_kelas' && $kelas) {
            $query->whereHas('santri', fn($q) => $q->where('kelas', $kelas));
        }
        if ($target === 'per_angkatan' && $request->filled('angkatan_id')) {
            $query->whereHas('santri', fn($q) => $q->where('angkatan_id', $request->angkatan_id));
        }
        if ($target === 'alumni') {
            $query = User::with(['santri' => fn($q) => $q->where('status_santri', 'lulus')])
                ->whereHas('santri', fn($q) => $q->where('status_santri', 'lulus'));
        }

        $total = (clone $query)->count();
        $penerima = $query->select('id', 'name', 'no_wa')->take(20)->get();

        return response()->json([
            'count'   => $total,
            'samples' => $penerima->map(fn (User $user) => $user->name),
        ]);
    }
}
