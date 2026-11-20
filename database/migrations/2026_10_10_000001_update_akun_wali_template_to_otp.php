<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $newTemplate = "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\n"
            . "Bapak/Ibu *{{nama_wali}}* (Wali dari *{{nama_santri}}*),\n\n"
            . "Akun Orang Tua/Wali untuk portal Pesantren Tahfizh Alam Qur'an telah aktif.\n\n"
            . "*CARA MASUK KE PORTAL:*\n"
            . "1. Buka tautan: {{link_login}}\n"
            . "2. Masukkan Nomor WhatsApp: *{{no_wa}}*\n"
            . "3. Klik tombol *Minta OTP*, lalu masukkan 6 digit kode yang dikirimkan ke WhatsApp ini.\n\n"
            . "Jazakumullah khairan katsiran.\n"
            . "_Pesantren Tahfizh Alam Qur'an_";

        DB::table('whatsapp_templates')
            ->where('kode', 'AKUN_WALI_BARU')
            ->update([
                'nama'       => 'Notifikasi Akun Wali Baru (OTP)',
                'judul'      => 'Aktivasi Akun Wali Santri',
                'konten'     => $newTemplate,
                'is_active'  => true,
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
