<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposit_transfer_manuals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->decimal('jumlah', 12, 2);
            $table->string('bukti_transfer');
            $table->enum('status', ['menunggu_verifikasi', 'disetujui', 'ditolak'])
                ->default('menunggu_verifikasi');
            $table->text('catatan_wali')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_transfer_manuals');
    }
};
