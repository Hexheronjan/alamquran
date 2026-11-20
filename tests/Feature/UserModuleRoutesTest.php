<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\FormGroup;
use App\Models\Angkatan;
use App\Models\Santri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModuleRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_user_module_routes_render_successfully(): void
    {
        $user = User::factory()->create([
            'no_wa' => '08123456789',
        ]);

        $angkatan = Angkatan::create([
            'nama_angkatan' => '2024/2025',
            'tahun_ajaran' => '2024/2025',
            'tanggal_mulai_daftar' => '2024-01-01',
            'tanggal_tutup_daftar' => '2024-12-31',
            'is_active' => true,
        ]);

        $formGroup = FormGroup::create([
            'name' => 'Gelombang 1',
            'angkatan_id' => $angkatan->id,
            'is_active' => true,
        ]);

        $santri = Santri::create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Santri Test',
            'nis' => '12345678',
            'form_group_id' => $formGroup->id,
            'status' => 'diterima',
        ]);

        $routes = [
            'user.dashboard',
            'user.uang_saku',
            'user.spp.index',
            'user.tahfidz',
            'user.absensi',
            'user.kegiatan',
            'user.kondisi',
            'user.perizinan',
            'user.kartu_santri',
            'user.infaq',
            'user.surat',
            'user.notifikasi',
        ];

        foreach ($routes as $routeName) {
            $response = $this->actingAs($user)->get(route($routeName));
            $response->assertStatus(200, "Route {$routeName} failed to load with status " . $response->status());
        }
    }
}
