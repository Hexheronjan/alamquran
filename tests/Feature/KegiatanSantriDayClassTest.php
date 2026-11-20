<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\KegiatanHarian;
use App\Models\Santri;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KegiatanSantriDayClassTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name' => 'Admin Test',
            'username' => 'admintest',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => Admin::ROLE_SUPER_ADMIN,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $this->user = User::factory()->create([
            'must_change_password' => false,
        ]);

        School::create([
            'id' => 1,
            'name' => 'Pesantren Tahfizh Alam Qur\'an',
            'email' => 'admin@alamquran.com',
            'address' => 'Jl. Kebon Agung No. 1',
            'phone' => '081234567890',
        ]);
    }

    /**
     * Test KegiatanHarian::getCurrentAndNextActivity resolves correctly based on day and class.
     */
    public function test_get_current_and_next_activity_matches_day_and_class()
    {
        // Buat kegiatan Senin Kelas 1
        KegiatanHarian::create([
            'nama_kegiatan' => 'Tahsin Pagi Kelas 1',
            'waktu_mulai' => '05:00:00',
            'waktu_selesai' => '06:30:00',
            'hari' => 'senin',
            'kelas' => '1',
            'is_rutin_24jam' => true,
        ]);

        // Buat kegiatan Senin Kelas 2
        KegiatanHarian::create([
            'nama_kegiatan' => 'Tahfidz Mandiri Kelas 2',
            'waktu_mulai' => '05:00:00',
            'waktu_selesai' => '06:30:00',
            'hari' => 'senin',
            'kelas' => '2',
            'is_rutin_24jam' => true,
        ]);

        // Buat kegiatan Selanjutnya Senin Semua Kelas
        KegiatanHarian::create([
            'nama_kegiatan' => 'Sarapan Pagi Bersama',
            'waktu_mulai' => '06:30:00',
            'waktu_selesai' => '07:30:00',
            'hari' => 'senin',
            'kelas' => 'semua',
            'is_rutin_24jam' => true,
        ]);

        // Simulasikan hari Senin jam 05:30 WIB
        $seninMorning = Carbon::create(2026, 10, 26, 5, 30, 0); // 26 Okt 2026 adalah Senin

        // Cek untuk santri Kelas 1
        $resKls1 = KegiatanHarian::getCurrentAndNextActivity('1', $seninMorning);
        $this->assertNotNull($resKls1['current']);
        $this->assertEquals('Tahsin Pagi Kelas 1', $resKls1['current']->nama_kegiatan);
        $this->assertNotNull($resKls1['next']);
        $this->assertEquals('Sarapan Pagi Bersama', $resKls1['next']->nama_kegiatan);

        // Cek untuk santri Kelas 2
        $resKls2 = KegiatanHarian::getCurrentAndNextActivity('2', $seninMorning);
        $this->assertNotNull($resKls2['current']);
        $this->assertEquals('Tahfidz Mandiri Kelas 2', $resKls2['current']->nama_kegiatan);
        $this->assertNotNull($resKls2['next']);
        $this->assertEquals('Sarapan Pagi Bersama', $resKls2['next']->nama_kegiatan);
    }

    /**
     * Test Admin can store and update kegiatan with hari and kelas.
     */
    public function test_admin_can_store_and_update_kegiatan_with_hari_and_kelas()
    {
        // 1. Store
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.kegiatan.store'), [
            'nama_kegiatan' => 'Kajian Fiqih Santri Kelas 3',
            'waktu_mulai' => '16:00',
            'waktu_selesai' => '17:30',
            'hari' => 'kamis',
            'kelas' => '3',
            'is_rutin_24jam' => 1,
            'deskripsi' => 'Kitab Safinatun Najah',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('kegiatan_harians', [
            'nama_kegiatan' => 'Kajian Fiqih Santri Kelas 3',
            'hari' => 'kamis',
            'kelas' => '3',
        ]);

        $created = KegiatanHarian::where('nama_kegiatan', 'Kajian Fiqih Santri Kelas 3')->first();

        // 2. Update
        $updateResp = $this->actingAs($this->admin, 'admin')->put(route('admin.kegiatan.update', $created->id), [
            'nama_kegiatan' => 'Kajian Hadits Santri Kelas 3',
            'waktu_mulai' => '16:15',
            'waktu_selesai' => '17:45',
            'hari' => 'jumat',
            'kelas' => '3',
            'is_rutin_24jam' => 1,
            'deskripsi' => 'Kitab Arbain Nawawi',
        ]);

        $updateResp->assertSessionHasNoErrors();
        $this->assertDatabaseHas('kegiatan_harians', [
            'id' => $created->id,
            'nama_kegiatan' => 'Kajian Hadits Santri Kelas 3',
            'hari' => 'jumat',
            'kelas' => '3',
        ]);

        // 3. Store Agenda Khusus (tanggal 25 Oktober 2026 adalah hari Minggu)
        $agendaResp = $this->actingAs($this->admin, 'admin')->post(route('admin.kegiatan.store'), [
            'nama_kegiatan' => 'Outbound & Rihlah Santri',
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '15:00',
            'kelas' => 'semua',
            'is_rutin_24jam' => 0,
            'tanggal_khusus' => '2026-10-25',
            'deskripsi' => 'Rihlah ke Coban Rondo',
        ]);

        $agendaResp->assertSessionHasNoErrors();
        $createdAgenda = KegiatanHarian::where('nama_kegiatan', 'Outbound & Rihlah Santri')->first();
        $this->assertNotNull($createdAgenda);
        $this->assertEquals('minggu', $createdAgenda->hari);
        $this->assertFalse($createdAgenda->is_rutin_24jam);
    }

    /**
     * Test User / Wali can view Kegiatan page with day filter.
     */
    public function test_user_can_view_kegiatan_page_with_day_filter()
    {
        $response = $this->actingAs($this->user, 'web')->get(route('user.kegiatan', ['hari' => 'senin']));
        $response->assertStatus(200);
        $response->assertSee('Rutinitas • Senin');
    }

    /**
     * Test User can view Kegiatan page with santri class filtering.
     */
    public function test_user_can_view_kegiatan_page_with_santri_class_filter()
    {
        Santri::create([
            'user_id' => $this->user->id,
            'nama_lengkap' => 'Ahmad Fathan',
            'nis' => 'NIS2026999',
            'status' => 'diterima',
            'kelas' => '1',
        ]);

        KegiatanHarian::create([
            'nama_kegiatan' => 'Sholat Berjamaah & Dzikir',
            'waktu_mulai' => '00:00:00',
            'waktu_selesai' => '23:59:59',
            'hari' => 'semua',
            'kelas' => 'semua',
            'is_rutin_24jam' => true,
        ]);

        $response = $this->actingAs($this->user, 'web')->get(route('user.kegiatan'));
        $response->assertStatus(200);
        $response->assertSee('Jadwal Kegiatan Santri 24 Jam');
        $response->assertSee('Ahmad Fathan');
        $response->assertSee('Sholat Berjamaah & Dzikir');
    }
}
