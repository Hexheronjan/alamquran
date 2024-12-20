<?php

namespace App\Http\Controllers\User;

use App\Models\FormField;
use App\Models\FormGroup;
use App\Models\FormSubmission;
use Illuminate\Http\Request;

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
                ]);

                // dd($filePath);
            } else {
                // Jika bukan file, simpan seperti biasa
                FormSubmission::create([
                    'field_name' => $formattedFieldName,
                    'value' => is_array($value) ? json_encode($value) : $value,
                    'submission_id' => $submissionId,
                    'user_id' => auth()->id(),
                    'status' => 'pending',
                    'form_group_id' => $request->form_group_id, // Simpan form group ID
                ]);
            }
        }
      
        return redirect()->route('user.dashboard')->with('success', 'Form berhasil dikirim!');
    }

    public function userHistory()
    {
        // dd(auth()->id());
        // Ambil submission_id unik milik user yang sedang login
        $userSubmissions = FormSubmission::where('user_id', auth()->id())
            ->get()
            ->groupBy('submission_id'); // Mengelompokkan berdasarkan ID pendaftaran

            
        return view('user.form.history', compact('userSubmissions'));
    }
}
