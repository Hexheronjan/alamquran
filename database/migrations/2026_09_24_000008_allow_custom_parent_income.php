<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Allow a wali to enter a custom income value when selecting "Lainnya". */
    public function up(): void
    {
        Schema::table('orang_tua', function (Blueprint $table) {
            $table->string('penghasilan_ayah', 100)->nullable()->change();
            $table->string('penghasilan_ibu', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Cannot safely restore ENUM because custom values may already exist.
    }
};
