<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\KalenderAkademik;
use App\Models\PelanggaranSantri;
use App\Models\Santri;
use App\Models\SantriPrestasi;
use App\Models\SaranMasukan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewClientFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private Admin $superAdmin;
    private User $wali;
    private Santri $santri;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = Admin::create([
            'name'      => 'Super Admin',
            'username'  => 'superadmin',
            'email'     => 'superadmin@example.com',
            'password'  => bcrypt('password'),
            'role'      => Admin::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->wali = User::create([
            'name'                 => 'Ahmad Wali',
            'email'                => 'ahmad@example.com',
            'password'             => bcrypt('password123'),
            'no_wa'                => '081234567890',
            'must_change_password' => false,
            'login_count'          => 0,
        ]);

        $this->santri = Santri::create([
            'user_id'      => $this->wali->id,
            'nama_lengkap' => 'Muhammad Bilal',
            'jenis_kelamin'=> 'L',
            'nis'          => '2026001',
            'kelas'        => '7A',
            'status'       => 'diterima',
        ]);
    }

    public function test_pelanggaran_crud_and_parent_visibility(): void
    {
        // 1. Admin mencatat pelanggaran santri
        $response = $this->actingAs($this->superAdmin, 'admin')->post(route('admin.pelanggaran.store'), [
            'santri_id'        => $this->santri->id,
            'tanggal'          => now()->toDateString(),
            'kategori'         => 'berat',
            'nama_pelanggaran' => 'Membawa HP ke asrama',
            'konsekuensi'      => 'Dicukur Gundul / Botak',
            'status_pembinaan' => 'sedang_dijalani',
            'poin'             => 50,
            'catatan'          => 'HP disita 1 semester',
        ]);

        $response->assertRedirect(route('admin.pelanggaran.index'));
        $this->assertDatabaseHas('pelanggaran_santris', [
            'santri_id'        => $this->santri->id,
            'nama_pelanggaran' => 'Membawa HP ke asrama',
            'konsekuensi'      => 'Dicukur Gundul / Botak',
            'poin'             => 50,
        ]);

        $pelanggaran = PelanggaranSantri::first();

        // 2. Admin mengupdate status pembinaan menjadi selesai
        $updateResp = $this->actingAs($this->superAdmin, 'admin')->put(route('admin.pelanggaran.update', $pelanggaran->id), [
            'santri_id'        => $this->santri->id,
            'tanggal'          => now()->toDateString(),
            'kategori'         => 'berat',
            'nama_pelanggaran' => 'Membawa HP ke asrama',
            'konsekuensi'      => 'Dicukur Gundul / Botak',
            'status_pembinaan' => 'selesai',
            'poin'             => 50,
            'catatan'          => 'Santri telah bertobat dan berjanji disiplin',
        ]);
        $updateResp->assertRedirect(route('admin.pelanggaran.index'));
        $this->assertDatabaseHas('pelanggaran_santris', [
            'id'               => $pelanggaran->id,
            'status_pembinaan' => 'selesai',
        ]);

        // 3. Wali Santri dapat melihat data pelanggaran dan tab multi-anak
        Santri::create([
            'user_id'       => $this->wali->id,
            'nama_lengkap'  => 'Adik Santri',
            'status'        => 'diterima',
            'kelas'         => '1',
            'nis'           => '2026002',
            'tanggal_lahir' => '2015-05-05',
        ]);

        // Kunjungi halaman default (menampilkan multi-tab anak)
        $defaultResp = $this->actingAs($this->wali, 'web')->get(route('user.pelanggaran.index'));
        $defaultResp->assertStatus(200);
        $defaultResp->assertSee('Adik Santri');
        $defaultResp->assertSee($this->santri->nama_lengkap);

        // Kunjungi anak pertama yang memiliki catatan pelanggaran
        $userResp = $this->actingAs($this->wali, 'web')->get(route('user.pelanggaran.index', ['santri_id' => $this->santri->id]));
        $userResp->assertStatus(200);
        $userResp->assertSee('Membawa HP ke asrama');
        $userResp->assertSee('Dicukur Gundul / Botak');
        $userResp->assertSee('Adik Santri');
    }

    public function test_saran_masukan_flow(): void
    {
        // 1. Wali santri mengirimkan saran & masukan
        $submitResp = $this->actingAs($this->wali, 'web')->post(route('user.saran.store'), [
            'kategori' => 'fitur_baru',
            'judul'    => 'Mohon tambahan jadwal mutaba\'ah malam',
            'pesan'    => 'Alangkah baiknya bila ada penambahan jam muroja\'ah mandiri setelah isya.',
        ]);

        $submitResp->assertRedirect(route('user.saran.index'));
        $this->assertDatabaseHas('saran_masukans', [
            'user_id' => $this->wali->id,
            'judul'   => 'Mohon tambahan jadwal mutaba\'ah malam',
            'status'  => 'menunggu_review',
        ]);

        $saran = SaranMasukan::first();

        // 2. Admin membaca dan membalas respon saran via modal (/admin/saran/{id}/status PATCH)
        $adminResp = $this->actingAs($this->superAdmin, 'admin')->patch("/admin/saran/{$saran->id}/status", [
            'status'          => 'sedang_diproses',
            'tanggapan_admin' => 'Terima kasih atas masukannya Bapak Ahmad, kami sampaikan ke pengasuh.',
        ]);

        $adminResp->assertRedirect(route('admin.saran.index'));
        $this->assertDatabaseHas('saran_masukans', [
            'id'              => $saran->id,
            'status'          => 'sedang_diproses',
            'tanggapan_admin' => 'Terima kasih atas masukannya Bapak Ahmad, kami sampaikan ke pengasuh.',
        ]);

        // Pastikan akses langsung GET ke /admin/saran/{id}/status me-redirect ke index tanpa 404
        $getRedirect = $this->actingAs($this->superAdmin, 'admin')->get("/admin/saran/{$saran->id}/status");
        $getRedirect->assertRedirect(route('admin.saran.index'));

        // 3. Wali melihat tanggapan admin di halaman saran miliknya
        $waliView = $this->actingAs($this->wali, 'web')->get(route('user.saran.index'));
        $waliView->assertStatus(200);
        $waliView->assertSee('Terima kasih atas masukannya Bapak Ahmad');
    }

    public function test_kalender_akademik_july_june_cycle(): void
    {
        // 1. Admin menambahkan agenda kalender akademik
        $storeResp = $this->actingAs($this->superAdmin, 'admin')->post(route('admin.kalender.store'), [
            'tahun_ajaran'    => '2026/2027',
            'judul_kegiatan'  => 'Ujian Penilaian Akhir Semester (PAS) Ganjil',
            'kategori'        => 'ujian',
            'warna'           => '#0284c7',
            'tanggal_mulai'   => '2026-12-05',
            'tanggal_selesai' => '2026-12-15',
            'keterangan'      => 'Seluruh santri wajib menuntaskan target hafalan sebelum ujian.',
            'is_public'       => '1',
        ]);

        $storeResp->assertRedirect(route('admin.kalender.index', ['tahun_ajaran' => '2026/2027']));
        $this->assertDatabaseHas('kalender_akademiks', [
            'judul_kegiatan' => 'Ujian Penilaian Akhir Semester (PAS) Ganjil',
            'tahun_ajaran'   => '2026/2027',
            'is_public'      => true,
        ]);

        // 2. Wali dapat melihat agenda di kalender akademik
        $waliResp = $this->actingAs($this->wali, 'web')->get(route('user.kalender.index', ['tahun_ajaran' => '2026/2027']));
        $waliResp->assertStatus(200);
        $waliResp->assertSee('Ujian Penilaian Akhir Semester (PAS) Ganjil');
    }

    public function test_prestasi_santri_crud_and_parent_view(): void
    {
        // 1. Admin mencatat prestasi santri
        $storeResp = $this->actingAs($this->superAdmin, 'admin')->post(route('admin.prestasi.store'), [
            'santri_id'         => $this->santri->id,
            'tahun_ajaran'      => '2026/2027',
            'nama_prestasi'     => 'Juara 1 MHQ 10 Juz',
            'kategori'          => 'tahfidz',
            'tingkat'           => 'provinsi',
            'peringkat'         => 'Juara 1',
            'penyelenggara'     => 'LPTQ Jawa Timur',
            'tanggal_perolehan' => '2026-08-17',
            'deskripsi'         => 'Meraih nilai tertinggi tajwid dan fashohah',
        ]);

        $storeResp->assertRedirect(route('admin.prestasi.index'));
        $this->assertDatabaseHas('santri_prestasis', [
            'santri_id'     => $this->santri->id,
            'nama_prestasi' => 'Juara 1 MHQ 10 Juz',
            'peringkat'     => 'Juara 1',
            'tingkat'       => 'provinsi',
        ]);

        // 2. Admin mencatat prestasi dengan kategori lainnya (custom)
        $storeLainnya = $this->actingAs($this->superAdmin, 'admin')->post(route('admin.prestasi.store'), [
            'santri_id'         => $this->santri->id,
            'tahun_ajaran'      => '2026/2027',
            'nama_prestasi'     => 'Juara 1 Lomba Robotik Nasional',
            'kategori'          => 'lainnya',
            'kategori_lainnya'  => 'Robotik & Otomasi',
            'tingkat'           => 'nasional',
            'peringkat'         => 'Juara 1',
            'penyelenggara'     => 'Kemenristek Dikti',
            'tanggal_perolehan' => '2026-09-10',
            'deskripsi'         => 'Membangun robot pemilah sampah otomatis',
        ]);
        $storeLainnya->assertRedirect(route('admin.prestasi.index'));
        $this->assertDatabaseHas('santri_prestasis', [
            'nama_prestasi' => 'Juara 1 Lomba Robotik Nasional',
            'kategori'      => 'Robotik & Otomasi',
        ]);

        // 3. Wali santri dapat melihat rekam prestasi anaknya
        $waliResp = $this->actingAs($this->wali, 'web')->get(route('user.prestasi.index'));
        $waliResp->assertStatus(200);
        $waliResp->assertSee('Juara 1 MHQ 10 Juz');
        $waliResp->assertSee('LPTQ Jawa Timur');
        $waliResp->assertSee('Robotik & Otomasi');
    }

    public function test_monitoring_wali_online_and_login_frequency(): void
    {
        // Set wali status: baru login & aktif sekarang
        $this->wali->update([
            'login_count'      => 5,
            'last_login_at'    => now(),
            'last_activity_at' => now(),
            'last_login_ip'    => '127.0.0.1',
        ]);

        $this->assertTrue($this->wali->isOnline());

        // Admin mengakses halaman monitoring wali
        $adminResp = $this->actingAs($this->superAdmin, 'admin')->get(route('admin.monitoring.wali', ['filter' => 'online']));
        $adminResp->assertStatus(200);
        $adminResp->assertSee($this->wali->name);
        $adminResp->assertSee('ONLINE');
        $adminResp->assertSee('5 kali');
    }
}
