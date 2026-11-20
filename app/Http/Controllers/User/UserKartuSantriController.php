<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use Illuminate\Http\Request;

class UserKartuSantriController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $santris = Santri::where('user_id', $user->id)
            ->where('status', 'diterima')
            ->with('orangTua', 'angkatan')
            ->get();

        $selectedSantriId = $request->get('santri_id', $santris->first()?->id);
        $activeSantri = $santris->firstWhere('id', $selectedSantriId);

        return view('user.kartu-santri.index', compact(
            'santris', 'activeSantri', 'selectedSantriId', 'user'
        ));
    }
}
