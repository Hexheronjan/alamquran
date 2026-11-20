<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        // Halaman pengguna hanya menampilkan wali dari santri yang sudah diterima.
        // Relasi juga dibatasi agar nama/status yang tampil selalu sesuai data diterima.
        $query = User::whereHas('santri', function ($santriQuery) {
            $santriQuery->where('status', 'diterima');
        })->with(['santri' => function ($santriQuery) {
            $santriQuery->where('status', 'diterima')->orderByDesc('created_at');
        }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_wa', 'like', "%{$search}%")
                    ->orWhereHas('santri', function ($santriQuery) use ($search) {
                        $santriQuery->where('nama_lengkap', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('tahun_ajaran')) {
            $tahunAjaran = $request->tahun_ajaran;
            $query->whereHas('santri', function ($santriQuery) use ($tahunAjaran) {
                $santriQuery->where('status', 'diterima')->where('tahun_ajaran', $tahunAjaran);
            });
        }

        $pengguna = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $tahunList = \App\Models\Santri::where('status', 'diterima')
            ->whereNotNull('tahun_ajaran')
            ->where('tahun_ajaran', '!=', '')
            ->distinct()
            ->pluck('tahun_ajaran')
            ->sortDesc();

        return view('admin.pengguna.index', compact('pengguna', 'tahunList'));
    }

    public function edit(User $pengguna)
    {
        $pengguna->load(['santri' => function ($santriQuery) {
            $santriQuery->where('status', 'diterima')->orderByDesc('created_at');
        }]);

        return view('admin.pengguna.edit', compact('pengguna'));
    }

    public function update(Request $request, User $pengguna)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $pengguna->id,
            'no_wa' => 'required|string|max:30',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $normalizedWa = \App\Services\WhatsappService::formatPhoneNumber($request->no_wa);
        if (!$normalizedWa) {
            return back()->withInput()->withErrors(['no_wa' => 'Format nomor WhatsApp tidak valid. Gunakan format 08xxxxxxxxxx atau 628xxxxxxxxxx.']);
        }

        // Cek apakah nomor baru sudah terdaftar pada user lain
        $isUsed = User::where('id', '!=', $pengguna->id)
            ->whereIn('no_wa', \App\Services\AkunWaliService::phoneLookupVariants($normalizedWa))
            ->exists();
        if ($isUsed) {
            return back()->withInput()->withErrors(['no_wa' => "Nomor WhatsApp {$normalizedWa} sudah digunakan oleh akun wali lainnya."]);
        }

        $pengguna->name = $validated['name'];
        $pengguna->email = $validated['email'];
        $pengguna->no_wa = $normalizedWa;

        if (!empty($validated['password'])) {
            $pengguna->password = Hash::make($validated['password']);
        }

        $pengguna->save();

        // Sinkronkan otomatis ke seluruh data santri milik wali ini
        \App\Models\Santri::where('user_id', $pengguna->id)->get()->each(function ($santri) use ($normalizedWa) {
            if ($santri->orangTua) {
                $santri->orangTua->update(['no_wa_ayah' => $normalizedWa]);
            }
        });

        \App\Services\AdminActivityLogger::log(
            'update_pengguna_wa',
            "Memperbarui data akun wali '{$pengguna->name}' (No. WA baru: {$normalizedWa}) dan sinkron ke data santri",
            'User',
            $pengguna->id
        );

        return redirect()->route('admin.pengguna.index')
            ->with('success', "Data akun wali {$pengguna->name} berhasil diperbarui (No. WA: {$normalizedWa}) dan otomatis disinkronkan ke seluruh data santri.");
    }
}
