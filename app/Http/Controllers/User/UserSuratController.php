<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SuratKeluar;
use Illuminate\Http\Request;

class UserSuratController extends Controller
{
    public function index(Request $request)
    {
        // Tampilkan surat keluar yang ditujukan ke wali (umum)
        // Surat keluar dari pondok bisa dilihat semua wali
        $surats = SuratKeluar::orderByDesc('tanggal_surat')
            ->paginate(20);

        return view('user.surat.index', compact('surats'));
    }

    public function show($id)
    {
        $surat  = SuratKeluar::findOrFail($id);
        $school = \App\Models\School::first();
        return view('user.surat.show', compact('surat', 'school'));
    }
}
