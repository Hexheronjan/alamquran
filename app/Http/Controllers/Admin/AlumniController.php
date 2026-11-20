<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AlumniExport;
use App\Exports\AlumniTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\AlumniImport;
use App\Models\Angkatan;
use App\Models\OrangTua;
use App\Models\Santri;
use App\Models\User;
use App\Services\AdminActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class AlumniController extends Controller
{
    public function index(Request $request)
    {
        $query = Santri::with(['orangTua', 'angkatan', 'user'])
            ->where(function ($q) {
                $q->where('kelas', 'alumni')
                  ->orWhere('status_santri', 'lulus');
            });

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhereHas('orangTua', function ($oq) use ($search) {
                      $oq->where('nama_ayah', 'like', "%{$search}%")
                         ->orWhere('no_wa_ayah', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('tahun_lulus')) {
            $query->where('tahun_lulus', $request->tahun_lulus);
        }

        if ($request->filled('angkatan_id')) {
            $query->where('angkatan_id', $request->angkatan_id);
        }

        $totalAlumni = (clone $query)->count();
        $alumniList  = $query->orderByDesc('tahun_lulus')
            ->orderBy('nama_lengkap', 'asc')
            ->paginate(20)
            ->withQueryString();

        $tahunLulusList = Santri::where(function ($q) {
                $q->where('kelas', 'alumni')->orWhere('status_santri', 'lulus');
            })
            ->whereNotNull('tahun_lulus')
            ->where('tahun_lulus', '!=', '')
            ->distinct()
            ->orderByDesc('tahun_lulus')
            ->pluck('tahun_lulus');

        $angkatanList = Angkatan::orderByDesc('tahun_ajaran')->get();

        return view('admin.alumni.index', compact('alumniList', 'totalAlumni', 'tahunLulusList', 'angkatanList'));
    }

    public function create()
    {
        $angkatanList = Angkatan::orderByDesc('tahun_ajaran')->get();
        return view('admin.alumni.create', compact('angkatanList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap'       => ['required', 'string', 'max:255'],
            'nisn'               => ['nullable', 'digits:10', 'unique:santri,nisn'],
            'nis'                => ['nullable', 'string', 'max:30', 'unique:santri,nis'],
            'tahun_masuk'        => ['nullable', 'digits:4'],
            'tahun_lulus'        => ['required', 'digits:4'],
            'angkatan_id'        => ['nullable', 'exists:angkatan,id'],
            'tempat_lahir'       => ['nullable', 'string', 'max:255'],
            'tanggal_lahir'      => ['nullable', 'date'],
            'nama_ayah'          => ['nullable', 'string', 'max:255'],
            'no_wa_ayah'         => ['nullable', 'string', 'max:25'],
            'email_ortu'         => ['nullable', 'email', 'max:255'],
            'alamat'             => ['nullable', 'string'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap santri wajib diisi.',
            'nisn.digits'           => 'NISN harus terdiri dari tepat 10 digit angka.',
            'nisn.unique'           => 'NISN ini sudah terdaftar pada data santri lain. Periksa kembali nomor NISN.',
            'nis.unique'            => 'NIS ini sudah terdaftar pada data santri lain. Periksa kembali nomor NIS.',
            'tahun_masuk.digits'    => 'Tahun masuk harus berupa 4 digit angka (contoh: 2023).',
            'tahun_lulus.required'  => 'Tahun lulus wajib diisi.',
            'tahun_lulus.digits'    => 'Tahun lulus harus berupa 4 digit angka (contoh: 2026).',
            'email_ortu.email'      => 'Format email orang tua tidak valid.',
        ]);

        DB::beginTransaction();
        try {
            $noWa = $this->normalizePhone($request->no_wa_ayah);
            $user = null;
            $orangTua = null;

            // Buat atau hubungkan akun Orang Tua & User
            if ($request->filled('nama_ayah') || !empty($noWa)) {
                $orangTua = OrangTua::create([
                    'nama_ayah'            => $request->nama_ayah ?? 'Wali ' . $request->nama_lengkap,
                    'no_wa_ayah'           => $noWa ?? '-',
                    'alamat_domisili_ayah' => $request->alamat,
                ]);

                if (!empty($noWa)) {
                    $user = User::where('no_wa', $noWa)->first();
                    if (!$user) {
                        $email = $request->email_ortu ?: ('alumni_' . ($request->nisn ?: ($request->nis ?: uniqid())) . '@alamquran.sch.id');
                        // Ensure unique email
                        if (User::where('email', $email)->exists()) {
                            $email = 'alumni_' . uniqid() . '@alamquran.sch.id';
                        }
                        $user = User::create([
                            'name'     => $request->nama_ayah ?? ('Wali ' . $request->nama_lengkap),
                            'email'    => $email,
                            'no_wa'    => $noWa,
                            'password' => Hash::make('alumni' . $request->tahun_lulus),
                        ]);
                    }
                }
            }

            $santri = Santri::create([
                'nama_lengkap'  => $request->nama_lengkap,
                'nisn'          => $request->nisn,
                'nis'           => $request->nis,
                'tempat_lahir'  => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'tahun_masuk'   => $request->tahun_masuk,
                'tahun_lulus'   => $request->tahun_lulus,
                'angkatan_id'   => $request->angkatan_id,
                'kelas'         => 'alumni',
                'status_santri' => 'lulus',
                'status'        => 'diterima',
                'orang_tua_id'  => $orangTua?->id,
                'user_id'       => $user?->id,
            ]);

            AdminActivityLogger::log(
                'create_alumni',
                "Menambahkan data alumni '{$santri->nama_lengkap}' lulusan tahun {$santri->tahun_lulus}",
                'Santri',
                $santri->id
            );

            DB::commit();

            return redirect()->route('admin.alumni.index')
                ->with('success', "Data alumni '{$santri->nama_lengkap}' berhasil disimpan.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan data alumni: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $alumni = Santri::with(['orangTua', 'angkatan'])->findOrFail($id);
        $angkatanList = Angkatan::orderByDesc('tahun_ajaran')->get();
        return view('admin.alumni.edit', compact('alumni', 'angkatanList'));
    }

    public function update(Request $request, $id)
    {
        $santri = Santri::with('orangTua')->findOrFail($id);

        $request->validate([
            'nama_lengkap'  => ['required', 'string', 'max:255'],
            'nisn'          => ['nullable', 'digits:10', 'unique:santri,nisn,' . $id],
            'nis'           => ['nullable', 'string', 'max:30', 'unique:santri,nis,' . $id],
            'tahun_masuk'   => ['nullable', 'digits:4'],
            'tahun_lulus'   => ['required', 'digits:4'],
            'angkatan_id'   => ['nullable', 'exists:angkatan,id'],
            'tempat_lahir'  => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'nama_ayah'     => ['nullable', 'string', 'max:255'],
            'no_wa_ayah'    => ['nullable', 'string', 'max:25'],
            'alamat'        => ['nullable', 'string'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap santri wajib diisi.',
            'nisn.digits'           => 'NISN harus terdiri dari tepat 10 digit angka.',
            'nisn.unique'           => 'NISN ini sudah terdaftar pada data santri lain. Periksa kembali nomor NISN.',
            'nis.unique'            => 'NIS ini sudah terdaftar pada data santri lain. Periksa kembali nomor NIS.',
            'tahun_masuk.digits'    => 'Tahun masuk harus berupa 4 digit angka (contoh: 2023).',
            'tahun_lulus.required'  => 'Tahun lulus wajib diisi.',
            'tahun_lulus.digits'    => 'Tahun lulus harus berupa 4 digit angka (contoh: 2026).',
        ]);

        DB::beginTransaction();
        try {
            $noWa = $this->normalizePhone($request->no_wa_ayah);

            $santri->update([
                'nama_lengkap'  => $request->nama_lengkap,
                'nisn'          => $request->nisn,
                'nis'           => $request->nis,
                'tempat_lahir'  => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'tahun_masuk'   => $request->tahun_masuk,
                'tahun_lulus'   => $request->tahun_lulus,
                'angkatan_id'   => $request->angkatan_id,
            ]);

            if ($santri->orangTua) {
                $santri->orangTua->update([
                    'nama_ayah'            => $request->nama_ayah,
                    'no_wa_ayah'           => $noWa ?: $santri->orangTua->no_wa_ayah,
                    'alamat_domisili_ayah' => $request->alamat,
                ]);
            } elseif ($request->filled('nama_ayah') || !empty($noWa)) {
                $orangTua = OrangTua::create([
                    'nama_ayah'            => $request->nama_ayah ?? 'Wali ' . $request->nama_lengkap,
                    'no_wa_ayah'           => $noWa ?? '-',
                    'alamat_domisili_ayah' => $request->alamat,
                ]);
                $santri->update(['orang_tua_id' => $orangTua->id]);
            }

            // Sync user phone if user exists
            if ($santri->user && !empty($noWa)) {
                $santri->user->update([
                    'no_wa' => $noWa,
                    'name'  => $request->nama_ayah ?: $santri->user->name,
                ]);
            }

            AdminActivityLogger::log(
                'update_alumni',
                "Memperbarui data alumni '{$santri->nama_lengkap}'",
                'Santri',
                $santri->id
            );

            DB::commit();

            return redirect()->route('admin.alumni.index')
                ->with('success', "Data alumni '{$santri->nama_lengkap}' berhasil diperbarui.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui data alumni: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!auth('admin')->user()?->isSuperAdmin()) {
            return redirect()->route('admin.alumni.index')
                ->with('error', 'Akses ditolak: Hanya Super Admin yang berwenang menghapus data alumni.');
        }

        $santri = Santri::findOrFail($id);
        $nama = $santri->nama_lengkap;
        $santri->delete();

        AdminActivityLogger::log(
            'delete_alumni',
            "Menghapus data alumni '{$nama}'",
            'Santri',
            $id
        );

        return redirect()->route('admin.alumni.index')
            ->with('success', "Data alumni '{$nama}' berhasil dihapus.");
    }

    public function downloadTemplate()
    {
        return Excel::download(new AlumniTemplateExport(), 'template_import_alumni.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ], [
            'file_excel.required' => 'Pilih file Excel terlebih dahulu.',
            'file_excel.mimes'    => 'Format file harus berupa XLSX, XLS, atau CSV.',
        ]);

        $import = new AlumniImport();

        try {
            Excel::import($import, $request->file('file_excel'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }

        $successCount = $import->getSuccessCount();
        $skippedCount = $import->getSkippedCount();
        $errors       = $import->getErrors();

        AdminActivityLogger::log(
            'import_alumni',
            "Mengimpor {$successCount} data alumni secara massal (Dilewati: {$skippedCount})",
            'Santri'
        );

        $message = "Import selesai! {$successCount} data alumni berhasil diimport, {$skippedCount} data dilewati/gagal.";

        if (!empty($errors)) {
            return back()
                ->with('success', $message)
                ->with('import_errors', $errors);
        }

        return back()->with('success', $message);
    }

    public function export(Request $request)
    {
        $filename = 'data_alumni_' . ($request->tahun_lulus ? "tahun_{$request->tahun_lulus}_" : '') . date('Ymd_His') . '.xlsx';
        return Excel::download(new AlumniExport($request->tahun_lulus, $request->angkatan_id, $request->search), $filename);
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleaned, '08')) {
            return '628' . substr($cleaned, 2);
        }
        if (str_starts_with($cleaned, '8')) {
            return '628' . substr($cleaned, 1);
        }
        return $cleaned;
    }
}
