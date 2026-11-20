<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\RekeningKelas;
use App\Services\AdminActivityLogger;
use Illuminate\Http\Request;

class RekeningKelasController extends Controller
{
    public function index()
    {
        $rekeningKelas = RekeningKelas::with('admin')->orderBy('kelas')->get();
        $adminUstadzList = Admin::where('role', Admin::ROLE_USTADZ_KELAS)
            ->orWhere('role', Admin::ROLE_SUPER_ADMIN)
            ->get();

        return view('admin.rekening_kelas.index', compact('rekeningKelas', 'adminUstadzList'));
    }

    public function storeOrUpdate(Request $request)
    {
        $request->validate([
            'kelas'          => ['required', 'in:1,2,3'],
            'nama_ustadz'    => ['required', 'string', 'max:255'],
            'bank_name'      => ['required', 'string', 'max:100'],
            'nomor_rekening' => ['required', 'string', 'max:50'],
            'atas_nama'      => ['required', 'string', 'max:150'],
            'no_wa_ustadz'   => ['nullable', 'string', 'max:30'],
            'admin_id'       => ['nullable', 'exists:admins,id'],
            'catatan'        => ['nullable', 'string', 'max:500'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        $rekening = RekeningKelas::updateOrCreate(
            ['kelas' => $request->kelas],
            [
                'nama_ustadz'    => $request->nama_ustadz,
                'bank_name'      => $request->bank_name,
                'nomor_rekening' => $request->nomor_rekening,
                'atas_nama'      => $request->atas_nama,
                'no_wa_ustadz'   => $request->no_wa_ustadz,
                'admin_id'       => $request->admin_id,
                'catatan'        => $request->catatan,
                'is_active'      => $request->has('is_active') ? (bool)$request->is_active : true,
            ]
        );

        // Jika admin_id diisi, update assigned_kelas pada akun admin tersebut
        // PENTING: Jangan pernah mengubah role Super Admin menjadi ustadz_kelas!
        if ($request->admin_id) {
            $targetAdmin = Admin::find($request->admin_id);
            if ($targetAdmin && $targetAdmin->role !== Admin::ROLE_SUPER_ADMIN) {
                $targetAdmin->update([
                    'assigned_kelas' => $request->kelas,
                    'role'           => Admin::ROLE_USTADZ_KELAS,
                ]);
            }
        }

        AdminActivityLogger::log(
            'update_rekening_kelas',
            "Memperbarui data rekening ustadz kelas {$request->kelas} ({$request->nama_ustadz} - {$request->bank_name} {$request->nomor_rekening})",
            'RekeningKelas',
            $rekening->id
        );

        return redirect()->route('admin.rekening_kelas.index')
            ->with('success', "Rekening ustadz Kelas {$request->kelas} berhasil diperbarui.");
    }
}
