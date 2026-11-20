<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\JadwalTelepon;
use App\Models\Santri;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class JadwalTeleponFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Santri $santri;
    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        School::create([
            'name'       => 'Pesantren Tahfizh Alam Quran',
            'address'    => 'Jl. Pesantren No. 1 Ponorogo',
            'phone'      => '081234567890',
            'email'      => 'info@alamquran.com',
            'is_active'  => true,
            'hero_title' => 'PPDB Online',
        ]);

        $this->user = User::factory()->create([
            'must_change_password' => false,
            'name'                 => 'Wali Santri Ahmad',
            'no_wa'                => '082338689711',
        ]);

        $this->santri = Santri::create([
            'user_id'         => $this->user->id,
            'nama_lengkap'    => 'Ahmad Fulan',
            'nis'             => 'NIS2026001',
            'kelas'           => '1',
            'status'          => 'diterima',
            'saldo_uang_saku' => 0,
            'nominal_spp'     => 500000,
        ]);

        $this->admin = Admin::create([
            'name'        => 'Super Admin',
            'email'       => 'admin@test.com',
            'password'    => bcrypt('password'),
            'role'        => Admin::ROLE_SUPER_ADMIN,
            'permissions' => ['all'],
            'is_active'   => true,
        ]);
    }

    public function test_admin_can_access_jadwal_telepon_index()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.telepon.index'));
        $response->assertStatus(200);
        $response->assertSee('Jadwal Sambungan Telepon Santri');
    }

    public function test_admin_can_create_single_jadwal_telepon()
    {
        $payload = [
            'santri_id'         => $this->santri->id,
            'tanggal_telepon'   => now()->toDateString(),
            'jam_mulai'         => '16:00',
            'jam_selesai'       => '16:15',
            'durasi_menit'      => 15,
            'ustadz_pendamping' => 'Ustadz Ahmad',
            'no_hp_posko'       => '081234567890',
            'topik'             => 'Kabar Pekanan Santri',
            'keterangan'        => 'Mohon stand by ponsel',
        ];

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.telepon.store'), $payload);
        $response->assertRedirect(route('admin.telepon.index'));

        $this->assertDatabaseHas('jadwal_telepons', [
            'santri_id'         => $this->santri->id,
            'ustadz_pendamping' => 'Ustadz Ahmad',
            'status'            => 'menunggu',
        ]);
    }

    public function test_admin_can_update_status_and_catatan()
    {
        $jadwal = JadwalTelepon::create([
            'santri_id'         => $this->santri->id,
            'tanggal_telepon'   => now()->toDateString(),
            'jam_mulai'         => '17:00',
            'jam_selesai'       => '17:15',
            'durasi_menit'      => 15,
            'ustadz_pendamping' => 'Ustadz Hasan',
            'no_hp_posko'       => '081234567890',
            'status'            => 'menunggu',
        ]);

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.telepon.update_status', $jadwal->id), [
            'status'            => 'terhubung',
            'catatan_panggilan' => 'Alhamdulillah santri bicara lancar dengan orang tua.',
        ]);

        $response->assertStatus(302);
        $jadwal->refresh();
        $this->assertEquals('terhubung', $jadwal->status);
        $this->assertNotNull($jadwal->waktu_dihubungi);
        $this->assertEquals('Alhamdulillah santri bicara lancar dengan orang tua.', $jadwal->catatan_panggilan);
    }

    public function test_user_can_access_jadwal_telepon_index()
    {
        $response = $this->actingAs($this->user)->get(route('user.telepon.index'));
        $response->assertStatus(200);
        $response->assertSee('Jadwal Sambungan Telepon');
    }

    public function test_admin_can_store_bulk_per_kelas()
    {
        $payload = [
            'kelas'             => '1',
            'tanggal_telepon'   => now()->toDateString(),
            'jam_mulai'         => '08:00',
            'durasi_menit'      => 15,
            'ustadz_pendamping' => 'Ustadz Zaid',
            'no_hp_posko'       => '081234567899',
            'topik'             => 'Evaluasi Bulanan Santri',
        ];

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.telepon.store_bulk'), $payload);
        $response->assertRedirect(route('admin.telepon.index'));

        $this->assertDatabaseHas('jadwal_telepons', [
            'santri_id'         => $this->santri->id,
            'ustadz_pendamping' => 'Ustadz Zaid',
            'jam_mulai'         => '08:00',
            'jam_selesai'       => '08:15',
        ]);
    }

    public function test_admin_can_update_jadwal_and_resets_reminder_flag()
    {
        $jadwal = JadwalTelepon::create([
            'santri_id'           => $this->santri->id,
            'tanggal_telepon'     => now()->toDateString(),
            'jam_mulai'           => '10:00',
            'jam_selesai'         => '10:15',
            'durasi_menit'        => 15,
            'ustadz_pendamping'   => 'Ustadz Ahmad',
            'no_hp_posko'         => '081234567890',
            'status'              => 'menunggu',
            'wa_reminder_sent'    => true,
            'wa_reminder_sent_at' => now(),
            'wa_reminder_status'  => 'berhasil',
        ]);

        $payload = [
            'santri_id'         => $this->santri->id,
            'tanggal_telepon'   => now()->toDateString(),
            'jam_mulai'         => '11:00', // Waktu diubah
            'jam_selesai'       => '11:15',
            'durasi_menit'      => 15,
            'ustadz_pendamping' => 'Ustadz Ahmad Baru',
            'no_hp_posko'       => '081234567890',
        ];

        $response = $this->actingAs($this->admin, 'admin')->put(route('admin.telepon.update', $jadwal->id), $payload);
        $response->assertRedirect(route('admin.telepon.index'));

        $jadwal->refresh();
        $this->assertEquals('11:00', substr($jadwal->jam_mulai, 0, 5));
        $this->assertEquals('Ustadz Ahmad Baru', $jadwal->ustadz_pendamping);
        // Memastikan flag reminder direset menjadi false agar jadwal baru bisa dikirimi WA pengingat
        $this->assertFalse($jadwal->wa_reminder_sent);
        $this->assertNull($jadwal->wa_reminder_sent_at);
    }

    public function test_admin_can_delete_jadwal_telepon()
    {
        $jadwal = JadwalTelepon::create([
            'santri_id'         => $this->santri->id,
            'tanggal_telepon'   => now()->toDateString(),
            'jam_mulai'         => '15:00',
            'jam_selesai'       => '15:15',
            'durasi_menit'      => 15,
            'ustadz_pendamping' => 'Ustadz Ahmad',
            'no_hp_posko'       => '081234567890',
            'status'            => 'menunggu',
        ]);

        $response = $this->actingAs($this->admin, 'admin')->delete(route('admin.telepon.destroy', $jadwal->id));
        $response->assertStatus(302);

        $this->assertDatabaseMissing('jadwal_telepons', [
            'id' => $jadwal->id,
        ]);
    }

    public function test_command_send_reminder_executes_cleanly()
    {
        $exitCode = Artisan::call('telepon:send-reminder');
        $this->assertEquals(0, $exitCode);
    }
}
