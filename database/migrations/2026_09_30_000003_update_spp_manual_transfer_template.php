<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('whatsapp_templates')
            ->where('kode', 'SPP_TAGIHAN_BULANAN')
            ->where('konten', 'like', '%Virtual Account%')
            ->update([
                'konten' => "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\nBapak/Ibu wali santri dari ananda *{{nama_santri}}* (Kelas {{kelas}}),\n\nBerikut kami sampaikan rincian tagihan SPP untuk bulan *{{bulan}} {{tahun}}*:\n• Nominal: *Rp {{nominal}}*\n• Rekening tujuan: *{{rekening_tujuan}}*\n\nSilakan transfer dan unggah bukti pembayaran melalui portal wali. Mohon pembayaran dapat dilakukan tepat waktu. Jazakumullah khairan katsiran.\n_Bagian Administrasi & Keuangan SPP_",
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Template lama tidak dikembalikan karena berisi instruksi VA yang tidak lagi digunakan.
    }
};
