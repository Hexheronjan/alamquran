<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            // Toko kantin yang dikelola oleh admin kasir.
            // NULL = akses ke semua toko (super admin / keuangan).
            $table->enum('assigned_toko', ['kantin_1', 'kantin_2', 'aqumart'])
                  ->nullable()
                  ->after('assigned_kelas')
                  ->comment('Toko kantin yang dikelola admin ini (null = semua toko)');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('assigned_toko');
        });
    }
};
