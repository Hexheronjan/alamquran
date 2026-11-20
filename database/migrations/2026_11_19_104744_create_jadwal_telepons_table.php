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
        Schema::create('jadwal_telepons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->date('tanggal_telepon');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->unsignedSmallInteger('durasi_menit')->default(15);
            $table->string('ustadz_pendamping', 100)->default('Ustadz Pendamping');
            $table->string('no_hp_posko', 30);
            $table->string('topik', 150)->nullable();
            $table->text('keterangan')->nullable();
            
            // Status Panggilan: menunggu, terhubung, tidak_terangkat, dijadwalkan_ulang, dibatalkan
            $table->string('status', 30)->default('menunggu');
            $table->text('catatan_panggilan')->nullable();
            $table->dateTime('waktu_dihubungi')->nullable();

            // Status Pengingat WhatsApp
            $table->boolean('wa_reminder_sent')->default(false);
            $table->dateTime('wa_reminder_sent_at')->nullable();
            $table->string('wa_reminder_status', 30)->nullable();
            $table->text('wa_reminder_error')->nullable();

            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            // Index performa query
            $table->index(['tanggal_telepon', 'status']);
            $table->index(['santri_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_telepons');
    }
};
