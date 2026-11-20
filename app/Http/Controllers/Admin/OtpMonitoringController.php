<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OtpMonitoringController extends Controller
{
    /**
     * Tampilkan halaman monitoring OTP pendaftar.
     */
    public function index(Request $request)
    {
        $now = Carbon::now();

        // Ringkasan Metrics
        $totalActive = User::whereNotNull('otp_code')
            ->where('otp_expired_at', '>', $now)
            ->count();

        $totalSuccess = User::whereNotNull('otp_code')
            ->where('otp_expired_at', '>', $now)
            ->where('otp_delivery_status', 'berhasil')
            ->count();

        $totalFailed = User::whereNotNull('otp_code')
            ->where('otp_expired_at', '>', $now)
            ->where('otp_delivery_status', 'gagal')
            ->count();

        $totalExpired = User::whereNotNull('otp_code')
            ->where('otp_expired_at', '<=', $now)
            ->count();

        // Query Data OTP
        $query = User::whereNotNull('otp_code');

        // Filter Status Tab (Default: active)
        $filter = $request->get('filter', 'active');
        if ($filter === 'active') {
            $query->where('otp_expired_at', '>', $now);
        } elseif ($filter === 'failed') {
            $query->where('otp_expired_at', '>', $now)
                ->where('otp_delivery_status', 'gagal');
        } elseif ($filter === 'success') {
            $query->where('otp_expired_at', '>', $now)
                ->where('otp_delivery_status', 'berhasil');
        } elseif ($filter === 'expired') {
            $query->where('otp_expired_at', '<=', $now);
        }
        // jika filter == 'all', tidak dibatasi status

        // Pencarian (Nama, No WA, Email)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('no_wa', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $otpUsers = $query->orderByDesc('otp_created_at')
            ->orderByDesc('otp_expired_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.otp.index', compact(
            'otpUsers',
            'totalActive',
            'totalSuccess',
            'totalFailed',
            'totalExpired',
            'filter'
        ));
    }

    /**
     * Kirim ulang kode OTP aktif via WhatsApp Gateway.
     */
    public function resend(User $user)
    {
        if (!$user->isOtpActive()) {
            return back()->with('error', 'Tidak dapat mengirim ulang. OTP pendaftar ini sudah kedaluwarsa atau tidak aktif.');
        }

        $formattedTime = $user->otp_expired_at ? $user->otp_expired_at->translatedFormat('H:i') . ' WIB' : '5 menit';

        $message = "*Kode OTP Pendaftaran PPDB*\n\n"
            . "Halo *{$user->name}*,\n\n"
            . "Berikut adalah kode OTP untuk verifikasi akun PPDB Pesantren Tahfizh Alam Qur'an Anda:\n\n"
            . "*{$user->otp_code}*\n\n"
            . "Kode ini berlaku sampai *{$formattedTime}*.\n\n"
            . "Jangan berikan kode ini kepada siapa pun.";

        $result = NotificationService::sendSensitive([
            'recipient_number' => $user->no_wa,
            'recipient_name' => $user->name,
            'user_id' => $user->id,
            'modul' => 'OTP Registrasi',
            'message' => $message,
            'audit_body' => '[OTP registrasi dikirim ulang; isi tidak disimpan]',
            'reference_id' => 'OTP-RESEND-' . $user->id . '-' . $user->otp_created_at?->timestamp,
            'sync' => true,
        ]);

        if ($result && in_array($result->status, ['pending', 'processing', 'sent'], true)) {
            $user->otp_delivery_status = $result->status === 'sent' ? 'berhasil' : 'menunggu';
            $user->otp_delivery_error = null;
            $user->save();

            return back()->with('success', "Kode OTP aktif ({$user->otp_code}) berhasil dikirim ulang ke nomor WhatsApp {$user->no_wa}.");
        } else {
            $user->otp_delivery_status = 'gagal';
            $user->otp_delivery_error = $result?->error_message ?? 'Gagal mengirim pesan WhatsApp';
            $user->save();

            return back()->with('error', 'Gagal mengirim ulang OTP via WhatsApp Gateway: ' . ($result['error'] ?? 'Layanan gateway bermasalah.') . '. Silakan berikan kode OTP secara manual kepada pendaftar.');
        }
    }

    /**
     * Perbarui nomor WhatsApp wali santri langsung dari monitoring OTP.
     */
    public function updatePhone(Request $request, User $user)
    {
        $request->validate([
            'no_wa' => ['required', 'string', 'max:25'],
        ], [
            'no_wa.required' => 'Nomor WhatsApp baru wajib diisi.',
        ]);

        $normalizedWa = \App\Services\WhatsappService::formatPhoneNumber($request->no_wa);
        if (!$normalizedWa) {
            return back()->with('error', 'Format nomor WhatsApp tidak valid. Gunakan format 08xxxxxxxxxx atau 628xxxxxxxxxx.');
        }

        // Cek apakah nomor sudah dipakai user lain
        $alreadyUsed = User::where('id', '!=', $user->id)
            ->whereIn('no_wa', \App\Services\AkunWaliService::phoneLookupVariants($normalizedWa))
            ->exists();
        if ($alreadyUsed) {
            return back()->with('error', "Nomor WhatsApp {$normalizedWa} sudah terdaftar pada akun wali lainnya.");
        }

        $oldWa = $user->no_wa;
        $user->no_wa = $normalizedWa;
        $user->save();

        // Sinkronkan ke seluruh data santri milik wali ini
        \App\Models\Santri::where('user_id', $user->id)->get()->each(function ($santri) use ($normalizedWa) {
            if ($santri->orangTua) {
                $santri->orangTua->update(['no_wa_ayah' => $normalizedWa]);
            }
        });

        \App\Services\AdminActivityLogger::log(
            'update_otp_user_wa',
            "Mengubah nomor WhatsApp akun wali '{$user->name}' dari {$oldWa} menjadi {$normalizedWa} dari Monitoring OTP",
            'User',
            $user->id
        );

        return back()->with('success', "Nomor WhatsApp untuk wali {$user->name} berhasil diperbarui menjadi {$normalizedWa}. Kode OTP berikutnya akan otomatis dikirim ke nomor ini.");
    }
}
