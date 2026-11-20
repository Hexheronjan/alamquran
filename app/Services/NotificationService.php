<?php

namespace App\Services;

use App\Jobs\ProcessWhatsappQueueJob;
use App\Models\DaftarUlangSetting;
use App\Models\DaftarUlangTagihan;
use App\Models\InfaqTransaksi;
use App\Models\PerizinanSantri;
use App\Models\Santri;
use App\Models\User;
use App\Models\WhatsappMessage;
use App\Models\WhatsappSetting;
use App\Models\WhatsappTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Kirim notifikasi terpusat via WhatsApp Gateway.
     * Mendukung: Idempotency check, rendering template, antrean queue, prioritas, dan logging terpusat.
     */
    public static function send(array $data): ?WhatsappMessage
    {
        $rawNumber = $data['recipient_number'] ?? null;
        $formattedNumber = WhatsappService::formatPhoneNumber($rawNumber);

        if (!$formattedNumber) {
            Log::info("[NotificationService] Pengiriman dilewati: Nomor WA kosong atau tidak valid ({$rawNumber}).");
            return null;
        }

        // 1. Anti Duplikasi / Idempotency Check
        $referenceId = $data['reference_id'] ?? null;
        if ($referenceId) {
            $existing = WhatsappMessage::where('reference_id', $referenceId)
                ->whereIn('status', ['sent', 'pending', 'processing'])
                ->first();

            if ($existing) {
                Log::info("[NotificationService] Notifikasi duplikat diabaikan dengan reference_id: {$referenceId}");
                return $existing;
            }
        }

        // 2. Render Template atau Gunakan Pesan Langsung
        $messageBody  = $data['message'] ?? '';
        $templateKode = $data['template_kode'] ?? null;

        if ($templateKode) {
            $template = WhatsappTemplate::where('kode', $templateKode)->where('is_active', true)->first();
            if ($template) {
                $params = $data['template_params'] ?? [];
                $content = $template->konten;

                // Cegah penulisan ganda "Kelas Kelas 1" jika template sudah memuat kata "Kelas {{kelas}}"
                if (!empty($params['kelas']) && preg_match('/kelas\s+\{\{\s*kelas\s*\}\}/i', $content)) {
                    $params['kelas'] = preg_replace('/^kelas\s+/i', '', (string) $params['kelas']);
                }

                // Fallback otomatis jika template TAHFIDZ_SETORAN di DB belum memiliki placeholder {{juz}}
                if ($templateKode === 'TAHFIDZ_SETORAN' && !empty($params['juz']) && $params['juz'] !== '-' && !str_contains($content, '{{juz}}') && !str_contains($content, '{{ juz }}')) {
                    $content = str_replace('{{surat}} (Ayat {{ayat}})', '{{surat}} (Ayat {{ayat}}) - Juz {{juz}}', $content);
                    $content = str_replace('{{ surat }} (Ayat {{ ayat }})', '{{ surat }} (Ayat {{ ayat }}) - Juz {{ juz }}', $content);
                }

                // Penyesuaian cerdas UANG_SAKU_MUTASI: bedakan titipan masuk vs penarikan keluar
                if ($templateKode === 'UANG_SAKU_MUTASI') {
                    $isMasuk = ($params['jenis_mutasi_raw'] ?? '') === 'masuk'
                        || (isset($params['saldo_sesudah_num'], $params['saldo_sebelum_num']) && $params['saldo_sesudah_num'] >= $params['saldo_sebelum_num']);

                    if ($isMasuk) {
                        $content = str_ireplace('mutasi penarikan uang saku', 'penerimaan titipan uang saku (Masuk)', $content);
                        $content = str_ireplace('penarikan uang saku', 'titipan uang saku (Masuk)', $content);
                        $content = str_ireplace('Sisa Saldo Sekarang', 'Total Saldo Sekarang', $content);
                        $content = str_ireplace('• Nominal:', '• Nominal Titipan:', $content);
                    } else {
                        $content = str_ireplace('mutasi penarikan uang saku', 'penarikan uang saku (Keluar)', $content);
                        $content = str_ireplace('• Nominal:', '• Nominal Penarikan:', $content);
                    }
                }
                $messageBody = (new WhatsappTemplate(['konten' => $content]))->render($params);
            } elseif ($templateKode === 'KONDISI_SANTRI_JEMPUT') {
                $params = $data['template_params'] ?? [];
                $defaultJemput = "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\nBapak/Ibu wali dari ananda *{{nama_santri}}* (Kelas {{kelas}}),\n\nDiberitahukan dari Pos Kesehatan (UKS) Pondok bahwa ananda saat ini telah sakit/dirawat selama *{{durasi_hari}} hari* dengan keluhan: *{{keluhan}}*.\n\nUntuk pemulihan yang lebih intensif dan kenyamanan ananda, pihak pesantren menyarankan agar ananda *dijemput untuk beristirahat dan dirawat di rumah*:\n• Tanggal Penjemputan: {{tanggal_jemput}}\n• Penjemput: {{nama_penjemput}}\n• Estimasi Kembali: {{estimasi_kembali}}\n• Catatan/Pesan: {{catatan}}\n\nMohon koordinasi dengan pengurus UKS/asrama saat menjemput ananda. Syafakumullah, semoga ananda lekas sembuh.\n_Pos Kesehatan Santri (UKS) Pesantren Alam Qur'an_";
                if (!empty($params['kelas']) && preg_match('/kelas\s+\{\{\s*kelas\s*\}\}/i', $defaultJemput)) {
                    $params['kelas'] = preg_replace('/^kelas\s+/i', '', (string) $params['kelas']);
                }
                $messageBody = (new WhatsappTemplate(['konten' => $defaultJemput]))->render($params);
            }
        }

        if (trim($messageBody) === '') {
            Log::warning("[NotificationService] Pesan kosong untuk recipient: {$formattedNumber}");
            return null;
        }

        // 3. Simpan Pesan ke database (whatsapp_messages)
        $priority = $data['priority'] ?? 'normal';
        $sync = $data['sync'] ?? false;

        $msg = WhatsappMessage::create([
            'reference_id'     => $referenceId,
            'recipient_number' => $formattedNumber,
            'recipient_name'   => $data['recipient_name'] ?? null,
            'user_id'          => $data['user_id'] ?? null,
            'santri_id'        => $data['santri_id'] ?? null,
            'modul'            => $data['modul'] ?? 'Umum',
            'template_kode'    => $templateKode,
            'message_body'     => $messageBody,
            'priority'         => $priority,
            'status'           => 'pending',
            'provider'         => WhatsappService::getActiveProvider(),
            'scheduled_at'     => $data['scheduled_at'] ?? null,
        ]);

        // 4. Jalankan Pengiriman: Sinkron atau Masuk Antrean Queue
        if ($sync) {
            // Pengiriman langsung sinkron (misal tombol Kirim WA Kredensial di modal admin)
            (new ProcessWhatsappQueueJob($msg->id))->handle();
            $msg->refresh();
        } else {
            // Masuk ke Laravel Queue database
            dispatch(new ProcessWhatsappQueueJob($msg->id));
        }

        return $msg;
    }

    // =========================================================================
    // HELPER METHODS PER MODUL
    // =========================================================================

    /**
     * Kirim data login kredensial akun wali.
     * Menggunakan template dinamis 'AKUN_WALI_BARU' dengan variabel:
     * {{nama_santri}}, {{nama_wali}}, {{no_wa}}, {{link_login}}
     */
    public static function sendAkunWali(User $user, string $plainPassword = '', ?Santri $santri = null, bool $sync = true): ?WhatsappMessage
    {
        $namaSantri = $santri?->nama_lengkap ?? '-';
        $namaWali = $user->name ?? 'Wali Santri';
        $linkLogin = url('/login-user');

        // Cek template dari database
        $template = WhatsappTemplate::where('kode', 'AKUN_WALI_BARU')->where('is_active', true)->first();

        // Jika template di database masih memuat password atau username lama, otomatis perbarui ke format OTP
        if ($template && (str_contains($template->konten, 'Password') || str_contains($template->konten, 'password') || str_contains($template->konten, 'Username:'))) {
            $template->update([
                'konten' => "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\n"
                    . "Bapak/Ibu *{{nama_wali}}* (Wali dari *{{nama_santri}}*),\n\n"
                    . "Akun Orang Tua/Wali untuk portal Pesantren Tahfizh Alam Qur'an telah aktif.\n\n"
                    . "*CARA MASUK KE PORTAL:*\n"
                    . "1. Buka tautan: {{link_login}}\n"
                    . "2. Masukkan Nomor WhatsApp: *{{no_wa}}*\n"
                    . "3. Klik tombol *Minta OTP*, lalu masukkan 6 digit kode yang dikirimkan ke WhatsApp ini.\n\n"
                    . "Jazakumullah khairan katsiran.\n"
                    . "_Pesantren Tahfizh Alam Qur'an_",
            ]);
        }

        $params = [
            'nama_santri' => $namaSantri,
            'nama_wali'   => $namaWali,
            'no_wa'       => $user->no_wa,
            'link_login'  => $linkLogin,
        ];

        if ($template) {
            $message = $template->render($params);
        } else {
            // Fallback default jika template di database belum ada / nonaktif
            $message = "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\n"
                . "Bapak/Ibu *{$namaWali}* (Wali dari *{$namaSantri}*),\n\n"
                . "Akun Orang Tua/Wali untuk portal Pesantren Tahfizh Alam Qur'an telah aktif.\n\n"
                . "*CARA MASUK KE PORTAL:*\n"
                . "1. Buka tautan: {$linkLogin}\n"
                . "2. Masukkan Nomor WhatsApp Anda: *{$user->no_wa}*\n"
                . "3. Klik tombol *Minta OTP*, lalu masukkan kode OTP yang dikirimkan ke WhatsApp ini.\n\n"
                . "Jazakumullah khairan katsiran.\n"
                . "_Pesantren Tahfizh Alam Qur'an_";
        }

        return self::send([
            'recipient_number' => $user->no_wa,
            'recipient_name'   => $user->name,
            'user_id'          => $user->id,
            'santri_id'        => $santri?->id,
            'modul'            => 'Akun Wali',
            'priority'         => 'urgent',
            'reference_id'     => 'AKUN-WALI-' . $user->id . '-' . Str::uuid(),
            'template_kode'    => 'AKUN_WALI_BARU',
            'template_params'  => $params,
            'message'          => $message,
            'sync'             => $sync,
        ]);
    }

    /**
     * Kirim pesan sensitif melalui gateway yang sama dan catat metadata tanpa
     * menyimpan isi rahasia, misalnya password sementara atau OTP.
     */
    public static function sendSensitive(array $data): ?WhatsappMessage
    {
        $number = WhatsappService::formatPhoneNumber($data['recipient_number'] ?? null);
        if (!$number || trim($data['message'] ?? '') === '') {
            return null;
        }

        $referenceId = $data['reference_id'] ?? null;
        if ($referenceId && ($existing = WhatsappMessage::where('reference_id', $referenceId)->whereIn('status', ['pending', 'processing', 'sent'])->first())) {
            // Pesan OTP yang sebelumnya masih pending dapat diproses langsung
            // saat wali meminta ulang, tanpa membuat OTP/pesan ganda.
            if (($data['sync'] ?? false) && $existing->status === 'pending') {
                (new ProcessWhatsappQueueJob($existing->id))->handle();
                $existing->refresh();
            }
            return $existing;
        }

        $message = WhatsappMessage::create([
            'reference_id' => $data['reference_id'] ?? null,
            'recipient_number' => $number,
            'recipient_name' => $data['recipient_name'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'santri_id' => $data['santri_id'] ?? null,
            'modul' => $data['modul'] ?? 'Umum',
            'message_body' => '__sensitive__:' . Crypt::encryptString($data['message']),
            'priority' => $data['priority'] ?? 'urgent',
            'status' => 'pending',
            'attempts' => 0,
            'max_attempts' => 3,
            'error_message' => null,
            'provider' => WhatsappService::getActiveProvider(),
            'sent_at' => null,
        ]);

        // OTP dan kredensial adalah interaksi yang sedang ditunggu pengguna.
        // Tetap menggunakan gateway/log pusat, namun langsung diproses agar
        // halaman login tidak bergantung pada worker queue yang aktif.
        if ($data['sync'] ?? false) {
            (new ProcessWhatsappQueueJob($message->id))->handle();
            $message->refresh();
        } else {
            dispatch(new ProcessWhatsappQueueJob($message->id));
        }

        return $message;
    }

    /**
     * Bersihkan prefix "Kelas " agar tidak dobel saat dirender ke template "(Kelas {{kelas}})".
     */
    public static function cleanKelas(?string $kelas): string
    {
        if (empty($kelas)) return '1';
        return preg_replace('/^kelas\s+/i', '', trim($kelas));
    }

    /**
     * Notifikasi penarikan saldo uang saku.
     */
    public static function sendUangSaku(
        Santri $santri,
        float $nominal,
        string $keterangan,
        float $saldoSebelum,
        float $saldoSesudah,
        string $referenceId,
        string $jenis = 'masuk'
    ): ?WhatsappMessage {
        $noWa = $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa;
        if (!$noWa) return null;

        $isMasuk = ($jenis === 'masuk') || ($saldoSesudah >= $saldoSebelum);
        $jenisMutasi = $isMasuk ? 'titipan / penambahan saldo uang saku (Masuk)' : 'penarikan uang saku (Keluar)';
        $labelNominal = $isMasuk ? 'Nominal Titipan' : 'Nominal Penarikan';
        $labelSaldoAkhir = $isMasuk ? 'Total Saldo Sekarang' : 'Sisa Saldo Sekarang';

        return self::send([
            'recipient_number' => $noWa,
            'recipient_name'   => $santri->orangTua?->nama_ayah ?? 'Wali Santri',
            'user_id'          => $santri->user_id,
            'santri_id'        => $santri->id,
            'modul'            => 'Uang Saku',
            'template_kode'    => 'UANG_SAKU_MUTASI',
            'priority'         => 'normal',
            'reference_id'     => 'US-' . $referenceId,
            'template_params'  => [
                'nama_santri'       => $santri->nama_lengkap,
                'kelas'             => self::cleanKelas($santri->kelas_label ?? $santri->kelas),
                'nominal'           => number_format($nominal, 0, ',', '.'),
                'keterangan'        => $keterangan,
                'saldo_sebelum'     => $saldoSebelum < 0 ? '-' . number_format(abs($saldoSebelum), 0, ',', '.') : number_format($saldoSebelum, 0, ',', '.'),
                'saldo_sesudah'     => $saldoSesudah < 0 ? '-' . number_format(abs($saldoSesudah), 0, ',', '.') . ' (Talangan Pondok)' : number_format($saldoSesudah, 0, ',', '.'),
                'saldo_sebelum_num' => $saldoSebelum,
                'saldo_sesudah_num' => $saldoSesudah,
                'jenis_mutasi_raw'  => $isMasuk ? 'masuk' : 'keluar',
                'jenis_mutasi'      => $jenisMutasi,
                'label_nominal'     => $labelNominal,
                'label_saldo_akhir' => $labelSaldoAkhir,
                'tanggal'           => date('d/m/Y'),
                'jam'               => date('H:i'),
            ],
            'sync'             => true,
        ]);
    }

    /**
     * Notifikasi pengajuan bukti transfer deposit uang saku baru dari wali santri
     * yang otomatis dikirim ke nomor WhatsApp Ustadz Pembimbing kelas terkait.
     */
    public static function sendDepositKonfirmasiKeUstadz(
        Santri $santri,
        \App\Models\DepositTransferManual $deposit,
        ?\App\Models\RekeningKelas $rekeningKelas = null
    ): ?WhatsappMessage {
        $noWaUstadz = $rekeningKelas?->no_wa_ustadz;
        if (!$noWaUstadz) {
            return null;
        }

        $formattedNumber = WhatsappService::formatPhoneNumber($noWaUstadz);
        if (!$formattedNumber) {
            return null;
        }

        $namaUstadz = $rekeningKelas->nama_ustadz ?: 'Ustadz Pembimbing';
        $namaSantri = $santri->nama_lengkap;
        $kelas      = self::cleanKelas($santri->kelas_label ?? $santri->kelas);
        $nominal    = number_format($deposit->jumlah, 0, ',', '.');
        $catatan    = $deposit->catatan_wali ?: '-';
        $waktu      = now()->format('d/m/Y H:i') . ' WIB';
        $linkAdmin  = url('/admin/deposit-transfer');

        $message = "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\n"
            . "Yth. *{$namaUstadz}* (Pembimbing Kelas {$kelas}),\n\n"
            . "Telah masuk pengajuan bukti transfer *Deposit Uang Saku* baru dari wali santri yang perlu diperiksa dan diverifikasi:\n\n"
            . "👤 *Santri:* {$namaSantri} (Kelas {$kelas})\n"
            . "💵 *Nominal Transfer:* *Rp {$nominal}*\n"
            . "📝 *Catatan Wali:* {$catatan}\n"
            . "⏰ *Waktu:* {$waktu}\n\n"
            . "Silakan periksa mutasi rekening Anda dan login ke panel Admin untuk menyetujui deposit uang saku ananda:\n"
            . "🔗 {$linkAdmin}\n\n"
            . "_Pesantren Tahfizh Alam Qur'an_";

        return self::send([
            'recipient_number' => $formattedNumber,
            'recipient_name'   => $namaUstadz,
            'santri_id'        => $santri->id,
            'modul'            => 'Uang Saku',
            'priority'         => 'urgent',
            'reference_id'     => 'DEPOSIT-MASUK-USTADZ-' . $deposit->id,
            'message'          => $message,
            'sync'             => true,
        ]);
    }

    /**
     * Notifikasi transaksi kantin / aqumart.
     */
    public static function sendKantin(
        Santri $santri,
        string $namaToko,
        string $produkSummary,
        float $total,
        float $saldoSebelum,
        float $saldoSesudah,
        string $referenceId
    ): ?WhatsappMessage {
        $noWa = $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa;
        if (!$noWa) return null;

        return self::send([
            'recipient_number' => $noWa,
            'recipient_name'   => $santri->orangTua?->nama_ayah ?? 'Wali Santri',
            'user_id'          => $santri->user_id,
            'santri_id'        => $santri->id,
            'modul'            => 'Kantin',
            'template_kode'    => 'KANTIN_BELANJA',
            'priority'         => 'normal',
            'reference_id'     => 'KANTIN-' . $referenceId,
            'template_params'  => [
                'nama_santri'   => $santri->nama_lengkap,
                'kelas'         => self::cleanKelas($santri->kelas_label ?? $santri->kelas),
                'nama_toko'     => $namaToko,
                'produk'        => $produkSummary,
                'total'         => number_format($total, 0, ',', '.'),
                'saldo_sebelum' => number_format($saldoSebelum, 0, ',', '.'),
                'saldo_sesudah' => number_format($saldoSesudah, 0, ',', '.'),
                'tanggal'       => date('d/m/Y'),
                'jam'           => date('H:i'),
            ],
            'sync'             => true,
        ]);
    }

    /**
     * Notifikasi tagihan SPP bulanan lengkap dengan rincian rekening, add-on (+),
     * tunggakan bulan-bulan sebelumnya, biaya admin 1x transaksi, dan total keseluruhan.
     */
    public static function sendSppTagihan(
        Santri $santri,
        int $bulan,
        int $tahun,
        float $nominal,
        string $rekeningTujuan,
        string $referenceId,
        float $biayaAdmin = 2500,
        bool $sync = true
    ): ?WhatsappMessage {
        $noWa = $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa;
        if (!$noWa) return null;

        // PENGAMAN MUTLAK: Jika santri belum diatur nomor rekeningnya dan rekening tujuan tidak valid, batalkan pengiriman!
        $cleanRek = trim(str_replace(['•', '-', 'a.n.', 'a.n', 'Rekening Pondok'], '', (string) $rekeningTujuan));
        if (empty($santri->nomor_rekening_spp) || empty($cleanRek) || str_contains($rekeningTujuan, 'Belum Diatur') || trim($rekeningTujuan) === 'a.n.') {
            \Illuminate\Support\Facades\Log::warning("[NotificationService] Pengiriman WA tagihan SPP dibatalkan untuk santri {$santri->nama_lengkap} karena rekening belum diatur / tidak valid.");
            return null;
        }

        // PENGAMAN MUTLAK ALUMNI: Santri yang sudah lulus/alumni tidak boleh ditagih via WA
        if ($santri->kelas === 'alumni' || $santri->status_santri === 'lulus' || $santri->status_santri === 'alumni') {
            \Illuminate\Support\Facades\Log::info("[NotificationService] Pengiriman WA tagihan SPP dibatalkan karena santri {$santri->nama_lengkap} sudah berstatus alumni / lulus.");
            return null;
        }

        // Ambil tagihan bulan berjalan
        $currentTagihan = \App\Models\SppTagihan::with('addons')
            ->where('santri_id', $santri->id)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();

        // Jika bulan berjalan ini sudah LUNAS (misal sudah bayar 3 bulan di muka / diinput lunas), jangan menagih lagi!
        if ($currentTagihan && $currentTagihan->status === 'lunas') {
            return null;
        }

        $namaBulan = \App\Models\SppTagihan::getNamaBulan($bulan);
        $linkSpp = url('/user/spp');

        // Hitung rincian bulan berjalan
        $nominalSppPokok = $currentTagihan ? (float) $currentTagihan->nominal_spp : $nominal;
        $sisaBulanIni = $currentTagihan ? (float) $currentTagihan->sisa_tagihan : $nominal;

        $rincianLines = [];
        $rincianLines[] = "• SPP {$namaBulan} {$tahun}: Rp " . number_format($nominalSppPokok, 0, ',', '.');

        if ($currentTagihan && $currentTagihan->addons->isNotEmpty()) {
            foreach ($currentTagihan->addons as $addon) {
                $ketAddon = $addon->keterangan ? " ({$addon->keterangan})" : '';
                $rincianLines[] = "  + {$addon->nama_biaya}{$ketAddon}: Rp " . number_format($addon->nominal, 0, ',', '.');
            }
        }
        if ($currentTagihan && (float) $currentTagihan->nominal_terbayar > 0) {
            $rincianLines[] = "  - Sudah Dibayar: Rp " . number_format($currentTagihan->nominal_terbayar, 0, ',', '.');
        }
        $rincianLines[] = "  *Subtotal {$namaBulan} {$tahun}: Rp " . number_format($sisaBulanIni, 0, ',', '.') . "*";

        // Cari semua tunggakan bulan-bulan sebelumnya yang belum lunas
        $tunggakanList = \App\Models\SppTagihan::with('addons')
            ->where('santri_id', $santri->id)
            ->where('status', '!=', 'lunas')
            ->where('sisa_tagihan', '>', 0)
            ->where(function ($q) use ($bulan, $tahun) {
                $q->where('tahun', '<', $tahun)
                  ->orWhere(function ($q2) use ($bulan, $tahun) {
                      $q2->where('tahun', $tahun)->where('bulan', '<', $bulan);
                  });
            })
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc')
            ->get();

        $totalTunggakan = 0.0;
        if ($tunggakanList->isNotEmpty()) {
            $rincianLines[] = "";
            $rincianLines[] = "⚠️ *Tunggakan Bulan Sebelumnya:*";
            foreach ($tunggakanList as $tgk) {
                $sisaTgk = (float) $tgk->sisa_tagihan;
                $totalTunggakan += $sisaTgk;
                $addonInfo = '';
                if ($tgk->addons->isNotEmpty()) {
                    $addonParts = $tgk->addons->map(fn($a) => "+ {$a->nama_biaya} Rp " . number_format($a->nominal, 0, ',', '.'))->implode(', ');
                    $addonInfo = " _(SPP Rp " . number_format($tgk->nominal_spp, 0, ',', '.') . " {$addonParts})_";
                }
                $rincianLines[] = "• {$tgk->bulan_label}: *Rp " . number_format($sisaTgk, 0, ',', '.') . "*{$addonInfo}";
            }
            $rincianLines[] = "  *Total Tunggakan Lalu: Rp " . number_format($totalTunggakan, 0, ',', '.') . "*";
        }

        // Biaya admin cukup 1 kali transaksi (tidak dikali per bulan)
        $totalTagihanPokok = $sisaBulanIni + $totalTunggakan;
        $totalHarusDibayar = $totalTagihanPokok + $biayaAdmin;

        $rincianLines[] = "";
        $rincianLines[] = "• Total Tagihan Keseluruhan: Rp " . number_format($totalTagihanPokok, 0, ',', '.');
        $rincianLines[] = "• + Biaya Admin (1x Transaksi): Rp " . number_format($biayaAdmin, 0, ',', '.');

        $rincianText = implode("\n", $rincianLines);

        // Pastikan template di database mendukung rincian tagihan, tunggakan, dan total bayar
        $templateContent = "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\n"
            . "Bapak/Ibu wali santri dari ananda *{{nama_santri}}* (Kelas {{kelas}}),\n\n"
            . "Berikut adalah rincian tagihan SPP periode *{{bulan}} {{tahun}}*:\n\n"
            . "💳 *Rekening Tujuan Pembayaran:*\n"
            . "• *{{rekening_tujuan}}*\n\n"
            . "📋 *Rincian Tagihan:*\n"
            . "{{rincian_tagihan}}\n\n"
            . "💰 *TOTAL YANG HARUS DIBAYAR: Rp {{total_harus_dibayar}}*\n"
            . "_(Pembayaran dilakukan lunas sekaligus sesuai total di atas dengan 1x biaya admin transaksi)_\n\n"
            . "Rincian tagihan dapat dicek dan dibayarkan melalui tautan portal wali berikut:\n"
            . "{{link_spp}}\n\n"
            . "Mohon pembayaran dapat dilakukan tepat waktu. Jazakumullah khairan katsiran.\n"
            . "_Bagian Administrasi & Keuangan SPP_";

        $template = WhatsappTemplate::where('kode', 'SPP_TAGIHAN_BULANAN')->where('is_active', true)->first();
        if ($template && (!str_contains($template->konten, '{{rincian_tagihan}}') || !str_contains($template->konten, '{{total_harus_dibayar}}'))) {
            $template->update(['konten' => $templateContent]);
        }

        $params = [
            'nama_santri'         => $santri->nama_lengkap,
            'kelas'               => self::cleanKelas($santri->kelas_label ?? $santri->kelas),
            'bulan'               => $namaBulan,
            'tahun'               => $tahun,
            'link_spp'            => $linkSpp,
            'nomor_va'            => $rekeningTujuan,
            'rekening_tujuan'     => $rekeningTujuan,
            'rincian_tagihan'     => $rincianText,
            'nominal'             => number_format($sisaBulanIni, 0, ',', '.'),
            'total_tunggakan'     => number_format($totalTunggakan, 0, ',', '.'),
            'biaya_admin'         => number_format($biayaAdmin, 0, ',', '.'),
            'total_bayar'         => number_format($totalHarusDibayar, 0, ',', '.'),
            'total_harus_dibayar' => number_format($totalHarusDibayar, 0, ',', '.'),
        ];

        $messageFallback = (new WhatsappTemplate(['konten' => $templateContent]))->render($params);

        return self::send([
            'recipient_number' => $noWa,
            'recipient_name'   => $santri->orangTua?->nama_ayah ?? 'Wali Santri',
            'user_id'          => $santri->user_id,
            'santri_id'        => $santri->id,
            'modul'            => 'SPP',
            'template_kode'    => 'SPP_TAGIHAN_BULANAN',
            'priority'         => 'normal',
            'reference_id'     => 'SPP-TAGIHAN-' . $referenceId,
            'message'          => $messageFallback,
            'template_params'  => $params,
            'sync'             => $sync,
        ]);
    }

    /**
     * Kirim pesan rekapitulasi seluruh tagihan SPP santri dari tab Tunggakan Admin.
     * Tidak menyebut bulan berjalan sebagai "tunggakan bulan sebelumnya", melainkan daftar per bulan.
     */
    public static function sendSppTotalTunggakan(
        Santri $santri,
        $unpaidTagihans,
        string $rekeningTujuan,
        float $biayaAdmin = 2500,
        bool $sync = true
    ): ?WhatsappMessage {
        $noWa = $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa;
        if (!$noWa) return null;

        $cleanRek = trim(str_replace(['•', '-', 'a.n.', 'a.n', 'Rekening Pondok'], '', (string) $rekeningTujuan));
        if (empty($santri->nomor_rekening_spp) || empty($cleanRek) || str_contains($rekeningTujuan, 'Belum Diatur') || trim($rekeningTujuan) === 'a.n.') {
            \Illuminate\Support\Facades\Log::warning("[NotificationService] WA tagihan total dibatalkan untuk santri {$santri->nama_lengkap} karena rekening belum diatur / tidak valid.");
            return null;
        }

        $linkSpp = url('/user/spp');
        $rincianLines = [];
        $totalPokok = 0.0;
        $jumlahBulan = $unpaidTagihans->count();

        foreach ($unpaidTagihans as $tagihan) {
            $sisa = (float) $tagihan->sisa_tagihan;
            $totalPokok += $sisa;

            $addonParts = [];
            if ($tagihan->addons->isNotEmpty()) {
                foreach ($tagihan->addons as $addon) {
                    $addonParts[] = "+ {$addon->nama_biaya} Rp " . number_format($addon->nominal, 0, ',', '.');
                }
            }
            $addonText = !empty($addonParts) ? ' ' . implode(', ', $addonParts) : '';

            $angsuranInfo = '';
            if ((float) $tagihan->nominal_terbayar > 0) {
                $angsuranInfo = " _(Sudah diangsur Rp " . number_format($tagihan->nominal_terbayar, 0, ',', '.') . ")_";
            }

            $rincianLines[] = "• *{$tagihan->bulan_label}*: *Rp " . number_format($sisa, 0, ',', '.') . "* (SPP Rp " . number_format($tagihan->nominal_spp, 0, ',', '.') . "{$addonText}){$angsuranInfo}";
        }

        $totalHarusDibayar = $totalPokok + $biayaAdmin;

        $rincianLines[] = "";
        $rincianLines[] = "• Total Tagihan SPP ({$jumlahBulan} Bulan): Rp " . number_format($totalPokok, 0, ',', '.');
        $rincianLines[] = "• + Biaya Admin (Cukup 1x Transaksi): Rp " . number_format($biayaAdmin, 0, ',', '.');

        $rincianText = implode("\n", $rincianLines);
        $periodeLabels = $unpaidTagihans->pluck('bulan_label')->implode(', ');

        $message = "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\n"
            . "Bapak/Ibu wali santri dari ananda *{$santri->nama_lengkap}* (Kelas " . self::cleanKelas($santri->kelas_label ?? $santri->kelas) . "),\n\n"
            . "Berikut adalah rincian tagihan SPP yang harus diselesaikan ({$periodeLabels}):\n\n"
            . "💳 *Rekening Tujuan Pembayaran:*\n"
            . "• *{$rekeningTujuan}*\n\n"
            . "📋 *Rincian Tagihan:*\n"
            . "{$rincianText}\n\n"
            . "💰 *TOTAL KESELURUHAN HARUS DIBAYAR: Rp " . number_format($totalHarusDibayar, 0, ',', '.') . "*\n"
            . "_(Cukup 1 kali biaya transfer/admin untuk melunasi seluruh bulan di atas)_\n\n"
            . "Pembayaran dapat dicek, dibayar lunas sekaligus, atau diangsur per bulan melalui tautan portal wali santri berikut:\n"
            . "{$linkSpp}\n\n"
            . "Mohon pembayaran dapat dilakukan tepat waktu. Jazakumullah khairan katsiran.\n"
            . "_Bagian Administrasi & Keuangan SPP_";

        return self::send([
            'recipient_number' => $noWa,
            'recipient_name'   => $santri->orangTua?->nama_ayah ?? 'Wali Santri',
            'user_id'          => $santri->user_id,
            'santri_id'        => $santri->id,
            'modul'            => 'SPP',
            'template_kode'    => 'SPP_TAGIHAN_TOTAL',
            'priority'         => 'normal',
            'reference_id'     => 'SPP-TOTAL-' . $santri->id . '-' . now()->format('YmdHis'),
            'message'          => $message,
            'sync'             => $sync,
        ]);
    }

    /**
     * Notifikasi WA saat biaya tambahan (Add-on) ditambahkan atau diperbarui pada tagihan santri.
     */
    public static function sendSppAddonNotif(
        Santri $santri,
        \App\Models\SppTagihan $tagihan,
        \App\Models\SppTagihanAddon $addon,
        string $aksi = 'ditambahkan'
    ): ?WhatsappMessage {
        $noWa = $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa;
        if (!$noWa) return null;

        $sppSetting = \App\Models\SppSetting::getActive();
        $biayaAdmin = $sppSetting?->biaya_admin ? (float) $sppSetting->biaya_admin : 2500.0;
        $rekSantri = $santri->info_rekening_spp;
        $rekeningTujuan = !empty($santri->nomor_rekening_spp)
            ? "{$rekSantri['bank']} {$rekSantri['nomor_rekening']} ({$rekSantri['atas_nama']})"
            : ($sppSetting ? trim("{$sppSetting->nama_bank} {$sppSetting->nomor_rekening} a.n. {$sppSetting->atas_nama_rekening}") : 'Rekening Pondok');

        return self::sendSppTagihan(
            $santri,
            (int) $tagihan->bulan,
            (int) $tagihan->tahun,
            (float) $tagihan->nominal_tagihan,
            $rekeningTujuan,
            $tagihan->id . '-ADDON-' . $addon->id . '-' . now()->format('His'),
            $biayaAdmin
        );
    }

    /**
     * Notifikasi konfirmasi pembayaran SPP baru dari wali ke WhatsApp Bendahara.
     */
    public static function sendSppKonfirmasiKeBendahara(
        Santri $santri,
        \App\Models\SppTagihan $tagihan,
        \App\Models\SppPembayaran $pembayaran,
        string $noWaBendahara
    ): ?WhatsappMessage {
        $noWa = WhatsappService::formatPhoneNumber($noWaBendahara);
        if (!$noWa) return null;

        $linkAdmin = url('/admin/spp');
        $namaSantri = $santri->nama_lengkap;
        $kelas = $santri->kelas_label ?? $santri->kelas;
        $periode = $tagihan->bulan_label;
        $nominal = number_format($pembayaran->jumlah_bayar, 0, ',', '.');
        $catatan = $pembayaran->catatan ?: '-';

        $message = "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\n"
            . "Pemberitahuan untuk Bagian Bendahara:\n"
            . "Telah masuk konfirmasi pembayaran SPP baru yang perlu divalidasi:\n\n"
            . "👤 *Santri:* {$namaSantri} ({$kelas})\n"
            . "📅 *Periode:* {$periode}\n"
            . "💵 *Nominal:* Rp {$nominal}\n"
            . "📝 *Catatan:* {$catatan}\n\n"
            . "Silakan login ke panel Admin SPP untuk memeriksa bukti transfer dan memvalidasi pembayaran:\n"
            . "🔗 {$linkAdmin}\n\n"
            . "_Pesantren Tahfizh Alam Qur'an_";

        return self::send([
            'recipient_number' => $noWa,
            'recipient_name'   => 'Bendahara Pesantren',
            'santri_id'        => $santri->id,
            'modul'            => 'SPP',
            'priority'         => 'urgent',
            'reference_id'     => 'SPP-NOTIF-BENDAHARA-' . $pembayaran->id,
            'message'          => $message,
            'sync'             => true,
        ]);
    }

    /**
     * Notifikasi bukti transfer biaya masuk / daftar ulang santri baru masuk ke Bendahara.
     */
    public static function sendDaftarUlangKonfirmasiKeBendahara(
        Santri $santri,
        \App\Models\DaftarUlangTagihan $tagihan,
        \App\Models\DaftarUlangPembayaran $pembayaran,
        string $noWaBendahara
    ): ?WhatsappMessage {
        $noWa = WhatsappService::formatPhoneNumber($noWaBendahara);
        if (!$noWa) return null;

        $linkAdmin = url('/admin/daftar-ulang');
        $namaSantri = $santri->nama_lengkap;
        $angkatan = $tagihan->tahun_angkatan;
        $nominal = number_format($pembayaran->jumlah_bayar, 0, ',', '.');
        $catatan = $pembayaran->catatan ?: '-';

        $message = "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\n"
            . "Pemberitahuan untuk Bagian Bendahara:\n"
            . "Telah masuk konfirmasi pembayaran *Biaya Daftar Ulang / Masuk Santri Baru*:\n\n"
            . "👤 *Santri:* {$namaSantri} (Angkatan {$angkatan})\n"
            . "💵 *Nominal Ditransfer:* Rp {$nominal}\n"
            . "📝 *Catatan:* {$catatan}\n\n"
            . "Silakan login ke panel Admin untuk memeriksa bukti transfer dan memvalidasi pembayaran:\n"
            . "🔗 {$linkAdmin}\n\n"
            . "_Pesantren Tahfizh Alam Qur'an_";

        return self::send([
            'recipient_number' => $noWa,
            'recipient_name'   => 'Bendahara Pesantren',
            'santri_id'        => $santri->id,
            'modul'            => 'Daftar Ulang',
            'priority'         => 'urgent',
            'reference_id'     => 'DU-NOTIF-BENDAHARA-' . $pembayaran->id,
            'message'          => $message,
            'sync'             => true,
        ]);
    }

    /**
     * Notifikasi cicilan / pelunasan daftar ulang diterima ke Wali Santri.
     */
    public static function sendDaftarUlangDiterima(
        Santri $santri,
        \App\Models\DaftarUlangTagihan $tagihan,
        float $jumlahBayar,
        string $pembayaranId
    ): ?WhatsappMessage {
        $noWa = $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa;
        if (!$noWa) return null;

        $namaSantri = $santri->nama_lengkap;
        $statusLabel = $tagihan->status === 'lunas' ? 'LUNAS' : 'ANGSURAN DITERIMA';
        $nominalBayar = number_format($jumlahBayar, 0, ',', '.');
        $totalBiaya = number_format($tagihan->total_tagihan, 0, ',', '.');
        $terbayar = number_format($tagihan->nominal_terbayar, 0, ',', '.');
        $sisa = number_format($tagihan->sisa_tagihan, 0, ',', '.');

        $addonNote = '';
        if (($tagihan->nominal_addon ?? 0) > 0) {
            $addonNote = "   _(Rincian: Pokok Rp " . number_format($tagihan->nominal_biaya, 0, ',', '.') . " + Add-on Rp " . number_format($tagihan->nominal_addon, 0, ',', '.') . ")_\n";
        }

        $message = "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\n"
            . "Alhamdulillah, pembayaran *Biaya Masuk / Daftar Ulang* santri baru ananda *{$namaSantri}* telah kami terima dan diverifikasi:\n\n"
            . "💵 *Jumlah Diterima:* Rp {$nominalBayar}\n"
            . "📊 *Status Tagihan:* *{$statusLabel}*\n"
            . "💰 *Total Biaya:* Rp {$totalBiaya}\n"
            . $addonNote
            . "✅ *Total Masuk:* Rp {$terbayar}\n"
            . "⏳ *Sisa Tagihan:* *Rp {$sisa}*\n\n"
            . "Jazakumullah khairan katsiran atas kerja samanya.\n\n"
            . "_Pesantren Tahfizh Alam Qur'an_";

        return self::send([
            'recipient_number' => $noWa,
            'recipient_name'   => $santri->orangTua?->nama_ayah ?? 'Wali Santri',
            'user_id'          => $santri->user_id,
            'santri_id'        => $santri->id,
            'modul'            => 'Daftar Ulang',
            'priority'         => 'urgent',
            'reference_id'     => 'DU-BAYAR-' . $pembayaranId,
            'message'          => $message,
            'sync'             => true,
        ]);
    }

    /**
     * Notifikasi WhatsApp resmi: Pembukaan Sistem Pembayaran Biaya Masuk & Daftar Ulang.
     * Menggunakan format teks asli tanpa perubahan.
     */
    public static function sendDaftarUlangDibuka(Santri $santri, DaftarUlangTagihan $tagihan, bool $sync = false): ?WhatsappMessage
    {
        $noWa = $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa;
        if (!$noWa) {
            return null;
        }

        // Jangan kirim jika beasiswa penuh, atau tagihan sudah lunas, atau rekening belum diatur
        if ($tagihan->is_beasiswa || $tagihan->status === 'lunas' || empty($santri->nomor_rekening_spp) || $santri->nomor_rekening_spp === 'Belum Diatur') {
            return null;
        }

        $namaSantri    = $santri->nama_lengkap;
        $nis           = $santri->nis ?: ($santri->nomor_pendaftaran ?: '-');
        $tahunAngkatan = $tagihan->tahun_angkatan ?: ($santri->tahun_masuk ?: date('Y'));

        $rekInfo       = $santri->info_rekening_spp;
        $namaBank      = $rekInfo['bank'] ?? ($santri->nama_bank_spp ?: '-');
        $nomorRekening = $rekInfo['nomor_rekening'] ?? ($santri->nomor_rekening_spp ?: '-');
        $atasNama      = $rekInfo['atas_nama'] ?? ($santri->atas_nama_rekening_spp ?: "a.n. {$namaSantri}");

        $linkPortalWali = url('/user/daftar-ulang');

        $message = "Assalamu’alaikum Warahmatullahi Wabarakatuh,\n\n"
            . "Yth. Ayah/Bunda Wali dari ananda:\n"
            . "👤 *Nama Santri:* {$namaSantri}\n"
            . "🏫 *Angkatan:* {$tahunAngkatan}\n\n"
            . "Diberitahukan bahwa sistem pembayaran *Biaya Masuk & Daftar Ulang* santri baru Pesantren Tahfizh Alam Qur’an telah resmi dibuka.\n\n"
            . "💳 *Rekening Tujuan Pembayaran:*\n"
            . "• *Bank:* {$namaBank}\n"
            . "• *No. Rekening / VA:* *{$nomorRekening}*\n"
            . "• *Atas Nama:* {$atasNama}\n\n"
            . "ℹ️ *Catatan Pembayaran:*\n"
            . " Setelah melakukan transfer, silakan unggah bukti transfer melalui portal wali santri pada tautan berikut:\n"
            . "🔗 {$linkPortalWali}\n\n"
            . "Jazakumullah khairan katsiran atas perhatian dan kerja samanya.\n\n"
            . "Wassalamu’alaikum Warahmatullahi Wabarakatuh.\n"
            . "_Pesantren Tahfizh Alam Qur'an_";

        $referenceId = 'DU-OPENING-' . $tagihan->id . '-' . now()->format('YmdHis');

        return self::send([
            'recipient_number' => $noWa,
            'recipient_name'   => $santri->orangTua?->nama_ayah ?? 'Wali Santri',
            'user_id'          => $santri->user_id,
            'santri_id'        => $santri->id,
            'modul'            => 'Daftar Ulang',
            'priority'         => 'normal',
            'reference_id'     => $referenceId,
            'message'          => $message,
            'sync'             => $sync,
        ]);
    }

    /**
     * Broadcast otomatis pembukaan sistem pembayaran Biaya Masuk & Daftar Ulang
     * ke semua wali santri yang sudah siap (biaya & rekening sudah diatur dan belum lunas).
     */
    public static function broadcastDaftarUlangDibuka(?string $tahunAngkatan = null, bool $sync = true): int
    {
        $setting = DaftarUlangSetting::getActive();
        if (!$setting->isOpen()) {
            return 0;
        }

        $sessionStart = $setting->getOpeningSessionTimestamp() ?? now()->startOfDay();

        $query = DaftarUlangTagihan::with(['santri.orangTua', 'santri.user'])
            ->where('is_ready', true)
            ->where('is_beasiswa', false)
            ->where('status', '!=', 'lunas')
            ->where('sisa_tagihan', '>', 0)
            ->whereHas('santri', function ($q) {
                $q->where('status', 'diterima')
                  ->whereNotNull('nomor_rekening_spp')
                  ->where('nomor_rekening_spp', '!=', '')
                  ->where('nomor_rekening_spp', '!=', 'Belum Diatur');
            });

        if ($tahunAngkatan) {
            $query->where('tahun_angkatan', $tahunAngkatan);
        }

        $tagihans = $query->get();
        $sentCount = 0;

        foreach ($tagihans as $tagihan) {
            $santri = $tagihan->santri;
            if (!$santri) continue;

            // Anti duplikasi dalam sesi pembukaan ini:
            // Cek apakah santri ini sudah pernah menerima notifikasi pembukaan sejak sesi buka saat ini dimulai
            $alreadySentThisSession = WhatsappMessage::where('santri_id', $santri->id)
                ->where('modul', 'Daftar Ulang')
                ->where('reference_id', 'like', 'DU-OPENING-%')
                ->where('created_at', '>=', $sessionStart)
                ->whereIn('status', ['sent', 'pending', 'processing'])
                ->exists();

            if ($alreadySentThisSession) {
                continue;
            }

            $res = self::sendDaftarUlangDibuka($santri, $tagihan, $sync);
            if ($res) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    /**
     * Notifikasi pembayaran SPP berhasil diterima.
     */
    public static function sendSppLunas(
        Santri $santri,
        int $bulan,
        int $tahun,
        float $nominal,
        string $status,
        float $sisaTagihan,
        string $referenceId
    ): ?WhatsappMessage {
        $noWa = $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa;
        if (!$noWa) return null;

        $namaBulan = \App\Models\SppTagihan::getNamaBulan($bulan);

        return self::send([
            'recipient_number' => $noWa,
            'recipient_name'   => $santri->orangTua?->nama_ayah ?? 'Wali Santri',
            'user_id'          => $santri->user_id,
            'santri_id'        => $santri->id,
            'modul'            => 'SPP',
            'template_kode'    => 'SPP_PEMBAYARAN_SUKSES',
            'priority'         => 'normal',
            'reference_id'     => 'SPP-BAYAR-' . $referenceId,
            'template_params'  => [
                'nama_santri'  => $santri->nama_lengkap,
                'kelas'        => self::cleanKelas($santri->kelas_label ?? $santri->kelas),
                'bulan'        => $namaBulan,
                'tahun'        => $tahun,
                'nominal'      => number_format($nominal, 0, ',', '.'),
                'status'       => ucfirst(str_replace('_', ' ', $status)),
                'sisa_tagihan' => number_format($sisaTagihan, 0, ',', '.'),
            ],
            'sync'             => true,
        ]);
    }

    /**
     * Notifikasi setoran hafalan tahfidz.
     */
    public static function sendTahfidz(
        Santri $santri,
        string $surat,
        string $ayat,
        string $jenis,
        string $hasil,
        ?string $catatan,
        string $referenceId,
        $juz = null
    ): ?WhatsappMessage {
        $noWa = $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa;
        if (!$noWa) return null;

        $juzClean = $juz ? trim(str_ireplace('juz', '', (string)$juz)) : '';
        $jenisFormatted = match (strtolower(trim($jenis))) {
            'ziyadah'       => 'Ziyadah',
            'murajaah'      => 'Murojaah',
            'ujian_juziyah', 'ujian juziyah', "ujian juz'iyah" => "Ujian Juz'iyah",
            default         => ucfirst($jenis),
        };

        return self::send([
            'recipient_number' => $noWa,
            'recipient_name'   => $santri->orangTua?->nama_ayah ?? 'Wali Santri',
            'user_id'          => $santri->user_id,
            'santri_id'        => $santri->id,
            'modul'            => 'Tahfidz',
            'template_kode'    => 'TAHFIDZ_SETORAN',
            'priority'         => 'normal',
            'reference_id'     => 'TAHFIDZ-' . $referenceId,
            'template_params'  => [
                'nama_santri'   => $santri->nama_lengkap,
                'kelas'         => self::cleanKelas($santri->kelas_label ?? $santri->kelas),
                'surat'         => $surat,
                'ayat'          => $ayat,
                'juz'           => $juzClean ?: '-',
                'nama_juz'      => $juzClean ? "Juz {$juzClean}" : '-',
                'jenis_setoran' => $jenisFormatted,
                'hasil'         => $hasil,
                'catatan'       => $catatan ?: '-',
                'tanggal'       => date('d/m/Y'),
            ],
            'sync'             => true,
        ]);
    }

    /**
     * Notifikasi santri sakit / masuk UKS.
     */
    public static function sendSantriSakit(
        Santri $santri,
        string $kondisi,
        ?string $tindakan,
        ?string $keterangan,
        string $referenceId
    ): ?WhatsappMessage {
        $noWa = $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa;
        if (!$noWa) return null;

        return self::send([
            'recipient_number' => $noWa,
            'recipient_name'   => $santri->orangTua?->nama_ayah ?? 'Wali Santri',
            'user_id'          => $santri->user_id,
            'santri_id'        => $santri->id,
            'modul'            => 'Kesehatan',
            'template_kode'    => 'KONDISI_SANTRI_SAKIT',
            'priority'         => 'high', // Prioritas tinggi
            'reference_id'     => 'SAKIT-' . $referenceId,
            'template_params'  => [
                'nama_santri' => $santri->nama_lengkap,
                'kelas'       => self::cleanKelas($santri->kelas_label ?? $santri->kelas),
                'kondisi'     => $kondisi,
                'tindakan'    => $tindakan ?: 'Istirahat di ruang UKS & pemberian obat/vitamin',
                'keterangan'  => $keterangan ?: '-',
                'tanggal'     => date('d/m/Y'),
                'jam'         => date('H:i'),
            ],
            'sync'             => true,
        ]);
    }

    /**
     * Notifikasi rekomendasi jemput santri sakit (istirahat di rumah).
     */
    public static function sendJemputSantri(
        Santri $santri,
        \App\Models\SantriKesehatan $catatan,
        int $durasiHari
    ): ?WhatsappMessage {
        $noWa = $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa;
        if (!$noWa) return null;

        return self::send([
            'recipient_number' => $noWa,
            'recipient_name'   => $santri->orangTua?->nama_ayah ?? 'Wali Santri',
            'user_id'          => $santri->user_id,
            'santri_id'        => $santri->id,
            'modul'            => 'Kesehatan',
            'template_kode'    => 'KONDISI_SANTRI_JEMPUT',
            'priority'         => 'high',
            'reference_id'     => 'JEMPUT-' . $catatan->id . '-' . date('YmdHi'),
            'template_params'  => [
                'nama_santri'      => $santri->nama_lengkap,
                'kelas'            => self::cleanKelas($santri->kelas_label ?? $santri->kelas),
                'keluhan'          => $catatan->keluhan_sakit,
                'durasi_hari'      => (string) $durasiHari,
                'tanggal_jemput'   => $catatan->tanggal_jemput ? \Carbon\Carbon::parse($catatan->tanggal_jemput)->translatedFormat('d F Y, H:i') : date('d/m/Y H:i'),
                'nama_penjemput'   => $catatan->nama_penjemput ?: 'Orang Tua / Wali',
                'estimasi_kembali' => $catatan->estimasi_kembali ? \Carbon\Carbon::parse($catatan->estimasi_kembali)->translatedFormat('d F Y') : 'Menyesuaikan kesembuhan ananda',
                'catatan'          => $catatan->catatan_penjemputan ?: ($catatan->tindakan_perawatan ?: 'Disarankan istirahat di rumah untuk pemulihan lebih optimal'),
                'tanggal'          => date('d/m/Y'),
            ],
            'sync'             => true,
        ]);
    }

    /**
     * Notifikasi perizinan disetujui.
     */
    public static function sendPerizinanDisetujui(PerizinanSantri $izin): ?WhatsappMessage
    {
        $santri = $izin->santri;
        if (!$santri) return null;

        $noWa = $izin->no_wa_penjemput ?: ($santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa);
        if (!$noWa) return null;

        return self::send([
            'recipient_number' => $noWa,
            'recipient_name'   => $santri->orangTua?->nama_ayah ?? 'Wali Santri',
            'user_id'          => $santri->user_id,
            'santri_id'        => $santri->id,
            'modul'            => 'Perizinan',
            'template_kode'    => 'PERIZINAN_DISETUJUI',
            'priority'         => 'high',
            'reference_id'     => 'IZIN-ACC-' . $izin->id,
            'template_params'  => [
                'nama_santri'   => $santri->nama_lengkap,
                'kelas'         => self::cleanKelas($santri->kelas_label ?? $santri->kelas),
                'jenis_izin'    => $izin->jenis_izin === 'pulang' ? 'Izin Pulang' : 'Keluar Sementara',
                'jam_keluar'    => $izin->waktu_keluar ? Carbon::parse($izin->waktu_keluar)->format('d/m/Y H:i') : '-',
                'batas_kembali' => $izin->batas_jam_kembali ? Carbon::parse($izin->batas_jam_kembali)->format('d/m/Y H:i') . ' WIB' : '17:00 WIB',
                'penjemput'     => $izin->nama_penjemput ?? 'Orang Tua / Wali',
            ],
            'sync'             => true,
        ]);
    }

    /**
     * Notifikasi ketidakhadiran (alpha/sakit/izin) pada KBM.
     */
    public static function sendAbsensi(
        Santri $santri,
        string $statusKehadiran,
        ?string $catatan,
        string $referenceId
    ): ?WhatsappMessage {
        $noWa = $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa;
        if (!$noWa) return null;

        return self::send([
            'recipient_number' => $noWa,
            'recipient_name'   => $santri->orangTua?->nama_ayah ?? 'Wali Santri',
            'user_id'          => $santri->user_id,
            'santri_id'        => $santri->id,
            'modul'            => 'Absensi',
            'template_kode'    => 'ABSENSI_KETIDAKHADIRAN',
            'priority'         => 'normal',
            'reference_id'     => 'ABS-' . $referenceId,
            'template_params'  => [
                'nama_santri'      => $santri->nama_lengkap,
                'kelas'            => self::cleanKelas($santri->kelas_label ?? $santri->kelas),
                'status_kehadiran' => strtoupper($statusKehadiran),
                'tanggal'          => date('d/m/Y'),
                'catatan'          => $catatan ?: 'Tercatat tidak hadir',
            ],
            'sync'             => true,
        ]);
    }

    /**
     * Notifikasi bukti tanda terima infaq.
     */
    public static function sendInfaq(InfaqTransaksi $transaksi): ?WhatsappMessage
    {
        $noWa = $transaksi->no_wa_donatur ?: $transaksi->user?->no_wa;
        if (!$noWa) return null;

        return self::send([
            'recipient_number' => $noWa,
            'recipient_name'   => $transaksi->nama_donatur,
            'user_id'          => $transaksi->user_id,
            'modul'            => 'Infaq',
            'template_kode'    => 'INFAQ_KONFIRMASI',
            'priority'         => 'normal',
            'reference_id'     => 'INFAQ-' . $transaksi->id,
            'template_params'  => [
                'nama_donatur' => $transaksi->nama_donatur,
                'program'      => $transaksi->program?->nama_program ?? 'Infaq Pondok',
                'nominal'      => number_format($transaksi->nominal, 0, ',', '.'),
                'tanggal'      => date('d/m/Y H:i'),
                'ref_id'       => $transaksi->kode_transaksi ?? ('INF-' . $transaksi->id),
            ],
            'sync'             => true,
        ]);
    }
}
