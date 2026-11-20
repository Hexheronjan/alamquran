<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\SantriKesehatan;
use Illuminate\Http\Request;

class UserKondisiController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $santris = Santri::where('user_id', $user->id)
            ->where('status', 'diterima')
            ->get();

        $selectedSantriId = $request->get('santri_id', $santris->first()?->id);
        $activeSantri = $santris->firstWhere('id', $selectedSantriId);

        $kondisiList = SantriKesehatan::with('petugas')
            ->where('santri_id', $selectedSantriId)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15);

        // Kondisi terkini
        $kondisiTerbaru = SantriKesehatan::where('santri_id', $selectedSantriId)
            ->latest('tanggal')
            ->first();

        return view('user.kondisi.index', compact(
            'santris', 'activeSantri', 'selectedSantriId',
            'kondisiList', 'kondisiTerbaru'
        ));
    }
}
