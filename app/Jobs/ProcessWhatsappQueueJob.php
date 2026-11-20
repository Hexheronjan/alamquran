<?php

namespace App\Jobs;

use App\Models\WhatsappMessage;
use App\Models\WhatsappSetting;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class ProcessWhatsappQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $messageId;
    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(int $messageId)
    {
        $this->messageId = $messageId;
    }

    public function handle(): void
    {
        $msg = WhatsappMessage::find($this->messageId);
        if (!$msg) {
            return;
        }

        // Jangan proses jika sudah terkirim atau dibatalkan
        if (in_array($msg->status, ['sent', 'cancelled'])) {
            return;
        }

        // Cek Quiet Hours (Jam Tenang: default 21:00 - 06:00), kecuali prioritas URGENT
        if ($msg->priority !== 'urgent' && self::isQuietHours()) {
            $wakeTime = self::getQuietHoursEndTime();
            $msg->update([
                'status'       => 'pending',
                'scheduled_at' => $wakeTime,
            ]);
            Log::info("[WA Queue] Pesan ID {$msg->id} ditunda karena Jam Tenang (Quiet Hours) hingga " . $wakeTime->toDateTimeString());
            return;
        }

        $msg->update([
            'status'   => 'processing',
            'attempts' => $msg->attempts + 1,
        ]);

        $body = $msg->message_body;
        if (str_starts_with($body, '__sensitive__:')) {
            try {
                $body = Crypt::decryptString(substr($body, strlen('__sensitive__:')));
            } catch (\Throwable $e) {
                $msg->update(['status' => 'failed', 'error_message' => 'Isi pesan sensitif tidak dapat didekripsi.']);
                Log::error('[WA Queue] Pesan sensitif gagal didekripsi.', ['message_id' => $msg->id]);
                return;
            }
        }

        $result = WhatsappService::send($msg->recipient_number, $body, $msg->provider);

        if ($result['success']) {
            $msg->update([
                'status'             => 'sent',
                'gateway_message_id' => $result['gateway_message_id'] ?? $msg->gateway_message_id,
                'sent_at'            => Carbon::now(),
                'error_message'      => null,
            ]);
        } else {
            $isMax = $msg->attempts >= $msg->max_attempts;
            $msg->update([
                'status'        => $isMax ? 'failed' : 'pending',
                'error_message' => $result['error'] ?? 'Pengiriman gagal dari provider',
            ]);

            // Jika belum mencapai batas retry, rilis kembali ke queue dengan delay bertahap (exponential backoff)
            if (!$isMax) {
                $delaySeconds = $msg->attempts * 60; // 1 min, 2 min
                if ($this->job) {
                    $this->release($delaySeconds);
                } else {
                    self::dispatch($msg->id)->delay($delaySeconds);
                }
            }
        }
    }

    /**
     * Cek apakah saat ini berada dalam rentang jam tenang.
     */
    public static function isQuietHours(): bool
    {
        $enabled = WhatsappSetting::get('quiet_hours_enabled', '1') === '1';
        if (!$enabled) {
            return false;
        }

        $start = (int) WhatsappSetting::get('quiet_hours_start', '21'); // 21:00
        $end   = (int) WhatsappSetting::get('quiet_hours_end', '6');    // 06:00

        $currentHour = (int) Carbon::now()->format('G');

        if ($start > $end) {
            // Melewati tengah malam (misal 21:00 s.d 06:00)
            return $currentHour >= $start || $currentHour < $end;
        }

        return $currentHour >= $start && $currentHour < $end;
    }

    /**
     * Waktu berakhirnya jam tenang (misal pkl 06:00 pagi berikutnya).
     */
    public static function getQuietHoursEndTime(): Carbon
    {
        $end = (int) WhatsappSetting::get('quiet_hours_end', '6');
        $now = Carbon::now();

        if ((int)$now->format('G') >= 21) {
            return $now->copy()->addDay()->setHour($end)->setMinute(0)->setSecond(0);
        }

        return $now->copy()->setHour($end)->setMinute(0)->setSecond(0);
    }
}
