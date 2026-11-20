<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahfidzSetoran;
use App\Models\TahfidzSurah;
use App\Models\Santri;
use App\Services\AdminActivityLogger;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TahfidzController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->tanggal ?? Carbon::today()->format('Y-m-d');
        $kelas   = $request->kelas ?? 'all';
        $search  = $request->search;

        $query = TahfidzSetoran::with(['santri', 'surah', 'ustadz'])
            ->whereDate('tanggal', $tanggal);

        if ($kelas !== 'all' && !empty($kelas)) {
            $query->whereHas('santri', function ($q) use ($kelas) {
                $q->where('kelas', $kelas)->orWhere('kelas', 'like', $kelas . '%');
            });
        }

        if (!empty($search)) {
            $query->whereHas('santri', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', '%' . $search . '%')
                  ->orWhere('nis', 'like', '%' . $search . '%')
                  ->orWhere('nisn', 'like', '%' . $search . '%');
            });
        }

        $setoranHariIni = $query->latest()->paginate(20)->withQueryString();

        $statsQuery = TahfidzSetoran::whereDate('tanggal', $tanggal);
        if ($kelas !== 'all' && !empty($kelas)) {
            $statsQuery->whereHas('santri', function ($q) use ($kelas) {
                $q->where('kelas', $kelas)->orWhere('kelas', 'like', $kelas . '%');
            });
        }
        if (!empty($search)) {
            $statsQuery->whereHas('santri', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', '%' . $search . '%')
                  ->orWhere('nis', 'like', '%' . $search . '%')
                  ->orWhere('nisn', 'like', '%' . $search . '%');
            });
        }

        // Statistik setoran hari ini
        $stats = [
            'total'          => (clone $statsQuery)->count(),
            'total_setoran'  => (clone $statsQuery)->count(),
            'ziyadah'        => (clone $statsQuery)->where('jenis_setoran', 'ziyadah')->count(),
            'murajaah'       => (clone $statsQuery)->where('jenis_setoran', 'murajaah')->count(),
            'ujian_juziyah'  => (clone $statsQuery)->where('jenis_setoran', 'ujian_juziyah')->count(),
            'sangat_lancar'  => (clone $statsQuery)->where('kelancaran', 'sangat_lancar')->count(),
        ];

        $surahList    = TahfidzSurah::orderBy('nomor_surah')->get();
        $santriAktif  = Santri::where('status', 'diterima')->where('status_santri', 'aktif')->orderBy('kelas')->orderBy('nama_lengkap')->get();

        return view('admin.tahfidz.index', compact('setoranHariIni', 'stats', 'surahList', 'santriAktif', 'tanggal', 'kelas', 'search'));
    }

    public function store(Request $request)
    {
        // Otomatis hitung Juz jika juz belum terisi tapi surat dan ayat awal ada
        if (!$request->filled('juz') && $request->filled('surah_id')) {
            $surah = TahfidzSurah::find($request->surah_id);
            if ($surah) {
                $request->merge([
                    'juz' => TahfidzSurah::getJuzForVerse($surah->nomor_surah, (int) ($request->ayat_awal ?: 1))
                ]);
            }
        }

        $request->validate([
            'santri_id'     => 'required|exists:santri,id',
            'tanggal'       => 'required|date',
            'surah_id'      => 'nullable|exists:tahfidz_surahs,id',
            'nama_surah'    => 'required|string|max:100',
            'ayat_awal'     => 'required|integer|min:1',
            'ayat_akhir'    => 'required|integer|min:1',
            'juz'           => 'required|integer|between:1,30',
            'jenis_setoran' => 'required|in:ziyadah,murajaah,ujian_juziyah',
            'kelancaran'    => 'required|in:sangat_lancar,lancar,kurang_lancar,mengulang',
            'catatan_ustadz' => 'nullable|string',
        ]);

        $setoran = TahfidzSetoran::create([
            ...$request->only(['santri_id','tanggal','surah_id','nama_surah','ayat_awal','ayat_akhir','juz','jenis_setoran','kelancaran','catatan_ustadz']),
            'ustadz_id' => auth('admin')->id(),
        ]);

        $santri = Santri::with('orangTua')->find($request->santri_id);
        $jenisLabel = match ($setoran->jenis_setoran) {
            'ziyadah'       => 'Ziyadah',
            'murajaah'      => 'Murojaah',
            'ujian_juziyah' => "Ujian Juz'iyah",
            default         => ucfirst($setoran->jenis_setoran),
        };

        AdminActivityLogger::log('input_setoran_tahfidz', "Setoran {$jenisLabel} santri {$santri->nama_lengkap} surah {$request->nama_surah} ayat {$request->ayat_awal}-{$request->ayat_akhir}");
        NotificationService::sendTahfidz(
            $santri,
            $setoran->nama_surah,
            $setoran->ayat_awal . '-' . $setoran->ayat_akhir,
            $jenisLabel,
            $setoran->kelancaran,
            $setoran->catatan_ustadz,
            (string) $setoran->id,
            $setoran->juz
        );

        return back()->with('success', "Setoran tahfidz berhasil dicatat!");
    }

    public function destroy($id)
    {
        $setoran = TahfidzSetoran::findOrFail($id);
        AdminActivityLogger::log('hapus_setoran_tahfidz', "Hapus setoran ID {$id}");
        $setoran->delete();
        return back()->with('success', 'Data setoran berhasil dihapus.');
    }

    public function rekapSantri(Request $request, $santriId)
    {
        $santri = Santri::findOrFail($santriId);

        $bulan = $request->bulan ?? Carbon::now()->month;
        $tahun = $request->tahun ?? Carbon::now()->year;

        $setoran = TahfidzSetoran::with(['surah', 'ustadz'])
            ->where('santri_id', $santriId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->latest()
            ->get();

        $totalAyat = $setoran->sum(fn($s) => ($s->ayat_akhir - $s->ayat_awal + 1));

        $stats = [
            'total_setoran' => $setoran->count(),
            'ziyadah'       => $setoran->where('jenis_setoran', 'ziyadah')->count(),
            'murajaah'      => $setoran->where('jenis_setoran', 'murajaah')->count(),
            'ujian_juziyah' => $setoran->where('jenis_setoran', 'ujian_juziyah')->count(),
            'total_ayat'    => $totalAyat,
        ];

        return view('admin.tahfidz.rekap-santri', compact('santri', 'setoran', 'stats', 'bulan', 'tahun'));
    }

    public function masterSurah()
    {
        $surahs = TahfidzSurah::orderBy('nomor_surah')->paginate(30);
        return view('admin.tahfidz.master-surah', compact('surahs'));
    }

    public function storeSurah(Request $request)
    {
        $request->validate([
            'nomor_surah' => 'required|integer|unique:tahfidz_surahs,nomor_surah',
            'nama_latin'  => 'required|string',
            'nama_arab'   => 'required|string',
            'arti'        => 'required|string',
            'jumlah_ayat' => 'required|integer',
            'tempat_turun' => 'required|string',
        ]);
        TahfidzSurah::create($request->all());
        return back()->with('success', 'Data surah berhasil ditambahkan.');
    }

    public function updateSurah(Request $request, $id)
    {
        $surah = TahfidzSurah::findOrFail($id);
        $request->validate([
            'nomor_surah'  => 'required|integer|unique:tahfidz_surahs,nomor_surah,' . $id,
            'nama_latin'   => 'required|string',
            'nama_arab'    => 'required|string',
            'arti'         => 'required|string',
            'jumlah_ayat'  => 'required|integer',
            'tempat_turun' => 'required|string',
        ]);
        $surah->update($request->all());
        AdminActivityLogger::log('update_surah', "Memperbarui surah {$surah->nama_latin}");
        return back()->with('success', 'Data surah berhasil diperbarui.');
    }

    public function destroySurah($id)
    {
        $surah = TahfidzSurah::findOrFail($id);
        $nama = $surah->nama_latin;
        $surah->delete();
        AdminActivityLogger::log('hapus_surah', "Menghapus surah {$nama}");
        return back()->with('success', "Data surah '{$nama}' berhasil dihapus.");
    }
}
