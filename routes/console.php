<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule otomatis kenaikan kelas setiap tahun ajaran baru (1 Juli pkl 07:00 WIB)
Schedule::command('santri:promote-classes')->yearlyOn(7, 1, '07:00')->timezone('Asia/Jakarta');
// Schedule otomatis broadcast tagihan SPP bulanan setiap tanggal 10 pkl 08:00 WIB
Schedule::command('spp:broadcast-monthly')->monthlyOn(10, '08:00')->timezone('Asia/Jakarta')->withoutOverlapping();
Schedule::command('spp:send-reminders')->dailyAt('09:15')->timezone('Asia/Jakarta')->withoutOverlapping();
Schedule::command('perizinan:send-reminder')->dailyAt('15:00')->timezone('Asia/Jakarta')->withoutOverlapping();
// Schedule otomatis kirim reminder WhatsApp jadwal telepon santri (otomatis 15 menit sebelum giliran dimulai)
Schedule::command('telepon:send-reminder')->everyMinute()->timezone('Asia/Jakarta')->withoutOverlapping();

// Schedule otomatis cek pembukaan daftar ulang jika tanggal buka tiba
Schedule::call(function () {
    \App\Services\NotificationService::broadcastDaftarUlangDibuka();
})->name('daftar-ulang-broadcast-dibuka')->hourly()->withoutOverlapping();

// Proses antrean pesan WhatsApp dan jobs background setiap menit via cron cPanel
Schedule::command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping();

// Sinkronkan status pengiriman pesan dari WhatsApp Gateway setiap 15 menit
Schedule::call(function () {
    \App\Services\WhatsappService::syncFromGateway(1);
})->name('whatsapp-sync-gateway')->everyFifteenMinutes()->withoutOverlapping();

// Artisan Command: php artisan whatsapp:sync-gateway
Artisan::command('whatsapp:sync-gateway {pages=2}', function ($pages = 2) {
    $this->info("Menyinkronkan status pesan dari WhatsApp Gateway...");
    $result = \App\Services\WhatsappService::syncFromGateway((int)$pages);
    $this->info($result['message']);
})->purpose('Sinkronkan status pesan terkini dari WhatsApp Gateway OneSender');

