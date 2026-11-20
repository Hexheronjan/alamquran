<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KalenderAkademik;
use App\Services\AdminActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminKalenderController extends Controller
{
    public function index(Request $request)
    {
        // Tahun Ajaran aktif (default misal 2026/2027)
        $currentYear = date('n') >= 7 ? date('Y') : date('Y') - 1;
        $defaultTahunAjaran = $currentYear . '/' . ($currentYear + 1);

        $selectedTahunAjaran = $request->get('tahun_ajaran', $defaultTahunAjaran);

        // Ambil data Hari Libur Nasional & Peringatan Penting sebagai Overlay Informasi (Tanpa Masuk Database)
        $startYear = (int) explode('/', $selectedTahunAjaran)[0];
        $masterNasional = KalenderAkademik::getMasterHariNasional($startYear);
        $hariNasionalByDate = [];
        foreach ($masterNasional as $item) {
            $tglMulai = Carbon::parse($item['tanggal_mulai']);
            $tglSelesai = Carbon::parse($item['tanggal_selesai'] ?? $item['tanggal_mulai']);
            while ($tglMulai->lte($tglSelesai)) {
                $dStr = $tglMulai->format('Y-m-d');
                $hariNasionalByDate[$dStr][] = $item;
                $tglMulai->addDay();
            }
        }

        // $events hanya mengambil agenda murni yang diinput oleh Admin
        $query = KalenderAkademik::where('tahun_ajaran', $selectedTahunAjaran);

        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('kategori', $request->kategori);
        }

        $events = $query->orderBy('tanggal_mulai', 'asc')->get();

        $tahunAjaranList = KalenderAkademik::select('tahun_ajaran')
            ->distinct()
            ->orderByDesc('tahun_ajaran')
            ->pluck('tahun_ajaran')
            ->toArray();

        if (!in_array($defaultTahunAjaran, $tahunAjaranList)) {
            array_unshift($tahunAjaranList, $defaultTahunAjaran);
        }

        $kategoriList = KalenderAkademik::getKategoriList();

        // 12 Bulan Akademik dari Juli s.d. Juni
        $monthsAcademic = [];
        for ($m = 7; $m <= 12; $m++) {
            $monthsAcademic[] = [
                'month' => $m,
                'year'  => $startYear,
                'label' => Carbon::createFromDate($startYear, $m, 1)->translatedFormat('F Y'),
            ];
        }
        for ($m = 1; $m <= 6; $m++) {
            $monthsAcademic[] = [
                'month' => $m,
                'year'  => $startYear + 1,
                'label' => Carbon::createFromDate($startYear + 1, $m, 1)->translatedFormat('F Y'),
            ];
        }

        return view('admin.kalender.index', compact(
            'events',
            'hariNasionalByDate',
            'selectedTahunAjaran',
            'tahunAjaranList',
            'kategoriList',
            'monthsAcademic'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun_ajaran'    => 'required|string|max:20',
            'judul_kegiatan'  => 'required|string|max:255',
            'kategori'        => 'required|string|max:50',
            'warna'           => 'nullable|string|max:30',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'nullable|string|max:2000',
            'is_public'       => 'nullable|boolean',
        ], [
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        // Warna otomatis mengikuti kategori yang dipilih
        $kategoriList = KalenderAkademik::getKategoriList();
        $validated['warna'] = $kategoriList[$validated['kategori']]['warna'] ?? ($validated['warna'] ?? '#0d9488');

        $validated['is_public'] = $request->has('is_public');
        $validated['admin_id']  = auth('admin')->id();

        $event = KalenderAkademik::create($validated);

        AdminActivityLogger::log(
            'create_agenda_kalender',
            "Menambahkan agenda kalender akademik: \"{$event->judul_kegiatan}\" ({$event->tanggal_mulai->format('d/m/Y')})"
        );

        return redirect()->route('admin.kalender.index', ['tahun_ajaran' => $event->tahun_ajaran])
            ->with('success', 'Agenda kalender akademik berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $event = KalenderAkademik::findOrFail($id);

        $validated = $request->validate([
            'tahun_ajaran'    => 'required|string|max:20',
            'judul_kegiatan'  => 'required|string|max:255',
            'kategori'        => 'required|string|max:50',
            'warna'           => 'nullable|string|max:30',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'nullable|string|max:2000',
            'is_public'       => 'nullable|boolean',
        ]);

        // Warna otomatis mengikuti kategori yang dipilih
        $kategoriList = KalenderAkademik::getKategoriList();
        $validated['warna'] = $kategoriList[$validated['kategori']]['warna'] ?? ($validated['warna'] ?? '#0d9488');

        $validated['is_public'] = $request->has('is_public');

        $event->update($validated);

        AdminActivityLogger::log(
            'update_agenda_kalender',
            "Memperbarui agenda kalender akademik: \"{$event->judul_kegiatan}\""
        );

        return redirect()->route('admin.kalender.index', ['tahun_ajaran' => $event->tahun_ajaran])
            ->with('success', 'Agenda kalender akademik berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $event = KalenderAkademik::findOrFail($id);
        $th = $event->tahun_ajaran;
        $judul = $event->judul_kegiatan;

        $event->delete();

        AdminActivityLogger::log('delete_agenda_kalender', "Menghapus agenda kalender: \"{$judul}\"");

        return redirect()->route('admin.kalender.index', ['tahun_ajaran' => $th])
            ->with('success', 'Agenda kalender akademik berhasil dihapus.');
    }
}
