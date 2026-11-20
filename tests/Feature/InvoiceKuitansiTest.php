<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\InvoiceSetting;
use App\Models\Santri;
use App\Models\School;
use App\Models\SppTagihan;
use App\Models\SppPembayaran;
use App\Models\DaftarUlangTagihan;
use App\Models\DaftarUlangPembayaran;
use App\Models\UangSaku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceKuitansiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Santri $santri;
    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'must_change_password' => false,
        ]);

        $this->santri = Santri::create([
            'user_id' => $this->user->id,
            'nama_lengkap' => 'Muhammad Yusuf',
            'nis' => 'NIS2026001',
            'status' => 'diterima',
            'saldo_uang_saku' => 100000,
            'nominal_spp' => 500000,
            'nama_bank_spp' => 'BNI',
            'nomor_rekening_spp' => '988001002003',
            'atas_nama_rekening_spp' => 'Pesantren Alam Quran',
        ]);

        $this->admin = Admin::create([
            'name' => 'Bendahara Utama',
            'username' => 'bendahara',
            'email' => 'bendahara@alamquran.test',
            'password' => bcrypt('password123'),
            'role' => Admin::ROLE_SUPER_ADMIN,
            'is_active' => true,
            'must_change_password' => false,
        ]);
    }

    public function test_spp_invoice_shows_review_page_and_allows_pdf_download(): void
    {
        $tagihan = SppTagihan::create([
            'santri_id' => $this->santri->id,
            'bulan' => 10,
            'tahun' => 2026,
            'nominal_spp' => 500000,
            'nominal_addon' => 0,
            'nominal_tagihan' => 500000,
            'nominal_terbayar' => 500000,
            'sisa_tagihan' => 0,
            'status' => 'lunas',
        ]);

        SppPembayaran::create([
            'santri_id' => $this->santri->id,
            'spp_tagihan_id' => $tagihan->id,
            'jumlah_bayar' => 500000,
            'status' => 'diterima',
            'metode_bayar' => 'transfer',
            'verified_by_admin_id' => $this->admin->id,
            'verified_at' => now(),
        ]);

        // Review page in browser (HTML)
        $adminReview = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.spp.tagihan.invoice', $tagihan->id));
        $adminReview->assertStatus(200);
        $adminReview->assertSee('Review Invoice SPP');
        $adminReview->assertSee('Cetak Langsung');
        $adminReview->assertSee('Download PDF');
        $adminReview->assertSee($tagihan->nomor_invoice);

        // Download PDF when ?download=pdf
        $adminPdf = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.spp.tagihan.invoice', ['id' => $tagihan->id, 'download' => 'pdf']));
        $adminPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $adminPdf->headers->get('content-type'));

        // User view
        $userReview = $this->actingAs($this->user, 'web')
            ->get(route('user.spp.tagihan.invoice', $tagihan->id));
        $userReview->assertStatus(200);
        $userReview->assertSee($tagihan->nomor_invoice);

        $userPdf = $this->actingAs($this->user, 'web')
            ->get(route('user.spp.tagihan.invoice', ['id' => $tagihan->id, 'download' => 'pdf']));
        $userPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $userPdf->headers->get('content-type'));
    }

    public function test_daftar_ulang_invoice_shows_review_page_and_allows_pdf_download(): void
    {
        $tagihan = DaftarUlangTagihan::create([
            'santri_id' => $this->santri->id,
            'nominal_biaya' => 6000000,
            'nominal_addon' => 0,
            'total_tagihan' => 6000000,
            'nominal_terbayar' => 2000000,
            'status' => 'cicilan',
            'gelombang' => 'Gelombang 1',
        ]);

        DaftarUlangPembayaran::create([
            'santri_id' => $this->santri->id,
            'daftar_ulang_tagihan_id' => $tagihan->id,
            'jumlah_bayar' => 2000000,
            'status' => 'diterima',
            'metode_bayar' => 'transfer',
            'verified_by_admin_id' => $this->admin->id,
            'verified_at' => now(),
        ]);

        // Review page
        $adminReview = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.daftar_ulang.tagihan.invoice', $tagihan->id));
        $adminReview->assertStatus(200);
        $adminReview->assertSee('Review Invoice Daftar Ulang');
        $adminReview->assertSee($tagihan->nomor_invoice);

        // PDF download
        $adminPdf = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.daftar_ulang.tagihan.invoice', ['id' => $tagihan->id, 'download' => 'pdf']));
        $adminPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $adminPdf->headers->get('content-type'));

        // User
        $userPdf = $this->actingAs($this->user, 'web')
            ->get(route('user.daftar_ulang.tagihan.invoice', ['id' => $tagihan->id, 'download' => 'pdf']));
        $userPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $userPdf->headers->get('content-type'));
    }

    public function test_uang_saku_kuitansi_shows_review_page_and_allows_pdf_download(): void
    {
        $transaksi = UangSaku::create([
            'santri_id' => $this->santri->id,
            'jenis' => 'masuk',
            'jumlah' => 250000,
            'keterangan' => 'Titipan Uang Saku Bulanan',
            'saldo_sebelum' => 0,
            'saldo_akhir' => 250000,
            'admin_id' => $this->admin->id,
        ]);

        // Review page
        $adminReview = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.uang_saku.kuitansi', $transaksi->id));
        $adminReview->assertStatus(200);
        $adminReview->assertSee('Review Kuitansi Uang Saku');
        $adminReview->assertSee($transaksi->nomor_invoice);

        // PDF download
        $adminPdf = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.uang_saku.kuitansi', ['id' => $transaksi->id, 'download' => 'pdf']));
        $adminPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $adminPdf->headers->get('content-type'));

        // User
        $userPdf = $this->actingAs($this->user, 'web')
            ->get(route('user.uang_saku.kuitansi', ['id' => $transaksi->id, 'download' => 'pdf']));
        $userPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $userPdf->headers->get('content-type'));
    }

    public function test_other_user_cannot_download_unauthorized_invoice(): void
    {
        $otherUser = User::factory()->create([
            'must_change_password' => false,
        ]);

        $transaksi = UangSaku::create([
            'santri_id' => $this->santri->id,
            'jenis' => 'masuk',
            'jumlah' => 150000,
            'keterangan' => 'Titipan Uang Saku',
            'saldo_sebelum' => 0,
            'saldo_akhir' => 150000,
            'admin_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($otherUser, 'web')
            ->get(route('user.uang_saku.kuitansi', $transaksi->id));

        $response->assertStatus(404);
    }

    public function test_invoice_settings_management(): void
    {
        $school = School::create([
            'name' => 'Pesantren Unggulan Test',
            'address' => 'Jl. Uji Coba No. 123',
            'phone' => '081234567890',
            'email' => 'pesantren@test.id',
        ]);

        // Can access settings page
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.invoice_setting.index'));
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Kop');

        // Can copy settings from school
        $copyResponse = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.invoice_setting.copy_school'), ['school_id' => $school->id]);
        $copyResponse->assertRedirect(route('admin.invoice_setting.index'));

        $setting = InvoiceSetting::getSetting();
        $this->assertEquals('Pesantren Unggulan Test', $setting->nama_lembaga);
        $this->assertEquals('Jl. Uji Coba No. 123', $setting->alamat);

        // Can update settings manually
        $updateResponse = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.invoice_setting.update'), [
                'nama_lembaga' => 'Pesantren Modern Al-Quran',
                'tagline_kop' => 'Mencetak Generasi Qurani',
                'alamat' => 'Kabupaten Ponorogo',
                'telepon' => '087756563507',
                'email' => 'info@alamquran.sch.id',
                'website' => 'alamquran.sch.id',
                'nama_bendahara' => 'Ustadzah Aminah',
                'jabatan_penandatangan' => 'Bendahara Pesantren',
                'catatan_bawah' => 'Bukti sah pembayaran.',
            ]);
        $updateResponse->assertRedirect(route('admin.invoice_setting.index'));

        $updatedSetting = InvoiceSetting::getSetting();
        $this->assertEquals('Pesantren Modern Al-Quran', $updatedSetting->nama_lembaga);
        $this->assertEquals('Ustadzah Aminah', $updatedSetting->nama_bendahara);
    }
}
