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
            . "Bapak/Ibu wali santri dari ananda *{{nama_santri}}* (Kelas {{kelas}}),\n\n"
            . "Pemberitahuan bahwa tagihan SPP untuk periode *{{bulan}} {{tahun}}* telah diterbitkan.\n\n"
            . "• Rekening tujuan: *{{rekening_tujuan}}*\n\n"
            . "Rincian tagihan dapat dicek dan dikonfirmasi pembayarannya melalui tautan portal berikut:\n"
            . "{{link_spp}}\n\n"
            . "Mohon pembayaran dapat dilakukan tepat waktu. Jazakumullah khairan katsiran.\n"
            . "_Bagian Administrasi & Keuangan SPP_";

        DB::table('whatsapp_templates')
            ->where('kode', 'SPP_TAGIHAN_BULANAN')
            ->update([
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
