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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('otp_created_at')->nullable()->after('otp_code');
            $table->string('otp_delivery_status', 20)->nullable()->after('otp_expired_at');
            $table->text('otp_delivery_error')->nullable()->after('otp_delivery_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['otp_created_at', 'otp_delivery_status', 'otp_delivery_error']);
        });
    }
};
