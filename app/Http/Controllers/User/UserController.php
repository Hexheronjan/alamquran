<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FormGroup;
use App\Models\School;

class UserController extends Controller
{
    public function dashboard()
    {
        // Ambil sekolah dengan ID tertentu
        $schools = School::find(1); 
        // Ambil semua form group beserta atributnya
        $formGroup = FormGroup::select('id_form', 'name', 'description')->get();
    
        // Debugging
        // dd($formGroup);
    
        return view('user.dashboard', compact('formGroup', 'schools'));
    }
    
}
