<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Angkatan;

class AngkatanController extends Controller
{
    public function index()
    {
        $angkatan = Angkatan::withCount('santri')
                            ->orderBy('tahun_ajaran', 'desc')
                            ->get();
        return view('admin.angkatan.index', compact('angkatan'));
    }

    public function create()
    {
        return view('admin.angkatan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_angkatan'        => 'required|string|max:255',
            'tahun_ajaran'         => 'required|string|max:20',
            'tanggal_mulai_daftar' => 'required|date',
            'tanggal_tutup_daftar' => 'required|date|after_or_equal:tanggal_mulai_daftar',
            'keterangan'           => 'nullable|string|max:500',
        ], [
            'tanggal_tutup_daftar.after_or_equal' => 'Tanggal tutup tidak boleh sebelum tanggal buka.',
        ]);

        Angkatan::create([
            'nama_angkatan'        => $request->nama_angkatan,
            'tahun_ajaran'         => $request->tahun_ajaran,
            'tanggal_mulai_daftar' => $request->tanggal_mulai_daftar,
            'tanggal_tutup_daftar' => $request->tanggal_tutup_daftar,
            'is_active'            => $request->boolean('is_active', true),
            'keterangan'           => $request->keterangan,
        ]);

        return redirect()->route('admin.angkatan.index')
                         ->with('success', 'Angkatan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $angkatan = Angkatan::findOrFail($id);
        return view('admin.angkatan.edit', compact('angkatan'));
    }

    public function update(Request $request, $id)
    {
        $angkatan = Angkatan::findOrFail($id);

        $request->validate([
            'nama_angkatan'        => 'required|string|max:255',
            'tahun_ajaran'         => 'required|string|max:20',
            'tanggal_mulai_daftar' => 'required|date',
            'tanggal_tutup_daftar' => 'required|date|after_or_equal:tanggal_mulai_daftar',
            'keterangan'           => 'nullable|string|max:500',
        ], [
            'tanggal_tutup_daftar.after_or_equal' => 'Tanggal tutup tidak boleh sebelum tanggal buka.',
        ]);

        $angkatan->update([
            'nama_angkatan'        => $request->nama_angkatan,
            'tahun_ajaran'         => $request->tahun_ajaran,
            'tanggal_mulai_daftar' => $request->tanggal_mulai_daftar,
            'tanggal_tutup_daftar' => $request->tanggal_tutup_daftar,
            'is_active'            => $request->boolean('is_active'),
            'keterangan'           => $request->keterangan,
        ]);

        return redirect()->route('admin.angkatan.index')
                         ->with('success', 'Data angkatan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $angkatan = Angkatan::withCount('santri')->findOrFail($id);

        if ($angkatan->santri_count > 0) {
            return redirect()->route('admin.angkatan.index')
                             ->with('error', 'Angkatan tidak bisa dihapus karena masih memiliki ' . $angkatan->santri_count . ' data santri.');
        }

        $angkatan->delete();
        return redirect()->route('admin.angkatan.index')
                         ->with('success', 'Angkatan berhasil dihapus.');
    }
}
