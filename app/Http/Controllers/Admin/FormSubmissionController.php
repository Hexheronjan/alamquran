<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FormSubmission;
use App\Models\FormGroup;
use App\Models\School;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use App\Services\NotificationService;

class FormSubmissionController extends Controller
{
    public function index()
    {
        $schools = School::first() ?? School::find(1); // Ambil data profil sekolah
        $nama_sekolah = $schools->name ?? 'Pesantren Tahfizh Alam Qur\'an';
        // Ambil semua form groups
        $formGroups = FormGroup::all();

        // Hitung total form submissions per group berdasarkan kondisi id_user dan submission_id
        foreach ($formGroups as $group) {
            $group->submission_count = DB::table('form_submissions')
                ->where('form_group_id', $group->id_form) // Hubungan ke grup
                ->select('submission_id') // Kelompokkan berdasarkan submission_id
                ->whereNotNull('user_id') // Hanya jika id_user ada
                ->distinct() // Hitung hanya submission_id unik
                ->count('submission_id');
        }

        return view('admin.form_submission.index', compact('formGroups', 'schools', 'nama_sekolah'));
    }
    
    // Menampilkan daftar submission berdasarkan group
    public function submissionsByGroup($formGroupId)
    {
        $formGroup = FormGroup::findOrFail($formGroupId);

        $submissions = FormSubmission::where('form_group_id', $formGroupId)
            ->get()
            ->groupBy('submission_id');

        return view('admin.form_submission.by_group', compact('formGroup', 'submissions'));
    }

    // Menampilkan detail submission berdasarkan SubmissionId
    public function submissionDetail($submissionId)
    {
        $formGroupId = FormSubmission::where('submission_id', $submissionId)->value('form_group_id');
        $formGroups = FormGroup::all();

        // Ambil semua field kecuali "form group id"
        $fields = FormSubmission::where('submission_id', $submissionId)
            ->whereRaw("LOWER(field_name) != 'form group id'")
            ->get();

        return view('admin.form_submission.detail_submission', compact(
            'fields', 'submissionId', 'formGroups', 'formGroupId'
        ));
    }
    

    public function updateStatus(Request $request, $submissionId)
    {
        $request->validate([
            'status' => 'required|in:pending,diterima,ditolak',
        ]);

        FormSubmission::where('submission_id', $submissionId)
            ->update(['status' => $request->status]);

        $firstField = FormSubmission::where('submission_id', $submissionId)->first();

        if ($firstField && $firstField->user) {
            $user = $firstField->user;
            $phone = $user->no_wa;
            $name = $user->name;
            $fields = FormSubmission::where('submission_id', $submissionId)->get();

            // Jika status diterima, otomatis masukkan/hubungkan ke Data Santri Aktif
            if ($request->status === 'diterima') {
                DB::transaction(function () use ($user, $fields, $name, $phone) {
                    $namaSantri = $fields->firstWhere('field_name', 'Nama Lengkap')?->value ?? $name;
                    $trimmedSantri = trim($namaSantri);

                    $santri = \App\Models\Santri::where('user_id', $user->id)
                        ->orWhereRaw('LOWER(TRIM(nama_lengkap)) = ?', [strtolower($trimmedSantri)])
                        ->first();

                    $activeStartYear = \App\Models\Santri::getActiveStartYear();
                    $angkatanAktif = \App\Models\Angkatan::open()->first() ?? \App\Models\Angkatan::where('is_active', true)->first();
                    $tahunMasuk = $angkatanAktif?->tahun_angkatan
                        ?? ($angkatanAktif?->tahun_ajaran ? (int) explode('/', $angkatanAktif->tahun_ajaran)[0] : $activeStartYear);
                    $tahunAjaran = $angkatanAktif?->tahun_ajaran ?: ($activeStartYear . '/' . ($activeStartYear + 1));

                    if (!$santri) {
                        $uniqueKk = substr('KK-' . time() . rand(10, 99), 0, 16);
                        $uniqueNikAyah = substr('NIKA' . time() . rand(10, 99), 0, 16);
                        $uniqueNikIbu = substr('NIKI' . time() . rand(10, 99), 0, 16);

                        $orangTua = \App\Models\OrangTua::create([
                            'nama_ayah'            => $name,
                            'no_wa_ayah'           => $phone,
                            'no_kk'                => $uniqueKk,
                            'nik_ayah'             => $uniqueNikAyah,
                            'tempat_lahir_ayah'    => 'Pendaftaran PPDB',
                            'tanggal_lahir_ayah'   => now()->toDateString(),
                            'pekerjaan_ayah'       => 'Lainnya',
                            'penghasilan_ayah'     => 'Lainnya',
                            'alamat_domisili_ayah' => 'Pendaftaran PPDB',
                            'nama_ibu'             => 'Ibu ' . $trimmedSantri,
                            'nik_ibu'              => $uniqueNikIbu,
                            'tempat_lahir_ibu'     => 'Pendaftaran PPDB',
                            'tanggal_lahir_ibu'    => now()->toDateString(),
                            'pekerjaan_ibu'        => 'Lainnya',
                            'penghasilan_ibu'      => 'Lainnya',
                            'alamat_domisili_ibu'  => 'Pendaftaran PPDB',
                        ]);

                        $uniqueNik = substr('M' . time() . rand(100, 999), 0, 16);
                        $uniqueNisn = substr('M' . rand(100000000, 999999999), 0, 10);

                        \App\Models\Santri::create([
                            'nama_lengkap'  => $trimmedSantri,
                            'status'        => 'diterima',
                            'kelas'         => '1',
                            'status_santri' => 'aktif',
                            'tahun_masuk'   => (string) $tahunMasuk,
                            'tahun_ajaran'  => $tahunAjaran,
                            'angkatan_id'   => $angkatanAktif?->id,
                            'user_id'       => $user->id,
                            'orang_tua_id'  => $orangTua->id,
                            'nik'           => $uniqueNik,
                            'nisn'          => $uniqueNisn,
                        ]);
                    } else {
                        $santri->update([
                            'status'        => 'diterima',
                            'status_santri' => 'aktif',
                            'user_id'       => $santri->user_id ?: $user->id,
                            'tahun_masuk'   => $santri->tahun_masuk ?: (string) $tahunMasuk,
                            'tahun_ajaran'  => $santri->tahun_ajaran ?: $tahunAjaran,
                            'angkatan_id'   => $santri->angkatan_id ?: $angkatanAktif?->id,
                        ]);
                    }
                });
            }

            $this->sendWhatsapp($phone, $name, $request->status, $submissionId, $fields);
        }

        return redirect()
            ->route('admin.form_submission.detail_submission', $submissionId)
            ->with('success', 'Status berhasil diperbarui dan notifikasi dikirim.');
    }

