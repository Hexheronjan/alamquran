<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('angkatan', function (Blueprint $table) {
            // Tahun angkatan sebagai angka (misal: 2026) untuk kalkulasi kelas otomatis
            $table->unsignedSmallInteger('tahun_angkatan')->nullable()->after('tahun_ajaran');
        });
    }
    public function down(): void {
        Schema::table('angkatan', function (Blueprint $table) {
            $table->dropColumn('tahun_angkatan');
        });
    }
};
