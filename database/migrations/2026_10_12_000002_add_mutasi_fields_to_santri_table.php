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
        Schema::table('santri', function (Blueprint $table) {
            if (!Schema::hasColumn('santri', 'keterangan_mutasi')) {
                $table->text('keterangan_mutasi')->nullable()->after('status_santri');
            }
            if (!Schema::hasColumn('santri', 'tanggal_mutasi')) {
                $table->date('tanggal_mutasi')->nullable()->after('keterangan_mutasi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            if (Schema::hasColumn('santri', 'tanggal_mutasi')) {
                $table->dropColumn('tanggal_mutasi');
            }
            if (Schema::hasColumn('santri', 'keterangan_mutasi')) {
                $table->dropColumn('keterangan_mutasi');
            }
        });
    }
};