    private function sendWhatsapp($nomorWA, $userName, $status, $submissionId, $fields)
    {
        // Buat informasi data pendaftar
        $info = "";
        foreach ($fields as $field) {
            if (!$field->file_path && strtolower($field->field_name) != 'form group id') {
                $info .= "*{$field->field_name}:* {$field->value}\n";
            }
        }

        // Buat isi pesan berdasarkan status
        $statusMessage = match ($status) {
            'diterima' => "Selamat! Pendaftaran Anda telah *DITERIMA*.",
            'ditolak' => "Mohon maaf, pendaftaran Anda telah *DITOLAK*.",
            default => "Status pendaftaran Anda saat ini adalah *PENDING*.",
        };

        $message = "*Konfirmasi Pendaftaran PSB*\n\n"
                . "Halo *{$userName}*,\n\n"
                . "{$statusMessage}\n\n"
                . "*ID Pendaftaran:* {$submissionId}\n"
                . "{$info}\n"
                . "_Pesan ini dikirim otomatis oleh sistem PSB Sekolah._";

        try {
            $result = NotificationService::send([
                'recipient_number' => $nomorWA,
                'recipient_name' => $userName,
                'modul' => 'PPDB',
                'message' => $message,
                'reference_id' => 'PPDB-STATUS-' . $submissionId . '-' . $status,
            ]);

            if ($result) {
                \Log::info("WA konfirmasi pendaftaran terkirim ke $nomorWA", [
                    'message_id' => $result->id,
                ]);
            } else {
                \Log::error("Gagal kirim WA konfirmasi", [
                    'error' => 'Nomor tidak valid atau pesan tidak dapat dibuat'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error("Gagal kirim WA konfirmasi", [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function downloadPdf($submissionId)
    {
        $formGroupId = FormSubmission::where('submission_id', $submissionId)->value('form_group_id');

        $fields = FormSubmission::where('submission_id', $submissionId)
            ->whereRaw("LOWER(field_name) != 'form group id'")
            ->get();

        $school = School::first(); // Ambil data sekolah pertama

        $pdf = PDF::loadView('admin.form_submission.pdf', compact('fields', 'submissionId', 'school'))
                ->setPaper('A4', 'portrait');

        return $pdf->download('detail_pendaftaran_'.$submissionId.'.pdf');
    }

    // public function showSubmissionsByGroup($formGroupId)
    // {
    //     // Ambil form group beserta submissionsnya
    //     $formGroup = FormGroup::with(['submissions' => function ($query) {
    //         $query->groupBy('submission_id');
    //     }])->findOrFail($formGroupId);

    //     return view('admin.form_submission.by_group', compact('formGroup'));
    // }

}
