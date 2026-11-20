<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spp_pembayarans', function (Blueprint $table) {
            $table->foreignId('submitted_by_user_id')->nullable()->after('santri_id')
                ->constrained('users')->nullOnDelete();
            $table->text('alasan_penolakan')->nullable()->after('catatan');
        });
    }

    public function down(): void
    {
        Schema::table('spp_pembayarans', function (Blueprint $table) {
            $table->dropForeign(['submitted_by_user_id']);
            $table->dropColumn(['submitted_by_user_id', 'alasan_penolakan']);
        });
    }
};
