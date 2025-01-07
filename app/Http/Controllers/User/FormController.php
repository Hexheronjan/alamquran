<?php

namespace App\Http\Controllers\User;

use App\Models\FormField;
use App\Models\FormGroup;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\School;

class FormController extends Controller
{
    // Menampilkan form dinamis
    public function showForm($id_form)
    {
        // dd(auth()->user());
        $form = FormGroup::with('fields')->findOrFail($id_form);
        return view('user.form.show', compact('form'));
    }

    // Menyimpan data form
    public function submitForm(Request $request)
    {
        $submissionId = time(); // ID unik untuk setiap form submission
        $now = Carbon::now('Asia/Jakarta');
        $dateToday = $now->toDateString();
    
        foreach ($request->except('_token') as $fieldName => $value) {
            // Format nama field
            $formattedFieldName = ucwords(str_replace('_', ' ', $fieldName));
    
            // Periksa apakah input adalah file
            if ($request->hasFile($fieldName)) {
                $file = $request->file($fieldName);
                $fileName = time() . '_' . $file->getClientOriginalName(); // Format nama file
                $filePath = $file->storeAs('uploads', $fileName, 'public'); // Simpan file di folder 'uploads' dalam storage
    
                FormSubmission::create([
                    'field_name' => $formattedFieldName,
                    'value' => $fileName, // Nama file asli (opsional)
                    'file_path' => $filePath, // Path file di storage
                    'submission_id' => $submissionId,
                    'user_id' => auth()->id(),
                    'status' => 'pending',
                    'form_group_id' => $request->form_group_id, // Simpan form group ID
                    'registration_date' => $dateToday, // Set tanggal pendaftaran saat ini
                ]);
            } else {
                // Jika bukan file, simpan seperti biasa
                FormSubmission::create([
                    'field_name' => $formattedFieldName,
                    'value' => is_array($value) ? json_encode($value) : $value,
                    'submission_id' => $submissionId,
                    'user_id' => auth()->id(),
                    'status' => 'pending',
                    'form_group_id' => $request->form_group_id, // Simpan form group ID
                    'registration_date' => $dateToday, // Set tanggal pendaftaran saat ini
                ]);
            }
        }
        return redirect()->route('user.dashboard')->with('success', 'Form berhasil dikirim!');
    }
    
    public function userHistory()
    {
        // Ambil submission_id unik milik user yang sedang login
        $userSubmissions = FormSubmission::where('user_id', auth()->id())
            ->get()
            ->groupBy('submission_id') // Mengelompokkan berdasarkan ID pendaftaran
            ->map(function ($fields) {
                // Ambil hanya informasi penting: status dan ID grup
                return [
                    'status' => $fields->first()->status ?? 'pending',
                    'registration_date' => $fields->first()->registration_date,
                    'form_group_id' => $fields->first()->form_group_id ?? null,
                ];
            });

        return view('user.form.history', compact('userSubmissions'));
    }
    
    public function detail($submissionId)
    {
        $schools = School::find(1); // Ambil data sekolah pertama
    
        // Ambil salah satu entri untuk mendapatkan form_group_id
        $formGroupId = FormSubmission::where('submission_id', $submissionId)->value('form_group_id');
    
        // Ambil data FormGroup berdasarkan form_group_id
        $form = FormGroup::with('fields')->findOrFail($formGroupId);
    
        // Ambil data fields dari FormSubmission
        $fields = FormSubmission::where('submission_id', $submissionId)
            ->get()
            ->filter(function ($field) {
                // Hapus field dengan nama 'form group id'
                return !in_array(strtolower($field->field_name), ['form group id']);
            })
            ->map(function ($field) {
                // Kosongkan value untuk 'foto calon peserta didik'
                if (strtolower($field->field_name) === 'foto calon peserta didik') {
                    $field->value = null;
                }
                return $field;
            });
    
        // Ambil status dan tanggal pendaftaran dari field pertama
        $status = $fields->first()->status ?? 'pending';
        $registration_date = $fields->first()->registration_date ?? null;
    
        return view('user.form.detail_history', compact('submissionId', 'fields', 'status', 'registration_date', 'schools', 'form'));
    }
}
