<?php

namespace App\Services;

use App\Models\OrangTua;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AkunWaliService
{
    /** Siapkan status banyak santri sekaligus agar tabel admin tidak menghasilkan query per baris. */
    public static function statusesFor(iterable $santris): array
    {
        $items = collect($santris);
        $phoneById = $items->mapWithKeys(function (Santri $santri) {
            $raw = $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa;
            return [$santri->id => WhatsappService::formatPhoneNumber($raw)];
        });
        $variants = $phoneById->filter()->flatMap(fn ($phone) => self::phoneLookupVariants($phone))->unique()->values();
        $users = $variants->isEmpty() ? collect() : User::whereIn('no_wa', $variants)->get();
        $userByPhone = [];
        foreach ($users as $user) {
            foreach (self::phoneLookupVariants($user->no_wa) as $variant) {
                $userByPhone[$variant] ??= $user;
            }
        }

        $statuses = [];
        foreach ($items as $santri) {
            $raw = $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa;
            $phone = $phoneById[$santri->id] ?? null;
            $user = $phone ? ($userByPhone[$phone] ?? null) : null;
            $statuses[$santri->id] = [
                'has_wa' => (bool) $phone,
                'no_wa_raw' => $raw ?: '-',
                'no_wa_normalized' => $phone,
                'has_account' => (bool) $user,
                'user' => $user,
                'status_code' => !$phone ? 'no_wa' : ($user ? 'active' : 'ready_to_generate'),
                'status_label' => !$phone ? '⚠ Belum ada No. WA' : ($user ? '✓ Akun Aktif' : 'Belum Ada Akun'),
                'sibling_count' => null,
            ];
        }

        return $statuses;
    }

    /**
     * Cek status kepemilikan akun wali santri.
     * Menggunakan prinsip: 1 Wali (berdasarkan No. WA Ternormalisasi) = 1 Akun.
     */
    public static function checkWaliStatus(Santri $santri): array
    {
        $rawWa = $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa;
        $normalized = WhatsappService::formatPhoneNumber($rawWa);

        if (!$normalized) {
            return [
                'has_wa'           => false,
                'no_wa_raw'        => $rawWa ?: '-',
                'no_wa_normalized' => null,
                'has_account'      => false,
                'user'             => null,
                'status_code'      => 'no_wa',
                'status_label'     => '⚠ Belum ada No. WA',
                'sibling_count'    => 0,
            ];
        }

        // Cari user berdasarkan no_wa yang dinormalisasi
        $user = User::whereIn('no_wa', array_merge(self::phoneLookupVariants($normalized), [$rawWa]))
            ->orderByRaw('CASE WHEN no_wa = ? THEN 0 ELSE 1 END', [$normalized])
            ->first();

        // Cari semua santri yang nomor WA ortunya sama (saudara kandung / 1 wali)
        $siblingCount = Santri::whereHas('orangTua', function ($q) use ($normalized) {
            $q->whereIn('no_wa_ayah', array_merge(self::phoneLookupVariants($normalized), [$rawWa]));
        })->count();

        if ($user) {
            return [
                'has_wa'           => true,
                'no_wa_raw'        => $rawWa,
                'no_wa_normalized' => $normalized,
                'has_account'      => true,
                'user'             => $user,
                'status_code'      => 'active',
                'status_label'     => '✓ Akun Aktif',
                'sibling_count'    => $siblingCount,
            ];
        }

        return [
            'has_wa'           => true,
            'no_wa_raw'        => $rawWa,
            'no_wa_normalized' => $normalized,
            'has_account'      => false,
            'user'             => null,
            'status_code'      => 'ready_to_generate',
            'status_label'     => 'Belum Ada Akun',
            'sibling_count'    => $siblingCount,
        ];
    }

    /**
     * Generate password sementara yang aman (kombinasi huruf besar, kecil, angka, 8 karakter).
     * Bukan 123456, bukan NIK, bukan tanggal lahir.
     */
    public static function generateSecureTempPassword(): string
    {
        $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lowercase = 'abcdefghjkmnpqrstuvwxyz';
        $numbers   = '23456789';

        $pass = '';
        $pass .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $pass .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $pass .= $numbers[random_int(0, strlen($numbers) - 1)];

        $allChars = $uppercase . $lowercase . $numbers;
        for ($i = 0; $i < 5; $i++) {
            $pass .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        return str_shuffle($pass);
    }

    /**
     * Generate Akun Wali Santri.
     * Mencegah duplikasi: 1 Wali (No. WA) = 1 Akun, menautkan semua santri milik wali tersebut.
     */
    public static function generateAkun(int $santriId): array
    {
        $santri = Santri::with(['orangTua', 'user'])->findOrFail($santriId);
        $rawWa = $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa;
        $normalized = WhatsappService::formatPhoneNumber($rawWa);

        if (!$normalized) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp wali belum diisi atau tidak valid. Silakan edit data santri terlebih dahulu.',
            ];
        }

        return DB::transaction(function () use ($santri, $rawWa, $normalized) {
            // Cek apakah akun dengan no_wa ini sudah ada
            $user = User::whereIn('no_wa', array_merge(self::phoneLookupVariants($normalized), [$rawWa]))
                ->orderByRaw('CASE WHEN no_wa = ? THEN 0 ELSE 1 END', [$normalized])
                ->lockForUpdate()
                ->first();

            if ($user) {
                // Tautkan santri ini dan semua santri lain yang memiliki no_wa sama
                self::linkAllSantriByPhone($normalized, $user->id, $rawWa);

                return [
                    'success'        => true,
                    'already_exists' => true,
                    'user'           => $user,
                    'username'       => $user->no_wa,
                    'nama_wali'      => $user->name,
                    'message'        => 'Nomor ini sudah memiliki akun aktif. Data santri telah berhasil ditautkan ke akun tersebut.',
                ];
            }

            // Buat akun baru
            $plainPassword = self::generateSecureTempPassword();
            $namaWali = $santri->orangTua?->nama_ayah ?: ('Wali ' . $santri->nama_lengkap);

            $email = $normalized . '@wali.alamquran.sch.id';
            if (User::where('email', $email)->exists()) {
                $email = $normalized . '_' . Str::random(4) . '@wali.alamquran.sch.id';
            }

            $user = User::create([
                'name'                  => $namaWali,
                'email'                 => $email,
                'no_wa'                 => $normalized,
                'password'              => Hash::make($plainPassword),
                'must_change_password'  => true,
            ]);

            // Tautkan santri ini dan santri lain dengan no_wa sama ke akun ini
            self::linkAllSantriByPhone($normalized, $user->id, $rawWa);

            // Simpan audit log
            AdminActivityLogger::log(
                'generate_akun_wali',
                "Membuat akun wali baru: '{$user->name}' (No. WA / Username: {$user->no_wa}) untuk santri {$santri->nama_lengkap}",
                'User',
                $user->id
            );

            return [
                'success'        => true,
                'already_exists' => false,
                'user'           => $user,
                'username'       => $user->no_wa,
                'plain_password' => $plainPassword,
                'nama_wali'      => $user->name,
                'santri_nama'    => $santri->nama_lengkap,
                'santri_id'      => $santri->id,
                'message'        => 'Akun wali berhasil dibuat.',
            ];
        });
    }

    /**
     * Tautkan semua santri yang orang tuanya memiliki nomor WA sama ke akun user yang ditentukan.
     */
    public static function linkAllSantriByPhone(string $normalizedPhone, int $userId, ?string $rawPhone = null): int
    {
        $variants = self::phoneLookupVariants($normalizedPhone);
        if ($rawPhone) {
            $variants[] = $rawPhone;
        }
        $orangTuaIds = OrangTua::whereIn('no_wa_ayah', array_unique($variants))->pluck('id');
        if ($orangTuaIds->isEmpty()) {
            return 0;
        }

        $count = Santri::whereIn('orang_tua_id', $orangTuaIds)->count();
        Santri::whereIn('orang_tua_id', $orangTuaIds)->update(['user_id' => $userId]);
        OrangTua::whereIn('id', $orangTuaIds)->update(['no_wa_ayah' => $normalizedPhone]);

        return $count;
    }

    /**
     * Kirim data aktivasi / informasi login akun wali melalui WhatsApp.
     */
    public static function sendCredentialsViaWa(int $userId, string $plainPassword = '', ?int $santriId = null): array
    {
        $user = User::findOrFail($userId);
        $santri = $santriId ? Santri::find($santriId) : $user->santri()->first();

        $msg = NotificationService::sendAkunWali($user, $plainPassword, $santri, sync: true);

        if ($msg && in_array($msg->status, ['pending', 'processing', 'sent'], true)) {
            AdminActivityLogger::log(
                'kirim_kredensial_wa',
                "Mengirim informasi akun wali '{$user->name}' via WhatsApp ke {$user->no_wa} (BERHASIL)",
                'User',
                $user->id
            );

            return [
                'success' => true,
                'message' => "Notifikasi login akun wali berhasil dikirimkan via WhatsApp ke nomor {$user->no_wa}.",
            ];
        }

        $error = $msg?->error_message ?? 'Gagal mengirim pesan WhatsApp. Gateway sedang offline atau nomor tidak dapat dihubungi.';

        AdminActivityLogger::log(
            'kirim_kredensial_wa_gagal',
            "Gagal mengirim data login akun wali '{$user->name}' via WA: {$error}",
            'User',
            $user->id
        );

        return [
            'success' => false,
            'message' => $error,
        ];
    }

    /** Nomor Indonesia lama, nomor canonical, dan varian internasional yang umum dipakai. */
    public static function phoneLookupVariants(string $phone): array
    {
        $normalized = WhatsappService::formatPhoneNumber($phone);
        if (!$normalized || !str_starts_with($normalized, '628')) {
            return array_values(array_unique([$phone]));
        }

        return array_values(array_unique([
            $normalized,
            '0' . substr($normalized, 2),
            '+' . $normalized,
        ]));
    }
}
