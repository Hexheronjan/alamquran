<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Santri;
use App\Models\OrangTua;
use App\Models\Dokumen;
use App\Models\Angkatan;
use App\Models\UangSaku;
use App\Models\SantriCorrectionNote;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SantriRegistrationController extends Controller
{
    public function showForm()
    {
        // Cek apakah ada angkatan yang sedang membuka pendaftaran sekarang
        $angkatanAktif = Angkatan::open()->first();

        // Jika tidak ada yang aktif, cari angkatan mendatang terdekat
        $angkatanMendatang = null;
        if (!$angkatanAktif) {
            $angkatanMendatang = Angkatan::where('is_active', true)
                ->where('tanggal_mulai_daftar', '>', now())
                ->orderBy('tanggal_mulai_daftar', 'asc')
                ->first();
        }

        // Ambil semua santri yang sudah didaftarkan oleh akun wali ini
        $santriTerdaftar = Santri::where('user_id', auth()->id())
            ->orderBy('created_at', 'asc')
            ->get();

        return view('santri.registration', compact('angkatanAktif', 'angkatanMendatang', 'santriTerdaftar'));
    }

    public function showStatus($id)
    {
        $santri = Santri::with(['orangTua', 'dokumen', 'correctionNoteOpen', 'uangSaku' => function($q) {
                $q->orderBy('id', 'desc');
            }])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('santri.status', compact('santri'));
    }

    public function editCorrection($id)
    {
        $santri = Santri::with(['orangTua', 'correctionNoteOpen'])
            ->where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        abort_unless($santri->correctionNoteOpen, 403, 'Tidak ada catatan perbaikan yang aktif.');

        return view('santri.edit-correction', compact('santri'));
    }

    public function updateCorrection(Request $request, $id)
    {
        $santri = Santri::with(['orangTua', 'correctionNoteOpen'])
            ->where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        abort_unless($santri->correctionNoteOpen, 403, 'Tidak ada catatan perbaikan yang aktif.');

        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'nik' => ['nullable', 'digits:16'],
            'nisn' => ['nullable', 'digits:10'],
            'sekolah_asal' => ['nullable', 'string', 'max:255'],
            'npsn' => ['nullable', 'string', 'max:30'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'no_wa_ayah' => ['nullable', 'string', 'max:20'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'alamat_domisili_ayah' => ['nullable', 'string'],
        ]);

        $santri->update(collect($data)->only(['nama_lengkap', 'tempat_lahir', 'tanggal_lahir', 'nik', 'nisn', 'sekolah_asal', 'npsn'])->toArray());
        if ($santri->orangTua) {
            $santri->orangTua->update(collect($data)->only(['nama_ayah', 'no_wa_ayah', 'nama_ibu', 'alamat_domisili_ayah'])->toArray());
        }

        $santri->correctionNoteOpen->update([
            'diselesaikan_pada' => now(),
            'diselesaikan_oleh_user_id' => auth()->id(),
        ]);
        $santri->update(['status' => 'menunggu_verifikasi']);

        \App\Services\AdminActivityLogger::log(
            'perbaikan_data_santri_oleh_wali',
            "Wali memperbaiki data '{$santri->nama_lengkap}' dan mengirim ulang untuk verifikasi.",
            'Santri', $santri->id
        );

        return redirect()->route('santri.registration.status', $santri->id)
            ->with('success', 'Data berhasil diperbarui dan dikirim kembali untuk verifikasi.');
    }

    public function userUangSaku(Request $request)
    {
        $request->validate([
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer|between:2000,2100',
        ]);

        $bulanTerpilih = $request->filled('bulan') ? (int) $request->bulan : null;
        $tahunTerpilih = $request->filled('tahun') ? (int) $request->tahun : null;
        $userId = auth()->id();

        $queryRiwayat = UangSaku::whereHas('santri', function ($query) use ($userId) {
            $query->where('user_id', $userId)->where('status', 'diterima');
        });

        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        $yearSql = $driver === 'sqlite' ? "strftime('%Y', created_at) as tahun" : "YEAR(created_at) as tahun";

        $tahunList = (clone $queryRiwayat)
            ->selectRaw($yearSql)
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $santris = Santri::with(['uangSaku' => function($q) use ($bulanTerpilih, $tahunTerpilih) {
                if ($bulanTerpilih) {
                    $q->whereMonth('created_at', $bulanTerpilih);
                }

                if ($tahunTerpilih) {
                    $q->whereYear('created_at', $tahunTerpilih);
                }

                $q->orderBy('id', 'desc');
            }])
            ->where('user_id', $userId)
            ->where('status', 'diterima')
            ->get();

        return view('user.uang_saku', compact(
            'santris', 'bulanList', 'tahunList', 'bulanTerpilih', 'tahunTerpilih'
        ));
    }

    public function cetakKuitansiUangSaku($id)
    {
        $transaksi = \App\Models\UangSaku::whereHas('santri', function ($q) {
            $q->where('user_id', auth()->id());
        })->with(['santri.orangTua', 'admin'])->where('jenis', 'masuk')->findOrFail($id);

        $setting = \App\Models\InvoiceSetting::getSetting();
        $fileName = 'Kuitansi_Titipan_' . \Illuminate\Support\Str::slug($transaksi->santri->nama_lengkap) . '_' . $transaksi->id . '.pdf';

        if (request('download') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.kuitansi_uang_saku', [
                'transaksi' => $transaksi,
                'setting' => $setting,
                'isPdf' => true,
            ])->setPaper('a4', 'portrait');
            return $pdf->download($fileName);
        }

        return view('pdf.kuitansi_uang_saku', [
            'transaksi' => $transaksi,
            'setting' => $setting,
            'fileName' => $fileName,
            'isPdf' => false,
        ]);
    }

    public function store(Request $request)
    {
        // 1. Cek duplikasi santri (NIK dan NISN harus unik per anak)
        $duplicateSantri = Santri::where('nik', $request->nik)
            ->orWhere('nisn', $request->nisn)
            ->first();

        if ($duplicateSantri) {
            $errors = [];
            if ($duplicateSantri->nik === $request->nik) {
                $errors['nik'] = ['Data santri dengan NIK ini sudah pernah didaftarkan.'];
            }
            if ($duplicateSantri->nisn === $request->nisn) {
                $errors['nisn'] = ['Data santri dengan NISN ini sudah pernah didaftarkan.'];
            }
            return response()->json([
                'success' => false,
                'message' => 'Data santri dengan NIK/NISN ini sudah pernah didaftarkan.',
                'errors' => $errors
            ], 422);
        }

        // 2. Tentukan aturan validasi NSM dinamis
        $nsmRule = 'nullable|numeric';
        if ($request->sekolah_asal_jenjang === 'MI') {
            $nsmRule = 'required|numeric';
        }

        // Validasi data
        $validator = Validator::make($request->all(), [
            // Data Calon Santri
            'nama_lengkap' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'nik' => 'required|digits:16',
            'sekolah_asal_jenjang' => 'required|in:SD,MI,Lainnya',
            'sekolah_asal' => 'required|string|max:255',
            'npsn' => 'required|numeric',
            'nsm' => $nsmRule,
            'nisn' => 'required|digits:10',
            'anak_ke' => 'required|integer|min:1',
            'dari_bersaudara' => 'required|integer|min:1',
            'prestasi' => 'nullable|string',
            'riwayat_penyakit' => 'nullable|string',
            'hobi' => 'nullable|string|max:255',
            'ekstrakurikuler' => 'nullable|array',
            'tujuan_mendaftar' => 'required|string',
            
            // Data Ayah
            'nama_ayah' => 'required|string|max:255',
            'no_wa_ayah' => ['required', 'string', 'regex:/^(08|628|\\+628)[0-9]{8,13}$/'],
            'no_kk' => 'required|digits:16',
            'nik_ayah' => 'required|digits:16',
            'tempat_lahir_ayah' => 'required|string|max:255',
            'tanggal_lahir_ayah' => 'required|date',
            'pekerjaan_ayah' => 'required|string|max:255',
            'penghasilan_ayah' => 'required|string|max:100',
            'penghasilan_ayah_lainnya' => 'required_if:penghasilan_ayah,Lainnya|nullable|string|max:100',
            'alamat_domisili_ayah' => 'required|string',
            
            // Data Ibu
            'nama_ibu' => 'required|string|max:255',
            'nik_ibu' => 'required|digits:16',
            'tempat_lahir_ibu' => 'required|string|max:255',
            'tanggal_lahir_ibu' => 'required|date',
            'pekerjaan_ibu' => 'required|string|max:255',
            'penghasilan_ibu' => 'required|string|max:100',
            'penghasilan_ibu_lainnya' => 'required_if:penghasilan_ibu,Lainnya|nullable|string|max:100',
            'alamat_domisili_ibu' => 'required|string',
            
            // Upload Dokumen
            'foto_santri'    => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'bukti_transfer' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'kartu_keluarga' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'ktp_ayah' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'ktp_ibu' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'rapor' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            // Custom error messages
            'nik.digits' => 'NIK harus terdiri dari 16 digit',
            'nisn.digits' => 'NISN harus terdiri dari 10 digit',
            'no_wa_ayah.regex' => 'Format nomor WhatsApp tidak valid',
            'no_kk.digits' => 'Nomor KK harus terdiri dari 16 digit',
            'nik_ayah.digits' => 'NIK Ayah harus terdiri dari 16 digit',
            'nik_ibu.digits' => 'NIK Ibu harus terdiri dari 16 digit',
            'bukti_transfer.max' => 'Ukuran file maksimal 5 MB',
            'bukti_transfer.mimes' => 'Format file harus JPG, JPEG, PNG, atau PDF',
            'kartu_keluarga.max' => 'Ukuran file maksimal 5 MB',
            'kartu_keluarga.mimes' => 'Format file harus JPG, JPEG, PNG, atau PDF',
            'ktp_ayah.max' => 'Ukuran file maksimal 5 MB',
            'ktp_ayah.mimes' => 'Format file harus JPG, JPEG, PNG, atau PDF',
            'ktp_ibu.max' => 'Ukuran file maksimal 5 MB',
            'ktp_ibu.mimes' => 'Format file harus JPG, JPEG, PNG, atau PDF',
            'rapor.max' => 'Ukuran file maksimal 5 MB',
            'rapor.mimes' => 'Format file harus JPG, JPEG, PNG, atau PDF',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Normalisasi nomor WA Ayah ke format 62xxx
            $noWaAyah = $request->no_wa_ayah;
            $noWaAyah = preg_replace('/^\+/', '', $noWaAyah);
            if (preg_match('/^08/', $noWaAyah)) {
                $noWaAyah = '62' . substr($noWaAyah, 1);
            }

            // Simpan / gunakan data orang tua (jika mendaftar anak ke-2, ke-3 dst dengan No KK yang sama)
            $orangTuaData = [
                'nama_ayah' => $request->nama_ayah,
                'no_wa_ayah' => $noWaAyah,
                'no_kk' => $request->no_kk,
                'nik_ayah' => $request->nik_ayah,
                'tempat_lahir_ayah' => $request->tempat_lahir_ayah,
                'tanggal_lahir_ayah' => $request->tanggal_lahir_ayah,
                'pekerjaan_ayah' => $request->pekerjaan_ayah,
                'penghasilan_ayah' => $request->penghasilan_ayah === 'Lainnya'
                    ? $request->penghasilan_ayah_lainnya
                    : $request->penghasilan_ayah,
                'alamat_domisili_ayah' => $request->alamat_domisili_ayah,
                'nama_ibu' => $request->nama_ibu,
                'nik_ibu' => $request->nik_ibu,
                'tempat_lahir_ibu' => $request->tempat_lahir_ibu,
                'tanggal_lahir_ibu' => $request->tanggal_lahir_ibu,
                'pekerjaan_ibu' => $request->pekerjaan_ibu,
                'penghasilan_ibu' => $request->penghasilan_ibu === 'Lainnya'
                    ? $request->penghasilan_ibu_lainnya
                    : $request->penghasilan_ibu,
                'alamat_domisili_ibu' => $request->alamat_domisili_ibu,
            ];

            $orangTua = OrangTua::where('no_kk', $request->no_kk)->first();
            if ($orangTua) {
                $orangTua->update($orangTuaData);
            } else {
                $orangTua = OrangTua::create($orangTuaData);
            }

            // NSM auto "0" jika SD
            $nsm = $request->nsm;
            if ($request->sekolah_asal_jenjang === 'SD') {
                $nsm = '0';
            }

            // Ambil angkatan aktif untuk disimpan
            $angkatanAktif = Angkatan::open()->first();
            // Tahun masuk mengikuti angkatan pendaftaran. Semua pendaftar baru
            // memulai pendidikan dari Kelas 1; status diterima tetap ditentukan admin.
            $tahunMasuk = $angkatanAktif?->tahun_angkatan
                ?? ($angkatanAktif?->tahun_ajaran ? (int) explode('/', $angkatanAktif->tahun_ajaran)[0] : (int) date('Y'));

            // Upload foto santri jika ada
            $fotoPath = null;
            if ($request->hasFile('foto_santri')) {
                $file = $request->file('foto_santri');
                $fileName = 'santri_reg_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $fotoPath = $file->storeAs('santri/foto', $fileName, 'public');
            }

            // Simpan data santri
            $santri = Santri::create([
                'nama_lengkap'    => $request->nama_lengkap,
                'tempat_lahir'    => $request->tempat_lahir,
                'tanggal_lahir'   => $request->tanggal_lahir,
                'nik'             => $request->nik,
                'sekolah_asal'    => '[' . $request->sekolah_asal_jenjang . '] ' . $request->sekolah_asal,
                'npsn'            => $request->npsn,
                'nsm'             => $nsm,
                'nisn'            => $request->nisn,
                'anak_ke'         => $request->anak_ke,
                'dari_bersaudara' => $request->dari_bersaudara,
                'prestasi'        => $request->prestasi,
                'riwayat_penyakit'=> $request->riwayat_penyakit,
                'hobi'            => $request->hobi,
                'ekstrakurikuler' => $request->ekstrakurikuler ? json_encode($request->ekstrakurikuler) : null,
                'tujuan_mendaftar'=> $request->tujuan_mendaftar,
                'status'          => 'menunggu_verifikasi',
                'kelas'           => '1',
                'status_santri'   => 'aktif',
                'tahun_masuk'     => (string) $tahunMasuk,
                'tahun_ajaran'    => $angkatanAktif ? $angkatanAktif->tahun_ajaran : date('Y') . '/' . (date('Y') + 1),
                'angkatan_id'     => $angkatanAktif ? $angkatanAktif->id : null,
                'user_id'         => auth()->id(),
                'orang_tua_id'    => $orangTua->id,
                'foto_santri'     => $fotoPath,
            ]);

            // Upload dan simpan dokumen
            $dokumenData = [
                'santri_id' => $santri->id,
                'bukti_transfer' => $this->uploadFile($request->file('bukti_transfer'), 'bukti_transfer'),
                'kartu_keluarga' => $this->uploadFile($request->file('kartu_keluarga'), 'kartu_keluarga'),
                'ktp_ayah' => $this->uploadFile($request->file('ktp_ayah'), 'ktp_ayah'),
                'ktp_ibu' => $this->uploadFile($request->file('ktp_ibu'), 'ktp_ibu'),
                'rapor' => $this->uploadFile($request->file('rapor'), 'rapor'),
            ];

            Dokumen::create($dokumenData);

            \App\Services\NotificationService::send([
                'recipient_number' => $orangTua->no_wa_ayah,
                'recipient_name' => $orangTua->nama_ayah,
                'user_id' => auth()->id(),
                'santri_id' => $santri->id,
                'modul' => 'PPDB',
                'priority' => 'normal',
                'reference_id' => 'PPDB-DAFTAR-' . $santri->id,
                'message' => "Pendaftaran calon santri {$santri->nama_lengkap} telah kami terima dan sedang menunggu proses verifikasi.",
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pendaftaran berhasil disimpan',
                'santri_id' => $santri->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkDuplicate(Request $request)
    {
        $type = $request->input('type');
        $value = $request->input('value');

        if (!$type || !$value) {
            return response()->json(['duplicate' => false]);
        }

        $duplicate = false;
        $message = null;

        if ($type === 'nik') {
            $duplicate = Santri::where('nik', $value)->exists();
            $message = $duplicate ? 'NIK ini sudah pernah didaftarkan untuk santri lain.' : null;
        } elseif ($type === 'nisn') {
            $duplicate = Santri::where('nisn', $value)->exists();
            $message = $duplicate ? 'NISN ini sudah pernah didaftarkan untuk santri lain.' : null;
        }
        // no_kk TIDAK dicek sebagai duplikat — 1 keluarga bisa mendaftarkan beberapa anak

        return response()->json([
            'duplicate' => $duplicate,
            'message' => $message,
        ]);
    }

    private function uploadFile($file, $prefix)
    {
        if ($file) {
            $fileName = $prefix . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('dokumen', $fileName, 'public');

            // Copy to public/storage/dokumen to ensure physical web access on Windows/Laragon
            $publicPath = public_path('storage/dokumen');
            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0777, true);
            }
            @copy(storage_path('app/public/dokumen/' . $fileName), $publicPath . '/' . $fileName);

            return $fileName;
        }
        return null;
    }
}
