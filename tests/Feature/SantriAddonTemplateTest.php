<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Santri;
use App\Models\SantriAddonTemplate;
use App\Models\SppSetting;
use App\Models\SppTagihan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SantriAddonTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SppSetting::create([
            'nama_bank' => 'BSI',
            'nomor_rekening' => '1234567890',
            'atas_nama_rekening' => 'Pondok Tahfizh',
            'no_wa_bendahara' => '081234567890',
            'biaya_admin' => 2500,
            'nominal_kelas_1' => 1000000,
            'nominal_kelas_2' => 1000000,
            'nominal_kelas_3' => 1000000,
            'nominal_default' => 1000000,
            'is_active' => true,
        ]);
    }

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Admin Keuangan',
            'username' => 'adminkeuangan',
            'email' => 'keu@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);
    }

    public function test_store_addon_template_does_not_create_tagihan_immediately()
    {
        $admin = $this->createAdmin();

        $santri = Santri::create([
            'nama_lengkap' => 'Santri Template Test',
            'kelas' => '1',
            'status' => 'diterima',
            'status_santri' => 'aktif',
            'tahun_masuk' => (string) date('Y'),
            'nominal_spp' => 1000000,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.spp.santri_addon_template.store', $santri->id), [
                'nama_biaya' => 'Laundry',
                'nominal'    => 60000,
                'keterangan' => 'Rutin Bulanan',
            ]);

        $response->assertRedirect(route('admin.spp.index', ['tab' => 'rekening']));

        // Pastikan template add-on tersimpan
        $this->assertDatabaseHas('santri_addon_templates', [
            'santri_id'  => $santri->id,
            'nama_biaya' => 'Laundry',
            'nominal'    => 60000,
        ]);

        // Pastikan TIDAK membuat tagihan seketika!
        $this->assertEquals(0, SppTagihan::where('santri_id', $santri->id)->count());
    }

    public function test_generate_monthly_automatically_copies_addon_template()
    {
        $admin = $this->createAdmin();

        $santri = Santri::create([
            'nama_lengkap' => 'Santri Template Auto',
            'kelas' => '1',
            'status' => 'diterima',
            'status_santri' => 'aktif',
            'tahun_masuk' => (string) date('Y'),
            'nominal_spp' => 1000000,
        ]);

        // Buat template add-on rutin
        SantriAddonTemplate::create([
            'santri_id'  => $santri->id,
            'nama_biaya' => 'Laundry',
            'nominal'    => 60000,
            'is_active'  => true,
        ]);

        // Admin generate bulanan
        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.spp.generate'), [
                'bulan' => 11,
                'tahun' => 2026,
                'nominal_default' => 1000000,
            ]);

        $response->assertRedirect();

        // Tagihan terbit dengan nominal total = 1.000.000 + 60.000 = 1.060.000
        $tagihan = SppTagihan::where('santri_id', $santri->id)->where('bulan', 11)->where('tahun', 2026)->first();
        $this->assertNotNull($tagihan);
        $this->assertEquals(1000000, (float) $tagihan->nominal_spp);
        $this->assertEquals(60000, (float) $tagihan->nominal_addon);
        $this->assertEquals(1060000, (float) $tagihan->nominal_tagihan);
        $this->assertEquals(1, $tagihan->addons()->count());
        $this->assertEquals('Laundry', $tagihan->addons->first()->nama_biaya);
    }

    public function test_destroy_addon_template_removes_template()
    {
        $admin = $this->createAdmin();

        $santri = Santri::create([
            'nama_lengkap' => 'Santri Delete Tpl',
            'kelas' => '1',
            'status' => 'diterima',
            'status_santri' => 'aktif',
            'tahun_masuk' => (string) date('Y'),
        ]);

        $tpl = SantriAddonTemplate::create([
            'santri_id'  => $santri->id,
            'nama_biaya' => 'Ekskul',
            'nominal'    => 50000,
            'is_active'  => true,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->delete(route('admin.spp.santri_addon_template.destroy', $tpl->id));

        $response->assertRedirect(route('admin.spp.index', ['tab' => 'rekening']));
        $this->assertDatabaseMissing('santri_addon_templates', [
            'id' => $tpl->id,
        ]);
    }
}
