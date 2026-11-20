<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santri', function ($table) {
            $table->dropUnique('santri_nik_unique');
            $table->dropUnique('santri_nisn_unique');
        });
    }

    public function down(): void
    {
        Schema::table('santri', function ($table) {
            $table->unique('nik');
            $table->unique('nisn');
        });
    }
};
