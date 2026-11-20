<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom beasiswa pada tabel santri
        if (!Schema::hasColumn('santri', 'is_beasiswa')) {
            Schema::table('santri', function (Blueprint $table) {
                $table->boolean('is_beasiswa')->default(false)->after('nominal_spp');
            });
        }

        // 2. Tambah rincian nominal_spp dan nominal_addon pada tabel spp_tagihans
        if (!Schema::hasColumn('spp_tagihans', 'nominal_spp')) {
            Schema::table('spp_tagihans', function (Blueprint $table) {
                $table->decimal('nominal_spp', 12, 2)->default(0)->after('tahun');
                $table->decimal('nominal_addon', 12, 2)->default(0)->after('nominal_spp');
            });

            // Isi nilai nominal_spp awal dari nominal_tagihan yang sudah ada
            DB::statement('UPDATE spp_tagihans SET nominal_spp = nominal_tagihan WHERE nominal_spp = 0 OR nominal_spp IS NULL');
        }

        // 3. Buat tabel spp_tagihan_addons untuk mencatat biaya add-on tambahan (laundry, kegiatan, dll)
        if (!Schema::hasTable('spp_tagihan_addons')) {
            Schema::create('spp_tagihan_addons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('spp_tagihan_id')->constrained('spp_tagihans')->cascadeOnDelete();
                $table->string('nama_biaya', 100);
                $table->decimal('nominal', 12, 2);
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->index('spp_tagihan_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spp_tagihan_addons');

        if (Schema::hasColumn('spp_tagihans', 'nominal_spp')) {
            Schema::table('spp_tagihans', function (Blueprint $table) {
                $table->dropColumn(['nominal_spp', 'nominal_addon']);
            });
        }

        if (Schema::hasColumn('santri', 'is_beasiswa')) {
            Schema::table('santri', function (Blueprint $table) {
                $table->dropColumn('is_beasiswa');
            });
        }
    }
};
