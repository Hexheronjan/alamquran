<?php

namespace Tests\Unit;

use App\Services\WhatsappService;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WhatsappServiceTest extends TestCase
{
    #[DataProvider('phoneNumberProvider')]
    public function test_format_phone_number(string $input, ?string $expected): void
    {
        $this->assertSame($expected, WhatsappService::formatPhoneNumber($input));
    }

    public static function phoneNumberProvider(): array
    {
        return [
            'format 08' => ['081234567890', '+6281234567890'],
            'format 628' => ['6281234567890', '+6281234567890'],
            'format +628' => ['+6281234567890', '+6281234567890'],
            'format 08 with spaces' => ['0812 3456 7890', '+6281234567890'],
            'invalid number' => ['12345', null],
            'empty string' => ['', null],
        ];
    }

    public function test_send_returns_success_on_api_ok(): void
    {
        Http::fake([
            '*' => Http::response(['message' => 'sent'], 200),
        ]);

        config(['app.env' => 'testing']);
        putenv('ONESENDER_API_KEY=test-key');
        putenv('ONESENDER_API_URL=https://api.example.com/send');

        $result = WhatsappService::send('081234567890', 'Test message');

        $this->assertTrue($result['success']);
        $this->assertSame('+6281234567890', $result['formatted_number']);
        $this->assertSame(200, $result['status']);
    }

    public function test_send_returns_failure_on_api_error(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'failed'], 500),
        ]);

        putenv('ONESENDER_API_KEY=test-key');
        putenv('ONESENDER_API_URL=https://api.example.com/send');

        $result = WhatsappService::send('6281234567890', 'Test message');

        $this->assertFalse($result['success']);
        $this->assertSame('+6281234567890', $result['formatted_number']);
        $this->assertSame(500, $result['status']);
    }
}
