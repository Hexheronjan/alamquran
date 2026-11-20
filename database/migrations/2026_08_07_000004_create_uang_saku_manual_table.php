<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uang_saku_manual', function (Blueprint $table) {
            $table->id();
            $table->string('nama_santri');           // Nama santri diketik bebas
            $table->string('kelas')->nullable();
            $table->string('tahun_ajaran')->nullable();
            $table->date('tanggal');
            $table->enum('jenis', ['masuk', 'keluar']);
            $table->decimal('jumlah', 12, 2);
            $table->text('keterangan')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uang_saku_manual');
    }
};
