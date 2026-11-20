<?php

namespace App\Services;

use App\Models\WhatsappMessage;
use App\Models\WhatsappSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    /**
     * Format dan normalisasi nomor WhatsApp ke format internasional 628xxx.
     * Mendukung format: 08xxx, +628xxx, 628xxx, spasi, strip.
     */
    public static function formatPhoneNumber(?string $number): ?string
    {
        if ($number === null || trim($number) === '' || trim($number) === '-') {
            return null;
        }

        $formatted = trim($number);
        $formatted = preg_replace('/[\s\-\.]/', '', $formatted);
        $formatted = preg_replace('/^\+/', '', $formatted);

        if (preg_match('/^08[0-9]{8,13}$/', $formatted)) {
            $formatted = '628' . substr($formatted, 2);
        } elseif (preg_match('/^628[0-9]{8,13}$/', $formatted)) {
            // Sudah format 628
        } elseif (preg_match('/^[1-9][0-9]{8,14}$/', $formatted)) {
            // Format internasional lainnya
        } else {
            return null;
        }

        return $formatted;
    }

    /**
     * Dapatkan provider WhatsApp aktif (onesender, fonnte, wablas, log).
     */
    public static function getActiveProvider(): string
    {
        return WhatsappSetting::get('whatsapp_provider', env('WHATSAPP_PROVIDER', 'onesender'));
    }

    /**
     * Kirim pesan langsung (sinkron).
     *
     * @return array{success: bool, formatted_number: ?string, status: ?int, response: mixed, error: ?string}
     */
    public static function send(string $number, string $message, ?string $provider = null): array
    {
        return self::sendMessage($number, $message, $provider);
    }

    public static function sendMessage(string $number, string $message, ?string $provider = null): array
    {
        $formattedNumber = self::formatPhoneNumber($number);

        if (!$formattedNumber) {
            Log::warning('[WA Gateway] Nomor WhatsApp tidak valid: ' . $number);
            return [
                'success'          => false,
                'formatted_number' => null,
                'status'           => null,
                'response'         => null,
                'error'            => 'Nomor WhatsApp tidak valid. Format: 08xxx atau 628xxx',
            ];
        }

        $provider = $provider ?: self::getActiveProvider();

        return match ($provider) {
            'fonnte'   => self::sendViaFonnte($formattedNumber, $message),
            'wablas'   => self::sendViaWablas($formattedNumber, $message),
            'log'      => self::sendViaLog($formattedNumber, $message),
            default    => self::sendViaOnesender($formattedNumber, $message),
        };
    }

    /**
     * Adapter: Onesender (API Utama)
     */
    protected static function sendViaOnesender(string $formattedNumber, string $message): array
    {
        $apiKey = WhatsappSetting::get('onesender_api_key', config('services.onesender.api_key', env('ONESENDER_API_KEY')));
        $apiUrl = WhatsappSetting::get('onesender_api_url', config('services.onesender.api_url', env('ONESENDER_API_URL')));

        if (!$apiKey || !$apiUrl) {
            Log::error('[WA Gateway - Onesender] ONESENDER_API_KEY atau ONESENDER_API_URL belum dikonfigurasi.');
            return [
                'success'          => false,
                'formatted_number' => $formattedNumber,
                'status'           => null,
                'response'         => null,
                'error'            => 'Konfigurasi Onesender API belum lengkap.',
            ];
        }

        try {
            $response = Http::timeout(15)->withHeaders([
                'Authorization' => "Bearer $apiKey",
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ])->post($apiUrl, [
                'recipient_type' => 'individual',
                'to'             => $formattedNumber,
                'type'           => 'text',
                'text'           => [
                    'body' => $message,
                ],
            ]);

            $responseBody = $response->json();
            $success      = $response->successful();

            $gatewayMessageId = $responseBody['data']['id'] ?? $responseBody['id'] ?? null;
            if (!$gatewayMessageId && isset($responseBody['messages'][0]['id'])) {
                $gatewayMessageId = $responseBody['messages'][0]['id'];
            }

            if ($success) {
                Log::info('[WA Gateway - Onesender] Pesan berhasil dikirim ke ' . $formattedNumber . ($gatewayMessageId ? " (GW ID: {$gatewayMessageId})" : ''));
            } else {
                Log::error('[WA Gateway - Onesender] Gagal mengirim pesan ke ' . $formattedNumber, [
                    'status'   => $response->status(),
                    'response' => $responseBody,
                ]);
            }

            return [
                'success'            => $success,
                'gateway_message_id' => $gatewayMessageId ? (string)$gatewayMessageId : null,
                'formatted_number'   => $formattedNumber,
                'status'             => $response->status(),
                'response'           => $responseBody,
                'error'              => $success ? null : 'Onesender HTTP ' . $response->status() . ': ' . json_encode($responseBody),
            ];
        } catch (\Exception $e) {
            Log::error('[WA Gateway - Onesender] Exception: ' . $e->getMessage());
            return [
                'success'          => false,
                'formatted_number' => $formattedNumber,
                'status'           => null,
                'response'         => null,
                'error'            => $e->getMessage(),
            ];
        }
    }

    /**
     * Adapter: Fonnte (Alternatif)
     */
    protected static function sendViaFonnte(string $formattedNumber, string $message): array
    {
        $token = WhatsappSetting::get('fonnte_token', env('FONNTE_TOKEN'));
        if (!$token) {
            return ['success' => false, 'formatted_number' => $formattedNumber, 'status' => null, 'response' => null, 'error' => 'Fonnte token belum dikonfigurasi'];
        }

        try {
            $response = Http::timeout(15)->withHeaders(['Authorization' => $token])->post('https://api.fonnte.com/send', [
                'target'  => $formattedNumber,
                'message' => $message,
            ]);
            $body = $response->json();
            return [
                'success'          => $response->successful() && ($body['status'] ?? false),
                'formatted_number' => $formattedNumber,
                'status'           => $response->status(),
                'response'         => $body,
                'error'            => $response->successful() ? null : 'Fonnte error: ' . $response->body(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'formatted_number' => $formattedNumber, 'status' => null, 'response' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Adapter: Wablas (Alternatif)
     */
    protected static function sendViaWablas(string $formattedNumber, string $message): array
    {
        $token = WhatsappSetting::get('wablas_token', env('WABLAS_TOKEN'));
        $domain = WhatsappSetting::get('wablas_domain', env('WABLAS_DOMAIN', 'https://bdg.wablas.com'));
        if (!$token) {
            return ['success' => false, 'formatted_number' => $formattedNumber, 'status' => null, 'response' => null, 'error' => 'Wablas token belum dikonfigurasi'];
        }

        try {
            $response = Http::timeout(15)->withHeaders(['Authorization' => $token])->post($domain . '/api/send-message', [
                'phone'   => $formattedNumber,
                'message' => $message,
            ]);
            $body = $response->json();
            return [
                'success'          => $response->successful() && ($body['status'] ?? false),
                'formatted_number' => $formattedNumber,
                'status'           => $response->status(),
                'response'         => $body,
                'error'            => $response->successful() ? null : 'Wablas error: ' . $response->body(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'formatted_number' => $formattedNumber, 'status' => null, 'response' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Adapter: Mock/Log (Untuk development tanpa kirim riil)
     */
    protected static function sendViaLog(string $formattedNumber, string $message): array
    {
        Log::info("[WA Gateway MOCK] Ke: {$formattedNumber} | Pesan: {$message}");
        return [
            'success'          => true,
            'formatted_number' => $formattedNumber,
            'status'           => 200,
            'response'         => ['status' => 'mock_sent'],
            'error'            => null,
        ];
    }

    /**
     * Cek status konektivitas provider WhatsApp.
     */
    public static function checkConnection(): array
    {
        $provider = self::getActiveProvider();

        if ($provider === 'log') {
            return [
                'connected' => true,
                'provider'  => 'log',
                'endpoint'  => 'local_log',
                'message'   => 'Mode Simulasi (Log) Aktif — Pesan dicatat ke storage/logs/laravel.log',
            ];
        }

        $apiKey = WhatsappSetting::get('onesender_api_key', config('services.onesender.api_key', env('ONESENDER_API_KEY')));
        $apiUrl = WhatsappSetting::get('onesender_api_url', config('services.onesender.api_url', env('ONESENDER_API_URL')));

        if (!$apiKey || !$apiUrl) {
            return [
                'connected' => false,
                'provider'  => $provider,
                'message'   => 'Konfigurasi API belum diisi di .env atau Pengaturan',
            ];
        }

        try {
            $parsedUrl = parse_url($apiUrl);
            $baseUrl = ($parsedUrl['scheme'] ?? 'https') . '://' . ($parsedUrl['host'] ?? '');
            if (!empty($parsedUrl['port'])) {
                $baseUrl .= ':' . $parsedUrl['port'];
            }

            // Lakukan ping HTTP ringan ke server gateway (timeout 3 detik)
            $response = Http::timeout(3)->withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Accept'        => 'application/json',
            ])->get($baseUrl ?: $apiUrl);

            $isReachable = $response->status() < 500;
            return [
                'connected'   => $isReachable,
                'provider'    => $provider,
                'endpoint'    => $apiUrl,
                'http_status' => $response->status(),
                'message'     => $isReachable
                    ? 'Gateway WhatsApp Terhubung & Siap Mengirim'
                    : 'Server Gateway merespons dengan kesalahan (HTTP ' . $response->status() . ')',
            ];
        } catch (\Throwable $e) {
            return [
                'connected' => false,
                'provider'  => $provider,
                'endpoint'  => $apiUrl,
                'message'   => 'Tidak dapat menghubungi server gateway: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sinkronkan status pesan dari server WhatsApp Gateway (OneSender).
     * Memperbarui status (sent, delivered, read, failed/error) dan rincian error.
     */
    public static function syncFromGateway(int $pages = 2): array
    {
        $provider = self::getActiveProvider();
        if ($provider !== 'onesender') {
            return [
                'success' => false,
                'message' => 'Sinkronisasi status pesan saat ini hanya didukung untuk provider Onesender.',
                'updated' => 0,
            ];
        }

        $apiKey = WhatsappSetting::get('onesender_api_key', config('services.onesender.api_key', env('ONESENDER_API_KEY')));
        $apiUrl = WhatsappSetting::get('onesender_api_url', config('services.onesender.api_url', env('ONESENDER_API_URL')));

        if (!$apiKey || !$apiUrl) {
            return [
                'success' => false,
                'message' => 'Konfigurasi Onesender API URL / Key belum lengkap.',
                'updated' => 0,
            ];
        }

        $totalUpdated = 0;
        $totalCreated = 0;
        $totalErrors  = 0;

        try {
            for ($page = 1; $page <= $pages; $page++) {
                $response = Http::timeout(15)->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Accept'        => 'application/json',
                ])->get($apiUrl, ['page' => $page]);

                if (!$response->successful()) {
                    break;
                }

                $data = $response->json();
                $messages = $data['data']['messages'] ?? [];
                if (empty($messages)) {
                    break;
                }

                foreach ($messages as $gw) {
                    $gwId = $gw['id'] ?? null;
                    $phone = $gw['phone'] ?? null;
                    if (!$gwId || !$phone) {
                        continue;
                    }

                    $rawStatus = strtolower(trim($gw['delivery_status'] ?? ''));
                    $content   = $gw['message_content'] ?? '';

                    // Format pesan error jika ada
                    $errorMessage = null;
                    $rawErrors = $gw['delivery_errors'] ?? $gw['errors'] ?? [];
                    if (!empty($rawErrors) && is_array($rawErrors)) {
                        $contents = [];
                        foreach ($rawErrors as $err) {
                            if (!empty($err['error_content'])) {
                                $ec = $err['error_content'];
                                if (stripos($ec, 'not registered on whatsapp') !== false) {
                                    $contents[] = 'Nomor belum terdaftar di WhatsApp';
                                } elseif (stripos($ec, 'failed to send after') !== false) {
                                    $contents[] = 'Gagal terkirim setelah beberapa kali percobaan';
                                } else {
                                    $contents[] = $ec;
                                }
                            }
                        }
                        if (!empty($contents)) {
                            $errorMessage = implode(' • ', array_unique($contents));
                        }
                    }

                    // Petakan status gateway ke status lokal
                    $appStatus = match ($rawStatus) {
                        'error', 'failed'   => 'failed',
                        'delivered'         => 'delivered',
                        'read'              => 'read',
                        'sent'              => 'sent',
                        'pending', 'queued' => 'pending',
                        default             => $rawStatus ?: 'sent',
                    };

                    if ($appStatus === 'failed') {
                        $totalErrors++;
                    }

                    // 1. Cari pesan di database lokal berdasarkan gateway_message_id
                    $localMsg = WhatsappMessage::where('gateway_message_id', (string)$gwId)->first();

                    // 2. Jika belum ada gateway_message_id, cari berdasarkan nomor penerima dan rentang waktu
                    if (!$localMsg) {
                        $gwCreatedAt = !empty($gw['created_at']) ? Carbon::parse($gw['created_at']) : null;
                        if ($gwCreatedAt) {
                            $localMsg = WhatsappMessage::where('recipient_number', $phone)
                                ->whereBetween('created_at', [
                                    $gwCreatedAt->copy()->subHours(2),
                                    $gwCreatedAt->copy()->addHours(2),
                                ])
                                ->whereNull('gateway_message_id')
                                ->first();
                        }
                    }

                    if ($localMsg) {
                        $updateData = [
                            'gateway_message_id' => (string)$gwId,
                            'status'             => $appStatus,
                        ];

                        if ($errorMessage) {
                            $updateData['error_message'] = $errorMessage;
                        } elseif (in_array($appStatus, ['sent', 'delivered', 'read'])) {
                            $updateData['error_message'] = null;
                        }

                        if ($appStatus === 'delivered' && empty($localMsg->delivered_at)) {
                            $updateData['delivered_at'] = !empty($gw['delivered_at']) && $gw['delivered_at'] !== '0001-01-01T00:00:00Z'
                                ? Carbon::parse($gw['delivered_at'])
                                : now();
                        }

                        if ($appStatus === 'read' && empty($localMsg->read_at)) {
                            $updateData['read_at'] = !empty($gw['read_at']) && $gw['read_at'] !== '0001-01-01T00:00:00Z'
                                ? Carbon::parse($gw['read_at'])
                                : now();
                        }

                        if ($localMsg->modul === 'Umum') {
                            if (stripos($content, 'hafalan') !== false || stripos($content, 'tahfidz') !== false) {
                                $updateData['modul'] = 'Tahfidz';
                            } elseif (stripos($content, 'spp') !== false) {
                                $updateData['modul'] = 'SPP';
                            }
                        }

                        if (empty($localMsg->recipient_name) || trim($localMsg->recipient_name) === '-') {
                            if (preg_match('/wali dari ananda \*([^\*]+)\*/i', $content, $mMatch)) {
                                $updateData['recipient_name'] = trim($mMatch[1]);
                            } else {
                                $resolved = WhatsappMessage::resolveRecipientNameByPhone($phone);
                                if ($resolved) {
                                    $updateData['recipient_name'] = $resolved;
                                }
                            }
                        }

                        $localMsg->update($updateData);
                        $totalUpdated++;
                    } else {
                        // Jika pesan berasal dari gateway namun belum ada di log lokal (misal dikirim manual/eksternal)
                        $modul = 'Umum';
                        if (stripos($content, 'hafalan') !== false || stripos($content, 'tahfidz') !== false) {
                            $modul = 'Tahfidz';
                        } elseif (stripos($content, 'spp') !== false) {
                            $modul = 'SPP';
                        } elseif (stripos($content, 'daftar ulang') !== false || stripos($content, 'biaya masuk') !== false) {
                            $modul = 'Daftar Ulang';
                        } elseif (stripos($content, 'otp') !== false) {
                            $modul = 'OTP Login';
                        } elseif (stripos($content, 'akun wali') !== false || stripos($content, 'login') !== false) {
                            $modul = 'Akun Wali';
                        } elseif (stripos($content, 'uang saku') !== false) {
                            $modul = 'Uang Saku';
                        } elseif (stripos($content, 'kantin') !== false || stripos($content, 'aqumart') !== false) {
                            $modul = 'Kantin';
                        }

                        $recipientName = null;
                        if (preg_match('/wali dari ananda \*([^\*]+)\*/i', $content, $mMatch)) {
                            $recipientName = trim($mMatch[1]);
                        } else {
                            $recipientName = WhatsappMessage::resolveRecipientNameByPhone($phone);
                        }

                        $time = !empty($gw['created_at']) ? Carbon::parse($gw['created_at']) : now();

                        WhatsappMessage::create([
                            'gateway_message_id' => (string)$gwId,
                            'reference_id'       => 'GW-' . $gwId,
                            'recipient_number'   => $phone,
                            'recipient_name'     => $recipientName,
                            'modul'              => $modul,
                            'message_body'       => $content,
                            'priority'           => 'normal',
                            'status'             => $appStatus,
                            'attempts'           => 1,
                            'max_attempts'       => 3,
                            'error_message'      => $errorMessage,
                            'provider'           => 'onesender',
                            'sent_at'            => $time,
                            'created_at'         => $time,
                        ]);
                        $totalCreated++;
                    }
                }
            }

            $info = [];
            if ($totalUpdated > 0) $info[] = "{$totalUpdated} diperbarui";
            if ($totalCreated > 0) $info[] = "{$totalCreated} pesan baru gateway ditambahkan";
            if ($totalErrors > 0)  $info[] = "{$totalErrors} pesan berstatus gagal/error di gateway";

            $summary = !empty($info) ? implode(', ', $info) : "semua data sudah sinkron";

            return [
                'success' => true,
                'updated' => $totalUpdated,
                'created' => $totalCreated,
                'errors'  => $totalErrors,
                'message' => "Sinkronisasi status dari WhatsApp Gateway selesai: {$summary}.",
            ];
        } catch (\Throwable $e) {
            Log::error('[WA Gateway Sync] Gagal sinkronisasi: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke WhatsApp Gateway saat sinkronisasi: ' . $e->getMessage(),
                'updated' => 0,
            ];
        }
    }

    /**
     * Memproses data Webhook callback dari OneSender secara otomatis dan real-time.
     */
    public static function processWebhook(array $payload): bool
    {
        $gwId   = $payload['data']['id'] ?? $payload['id'] ?? $payload['message_id'] ?? null;
        $phone  = $payload['data']['phone'] ?? $payload['phone'] ?? $payload['recipient'] ?? null;
        $status = strtolower($payload['data']['delivery_status'] ?? $payload['delivery_status'] ?? $payload['status'] ?? '');

        if (!$gwId && !$phone) {
            return false;
        }

        $localMsg = null;
        if ($gwId) {
            $localMsg = WhatsappMessage::where('gateway_message_id', (string)$gwId)->first();
        }
        if (!$localMsg && $phone) {
            $localMsg = WhatsappMessage::where('recipient_number', self::formatPhoneNumber($phone))
                ->latest()
                ->first();
        }

        if (!$localMsg) {
            return false;
        }

        $appStatus = match ($status) {
            'error', 'failed'   => 'failed',
            'delivered'         => 'delivered',
            'read'              => 'read',
            'sent'              => 'sent',
            'pending', 'queued' => 'pending',
            default             => $status ?: $localMsg->status,
        };

        $rawErrors = $payload['data']['delivery_errors'] ?? $payload['data']['errors'] ?? $payload['errors'] ?? [];
        $errorMessage = null;
        if (!empty($rawErrors) && is_array($rawErrors)) {
            $contents = [];
            foreach ($rawErrors as $err) {
                if (!empty($err['error_content'])) {
                    $ec = $err['error_content'];
                    if (stripos($ec, 'not registered on whatsapp') !== false) {
                        $contents[] = 'Nomor belum terdaftar di WhatsApp';
                    } elseif (stripos($ec, 'failed to send after') !== false) {
                        $contents[] = 'Gagal terkirim setelah beberapa kali percobaan';
                    } else {
                        $contents[] = $ec;
                    }
                }
            }
            if (!empty($contents)) {
                $errorMessage = implode(' • ', array_unique($contents));
            }
        }

        $updateData = ['status' => $appStatus];
        if ($gwId) {
            $updateData['gateway_message_id'] = (string)$gwId;
        }
        if ($errorMessage) {
            $updateData['error_message'] = $errorMessage;
        }
        if ($appStatus === 'delivered' && empty($localMsg->delivered_at)) {
            $updateData['delivered_at'] = now();
        }
        if ($appStatus === 'read' && empty($localMsg->read_at)) {
            $updateData['read_at'] = now();
        }

        $localMsg->update($updateData);
        Log::info("[WA Webhook] Status pesan ID {$localMsg->id} diperbarui via callback: {$appStatus}");

        return true;
    }
}
