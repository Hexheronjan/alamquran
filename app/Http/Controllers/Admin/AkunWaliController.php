<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Services\AkunWaliService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AkunWaliController extends Controller
{
    /**
     * Endpoint untuk mengecek status akun wali santri.
     */
    public function checkStatus($santriId): JsonResponse
    {
        $santri = Santri::with(['orangTua', 'user'])->findOrFail($santriId);
        $status = AkunWaliService::checkWaliStatus($santri);

        return response()->json($status);
    }

    /**
     * Endpoint untuk generate akun wali santri.
     */
    public function generate(Request $request, $santriId): JsonResponse
    {
        $result = AkunWaliService::generateAkun((int) $santriId);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        // Untuk akun yang baru dibuat, kredensial langsung dikirim ke nomor
        // WhatsApp wali. Akun tetap berhasil dibuat jika gateway sedang gagal,
        // sehingga admin dapat melakukan kirim ulang dari modal.
        $whatsapp = null;
        if (!($result['already_exists'] ?? false) && !empty($result['plain_password'])) {
            $whatsapp = AkunWaliService::sendCredentialsViaWa(
                $result['user']->id,
                $result['plain_password'],
                $result['santri_id'] ?? (int) $santriId,
            );
        }

        return response()->json([
            'success' => true,
            'already_exists' => (bool) ($result['already_exists'] ?? false),
            'user_id' => $result['user']->id,
            'username' => $result['username'],
            'nama_wali' => $result['nama_wali'],
            'santri_nama' => $result['santri_nama'] ?? null,
            'santri_id' => $result['santri_id'] ?? (int) $santriId,
            'plain_password' => $result['plain_password'] ?? null,
            'whatsapp_sent' => $whatsapp['success'] ?? null,
            'whatsapp_message' => $whatsapp['message'] ?? null,
            'message' => $result['message'],
        ]);
    }

    /**
     * Endpoint untuk mengirim kredensial login akun wali via WhatsApp.
     */
    public function sendWa(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'        => 'required|exists:users,id',
            'plain_password' => 'nullable|string',
            'santri_id'      => 'nullable|exists:santri,id',
        ]);

        $user = \App\Models\User::findOrFail((int) $request->user_id);

        $result = AkunWaliService::sendCredentialsViaWa(
            (int) $request->user_id,
            (string) ($request->plain_password ?? ''),
            $request->santri_id ? (int) $request->santri_id : null
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ]);
    }
}
