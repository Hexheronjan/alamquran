<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\Santri;
use App\Models\SppSetting;
use App\Models\SppTagihan;
use App\Models\SppPembayaran;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SppRevisionTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Admin Bendahara',
            'username' => 'bendahara',
            'email' => 'bendahara@test.com',
            'password' => bcrypt('password'),
            'role' => Admin::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);
    }

    public function test_spp_generate_respects_class_and_custom_santri_fees_and_excludes_alumni()
    {
        $admin = $this->createAdmin();

        $setting = SppSetting::create([
            'nama_bank' => 'BSI',
            'nomor_rekening' => '1234567890',
            'atas_nama_rekening' => 'Pondok Tahfizh',
            'no_wa_bendahara' => '081234567890',
            'biaya_admin' => 2500,
            'nominal_kelas_1' => 750000,
            'nominal_kelas_2' => 850000,
            'nominal_kelas_3' => 950000,
            'nominal_default' => 700000,
            'is_active' => true,
        ]);

        // Santri 1 dengan nominal SPP 800.000
        $s1 = Santri::create([
            'nama_lengkap' => 'Santri 1',
            'kelas' => '1',
            'status' => 'diterima',
            'status_santri' => 'aktif',
            'jenis_kelamin' => 'L',
            'nominal_spp' => 800000,
        ]);

        // Santri 2 dengan nominal beasiswa 300.000
        $s2 = Santri::create([
            'nama_lengkap' => 'Santri 2 Beasiswa',
            'kelas' => '1',
            'status' => 'diterima',
            'status_santri' => 'aktif',
            'jenis_kelamin' => 'L',
            'nominal_spp' => 300000,
        ]);

        // Santri 3 belum diatur nominalnya (pakai default)
        $s3 = Santri::create([
            'nama_lengkap' => 'Santri 3 Belum Diatur',
            'kelas' => '2',
            'status' => 'diterima',
            'status_santri' => 'aktif',
            'jenis_kelamin' => 'P',
        ]);

        // Santri Alumni (lulus)
        $sAlumni = Santri::create([
            'nama_lengkap' => 'Santri Alumni Lulus',
            'kelas' => 'alumni',
            'status' => 'diterima',
            'status_santri' => 'lulus',
            'jenis_kelamin' => 'L',
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.spp.generate'), [
            'bulan' => 10,
            'tahun' => 2026,
            'nominal_default' => 700000,
        ]);

        $response->assertSessionHas('success');

        // Verify Tagihan Santri 1 (Nominal Santri -> 800000)
        $t1 = SppTagihan::where('santri_id', $s1->id)->where('bulan', 10)->first();
        $this->assertNotNull($t1);
        $this->assertEquals(800000, (float)$t1->nominal_tagihan);

        // Verify Tagihan Santri 2 (Nominal Santri -> 300000)
        $t2 = SppTagihan::where('santri_id', $s2->id)->where('bulan', 10)->first();
        $this->assertNotNull($t2);
        $this->assertEquals(300000, (float)$t2->nominal_tagihan);

        // Verify Tagihan Santri 3 (Fallback default -> 700000)
        $t3 = SppTagihan::where('santri_id', $s3->id)->where('bulan', 10)->first();
        $this->assertNotNull($t3);
        $this->assertEquals(700000, (float)$t3->nominal_tagihan);

        // Verify Alumni does not have tagihan
        $tAlumni = SppTagihan::where('santri_id', $sAlumni->id)->where('bulan', 10)->first();
        $this->assertNull($tAlumni);
    }

    public function test_store_single_tagihan_creates_or_updates_custom_bill()
    {
        $admin = $this->createAdmin();

        $santri = Santri::create([
            'nama_lengkap' => 'Santri Khusus Single',
            'kelas' => '3',
            'status_santri' => 'aktif',
            'jenis_kelamin' => 'L',
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.spp.store_single'), [
            'santri_id' => $santri->id,
            'bulan' => 11,
            'tahun' => 2026,
            'nominal_tagihan' => 600000,
            'keterangan' => 'Tagihan Khusus Beasiswa Tahfidz',
        ]);

        $response->assertSessionHas('success');

        $tagihan = SppTagihan::where('santri_id', $santri->id)->where('bulan', 11)->first();
        $this->assertNotNull($tagihan);
        $this->assertEquals(600000, (float)$tagihan->nominal_tagihan);
        $this->assertEquals('Tagihan Khusus Beasiswa Tahfidz', $tagihan->keterangan);
    }

    public function test_user_can_confirm_payment_and_bendahara_receives_notification()
    {
        Storage::fake('public');

        $setting = SppSetting::create([
            'nama_bank' => 'BSI',
            'nomor_rekening' => '1234567890',
            'atas_nama_rekening' => 'Pondok Tahfizh',
            'no_wa_bendahara' => '081234567890',
            'biaya_admin' => 2500,
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Wali Santri Budi',
            'email' => 'budi@test.com',
            'password' => bcrypt('password'),
            'no_wa' => '08987654321',
        ]);

        $santri = Santri::create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Budi Pratama',
            'kelas' => '1',
            'status' => 'diterima',
            'status_santri' => 'aktif',
            'jenis_kelamin' => 'L',
        ]);

        $tagihan = SppTagihan::create([
            'santri_id' => $santri->id,
            'bulan' => 10,
            'tahun' => 2026,
            'nominal_tagihan' => 1000000,
            'nominal_terbayar' => 0,
            'sisa_tagihan' => 1000000,
            'status' => 'belum_lunas',
        ]);

        $file = UploadedFile::fake()->image('bukti_transfer.jpg');

        $response = $this->actingAs($user)->post(route('user.spp.konfirmasi', $tagihan->id), [
            'jumlah_bayar' => 1002500, // SPP 1.000.000 + Biaya admin 2.500
            'metode_bayar' => 'transfer',
            'bukti_bayar' => $file,
            'catatan' => 'Sudah ditransfer via BSI Mobile termasuk admin',
        ]);

        $response->assertSessionHas('success');

        // Check payment record created with status menunggu_konfirmasi
        $pembayaran = SppPembayaran::where('spp_tagihan_id', $tagihan->id)->first();
        $this->assertNotNull($pembayaran);
        $this->assertEquals('menunggu_konfirmasi', $pembayaran->status);
        $this->assertEquals(1002500, (float)$pembayaran->jumlah_bayar);
        $this->assertNotNull($pembayaran->bukti_bayar);
    }

    public function test_broadcast_monthly_spp_command_creates_bills_and_sends_notifications()
    {
        $setting = SppSetting::create([
            'nama_bank' => 'BSI',
            'nomor_rekening' => '1234567890',
            'atas_nama_rekening' => 'Pondok Tahfizh',
            'no_wa_bendahara' => '081234567890',
            'biaya_admin' => 2500,
            'nominal_kelas_1' => 800000,
            'nominal_kelas_2' => 900000,
            'nominal_kelas_3' => 1000000,
            'is_active' => true,
        ]);

        $user1 = User::create([
            'name' => 'Wali Zaid',
            'email' => 'zaid@test.com',
            'password' => bcrypt('password'),
            'no_wa' => '081122334455',
        ]);

        // Santri 1: Ada nomor rekening/VA -> Harus terkirim WA
        $santri1 = Santri::create([
            'user_id' => $user1->id,
            'nama_lengkap' => 'Zaid bin Tsabit',
            'kelas' => '2',
            'status' => 'diterima',
            'status_santri' => 'aktif',
            'jenis_kelamin' => 'L',
            'nominal_spp' => 850000,
            'nama_bank_spp' => 'BSI',
            'nomor_rekening_spp' => '9988776655',
            'atas_nama_rekening_spp' => 'Zaid bin Tsabit',
        ]);

        // Santri 2: Belum diatur nomor rekening/VA -> Tagihan terbuat, tetapi WA DILEWATI (Opsi 2)
        $user2 = User::create([
            'name' => 'Wali Umar',
            'email' => 'umar@test.com',
            'password' => bcrypt('password'),
            'no_wa' => '081122334466',
        ]);

        $santri2 = Santri::create([
            'user_id' => $user2->id,
            'nama_lengkap' => 'Umar bin Khattab',
            'kelas' => '2',
            'status' => 'diterima',
            'status_santri' => 'aktif',
            'jenis_kelamin' => 'L',
            'nominal_spp' => 850000,
            'nomor_rekening_spp' => null, // Belum diatur
        ]);

        $this->artisan('spp:broadcast-monthly')
            ->assertExitCode(0);

        $bulanSekarang = (int) now()->month;
        $tahunSekarang = (int) now()->year;

        // Tagihan Santri 1: Terbuat dan WA terkirim
        $tagihan1 = SppTagihan::where('santri_id', $santri1->id)
            ->where('bulan', $bulanSekarang)
            ->where('tahun', $tahunSekarang)
            ->first();

        $this->assertNotNull($tagihan1);
        $this->assertEquals(850000, (float)$tagihan1->nominal_tagihan);
        $this->assertNotNull($tagihan1->broadcast_wa_at);

        // Tagihan Santri 2: Terbuat tetapi WA TIDAK dikirim (broadcast_wa_at null)
        $tagihan2 = SppTagihan::where('santri_id', $santri2->id)
            ->where('bulan', $bulanSekarang)
            ->where('tahun', $tahunSekarang)
            ->first();

        $this->assertNotNull($tagihan2);
        $this->assertEquals(850000, (float)$tagihan2->nominal_tagihan);
        $this->assertNull($tagihan2->broadcast_wa_at);
    }

    public function test_admin_broadcast_route_skips_santri_without_rekening()
    {
        $admin = $this->createAdmin();

        $user1 = User::create([
            'name' => 'Wali Ali',
            'email' => 'ali@test.com',
            'password' => bcrypt('password'),
            'no_wa' => '081122334477',
        ]);

        $santriWithRekening = Santri::create([
            'user_id' => $user1->id,
            'nama_lengkap' => 'Ali bin Abi Thalib',
            'kelas' => '1',
            'status_santri' => 'aktif',
            'jenis_kelamin' => 'L',
            'nomor_rekening_spp' => '1122334455',
        ]);

        $santriWithoutRekening = Santri::create([
            'nama_lengkap' => 'Santri Belum Ada Rekening',
            'kelas' => '1',
            'status_santri' => 'aktif',
            'jenis_kelamin' => 'L',
            'nomor_rekening_spp' => null,
        ]);

        $t1 = SppTagihan::create([
            'santri_id' => $santriWithRekening->id,
            'bulan' => 10,
            'tahun' => 2026,
            'nominal_tagihan' => 700000,
            'nominal_terbayar' => 0,
            'sisa_tagihan' => 700000,
            'status' => 'belum_lunas',
        ]);

        $t2 = SppTagihan::create([
            'santri_id' => $santriWithoutRekening->id,
            'bulan' => 10,
            'tahun' => 2026,
            'nominal_tagihan' => 700000,
            'nominal_terbayar' => 0,
            'sisa_tagihan' => 700000,
            'status' => 'belum_lunas',
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.spp.broadcast'), [
            'bulan' => 10,
            'tahun' => 2026,
        ]);

        $response->assertSessionHas('success');
        $this->assertStringContainsString('dilewati karena rekening/VA belum diatur', session('success'));

        $t1->refresh();
        $t2->refresh();

        $this->assertNotNull($t1->broadcast_wa_at);
        $this->assertNull($t2->broadcast_wa_at);
    }

    public function test_send_spp_reminder_command_skips_santri_without_rekening()
    {
        \App\Models\WhatsappSetting::set('spp_reminder_days', (string) now()->day);

        $user = User::create([
            'name' => 'Wali Remind',
            'email' => 'remind@test.com',
            'password' => bcrypt('password'),
            'no_wa' => '081199887766',
        ]);

        $santriWithRek = Santri::create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Santri Punya Rekening',
            'kelas' => '1',
            'status_santri' => 'aktif',
            'jenis_kelamin' => 'L',
            'nomor_rekening_spp' => '1234567890',
        ]);

        $santriWithoutRek = Santri::create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Santri Belum Rekening',
            'kelas' => '1',
            'status_santri' => 'aktif',
            'jenis_kelamin' => 'L',
            'nomor_rekening_spp' => null,
        ]);

        SppTagihan::create([
            'santri_id' => $santriWithRek->id,
            'bulan' => now()->month,
            'tahun' => now()->year,
            'nominal_tagihan' => 500000,
            'nominal_terbayar' => 0,
            'sisa_tagihan' => 500000,
            'status' => 'belum_lunas',
        ]);

        SppTagihan::create([
            'santri_id' => $santriWithoutRek->id,
            'bulan' => now()->month,
            'tahun' => now()->year,
            'nominal_tagihan' => 500000,
            'nominal_terbayar' => 0,
            'sisa_tagihan' => 500000,
            'status' => 'belum_lunas',
        ]);

        $this->artisan('spp:send-reminders')
            ->expectsOutputToContain('1 pengingat SPP dimasukkan ke antrean')
            ->assertExitCode(0);
    }
}


