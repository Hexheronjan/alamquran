<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KegiatanHarian;
use App\Services\AdminActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KegiatanSantriController extends Controller
{
    public function index(Request $request)
    {
        $tipe  = $request->get('tipe', 'semua');  // semua, rutin, khusus
        $hari  = $request->get('hari', 'semua');  // semua, senin, selasa, rabu, kamis, jumat, sabtu, minggu
        $kelas = $request->get('kelas', 'semua'); // semua, 1, 2, 3

        $query = KegiatanHarian::with('admin')->orderBy('waktu_mulai');

        if ($tipe === 'rutin') {
            $query->where('is_rutin_24jam', true);
        } elseif ($tipe === 'khusus') {
            $query->where('is_rutin_24jam', false);
        }

        if ($hari !== 'semua') {
            $query->where(function ($q) use ($hari) {
                $q->where('hari', $hari)->orWhere('hari', 'semua');
            });
        }

        if ($kelas !== 'semua') {
            $query->where(function ($q) use ($kelas) {
                $q->where('kelas', $kelas)->orWhere('kelas', 'semua');
            });
        }

        $kegiatans = $query->paginate(20)->withQueryString();

        $totalRutin  = KegiatanHarian::where('is_rutin_24jam', true)->count();
        $totalKhusus = KegiatanHarian::where('is_rutin_24jam', false)->count();

        return view('admin.kegiatan.index', compact(
            'kegiatans', 'tipe', 'hari', 'kelas', 'totalRutin', 'totalKhusus'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kegiatan'   => 'required|string|max:255',
            'waktu_mulai'     => 'required',
            'waktu_selesai'   => 'required',
            'hari'            => 'nullable|required_if:is_rutin_24jam,1|in:semua,senin,selasa,rabu,kamis,jumat,sabtu,minggu',
            'kelas'           => 'required|in:semua,1,2,3',
            'is_rutin_24jam'  => 'required|boolean',
            'tanggal_khusus'  => 'nullable|required_if:is_rutin_24jam,0|date',
            'deskripsi'       => 'nullable|string',
        ]);

        $hari = $request->hari ?: 'semua';
        if (!$request->is_rutin_24jam && $request->tanggal_khusus) {
            $dayMap = [
                0 => 'minggu',
                1 => 'senin',
                2 => 'selasa',
                3 => 'rabu',
                4 => 'kamis',
                5 => 'jumat',
                6 => 'sabtu',
            ];
            $tgl = Carbon::parse($request->tanggal_khusus);
            $hari = $dayMap[$tgl->dayOfWeek] ?? 'semua';
        }

        $kegiatan = KegiatanHarian::create([
            'nama_kegiatan'  => $request->nama_kegiatan,
            'waktu_mulai'    => substr($request->waktu_mulai, 0, 5),
            'waktu_selesai'  => substr($request->waktu_selesai, 0, 5),
            'hari'           => $hari,
            'kelas'          => $request->kelas,
            'is_rutin_24jam' => $request->is_rutin_24jam,
            'tanggal_khusus' => $request->is_rutin_24jam ? null : $request->tanggal_khusus,
            'deskripsi'      => $request->deskripsi,
            'admin_id'       => auth('admin')->id(),
        ]);

        AdminActivityLogger::log('tambah_kegiatan_santri', "Tambah kegiatan santri: {$kegiatan->nama_kegiatan} (Hari: {$kegiatan->hari_label}, Kelas: {$kegiatan->kelas_label})");

        return back()->with('success', "Kegiatan '{$kegiatan->nama_kegiatan}' berhasil ditambahkan.");
    }

    public function update(Request $request, $id)
    {
        $kegiatan = KegiatanHarian::findOrFail($id);

        $request->validate([
            'nama_kegiatan'   => 'required|string|max:255',
            'waktu_mulai'     => 'required',
            'waktu_selesai'   => 'required',
            'hari'            => 'nullable|required_if:is_rutin_24jam,1|in:semua,senin,selasa,rabu,kamis,jumat,sabtu,minggu',
            'kelas'           => 'required|in:semua,1,2,3',
            'is_rutin_24jam'  => 'required|boolean',
            'tanggal_khusus'  => 'nullable|required_if:is_rutin_24jam,0|date',
            'deskripsi'       => 'nullable|string',
        ]);

        $hari = $request->hari ?: 'semua';
        if (!$request->is_rutin_24jam && $request->tanggal_khusus) {
            $dayMap = [
                0 => 'minggu',
                1 => 'senin',
                2 => 'selasa',
                3 => 'rabu',
                4 => 'kamis',
                5 => 'jumat',
                6 => 'sabtu',
            ];
            $tgl = Carbon::parse($request->tanggal_khusus);
            $hari = $dayMap[$tgl->dayOfWeek] ?? 'semua';
        }

        $kegiatan->update([
            'nama_kegiatan'  => $request->nama_kegiatan,
            'waktu_mulai'    => substr($request->waktu_mulai, 0, 5),
            'waktu_selesai'  => substr($request->waktu_selesai, 0, 5),
            'hari'           => $hari,
            'kelas'          => $request->kelas,
            'is_rutin_24jam' => $request->is_rutin_24jam,
            'tanggal_khusus' => $request->is_rutin_24jam ? null : $request->tanggal_khusus,
            'deskripsi'      => $request->deskripsi,
        ]);

        AdminActivityLogger::log('update_kegiatan_santri', "Update kegiatan santri ID {$id}: {$kegiatan->nama_kegiatan}");

        return back()->with('success', "Kegiatan '{$kegiatan->nama_kegiatan}' berhasil diperbarui.");
    }

    public function destroy($id)
    {
        $kegiatan = KegiatanHarian::findOrFail($id);
        $nama = $kegiatan->nama_kegiatan;
        $kegiatan->delete();

        AdminActivityLogger::log('hapus_kegiatan_santri', "Hapus kegiatan santri: {$nama}");

        return back()->with('success', "Kegiatan '{$nama}' berhasil dihapus.");
    }
}
