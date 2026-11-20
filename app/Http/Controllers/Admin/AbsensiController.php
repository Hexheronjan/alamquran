<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSantri;
use App\Models\AbsensiGuru;
use App\Models\Admin;
use App\Models\Santri;
use App\Services\AdminActivityLogger;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->tanggal ? Carbon::parse($request->tanggal) : Carbon::today();
        $kelas = $request->kelas ?? 'all';

        // Absensi santri per tanggal & kelas
        $query = AbsensiSantri::with(['santri', 'guru'])
            ->whereDate('tanggal', $tanggal);

        if ($kelas !== 'all') {
            $query->where('kelas', $kelas);
        }

        $absensiSantri = $query->latest()->paginate(20, ['*'], 'santri_page');

        // Absensi guru/ustadz
        $absensiGuru = AbsensiGuru::with('admin')
            ->whereDate('tanggal', $tanggal)
            ->latest()
            ->get();

        // Statistik hari ini
        $stats = [
            'hadir'  => AbsensiSantri::whereDate('tanggal', $tanggal)->where('status_kehadiran', 'hadir')->count(),
            'sakit'  => AbsensiSantri::whereDate('tanggal', $tanggal)->where('status_kehadiran', 'sakit')->count(),
            'izin'   => AbsensiSantri::whereDate('tanggal', $tanggal)->where('status_kehadiran', 'izin')->count(),
            'alpa'   => AbsensiSantri::whereDate('tanggal', $tanggal)->where('status_kehadiran', 'alpa')->count(),
            'guru_hadir' => AbsensiGuru::whereDate('tanggal', $tanggal)->where('status_kehadiran', 'hadir')->count(),
        ];

        // Sync kelas otomatis dibatasi cache harian agar tidak membebani query update setiap page load
        \Illuminate\Support\Facades\Cache::remember('santri_classes_synced_' . date('Y-m-d'), 86400, function () {
            Santri::syncClassesForActiveYear();
            return true;
        });

        // Daftar santri aktif untuk input absensi (Hanya Santri DITERIMA, Aktif & Hasil kalkulasi kelas 1-3)
        $santriAktif = Santri::aktif()
            ->orderBy('kelas')
            ->orderBy('nama_lengkap')
            ->get();

        // Daftar admin/guru
        $guruList = Admin::where('is_active', true)->orderBy('name')->get();

        return view('admin.absensi.index', compact(
            'absensiSantri', 'absensiGuru', 'stats', 'santriAktif',
            'guruList', 'tanggal', 'kelas'
        ));
    }

    public function storeSantri(Request $request)
    {
        $request->validate([
            'tanggal'           => 'required|date',
            'kelas'             => 'required|string',
            'absensi'           => 'required|array',
            'absensi.*.santri_id' => 'required|exists:santri,id',
            'absensi.*.status'    => 'required|in:hadir,sakit,izin,alpa',
        ]);

        $tanggal = $request->tanggal;
        $kelas   = $request->kelas;
        $adminId = auth('admin')->id();
        $count   = 0;

        foreach ($request->absensi as $item) {
            $absensi = AbsensiSantri::updateOrCreate(
                ['santri_id' => $item['santri_id'], 'tanggal' => $tanggal],
                [
                    'kelas'             => $kelas,
                    'status_kehadiran'  => $item['status'],
                    'catatan'           => $item['catatan'] ?? null,
                    'guru_id'           => $adminId,
                ]
            );
            if ($item['status'] !== 'hadir') {
                $santri = Santri::with('orangTua')->find($item['santri_id']);
                NotificationService::sendAbsensi(
                    $santri,
                    $item['status'],
                    $item['catatan'] ?? null,
                    (string) $absensi->id
                );
            }
            $count++;
        }

        AdminActivityLogger::log('input_absensi_santri', "Input absensi santri kelas {$kelas} tanggal {$tanggal}: {$count} data");

        return back()->with('success', "Absensi {$count} santri kelas {$kelas} berhasil disimpan.");
    }

    public function storeGuru(Request $request)
    {
        $request->validate([
            'admin_id'         => 'required|exists:admins,id',
            'tanggal'          => 'required|date',
            'jam_masuk'        => 'nullable|date_format:H:i',
            'jam_pulang'       => 'nullable|date_format:H:i',
            'status_kehadiran' => 'required|in:hadir,sakit,izin,alpa',
            'catatan'          => 'nullable|string',
        ]);

        AbsensiGuru::updateOrCreate(
            ['admin_id' => $request->admin_id, 'tanggal' => $request->tanggal],
            [
                'jam_masuk'        => $request->jam_masuk,
                'jam_pulang'       => $request->jam_pulang,
                'status_kehadiran' => $request->status_kehadiran,
                'catatan'          => $request->catatan,
            ]
        );

        AdminActivityLogger::log('input_absensi_guru', "Input absensi guru ID {$request->admin_id} tanggal {$request->tanggal}");

        return back()->with('success', 'Absensi guru berhasil disimpan.');
    }

    public function rekap(Request $request)
    {
        $bulan = $request->bulan ?? Carbon::now()->month;
        $tahun = $request->tahun ?? Carbon::now()->year;
        $kelas = $request->kelas ?? '1';

        // Sync kelas santri otomatis
        Santri::syncClassesForActiveYear();

        // Santri di kelas yang dipilih (Hanya Santri DITERIMA & Aktif)
        $santriList = Santri::aktif()
            ->where(function($q) use ($kelas) {
                $q->where('kelas', $kelas)
                  ->orWhere('kelas', 'Kelas ' . $kelas)
                  ->orWhere('kelas', 'kelas ' . $kelas);
            })
            ->orderBy('nama_lengkap')
            ->get();

        // Tanggal dalam bulan
        $daysInMonth = Carbon::create($tahun, $bulan, 1)->daysInMonth;
        $dates = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dates[] = Carbon::create($tahun, $bulan, $d)->format('Y-m-d');
        }

        // Data absensi bulan ini
        $absensiData = AbsensiSantri::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where(function($q) use ($kelas) {
                $q->where('kelas', $kelas)
                  ->orWhere('kelas', 'Kelas ' . $kelas)
                  ->orWhere('kelas', 'kelas ' . $kelas);
            })
            ->get()
            ->groupBy('santri_id');

        $kelasOptions = ['1', '2', '3'];
        $bulanOptions = [];
        for ($m = 1; $m <= 12; $m++) {
            $bulanOptions[$m] = Carbon::create(null, $m, 1)->translatedFormat('F');
        }

        return view('admin.absensi.rekap', compact(
            'santriList', 'dates', 'absensiData', 'bulan', 'tahun',
            'kelas', 'kelasOptions', 'bulanOptions', 'daysInMonth'
        ));
    }

    public function inputHarian(Request $request)
    {
        $tanggal = $request->tanggal ?? Carbon::today()->format('Y-m-d');
        $kelas   = $request->kelas ?? '1';

        // Sync kelas santri otomatis
        Santri::syncClassesForActiveYear();

        // Santri di kelas yang dipilih (Hanya Santri DITERIMA & Aktif)
        $santriList = Santri::aktif()
            ->where(function($q) use ($kelas) {
                $q->where('kelas', $kelas)
                  ->orWhere('kelas', 'Kelas ' . $kelas)
                  ->orWhere('kelas', 'kelas ' . $kelas);
            })
            ->orderBy('nama_lengkap')
            ->get();

        // Cek apakah sudah ada absensi hari ini (ambil model lengkap agar view bisa akses status_kehadiran dan catatan)
        $existingAbsensi = AbsensiSantri::whereDate('tanggal', $tanggal)
            ->where(function($q) use ($kelas) {
                $q->where('kelas', $kelas)
                  ->orWhere('kelas', 'Kelas ' . $kelas);
            })
            ->get()
            ->keyBy('santri_id');

        return view('admin.absensi.input', compact('santriList', 'tanggal', 'kelas', 'existingAbsensi'));
    }
}
