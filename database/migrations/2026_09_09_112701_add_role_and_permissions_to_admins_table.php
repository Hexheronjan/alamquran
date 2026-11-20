<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('role', 50)->default('admin_ppdb')->after('email');
            $table->boolean('is_active')->default(true)->after('role');
            $table->json('permissions')->nullable()->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('permissions');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
        });

        // Set existing admin (id: 1) as Super Admin
        DB::table('admins')->where('id', 1)->update([
            'username' => 'admin',
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'role',
                'is_active',
                'permissions',
                'last_login_at',
                'last_login_ip',
            ]);
        });
    }
};