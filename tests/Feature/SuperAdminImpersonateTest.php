<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Models\AdminActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SuperAdminImpersonateTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_super_admin_cannot_impersonate(): void
    {
        $operator = Admin::create([
            'name' => 'Operator Test',
            'username' => 'operator1',
            'email' => 'op@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_OPERATOR,
            'is_active' => true,
        ]);

        $wali = User::create([
            'name' => 'Wali Santri 1',
            'email' => 'wali1@example.com',
            'password' => bcrypt('password'),
            'no_wa' => '081234567890',
        ]);

        $response = $this->actingAs($operator, 'admin')->post(route('admin.impersonate', $wali->id));
        // Admin without admin_management permission gets redirected to admin dashboard
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertNull(Auth::guard('web')->user());
    }

    public function test_super_admin_can_impersonate_wali_without_otp_and_leave(): void
    {
        $superAdmin = Admin::create([
            'name' => 'Super Admin Test',
            'username' => 'superadmin',
            'email' => 'super@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $wali = User::create([
            'name' => 'Wali Santri Budi',
            'email' => 'budi@example.com',
            'password' => bcrypt('password'),
            'no_wa' => '081234567899',
        ]);

        // 1. Super Admin impersonates wali
        $response = $this->actingAs($superAdmin, 'admin')->post(route('admin.impersonate', $wali->id));

        $response->assertRedirect(route('user.dashboard'));
        $this->assertEquals($wali->id, Auth::guard('web')->id());
        $this->assertEquals($superAdmin->id, session('impersonated_by_admin_id'));

        // Activity log recorded
        $this->assertTrue(AdminActivityLog::where('action', 'impersonate_wali')->exists());

        // 2. Super Admin leaves impersonation
        $responseLeave = $this->post(route('admin.impersonate.leave'));
        $responseLeave->assertRedirect(route('admin.pengguna.index'));

        $this->assertNull(Auth::guard('web')->user());
        $this->assertNull(session('impersonated_by_admin_id'));
    }

    public function test_super_admin_bypasses_must_change_password_on_impersonate(): void
    {
        $superAdmin = Admin::create([
            'name' => 'Super Admin Test',
            'username' => 'superadmin2',
            'email' => 'super2@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $wali = User::create([
            'name' => 'Wali Jemani',
            'email' => 'jemani@example.com',
            'password' => bcrypt('password'),
            'no_wa' => '083845457572',
            'must_change_password' => true,
        ]);

        // Impersonate
        $this->actingAs($superAdmin, 'admin')->post(route('admin.impersonate', $wali->id));

        // When accessing user.dashboard, middleware must.change.password allows the request because impersonated
        $response = $this->get(route('user.dashboard'));
        $response->assertOk();
    }
}
