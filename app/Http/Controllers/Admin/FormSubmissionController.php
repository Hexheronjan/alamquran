<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FormSubmission;
use App\Models\FormGroup;
use App\Models\School;
use Illuminate\Support\Facades\DB;

class FormSubmissionController extends Controller
{
    public function index()
    {
        $schools = School::find(1); // Ambil sekolah dengan ID tertentu

        $nama_sekolah = $schools->name ?? 'Sekolah Tidak Ditemukan';
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

        $fields = FormSubmission::where('submission_id', $submissionId)
            ->get()
            ->filter(function ($field) {
                return !in_array(strtolower($field->field_name), ['form group id']);
            })
            ->map(function ($field) {
                if (strtolower($field->field_name) === 'foto calon peserta didik') {
                    $field->value = null;
                }
                return $field;
            });

        return view('admin.form_submission.detail_submission', compact('fields', 'submissionId', 'formGroups', 'formGroupId'));
    }
    

    public function updateStatus(Request $request, $submissionId)
    {
        // Validasi input
        $request->validate([
            'status' => 'required|in:pending,diterima,ditolak',
        ]);

        // Update status untuk semua field dengan submission_id yang sama
        FormSubmission::where('submission_id', $submissionId)
            ->update(['status' => $request->status]);

        return redirect()->route('admin.form_submission.index')->with('success', 'Status berhasil diperbarui.');
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
