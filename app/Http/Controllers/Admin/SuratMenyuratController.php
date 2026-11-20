<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Services\AdminActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SuratMenyuratController extends Controller
{
    // ============================
    //  SURAT KELUAR
    // ============================
    public function indexKeluar(Request $request)
    {
        $query = SuratKeluar::with('admin')->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nomor_surat', 'like', "%{$request->search}%")
                  ->orWhere('perihal', 'like', "%{$request->search}%")
                  ->orWhere('tujuan_surat', 'like', "%{$request->search}%");
            });
        }

        $suratKeluar = $query->paginate(15)->withQueryString();
        $nomorBerikut = $this->generateNomorSurat();

        return view('admin.surat.keluar.index', compact('suratKeluar', 'nomorBerikut'));
    }

    public function createKeluar()
    {
        $nomorBerikut = $this->generateNomorSurat();
        return view('admin.surat.keluar.create', compact('nomorBerikut'));
    }

    public function storeKeluar(Request $request)
    {
        $request->validate([
            'nomor_surat'    => 'required|string|unique:surat_keluars,nomor_surat',
            'tanggal_surat'  => 'required|date',
            'tujuan_surat'   => 'required|string',
            'perihal'        => 'required|string',
            'isi_surat'      => 'required|string',
            'penandatangan'  => 'required|string',
            'file_lampiran'  => 'nullable|file|mimes:pdf,jpg,png|max:5120',
        ]);

        $data = $request->only(['nomor_surat','tanggal_surat','tujuan_surat','perihal','isi_surat','penandatangan']);
        $data['admin_id'] = auth('admin')->id();

        if ($request->hasFile('file_lampiran')) {
            $data['file_lampiran'] = $request->file('file_lampiran')->store('surat-keluar', 'public');
        }

        $surat = SuratKeluar::create($data);
        AdminActivityLogger::log('buat_surat_keluar', "Buat surat keluar No. {$surat->nomor_surat} perihal: {$surat->perihal}");

        return redirect()->route('admin.surat.keluar.index')->with('success', "Surat Keluar No. {$surat->nomor_surat} berhasil dibuat.");
    }

    public function showKeluar($id)
    {
        $surat  = SuratKeluar::with('admin')->findOrFail($id);
        $school = \App\Models\School::first();
        return view('admin.surat.keluar.show', compact('surat', 'school'));
    }

    public function destroyKeluar($id)
    {
        $surat = SuratKeluar::findOrFail($id);
        if ($surat->file_lampiran) {
            Storage::disk('public')->delete($surat->file_lampiran);
        }
        AdminActivityLogger::log('hapus_surat_keluar', "Hapus surat keluar No. {$surat->nomor_surat}");
        $surat->delete();
        return back()->with('success', 'Surat keluar berhasil dihapus.');
    }

    // ============================
    //  SURAT MASUK
    // ============================
    public function indexMasuk(Request $request)
    {
        $query = SuratMasuk::with('admin')->latest('tanggal_terima');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nomor_surat', 'like', "%{$request->search}%")
                  ->orWhere('perihal', 'like', "%{$request->search}%")
                  ->orWhere('asal_instansi', 'like', "%{$request->search}%");
            });
        }

        $suratMasuk = $query->paginate(15)->withQueryString();
        return view('admin.surat.masuk.index', compact('suratMasuk'));
    }

    public function storeMasuk(Request $request)
    {
        $request->validate([
            'nomor_surat'    => 'required|string',
            'tanggal_terima' => 'required|date',
            'tanggal_surat'  => 'required|date',
            'asal_instansi'  => 'required|string',
            'perihal'        => 'required|string',
            'ringkasan_isi'  => 'nullable|string',
            'file_arsip'     => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $filePath = $request->file('file_arsip')->store('surat-masuk', 'public');

        $surat = SuratMasuk::create([
            ...$request->only(['nomor_surat','tanggal_terima','tanggal_surat','asal_instansi','perihal','ringkasan_isi']),
            'file_arsip' => $filePath,
            'admin_id'   => auth('admin')->id(),
        ]);

        AdminActivityLogger::log('arsip_surat_masuk', "Arsip surat masuk dari {$surat->asal_instansi} perihal: {$surat->perihal}");

        return back()->with('success', "Surat masuk dari {$surat->asal_instansi} berhasil diarsipkan.");
    }

    public function destroyMasuk($id)
    {
        $surat = SuratMasuk::findOrFail($id);
        Storage::disk('public')->delete($surat->file_arsip);
        AdminActivityLogger::log('hapus_surat_masuk', "Hapus arsip surat masuk ID {$id}");
        $surat->delete();
        return back()->with('success', 'Arsip surat masuk berhasil dihapus.');
    }

    // Helper
    private function generateNomorSurat(): string
    {
        $tahun = Carbon::now()->year;
        $bulan = Carbon::now()->format('m');
        $count = SuratKeluar::whereYear('tanggal_surat', $tahun)->count() + 1;
        $nomorUrut = str_pad($count, 3, '0', STR_PAD_LEFT);
        return "{$nomorUrut}/PS-AL/SK/{$bulan}/{$tahun}";
    }
}
