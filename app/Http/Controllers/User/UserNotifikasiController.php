<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSantri;
use App\Models\InfaqTransaksi;
use App\Models\PerizinanSantri;
use App\Models\Santri;
use App\Models\SppTagihan;
use App\Models\TahfidzSetoran;
use App\Models\UangSaku;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserNotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $santris = Santri::where('user_id', $user->id)
            ->where('status', 'diterima')
            ->get();

        $santriIds = $santris->pluck('id');

        $notifikasis = collect();

        // 1. SPP belum lunas
        $sppPending = SppTagihan::whereIn('santri_id', $santriIds)
            ->whereIn('status', ['belum_lunas', 'cicilan'])
            ->with('santri')
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->take(10)
            ->get();

        foreach ($sppPending as $spp) {
            $notifikasis->push([
                'tipe'    => 'spp',
                'icon'    => 'ti-receipt',
                'warna'   => 'orange',
                'judul'   => 'Tagihan SPP Belum Lunas',
                'pesan'   => "SPP {$spp->bulan_label} atas nama {$spp->santri->nama_lengkap} belum lunas. Sisa: Rp " . number_format($spp->sisa_tagihan, 0, ',', '.'),
                'tanggal' => $spp->updated_at,
                'link'    => route('user.spp.index'),
            ]);
        }

        // 2. Uang saku: transaksi 7 hari terakhir
        $mutasiTerbaru = UangSaku::whereIn('santri_id', $santriIds)
            ->with('santri')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        foreach ($mutasiTerbaru as $mutasi) {
            $jenis = $mutasi->jenis === 'masuk' ? 'Masuk' : 'Keluar';
            $notifikasis->push([
                'tipe'    => 'uang_saku',
                'icon'    => 'ti-wallet',
                'warna'   => $mutasi->jenis === 'masuk' ? 'green' : 'red',
                'judul'   => "Uang Saku {$jenis}",
                'pesan'   => "Rp " . number_format($mutasi->jumlah, 0, ',', '.') . " - {$mutasi->keterangan} ({$mutasi->santri->nama_lengkap})",
                'tanggal' => $mutasi->created_at,
                'link'    => route('user.uang_saku'),
            ]);
        }

        // 3. Tahfidz: setoran terbaru 7 hari
        $setoranTerbaru = TahfidzSetoran::whereIn('santri_id', $santriIds)
            ->with('santri')
            ->where('tanggal', '>=', Carbon::now()->subDays(7))
            ->orderByDesc('tanggal')
            ->take(5)
            ->get();

        foreach ($setoranTerbaru as $setoran) {
            $notifikasis->push([
                'tipe'    => 'tahfidz',
                'icon'    => 'ti-book',
                'warna'   => 'blue',
                'judul'   => 'Setoran Tahfidz',
                'pesan'   => "{$setoran->santri->nama_lengkap}: {$setoran->nama_surah} Ayat {$setoran->ayat_awal}-{$setoran->ayat_akhir} ({$setoran->jenis_setoran_label}) — {$setoran->kelancaran}",
                'tanggal' => $setoran->tanggal,
                'link'    => route('user.tahfidz'),
            ]);
        }

        // 4. Absensi alpa/sakit dalam 7 hari
        $absensiAlert = AbsensiSantri::whereIn('santri_id', $santriIds)
            ->whereIn('status_kehadiran', ['alpa', 'sakit'])
            ->with('santri')
            ->where('tanggal', '>=', Carbon::now()->subDays(7))
            ->orderByDesc('tanggal')
            ->take(5)
            ->get();

        foreach ($absensiAlert as $abs) {
            $notifikasis->push([
                'tipe'    => 'absensi',
                'icon'    => 'ti-calendar-x',
                'warna'   => $abs->status_kehadiran === 'alpa' ? 'red' : 'yellow',
                'judul'   => 'Info Kehadiran: ' . ucfirst($abs->status_kehadiran),
                'pesan'   => "{$abs->santri->nama_lengkap} — {$abs->tanggal->translatedFormat('d M Y')}" . ($abs->catatan ? ": {$abs->catatan}" : ''),
                'tanggal' => $abs->tanggal,
                'link'    => route('user.absensi'),
            ]);
        }

        // 5. Izin aktif (status keluar)
        $izinAktif = PerizinanSantri::whereIn('santri_id', $santriIds)
            ->where('status', 'keluar')
            ->with('santri')
            ->get();

        foreach ($izinAktif as $izin) {
            $batas = $izin->batas_jam_kembali ? Carbon::parse($izin->batas_jam_kembali) : null;
            $terlambat = $batas && $batas->isPast();
            $notifikasis->push([
                'tipe'    => 'perizinan',
                'icon'    => $terlambat ? 'ti-alert-triangle' : 'ti-door-exit',
                'warna'   => $terlambat ? 'red' : 'orange',
                'judul'   => $terlambat ? '⚠️ Terlambat Kembali!' : 'Izin Aktif',
                'pesan'   => "{$izin->santri->nama_lengkap} sedang di luar." . ($batas ? " Batas kembali: " . $batas->format('d M Y H:i') : ''),
                'tanggal' => $izin->waktu_keluar,
                'link'    => route('user.perizinan'),
            ]);
        }

        // 6. Infaq: donasi menunggu konfirmasi
        $infaqPending = InfaqTransaksi::where('user_id', $user->id)
            ->where('status', 'menunggu_konfirmasi')
            ->with('program')
            ->take(5)
            ->get();

        foreach ($infaqPending as $infaq) {
            $notifikasis->push([
                'tipe'    => 'infaq',
                'icon'    => 'ti-heart',
                'warna'   => 'purple',
                'judul'   => 'Donasi Menunggu Konfirmasi',
                'pesan'   => "Donasi Rp " . number_format($infaq->nominal, 0, ',', '.') . " ke {$infaq->program->nama_program} menunggu konfirmasi admin.",
                'tanggal' => $infaq->created_at,
                'link'    => route('user.infaq'),
            ]);
        }

        // Urutkan semua notifikasi dari terbaru
        $notifikasis = $notifikasis->sortByDesc('tanggal')->values();

        return view('user.notifikasi.index', compact('notifikasis', 'santris'));
    }
}
