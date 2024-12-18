<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FormSubmission;

class FormSubmissionController extends Controller
{
    public function verifySubmissions()
    {
        // Ambil semua data form submissions dan kelompokkan berdasarkan submission_id
        $allSubmissions = FormSubmission::with('user') // Relasi opsional ke tabel users
            ->get()
            ->groupBy('submission_id');

        $status = $submission->status ?? 'pending'; // Jika belum ada, default 'pending'

        return view('admin.form_submission.index', compact('allSubmissions', 'status'));
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

}
