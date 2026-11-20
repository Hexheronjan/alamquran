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
        if (Schema::hasTable('daftar_ulang_tagihans')) {
            if (!Schema::hasColumn('daftar_ulang_tagihans', 'nominal_addon')) {
                Schema::table('daftar_ulang_tagihans', function (Blueprint $table) {
                    $table->decimal('nominal_addon', 12, 2)->default(0)->after('nominal_biaya');
                });
            }
        }

        if (!Schema::hasTable('daftar_ulang_tagihan_addons')) {
            Schema::create('daftar_ulang_tagihan_addons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('daftar_ulang_tagihan_id')->constrained('daftar_ulang_tagihans')->cascadeOnDelete();
                $table->string('nama_biaya', 100);
                $table->decimal('nominal', 12, 2);
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daftar_ulang_tagihan_addons');

        if (Schema::hasTable('daftar_ulang_tagihans')) {
            if (Schema::hasColumn('daftar_ulang_tagihans', 'nominal_addon')) {
                Schema::table('daftar_ulang_tagihans', function (Blueprint $table) {
                    $table->dropColumn('nominal_addon');
                });
            }
        }
    }
};
