<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\KegiatanHarian;
use App\Models\Santri;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserKegiatanController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $now = Carbon::now();

        // 1. Ambil tanggal yang dipilih (default = hari ini)
        $inputTanggal = $request->get('tanggal');
        if ($inputTanggal) {
            try {
                $selectedDate = Carbon::parse($inputTanggal)->startOfDay();
            } catch (\Throwable $e) {
                $selectedDate = $today->copy();
            }
        } elseif ($request->filled('hari')) {
            // Kompatibilitas jika mengakses via param hari (misal ?hari=senin)
            $dayKey = strtolower($request->get('hari'));
            $dayOffset = match ($dayKey) {
                'senin'  => 0,
                'selasa' => 1,
                'rabu'   => 2,
                'kamis'  => 3,
                'jumat'  => 4,
                'sabtu'  => 5,
                'minggu' => 6,
                default  => null,
            };
            if ($dayOffset !== null) {
                $selectedDate = $today->copy()->startOfWeek(Carbon::MONDAY)->addDays($dayOffset);
            } else {
                $selectedDate = $today->copy();
            }
        } else {
            $selectedDate = $today->copy();
        }

        // Peta Nama Hari Bahasa Indonesia
        $dayMap = [
            0 => 'minggu',
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
        ];

        $dayNames = [
            'senin'  => 'Senin',
            'selasa' => 'Selasa',
            'rabu'   => 'Rabu',
            'kamis'  => 'Kamis',
            'jumat'  => 'Jum\'at',
            'sabtu'  => 'Sabtu',
            'minggu' => 'Minggu',
        ];

        $selectedDayKey = $dayMap[$selectedDate->dayOfWeek] ?? 'senin';
        $selectedDayLabel = $dayNames[$selectedDayKey] ?? ucfirst($selectedDayKey);

        // Hitung awal pekan (Senin) dari tanggal terpilih
        $startOfWeek = $selectedDate->copy()->startOfWeek(Carbon::MONDAY);
        $prevWeekDate = $selectedDate->copy()->subWeek()->toDateString();
        $nextWeekDate = $selectedDate->copy()->addWeek()->toDateString();
        $todayDateStr = $today->toDateString();

        // 7 Hari dalam sepekan berdasarkan tanggal yang aktif
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $dayCarbon = $startOfWeek->copy()->addDays($i);
            $dKey = $dayMap[$dayCarbon->dayOfWeek];
            $weekDays[] = [
                'key'            => $dKey,
                'name'           => $dayNames[$dKey],
                'date'           => $dayCarbon->toDateString(),
                'formatted'      => $dayCarbon->translatedFormat('d M'),
                'full_formatted' => $dayCarbon->translatedFormat('l, d F Y'),
                'is_selected'    => ($dayCarbon->toDateString() === $selectedDate->toDateString()),
                'is_today'       => ($dayCarbon->toDateString() === $todayDateStr),
            ];
        }

        // Ambil santri user
        $user = auth()->user();
        $santris = collect();
        if ($user) {
            $santris = Santri::where('user_id', $user->id)
                ->where('status', 'diterima')
                ->get();
        }

        // Filter Kelas atau Santri
        $selectedSantriId = $request->get('santri_id');
        $selectedSantri = null;
        $selectedKelas = $request->get('kelas');

        if ($selectedSantriId && $santris->isNotEmpty()) {
            $selectedSantri = $santris->firstWhere('id', $selectedSantriId);
            if ($selectedSantri) {
                $selectedKelas = (string)($selectedSantri->calculated_kelas ?: '1');
            }
        } elseif (!$selectedKelas && $santris->isNotEmpty()) {
            $selectedSantri = $santris->first();
            $selectedSantriId = $selectedSantri->id;
            $selectedKelas = (string)($selectedSantri->calculated_kelas ?: '1');
        }

        // Query Kegiatan Jadwal Rutin 24 Jam untuk hari tersebut ATAU Agenda Khusus tanggal tersebut
        $query = KegiatanHarian::query();

        $query->where(function ($q) use ($selectedDayKey, $selectedDate) {
            $q->where(function ($sub) use ($selectedDayKey) {
                $sub->where('is_rutin_24jam', true)
                    ->where(function ($d) use ($selectedDayKey) {
                        $d->where('hari', $selectedDayKey)
                          ->orWhere('hari', 'semua');
                    });
            })->orWhere(function ($sub) use ($selectedDate) {
                $sub->where('is_rutin_24jam', false)
                    ->whereDate('tanggal_khusus', $selectedDate->toDateString());
            });
        });

        // Filter Kelas
        if ($selectedKelas && in_array($selectedKelas, ['1', '2', '3'])) {
            $query->where(function ($q) use ($selectedKelas) {
                $q->where('kelas', $selectedKelas)
                  ->orWhere('kelas', 'semua');
            });
        }

        $jadwalKegiatan = $query->orderBy('waktu_mulai', 'asc')->get();

        // Cari kegiatan yang sedang berlangsung saat ini jika melihat hari ini
        $currentActivityId = null;
        $isViewingToday = ($selectedDate->toDateString() === $todayDateStr);
        if ($isViewingToday) {
            $activeNow = KegiatanHarian::getCurrentAndNextActivity($selectedKelas, $now);
            if (!empty($activeNow['current'])) {
                $currentActivityId = $activeNow['current']->id;
            }
        }

        // Kegiatan Khusus Mendatang
        $kegiatanMendatang = KegiatanHarian::where('is_rutin_24jam', false)
            ->where('tanggal_khusus', '>=', $todayDateStr)
            ->orderBy('tanggal_khusus')
            ->orderBy('waktu_mulai')
            ->take(8)
            ->get();

        return view('user.kegiatan.index', compact(
            'today',
            'now',
            'selectedDate',
            'selectedDayKey',
            'selectedDayLabel',
            'isViewingToday',
            'weekDays',
            'prevWeekDate',
            'nextWeekDate',
            'todayDateStr',
            'santris',
            'selectedSantri',
            'selectedSantriId',
            'selectedKelas',
            'jadwalKegiatan',
            'currentActivityId',
            'kegiatanMendatang'
        ));
    }
}
