<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;

class PesantrenAppModulesTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Super Admin Test',
            'username' => 'superadmin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_access_ppdb_submissions()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.form_submission.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.santri.kenaikan_kelas'));
        $response->assertStatus(200);
    }

    public function test_absensi_module_routes()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.absensi.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.absensi.input'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.absensi.rekap'));
        $response->assertStatus(200);
    }

    public function test_tahfidz_module_routes()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.tahfidz.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.tahfidz.master_surah'));
        $response->assertStatus(200);
    }

    public function test_kantin_module_routes()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.kantin.kasir.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.kantin.produk.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.kantin.produk.create'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.kantin.laporan.index'));
        $response->assertStatus(200);
    }

    public function test_surat_menyurat_module_routes()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.surat.keluar.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.surat.masuk.index'));
        $response->assertStatus(200);
    }

    public function test_infaq_module_routes()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.infaq.index'));
        $response->assertStatus(200);

        $user = \App\Models\User::factory()->create();
        $responseUser = $this->actingAs($user, 'web')->get(route('user.infaq'));
        $responseUser->assertStatus(200);

        $program = \App\Models\InfaqProgram::create([
            'nama_program' => 'Test Program Donasi',
            'deskripsi'    => 'Deskripsi program',
            'target_dana'  => 1000000,
            'is_active'    => true,
        ]);

        \Illuminate\Support\Facades\Storage::fake('public');
        $file = \Illuminate\Http\UploadedFile::fake()->image('bukti.jpg');

        $responsePost = $this->actingAs($user, 'web')->post(route('user.infaq.store'), [
            'infaq_program_id'  => $program->id,
            'nominal'           => 50000,
            'metode_pembayaran' => 'transfer',
            'bukti_transfer'    => $file,
            'doa_harapan'       => 'Semoga berkah',
        ]);
        $responsePost->assertSessionHas('success');

        $transaksi = \App\Models\InfaqTransaksi::where('infaq_program_id', $program->id)->first();
        $this->assertNotNull($transaksi);

        $responseKonfirmasi = $this->actingAs($admin, 'admin')->patch(route('admin.infaq.konfirmasiTransaksi', $transaksi->id));
        $responseKonfirmasi->assertSessionHas('success');
        $this->assertEquals('berhasil', $transaksi->fresh()->status);
    }

    public function test_broadcast_module_routes()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.broadcast.index'));
        $response->assertStatus(200);
    }

    public function test_perizinan_module_routes()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.perizinan.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.perizinan.scanner'));
        $response->assertStatus(200);
    }

    public function test_kasir_role_can_access_kantin_pos()
    {
        $kasir = Admin::create([
            'name' => 'Kasir Toko',
            'username' => 'kasir1',
            'email' => 'kasir@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_KASIR,
            'permissions' => ['kantin'],
            'assigned_toko' => 'aqumart',
            'is_active' => true,
        ]);

        $response = $this->actingAs($kasir, 'admin')->get(route('admin.kantin.kasir.index'));
        $response->assertStatus(200);
    }

    public function test_kasir_cannot_checkout_to_unassigned_toko()
    {
        $kasir = Admin::create([
            'name' => 'Kasir Aqumart Only',
            'username' => 'kasiraqumart',
            'email' => 'kasiraqumart@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_KASIR,
            'permissions' => ['kantin'],
            'assigned_toko' => 'aqumart',
            'is_active' => true,
        ]);

        $response = $this->actingAs($kasir, 'admin')->postJson(route('admin.kantin.kasir.checkout'), [
            'santri_id' => 9999,
            'toko' => 'kantin_1',
            'items' => [['produk_id' => 1, 'qty' => 1]],
        ]);

        $response->assertStatus(403);
    }

    public function test_ppdb_read_permission_is_registered()
    {
        $this->assertArrayHasKey('ppdb_read', Admin::getAvailablePermissions());
    }

    public function test_hosting_setup_blocks_unauthorized_access()
    {
        $response = $this->get('/hosting-setup/migrate');
        $response->assertStatus(403);
    }
}
