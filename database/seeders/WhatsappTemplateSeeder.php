<?php

namespace Database\Seeders;

use App\Models\WhatsappTemplate;
use Illuminate\Database\Seeder;

class WhatsappTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'kode'   => 'AKUN_WALI_BARU',
                'nama'   => 'Notifikasi Akun Wali Baru (OTP)',
                'modul'  => 'Akun Wali',
                'judul'  => 'Aktivasi Akun Wali Santri',
                'konten' => "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\nBapak/Ibu *{{nama_wali}}* (Wali dari *{{nama_santri}}*),\n\nAkun Orang Tua/Wali untuk portal Pesantren Tahfizh Alam Qur'an telah aktif.\n\n*CARA MASUK KE PORTAL:*\n1. Buka tautan: {{link_login}}\n2. Masukkan Nomor WhatsApp: *{{no_wa}}*\n3. Klik tombol *Minta OTP*, lalu masukkan 6 digit kode yang dikirimkan ke WhatsApp ini.\n\nJazakumullah khairan katsiran.\n_Pesantren Tahfizh Alam Qur'an_",
            ],
            [
                'kode'   => 'UANG_SAKU_MUTASI',
                'nama'   => 'Mutasi Saldo Uang Saku (Titipan / Penarikan)',
                'modul'  => 'Uang Saku',
                'judul'  => 'Mutasi Uang Saku Santri',
                'konten' => "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\nBapak/Ibu wali dari ananda *{{nama_santri}}* (Kelas {{kelas}}),\n\nKami informasikan adanya {{jenis_mutasi}}:\n• {{label_nominal}}: *Rp {{nominal}}*\n• Keterangan: {{keterangan}}\n• Saldo Sebelumnya: Rp {{saldo_sebelum}}\n• {{label_saldo_akhir}}: *Rp {{saldo_sesudah}}*\n• Waktu: {{tanggal}} {{jam}} WIB\n\nTerima kasih atas perhatiannya.\n_Bagian Keuangan & Uang Saku_",
            ],
            [
                'kode'   => 'KANTIN_BELANJA',
                'nama'   => 'Transaksi Belanja Kantin/Aqumart',
                'modul'  => 'Kantin',
                'judul'  => 'Transaksi Belanja Santri',
                'konten' => "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\nBapak/Ibu wali dari ananda *{{nama_santri}}* (Kelas {{kelas}}),\n\nKami informasikan transaksi belanja ananda di *{{nama_toko}}*:\n• Rincian: {{produk}}\n• Total Belanja: *Rp {{total}}*\n• Saldo Sebelum: Rp {{saldo_sebelum}}\n• Sisa Saldo: *Rp {{saldo_sesudah}}*\n• Waktu: {{tanggal}} {{jam}} WIB\n\n_Pengelola Kantin/Aqumart Pesantren_",
            ],
            [
                'kode'   => 'SPP_TAGIHAN_BULANAN',
                'nama'   => 'Tagihan SPP Bulanan Baru',
                'modul'  => 'SPP',
                'judul'  => 'Informasi Tagihan SPP',
                'konten' => "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\nBapak/Ibu wali santri dari ananda *{{nama_santri}}* (Kelas {{kelas}}),\n\nPemberitahuan bahwa tagihan SPP untuk periode *{{bulan}} {{tahun}}* telah diterbitkan.\n\n• Rekening tujuan: *{{rekening_tujuan}}*\n\nRincian tagihan dapat dicek dan dikonfirmasi pembayarannya melalui tautan portal berikut:\n{{link_spp}}\n\nMohon pembayaran dapat dilakukan tepat waktu. Jazakumullah khairan katsiran.\n_Bagian Administrasi & Keuangan SPP_",
            ],
            [
                'kode'   => 'SPP_PEMBAYARAN_SUKSES',
                'nama'   => 'Konfirmasi Pembayaran SPP',
                'modul'  => 'SPP',
                'judul'  => 'Bukti Pembayaran SPP',
                'konten' => "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\nAlhamdulillah, pembayaran SPP santri *{{nama_santri}}* (Kelas {{kelas}}) untuk bulan *{{bulan}} {{tahun}}* telah kami terima.\n• Nominal Dibayar: *Rp {{nominal}}*\n• Status: *{{status}}*\n• Sisa Tagihan: Rp {{sisa_tagihan}}\n\nTerima kasih atas kerja samanya.\n_Bendahara Pesantren_",
            ],
            [
                'kode'   => 'TAHFIDZ_SETORAN',
                'nama'   => 'Laporan Setoran Tahfidz',
                'modul'  => 'Tahfidz',
                'judul'  => 'Laporan Hafalan Al-Qur\'an',
                'konten' => "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\nBapak/Ibu wali dari ananda *{{nama_santri}}* (Kelas {{kelas}}),\n\nAlhamdulillah, ananda telah menyetorkan hafalan:\n• Surat/Juz: *{{surat}} (Ayat {{ayat}}) - Juz {{juz}}*\n• Jenis: {{jenis_setoran}}\n• Kualitas/Hasil: *{{hasil}}*\n• Catatan Ustadz: {{catatan}}\n• Tanggal: {{tanggal}}\n\nSemoga ananda istiqomah dalam menjaga kalamullah.\n_Divisi Tahfidz Al-Qur'an_",
            ],
            [
                'kode'   => 'KONDISI_SANTRI_SAKIT',
                'nama'   => 'Informasi Santri Sakit / UKS',
                'modul'  => 'Kesehatan',
                'judul'  => 'Kondisi Kesehatan Santri',
                'konten' => "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\nBapak/Ibu wali dari ananda *{{nama_santri}}* (Kelas {{kelas}}),\n\nKami menginformasikan bahwa ananda saat ini sedang dirawat/diistirahatkan di ruang UKS Pondok:\n• Keluhan/Kondisi: *{{kondisi}}*\n• Penanganan: {{tindakan}}\n• Keterangan: {{keterangan}}\n• Waktu Pemeriksaan: {{tanggal}} {{jam}} WIB\n\nMohon doa dari Bapak/Ibu sekalian untuk kesembuhan ananda. Untuk informasi lebih lanjut dapat menghubungi bagian kesehatan pondok.\n_Pos Kesehatan Santri (UKS)_",
            ],
            [
                'kode'   => 'KONDISI_SANTRI_JEMPUT',
                'nama'   => 'Rekomendasi Jemput Santri Sakit (>3 Hari)',
                'modul'  => 'Kesehatan',
                'judul'  => 'Pemberitahuan Penjemputan Santri Sakit',
                'konten' => "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\nBapak/Ibu wali dari ananda *{{nama_santri}}* (Kelas {{kelas}}),\n\nDiberitahukan dari Pos Kesehatan (UKS) Pondok bahwa ananda saat ini telah sakit/dirawat selama *{{durasi_hari}} hari* dengan keluhan: *{{keluhan}}*.\n\nUntuk pemulihan yang lebih intensif dan kenyamanan ananda, pihak pesantren menyarankan agar ananda *dijemput untuk beristirahat dan dirawat di rumah*:\n• Tanggal Penjemputan: {{tanggal_jemput}}\n• Penjemput: {{nama_penjemput}}\n• Estimasi Kembali: {{estimasi_kembali}}\n• Catatan/Pesan: {{catatan}}\n\nMohon koordinasi dengan pengurus UKS/asrama saat menjemput ananda. Syafakumullah, semoga ananda lekas sembuh.\n_Pos Kesehatan Santri (UKS) Pesantren Alam Qur'an_",
            ],
            [
                'kode'   => 'PERIZINAN_DISETUJUI',
                'nama'   => 'Pemberitahuan Izin Keluar Disetujui',
                'modul'  => 'Perizinan',
                'judul'  => 'Persetujuan Izin Santri',
                'konten' => "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\nBapak/Ibu wali santri, pengajuan izin santri ananda *{{nama_santri}}* (Kelas {{kelas}}) telah *DISETUJUI*:\n• Jenis Izin: {{jenis_izin}}\n• Waktu Keluar: {{jam_keluar}}\n• Batas Maksimal Kembali: *{{batas_kembali}}*\n• Penjemput: {{penjemput}}\n\nMohon ananda kembali tepat waktu sebelum batas jam yang ditentukan.\n_Pengasuhan Santri_",
            ],
            [
                'kode'   => 'PERIZINAN_REMINDER',
                'nama'   => 'Reminder Jam Batas Pengantaran Kembali',
                'modul'  => 'Perizinan',
                'judul'  => 'Pengingat Kembali ke Pondok',
                'konten' => "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\nBapak/Ibu wali santri dari ananda *{{nama_santri}}* (Kelas {{kelas}}),\n\n⏰ *PENGINGAT KEMBALI KE PONDOK*\nDiberitahukan bahwa batas maksimal pengantaran kembali santri ke Pesantren Tahfizh Alam Qur'an adalah sore ini pukul *{{batas_kembali}}*.\n\nMohon dapat mengantar ananda tepat waktu sebelum pintu gerbang asrama ditutup.\n\nJazakumullah khairan katsiran.\n_Pengurus Asrama Pesantren Tahfizh Alam Qur'an_",
            ],
            [
                'kode'   => 'ABSENSI_KETIDAKHADIRAN',
                'nama'   => 'Pemberitahuan Ketidakhadiran Kelas',
                'modul'  => 'Absensi',
                'judul'  => 'Laporan Absensi KBM',
                'konten' => "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\nBapak/Ibu wali dari ananda *{{nama_santri}}* (Kelas {{kelas}}),\n\nKami menginformasikan bahwa ananda tercatat *{{status_kehadiran}}* pada kegiatan belajar mengajar hari ini ({{tanggal}}).\n• Keterangan: {{catatan}}\n\nDemikian informasi ini kami sampaikan.\n_Bagian Akademik & KBM_",
            ],
            [
                'kode'   => 'INFAQ_KONFIRMASI',
                'nama'   => 'Konfirmasi Penerimaan Infaq',
                'modul'  => 'Infaq',
                'judul'  => 'Bukti Tanda Terima Infaq',
                'konten' => "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\nTerima kasih kepada Bapak/Ibu *{{nama_donatur}}*,\n\nAlhamdulillah, donasi/infaq Anda telah kami verifikasi:\n• Program: *{{program}}*\n• Nominal: *Rp {{nominal}}*\n• Tanggal: {{tanggal}}\n• No. Referensi: {{ref_id}}\n\nSemoga menjadi amal jariyah yang berlipat ganda bagi keluarga. Aamiin.\n_Lembaga Infaq & Wakaf Pesantren Tahfizh Alam Qur'an_",
            ],
        ];

        foreach ($templates as $tpl) {
            // Template yang sudah diubah admin tidak boleh tertimpa saat seed dijalankan ulang.
            WhatsappTemplate::firstOrCreate(['kode' => $tpl['kode']], $tpl);
        }
    }
}
