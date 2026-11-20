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
        Schema::table('kegiatan_harians', function (Blueprint $table) {
            $table->string('hari', 20)->default('semua')->after('deskripsi'); // semua, senin, selasa, rabu, kamis, jumat, sabtu, minggu
            $table->string('kelas', 20)->default('semua')->after('hari'); // semua, 1, 2, 3
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatan_harians', function (Blueprint $table) {
            $table->dropColumn(['hari', 'kelas']);
        });
    }
};
