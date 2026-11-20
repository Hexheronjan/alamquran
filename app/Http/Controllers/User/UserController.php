<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSantri;
use App\Models\Angkatan;
use App\Models\FormGroup;
use App\Models\InfaqTransaksi;
use App\Models\KegiatanHarian;
use App\Models\PerizinanSantri;
use App\Models\Santri;
use App\Models\School;
use App\Models\SppTagihan;
use App\Models\TahfidzSetoran;
use Carbon\Carbon;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        // Ambil sekolah dengan ID tertentu, atau buat default jika tidak ada
        $schools = School::find(1) ?? new School(['name' => 'Pesantren Tahfizh Alam Qur\'an']);

        // Ambil semua form group beserta atributnya
        $formGroup = FormGroup::select('id_form', 'name', 'description')->where('is_active', true)->get();

        // Cari angkatan aktif saat ini
        $angkatanAktif = Angkatan::open()->first();

        // Cari angkatan mendatang terdekat jika tidak ada yang aktif
        $angkatanMendatang = null;
        if (!$angkatanAktif) {
            $angkatanMendatang = Angkatan::where('is_active', true)
                ->where('tanggal_mulai_daftar', '>', now())
                ->orderBy('tanggal_mulai_daftar', 'asc')
                ->first();
        }

        // ── Ringkasan untuk dashboard ──────────────────────────────────────────
        $santris = Santri::where('user_id', $user->id)
            ->where('status', 'diterima')
            ->get();

        $santriIds = $santris->pluck('id');
        $dashboardSummary = null;

        if ($santriIds->isNotEmpty()) {
            // Saldo total uang saku semua anak
            $totalSaldo = $santris->sum(fn($s) => $s->saldo_uang_saku);

            // SPP pending
            $sppPendingCount = SppTagihan::whereIn('santri_id', $santriIds)
                ->whereIn('status', ['belum_lunas', 'cicilan'])
                ->count();

            // Izin aktif
            $izinAktif = PerizinanSantri::whereIn('santri_id', $santriIds)
                ->where('status', 'keluar')
                ->with('santri')
                ->first();

            // Setoran tahfidz bulan ini
            $setoranBulanIni = TahfidzSetoran::whereIn('santri_id', $santriIds)
                ->whereMonth('tanggal', now()->month)
                ->whereYear('tanggal', now()->year)
                ->count();

            // Absensi hari ini
            $absensiHariIni = AbsensiSantri::whereIn('santri_id', $santriIds)
                ->whereDate('tanggal', today())
                ->get();

            // Infaq menunggu konfirmasi
            $infaqPendingCount = InfaqTransaksi::where('user_id', $user->id)
                ->where('status', 'menunggu_konfirmasi')
                ->count();

            // Jadwal sambungan telepon santri terdekat
            $jadwalTeleponMendatang = null;
            if (\Illuminate\Support\Facades\Schema::hasTable('jadwal_telepons')) {
                try {
                    $jadwalTeleponMendatang = \App\Models\JadwalTelepon::with('santri')
                        ->whereIn('santri_id', $santriIds)
                        ->where('status', \App\Models\JadwalTelepon::STATUS_MENUNGGU)
                        ->where(function ($q) {
                            $q->whereDate('tanggal_telepon', '>', now()->toDateString())
                                ->orWhere(function ($sub) {
                                    $sub->whereDate('tanggal_telepon', now()->toDateString())
                                        ->where('jam_selesai', '>=', now()->format('H:i'));
                                });
                        })
                        ->orderBy('tanggal_telepon', 'asc')
                        ->orderBy('jam_mulai', 'asc')
                        ->first();
                } catch (\Throwable $e) {
                    $jadwalTeleponMendatang = null;
                }
            }

            $dashboardSummary = compact(
                'totalSaldo', 'sppPendingCount', 'izinAktif',
                'setoranBulanIni', 'absensiHariIni', 'infaqPendingCount',
                'jadwalTeleponMendatang'
            );
        }

        return view('user.dashboard', compact(
            'formGroup', 'schools', 'angkatanAktif', 'angkatanMendatang',
            'santris', 'dashboardSummary'
        ));
    }
}
