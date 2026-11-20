<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('whatsapp_messages', 'gateway_message_id')) {
                $table->string('gateway_message_id')->nullable()->index()->after('reference_id');
            }
            if (!Schema::hasColumn('whatsapp_messages', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('sent_at');
            }
            if (!Schema::hasColumn('whatsapp_messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('delivered_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_messages', 'gateway_message_id')) {
                $table->dropColumn('gateway_message_id');
            }
            if (Schema::hasColumn('whatsapp_messages', 'delivered_at')) {
                $table->dropColumn('delivered_at');
            }
            if (Schema::hasColumn('whatsapp_messages', 'read_at')) {
                $table->dropColumn('read_at');
            }
        });
    }
};
