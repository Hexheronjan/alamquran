<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\JadwalTelepon;
use App\Models\Santri;
use Illuminate\Http\Request;

class UserJadwalTeleponController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Ambil semua santri milik wali yang berstatus diterima
        $santris = Santri::where('user_id', $user->id)
            ->where('status', 'diterima')
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        if ($santris->isEmpty() || !\Illuminate\Support\Facades\Schema::hasTable('jadwal_telepons')) {
            return view('user.telepon.index', [
                'santris'          => $santris,
                'selectedSantri'   => $santris->first(),
                'jadwalMendatang'  => collect(),
                'riwayatJadwal'    => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10),
                'jadwalTerdekat'   => null,
            ]);
        }

        // Tentukan santri yang sedang dipilih (default santri pertama jika tidak ada query)
        $selectedSantriId = $request->get('santri_id');
        $selectedSantri = $selectedSantriId
            ? $santris->firstWhere('id', $selectedSantriId)
            : $santris->first();

        if (!$selectedSantri) {
            $selectedSantri = $santris->first();
        }

        $allSantriIds = $santris->pluck('id');
        $targetIds = $selectedSantriId ? [$selectedSantri->id] : $allSantriIds;

        $today = now()->toDateString();
        $currentTime = now()->format('H:i');

        // Jadwal Mendatang: Hari ini (dan jam belum selesai) atau tanggal di masa depan
        $jadwalMendatang = JadwalTelepon::with('santri')
            ->whereIn('santri_id', $targetIds)
            ->where(function ($q) use ($today, $currentTime) {
                $q->whereDate('tanggal_telepon', '>', $today)
                    ->orWhere(function ($sub) use ($today, $currentTime) {
                        $sub->whereDate('tanggal_telepon', $today)
                            ->where('jam_selesai', '>=', $currentTime);
                    });
            })
            ->whereIn('status', [JadwalTelepon::STATUS_MENUNGGU, JadwalTelepon::STATUS_DIJADWALKAN_ULANG])
            ->orderBy('tanggal_telepon', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->get();

        // Jadwal Paling Dekat (untuk Banner Highlight)
        $jadwalTerdekat = $jadwalMendatang->first();

        // Riwayat Jadwal Panggilan (yang sudah lewat atau sudah ada status hasil telepon)
        $riwayatJadwal = JadwalTelepon::with('santri')
            ->whereIn('santri_id', $targetIds)
            ->where(function ($q) use ($today, $currentTime) {
                $q->whereIn('status', [
                    JadwalTelepon::STATUS_TERHUBUNG,
                    JadwalTelepon::STATUS_TIDAK_TERANGKAT,
                    JadwalTelepon::STATUS_DIJADWALKAN_ULANG,
                    JadwalTelepon::STATUS_DIBATALKAN,
                ])->orWhere(function ($sub) use ($today, $currentTime) {
                    $sub->whereDate('tanggal_telepon', '<', $today)
                        ->orWhere(function ($inner) use ($today, $currentTime) {
                            $inner->whereDate('tanggal_telepon', $today)
                                ->where('jam_selesai', '<', $currentTime);
                        });
                });
            })
            ->orderBy('tanggal_telepon', 'desc')
            ->orderBy('jam_mulai', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('user.telepon.index', compact(
            'santris',
            'selectedSantri',
            'jadwalMendatang',
            'riwayatJadwal',
            'jadwalTerdekat'
        ));
    }
}
