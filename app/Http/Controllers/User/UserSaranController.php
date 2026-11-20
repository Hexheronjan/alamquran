<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SaranMasukan;
use Illuminate\Http\Request;

class UserSaranController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $sarans = SaranMasukan::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $kategoriList = SaranMasukan::getKategoriList();

        return view('user.saran.index', compact('sarans', 'kategoriList'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'kategori' => 'required|in:fitur_baru,tampilan_ui,kendala_bug,pelayanan,saran',
            'judul'    => 'required|string|max:200',
            'pesan'    => 'required|string|max:2000',
            'lampiran' => 'nullable|image|max:3072',
        ], [
            'kategori.required' => 'Pilih kategori masukan.',
            'judul.required'    => 'Judul saran / masukan wajib diisi.',
            'pesan.required'    => 'Deskripsi masukan wajib diisi.',
            'lampiran.image'    => 'Lampiran harus berupa gambar.',
            'lampiran.max'      => 'Ukuran gambar maksimal 3 MB.',
        ]);

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('saran_masukan', 'public');
        }

        SaranMasukan::create([
            'user_id'       => $user->id,
            'nama_pengirim' => $user->name ?: 'Wali Santri (' . $user->no_wa . ')',
            'tipe_pengirim' => 'wali_santri',
            'kategori'      => $validated['kategori'],
            'judul'         => $validated['judul'],
            'pesan'         => $validated['pesan'],
            'lampiran'      => $lampiranPath,
            'status'        => 'menunggu_review',
        ]);

        return redirect()->route('user.saran.index')
            ->with('success', 'Jazakumullah khairan! Saran dan masukan Anda telah kami terima dan akan ditinjau oleh tim pengembang.');
    }
}
