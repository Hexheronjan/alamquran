<?php

namespace Tests\Feature;

use App\Models\Santri;
use App\Models\Admin;
use App\Models\DaftarUlangTagihan;
use App\Models\DaftarUlangTagihanAddon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DaftarUlangAddonTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_and_delete_daftar_ulang_addon_and_recalculate_totals(): void
    {
        $admin = Admin::create([
            'name' => 'Super Admin Test',
            'username' => 'superadmin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $santri = Santri::create([
            'nama_lengkap' => 'Santri Baru Test',
            'status' => 'diterima',
            'tahun_masuk' => date('Y'),
        ]);

        $tagihan = DaftarUlangTagihan::create([
            'santri_id' => $santri->id,
            'tahun_angkatan' => date('Y'),
            'nominal_biaya' => 5000000,
            'nominal_addon' => 0,
            'nominal_terbayar' => 0,
            'sisa_tagihan' => 5000000,
            'status' => 'belum_lunas',
            'is_ready' => true,
        ]);

        $this->assertEquals(5000000, $tagihan->total_tagihan);
        $this->assertEquals(5000000, $tagihan->sisa_tagihan);

        // 1. Admin adds an Add-on
        $response = $this->actingAs($admin, 'admin')->post(route('admin.daftar_ulang.addon.store', $tagihan->id), [
            'nama_biaya' => 'Seragam Tambahan',
            'nominal' => 250000,
            'keterangan' => '2 set gamis',
        ]);

        $response->assertSessionHas('success');

        $tagihan->refresh();
        $this->assertCount(1, $tagihan->addons);
        $this->assertEquals(250000, $tagihan->nominal_addon);
        $this->assertEquals(5250000, $tagihan->total_tagihan);
        $this->assertEquals(5250000, $tagihan->sisa_tagihan);
        $this->assertEquals('belum_lunas', $tagihan->status);

        $addon = $tagihan->addons->first();
        $this->assertEquals('Seragam Tambahan', $addon->nama_biaya);
        $this->assertEquals(250000, $addon->nominal);

        // 2. Admin adds a second Add-on
        $response2 = $this->actingAs($admin, 'admin')->post(route('admin.daftar_ulang.addon.store', $tagihan->id), [
            'nama_biaya' => 'Kitab & Buku',
            'nominal' => 150000,
        ]);

        $response2->assertSessionHas('success');

        $tagihan->refresh();
        $this->assertCount(2, $tagihan->addons);
        $this->assertEquals(400000, $tagihan->nominal_addon);
        $this->assertEquals(5400000, $tagihan->total_tagihan);
        $this->assertEquals(5400000, $tagihan->sisa_tagihan);

        // 3. Admin deletes the first Add-on
        $responseDel = $this->actingAs($admin, 'admin')->delete(route('admin.daftar_ulang.addon.destroy', $addon->id));
        $responseDel->assertSessionHas('success');

        $tagihan->refresh();
        $this->assertCount(1, $tagihan->addons);
        $this->assertEquals(150000, $tagihan->nominal_addon);
        $this->assertEquals(5150000, $tagihan->total_tagihan);
        $this->assertEquals(5150000, $tagihan->sisa_tagihan);
    }
}
