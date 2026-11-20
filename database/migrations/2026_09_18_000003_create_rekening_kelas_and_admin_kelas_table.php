<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rekening Ustadz Per Kelas
        Schema::create('rekening_kelas', function (Blueprint $table) {
            $table->id();
            $table->string('kelas', 10); // '1', '2', '3'
            $table->string('nama_ustadz');
            $table->string('bank_name');
            $table->string('nomor_rekening');
            $table->string('atas_nama');
            $table->string('no_wa_ustadz')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Kolom penugasan kelas pada admin (khusus ustadz kelas)
        Schema::table('admins', function (Blueprint $table) {
            $table->string('assigned_kelas', 10)->nullable()->after('role'); // '1', '2', '3', atau null (semua)
        });

        // Kolom rekening ustadz tujuan pada deposit transfer manual
        Schema::table('deposit_transfer_manuals', function (Blueprint $table) {
            $table->foreignId('rekening_kelas_id')->nullable()->after('santri_id')->constrained('rekening_kelas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposit_transfer_manuals', function (Blueprint $table) {
            $table->dropForeign(['rekening_kelas_id']);
            $table->dropColumn('rekening_kelas_id');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('assigned_kelas');
        });

        Schema::dropIfExists('rekening_kelas');
    }
};
