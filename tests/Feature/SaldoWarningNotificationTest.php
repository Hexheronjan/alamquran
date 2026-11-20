<?php

namespace Tests\Feature;

use App\Models\OrangTua;
use App\Models\Santri;
use App\Models\UangSakuSetting;
use App\Services\WhatsappService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SaldoWarningNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function createSantri(string $noWa = '081234567890', bool $warningSent = false): Santri
    {
        $orangTua = OrangTua::create([
            'nama_ayah' => 'Ayah Test',
            'no_wa_ayah' => $noWa,
            'no_kk' => '1234567890123456',
            'nik_ayah' => '1234567890123456',
            'tempat_lahir_ayah' => 'Jakarta',
            'tanggal_lahir_ayah' => '1980-01-01',
            'pekerjaan_ayah' => 'Wiraswasta',
            'penghasilan_ayah' => 'Rp2.000.000–Rp5.000.000',
            'alamat_domisili_ayah' => 'Jakarta',
            'nama_ibu' => 'Ibu Test',
            'nik_ibu' => '6543210987654321',
            'tempat_lahir_ibu' => 'Jakarta',
            'tanggal_lahir_ibu' => '1982-01-01',
            'pekerjaan_ibu' => 'IRT',
            'penghasilan_ibu' => 'Lainnya',
            'alamat_domisili_ibu' => 'Jakarta',
        ]);

        return Santri::create([
            'nama_lengkap' => 'Santri Test',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'nik' => '1111222233334444',
            'sekolah_asal' => 'SD Test',
            'npsn' => '12345678',
            'nisn' => '1234567890',
            'anak_ke' => 1,
            'dari_bersaudara' => 2,
            'tujuan_mendaftar' => 'Test',
            'status' => 'diterima',
            'tahun_ajaran' => '2026/2027',
            'orang_tua_id' => $orangTua->id,
            'wa_saldo_warning_sent' => $warningSent,
        ])->fresh(['orangTua']);
    }

    private function setupSettings(): void
    {
        UangSakuSetting::create([
            'batas_minimal_saldo' => 50000,
            'is_active' => true,
            'template_pesan' => 'Saldo [NAMA_SANTRI] [KELAS] [SALDO] [BATAS_MINIMAL]',
        ]);

        putenv('ONESENDER_API_KEY=test-key');
        putenv('ONESENDER_API_URL=https://api.example.com/send');
    }

    private function invokeNotification(Santri $santri, float $balance): void
    {
        $controller = app(\App\Http\Controllers\Admin\UangSakuController::class);
        $method = new \ReflectionMethod($controller, 'processSaldoWarningNotification');
        $method->setAccessible(true);
        $method->invoke($controller, $santri, $balance);
    }

    public function test_notification_sent_when_balance_drops_below_threshold(): void
    {
        $this->setupSettings();
        Http::fake(['*' => Http::response(['message' => 'sent'], 200)]);

        $santri = $this->createSantri();
        $this->invokeNotification($santri, 40000);

        $santri->refresh();
        $this->assertTrue($santri->wa_saldo_warning_sent);
        Http::assertSentCount(1);
    }

    public function test_notification_not_sent_again_while_below_threshold(): void
    {
        $this->setupSettings();
        Http::fake(['*' => Http::response(['message' => 'sent'], 200)]);

        $santri = $this->createSantri('081234567890', true);
        $this->invokeNotification($santri, 30000);

        Http::assertNothingSent();
    }

    public function test_notification_can_be_sent_again_after_reset(): void
    {
        $this->setupSettings();
        Http::fake(['*' => Http::response(['message' => 'sent'], 200)]);

        $santri = $this->createSantri();
        $this->invokeNotification($santri, 40000);
        $santri->refresh();
        $this->assertTrue($santri->wa_saldo_warning_sent);

        $santri->update(['wa_saldo_warning_sent' => false]);
        $this->invokeNotification($santri->fresh(['orangTua']), 35000);

        Http::assertSentCount(2);
    }

    public function test_phone_formats_are_supported(): void
    {
        putenv('ONESENDER_API_KEY=test-key');
        putenv('ONESENDER_API_URL=https://api.example.com/send');

        foreach (['081234567890', '6281234567890', '+6281234567890'] as $noWa) {
            Http::fake(['*' => Http::response(['message' => 'sent'], 200)]);

            $result = WhatsappService::send($noWa, 'Test');
            $this->assertTrue($result['success'], "Failed for format: $noWa");
            $this->assertSame('+6281234567890', $result['formatted_number'], "Wrong format for: $noWa");
        }
    }
}
