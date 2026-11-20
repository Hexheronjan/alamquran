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
        if (Schema::hasTable('daftar_ulang_tagihans') && !Schema::hasColumn('daftar_ulang_tagihans', 'is_beasiswa')) {
            Schema::table('daftar_ulang_tagihans', function (Blueprint $table) {
                $table->boolean('is_beasiswa')->default(false)->after('gelombang');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('daftar_ulang_tagihans') && Schema::hasColumn('daftar_ulang_tagihans', 'is_beasiswa')) {
            Schema::table('daftar_ulang_tagihans', function (Blueprint $table) {
                $table->dropColumn('is_beasiswa');
            });
        }
    }
};
