<?php

namespace App\Jobs;

use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * @deprecated Gunakan WhatsappService::send() langsung untuk pengiriman sinkron.
 * Job ini tetap ada untuk kompatibilitas dan menjalankan pengiriman secara langsung.
 */
class SendWhatsappNotification
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $nomorWA;
    protected $message;

    public function __construct($nomorWA, $message)
    {
        $this->nomorWA = $nomorWA;
        $this->message = $message;
    }

    public function handle(): void
    {
        WhatsappService::send($this->nomorWA, $this->message);
    }
}
