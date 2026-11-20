<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('kelas_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->onDelete('cascade');
            $table->string('kelas_dari', 20)->nullable(); // kelas sebelum naik
            $table->string('kelas_ke', 20);               // kelas setelah naik
            $table->string('tahun_ajaran', 20)->nullable();  // tahun ajaran kenaikan
            $table->unsignedSmallInteger('tahun_kenaikan')->nullable(); // tahun kalender
            $table->string('diproses_oleh', 50)->default('system'); // 'system' atau nama admin
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('kelas_histories');
    }
};
