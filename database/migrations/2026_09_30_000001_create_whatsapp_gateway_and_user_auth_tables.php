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
        // 1. Tambah kolom must_change_password ke tabel users jika belum ada
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'must_change_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('must_change_password')->default(false)->after('password');
            });
        }

        // 2. Buat tabel whatsapp_templates
        if (!Schema::hasTable('whatsapp_templates')) {
            Schema::create('whatsapp_templates', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 60)->unique();
                $table->string('nama', 150);
                $table->string('modul', 50)->index();
                $table->string('judul', 150)->nullable();
                $table->text('konten');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 3. Buat tabel whatsapp_messages (queue & logs terpusat)
        if (!Schema::hasTable('whatsapp_messages')) {
            Schema::create('whatsapp_messages', function (Blueprint $table) {
                $table->id();
                $table->string('reference_id', 100)->nullable()->index(); // Idempotency key
                $table->string('recipient_number', 30)->index();
                $table->string('recipient_name', 150)->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('santri_id')->nullable()->constrained('santri')->nullOnDelete();
                $table->string('modul', 50)->index();
                $table->string('template_kode', 60)->nullable()->index();
                $table->text('message_body');
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
                $table->enum('status', ['pending', 'processing', 'sent', 'failed', 'cancelled'])->default('pending')->index();
                $table->unsignedInteger('attempts')->default(0);
                $table->unsignedInteger('max_attempts')->default(3);
                $table->text('error_message')->nullable();
                $table->string('provider', 50)->default('onesender');
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. Buat tabel whatsapp_settings
        if (!Schema::hasTable('whatsapp_settings')) {
            Schema::create('whatsapp_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 80)->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_templates');
        Schema::dropIfExists('whatsapp_settings');

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'must_change_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('must_change_password');
            });
        }
    }
};
