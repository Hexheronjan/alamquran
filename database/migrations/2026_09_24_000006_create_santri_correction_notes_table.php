<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santri_correction_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->text('catatan');
            $table->timestamp('diberikan_pada');
            $table->timestamp('diselesaikan_pada')->nullable();
            $table->unsignedBigInteger('diselesaikan_oleh_user_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santri_correction_notes');
    }
};
