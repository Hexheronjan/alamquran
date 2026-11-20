<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('santri', function (Blueprint $table) {
            // Tahun ajaran terakhir santri naik kelas — mencegah double-promotion
            $table->string('promoted_tahun_ajaran', 20)->nullable()->after('tahun_lulus');
        });
    }
    public function down(): void {
        Schema::table('santri', function (Blueprint $table) {
            $table->dropColumn('promoted_tahun_ajaran');
        });
    }
};
