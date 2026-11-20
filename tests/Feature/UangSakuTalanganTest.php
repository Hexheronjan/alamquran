<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Santri;
use App\Models\OrangTua;
use App\Models\UangSaku;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UangSakuTalanganTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_can_exceed_balance_and_creates_talangan_negative_balance(): void
    {
        $admin = Admin::create([
            'name' => 'Ust. Ahmad',
            'username' => 'ahmad',
            'email' => 'ahmad@example.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_UANG_SAKU,
            'is_active' => true,
        ]);

        $orangTua = OrangTua::create([
            'nama_ayah' => 'Bapak Fulan',
            'no_wa_ayah' => '081234567890',
            'no_kk' => '1234567890123456',
            'nik_ayah' => '1234567890123456',
            'tempat_lahir_ayah' => 'Jakarta',
            'tanggal_lahir_ayah' => '1980-01-01',
            'pekerjaan_ayah' => 'PNS',
            'penghasilan_ayah' => '1-3 juta',
            'alamat_domisili_ayah' => 'Jl. Test',
            'nama_ibu' => 'Ibu Fulan',
            'nik_ibu' => '1234567890123457',
            'tempat_lahir_ibu' => 'Jakarta',
            'tanggal_lahir_ibu' => '1982-01-01',
            'pekerjaan_ibu' => 'IRT',
            'penghasilan_ibu' => '0',
            'alamat_domisili_ibu' => 'Jl. Test',
        ]);

        $santri = Santri::create([
            'nama_lengkap' => 'Santri Fulan',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'nik' => '1234567890123458',
            'nisn' => '1234567890',
            'sekolah_asal' => 'SD Test',
            'npsn' => '12345678',
            'anak_ke' => 1,
            'dari_bersaudara' => 1,
            'status' => 'diterima',
            'status_santri' => 'aktif',
            'kelas' => '1',
            'orang_tua_id' => $orangTua->id,
        ]);

        // 1. Initial Deposit Rp 20.000
        $this->actingAs($admin, 'admin')->post(route('admin.uang_saku.deposit', $santri->id), [
            'jumlah' => 20000,
            'keterangan' => 'Titipan awal',
        ]);

        $this->assertEquals(20000, $santri->fresh()->saldo_uang_saku);

        // 2. Expense Rp 50.000 (exceeds balance by Rp 30.000 -> Talangan Pondok)
        $response = $this->actingAs($admin, 'admin')->post(route('admin.uang_saku.expense', $santri->id), [
            'jumlah' => 50000,
            'keterangan' => 'Beli obat di apotek (Talangan)',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(-30000, $santri->fresh()->saldo_uang_saku);

        // Check last transaction
        $lastTrans = $santri->fresh()->uangSaku()->latest('id')->first();
        $this->assertEquals('keluar', $lastTrans->jenis);
        $this->assertEquals(50000, (float) $lastTrans->jumlah);
        $this->assertEquals(-30000, (float) $lastTrans->saldo_akhir);
        $this->assertEquals($admin->id, $lastTrans->admin_id);

        // 3. Wali pays back deposit Rp 100.000 -> saldo becomes +70.000
        $this->actingAs($admin, 'admin')->post(route('admin.uang_saku.deposit', $santri->id), [
            'jumlah' => 100000,
            'keterangan' => 'Titipan pelunasan talangan & saldo baru',
        ]);

        $this->assertEquals(70000, $santri->fresh()->saldo_uang_saku);
    }
}
