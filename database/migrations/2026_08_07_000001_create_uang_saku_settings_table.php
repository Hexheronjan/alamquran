<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uang_saku_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('batas_minimal_saldo', 12, 2)->default(50000.00);
            $table->boolean('is_active')->default(true);
            $table->text('template_pesan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uang_saku_settings');
    }
};
