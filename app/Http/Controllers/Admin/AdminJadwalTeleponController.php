<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalTelepon;
use App\Models\Santri;
use App\Services\AdminActivityLogger;
use App\Services\JadwalTeleponService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminJadwalTeleponController extends Controller
{
    public function index(Request $request)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('jadwal_telepons')) {
            return back()->with('error', 'Tabel jadwal_telepons belum dimigrasi. Silakan jalankan migrasi database terlebih dahulu.');
        }

        $query = JadwalTelepon::with(['santri.orangTua', 'santri.user', 'admin']);

        // Filter Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter Kelas
        if ($request->filled('kelas') && $request->kelas !== 'all') {
            $query->whereHas('santri', function ($q) use ($request) {
                $q->where('kelas', $request->kelas);
            });
        }

        // Filter Tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_telepon', $request->tanggal);
        }

        // Search (Santri, Ustadz, No Posko, Topik)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('ustadz_pendamping', 'like', "%{$search}%")
                    ->orWhere('no_hp_posko', 'like', "%{$search}%")
                    ->orWhere('topik', 'like', "%{$search}%")
                    ->orWhereHas('santri', function ($sq) use ($search) {
                        $sq->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nis', 'like', "%{$search}%");
                    });
            });
        }

        $jadwals = $query->orderBy('tanggal_telepon', 'desc')
            ->orderBy('jam_mulai', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Statistik
        $totalJadwal        = JadwalTelepon::count();
        $totalMenunggu      = JadwalTelepon::where('status', JadwalTelepon::STATUS_MENUNGGU)->count();
        $totalTerhubung     = JadwalTelepon::where('status', JadwalTelepon::STATUS_TERHUBUNG)->count();
        $totalTidakTerangkat = JadwalTelepon::where('status', JadwalTelepon::STATUS_TIDAK_TERANGKAT)->count();
        $totalJadwalUlang   = JadwalTelepon::where('status', JadwalTelepon::STATUS_DIJADWALKAN_ULANG)->count();
        $totalHariIni       = JadwalTelepon::whereDate('tanggal_telepon', now()->toDateString())->count();

        // Daftar Santri Diterima untuk pilihan dropdown
        $santris = Santri::where('status', 'diterima')
            ->orderBy('kelas', 'asc')
            ->orderBy('nama_lengkap', 'asc')
            ->get(['id', 'nama_lengkap', 'kelas', 'nis']);

        // Daftar Kelas Unik
        $kelasList = Santri::where('status', 'diterima')
            ->whereNotNull('kelas')
            ->distinct()
            ->orderBy('kelas', 'asc')
            ->pluck('kelas');

        $statusList = JadwalTelepon::getStatusList();

        return view('admin.telepon.index', compact(
            'jadwals',
            'santris',
            'kelasList',
            'statusList',
            'totalJadwal',
            'totalMenunggu',
            'totalTerhubung',
            'totalTidakTerangkat',
            'totalJadwalUlang',
            'totalHariIni'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'santri_id'          => 'required|exists:santri,id',
            'tanggal_telepon'    => 'required|date',
            'jam_mulai'          => 'required|string',
            'jam_selesai'        => 'required|string',
            'durasi_menit'       => 'nullable|integer|min:5|max:120',
            'ustadz_pendamping'  => 'required|string|max:100',
            'no_hp_posko'        => 'required|string|max:30',
            'topik'              => 'nullable|string|max:150',
            'keterangan'         => 'nullable|string|max:500',
            'kirim_wa_sekarang'  => 'nullable|boolean',
        ], [
            'santri_id.required'         => 'Pilih santri terlebih dahulu.',
            'tanggal_telepon.required'   => 'Tanggal jadwal telepon wajib diisi.',
            'jam_mulai.required'         => 'Jam mulai telepon wajib diisi.',
            'jam_selesai.required'       => 'Jam selesai telepon wajib diisi.',
            'ustadz_pendamping.required' => 'Nama Ustadz pendamping wajib diisi.',
            'no_hp_posko.required'       => 'Nomor HP penelepon/posko wajib diisi.',
        ]);

        $durasi = 15;
        if (!empty($validated['durasi_menit'])) {
            $durasi = (int) $validated['durasi_menit'];
        } elseif (!empty($validated['jam_mulai']) && !empty($validated['jam_selesai'])) {
            $mulai = Carbon::parse($validated['jam_mulai']);
            $selesai = Carbon::parse($validated['jam_selesai']);
            if ($selesai->greaterThan($mulai)) {
                $durasi = $mulai->diffInMinutes($selesai);
            }
        }

        $validated['durasi_menit'] = $durasi;
        $validated['status'] = JadwalTelepon::STATUS_MENUNGGU;
        $validated['created_by_admin_id'] = auth('admin')->id();

        $jadwal = JadwalTelepon::create($validated);

        AdminActivityLogger::log(
            'create_jadwal_telepon',
            "Menambahkan jadwal telepon ananda '{$jadwal->santri?->nama_lengkap}' pada {$jadwal->formatted_tanggal} ({$jadwal->formatted_jam}) bersama {$jadwal->ustadz_pendamping}",
            'JadwalTelepon',
            $jadwal->id
        );

        $waMessage = '';
        if ($request->boolean('kirim_wa_sekarang')) {
            $result = JadwalTeleponService::sendReminder($jadwal, true);
            $waMessage = $result['success']
                ? ' Notifikasi WhatsApp pengingat berhasil dikirim ke orang tua.'
                : ' Namun pengingat WA belum terkirim: ' . $result['message'];
        }

        return redirect()->route('admin.telepon.index')
            ->with('success', "Alhamdulillah! Jadwal telepon untuk ananda {$jadwal->santri?->nama_lengkap} berhasil disimpan.{$waMessage}");
    }

    public function storeBulk(Request $request)
    {
        $validated = $request->validate([
            'kelas'              => 'required|string',
            'tanggal_telepon'    => 'required|date',
            'jam_mulai'          => 'required|string',
            'durasi_menit'       => 'required|integer|min:5|max:60',
            'ustadz_pendamping'  => 'required|string|max:100',
            'no_hp_posko'        => 'required|string|max:30',
            'topik'              => 'nullable|string|max:150',
            'keterangan'         => 'nullable|string|max:500',
        ], [
            'kelas.required'             => 'Pilih kelas santri.',
            'tanggal_telepon.required'   => 'Tanggal telepon wajib ditentukan.',
            'jam_mulai.required'         => 'Jam mulai sesi telepon wajib diisi.',
            'durasi_menit.required'      => 'Durasi waktu per santri wajib ditentukan.',
            'ustadz_pendamping.required' => 'Nama Ustadz pendamping wajib diisi.',
            'no_hp_posko.required'       => 'Nomor HP penelepon/posko wajib diisi.',
        ]);

        $validated['admin_id'] = auth('admin')->id();

        $result = JadwalTeleponService::generateBulkPerKelas($validated);

        if (!$result['success']) {
            return back()->withInput()->with('error', $result['message']);
        }

        AdminActivityLogger::log(
            'generate_bulk_jadwal_telepon',
            "Membuat {$result['count']} jadwal telepon massal untuk seluruh santri Kelas {$validated['kelas']} pada {$validated['tanggal_telepon']}",
            'JadwalTelepon'
        );

        return redirect()->route('admin.telepon.index')
            ->with('success', $result['message']);
    }

    public function update(Request $request, JadwalTelepon $jadwal)
    {
        $validated = $request->validate([
            'santri_id'         => 'required|exists:santri,id',
            'tanggal_telepon'   => 'required|date',
            'jam_mulai'         => 'required|string',
            'jam_selesai'       => 'required|string',
            'durasi_menit'      => 'nullable|integer|min:5|max:120',
            'ustadz_pendamping' => 'required|string|max:100',
            'no_hp_posko'       => 'required|string|max:30',
            'topik'             => 'nullable|string|max:150',
            'keterangan'        => 'nullable|string|max:500',
        ]);

        $durasi = 15;
        if (!empty($validated['durasi_menit'])) {
            $durasi = (int) $validated['durasi_menit'];
        } elseif (!empty($validated['jam_mulai']) && !empty($validated['jam_selesai'])) {
            $mulai = Carbon::parse($validated['jam_mulai']);
            $selesai = Carbon::parse($validated['jam_selesai']);
            if ($selesai->greaterThan($mulai)) {
                $durasi = $mulai->diffInMinutes($selesai);
            }
        }
        $validated['durasi_menit'] = $durasi;

        // Jika waktu atau tanggal diubah, reset status reminder agar sistem otomatis mengirimkan pengingat untuk jadwal baru ini
        $waktuBerubah = ($jadwal->getRawOriginal('tanggal_telepon') !== $validated['tanggal_telepon'])
            || ($jadwal->jam_mulai !== $validated['jam_mulai']);

        if ($waktuBerubah || in_array($jadwal->status, [JadwalTelepon::STATUS_MENUNGGU, JadwalTelepon::STATUS_DIJADWALKAN_ULANG])) {
            $validated['wa_reminder_sent'] = false;
            $validated['wa_reminder_sent_at'] = null;
            $validated['wa_reminder_status'] = null;
            $validated['status'] = JadwalTelepon::STATUS_MENUNGGU;
        }

        $jadwal->update($validated);

        AdminActivityLogger::log(
            'update_jadwal_telepon',
            "Memperbarui data jadwal telepon ananda '{$jadwal->santri?->nama_lengkap}' (#{$jadwal->id})",
            'JadwalTelepon',
            $jadwal->id
        );

        return redirect()->route('admin.telepon.index')
            ->with('success', "Jadwal telepon ananda {$jadwal->santri?->nama_lengkap} berhasil diperbarui.");
    }

    public function updateStatus(Request $request, JadwalTelepon $jadwal)
    {
        $validated = $request->validate([
            'status'            => 'required|in:menunggu,terhubung,tidak_terangkat,dijadwalkan_ulang,dibatalkan',
            'catatan_panggilan' => 'nullable|string|max:1000',
        ]);

        $updateData = [
            'status'            => $validated['status'],
            'catatan_panggilan' => $validated['catatan_panggilan'],
        ];

        if ($validated['status'] === JadwalTelepon::STATUS_TERHUBUNG) {
            if (!$jadwal->waktu_dihubungi) {
                $updateData['waktu_dihubungi'] = now();
            }
        } elseif (in_array($validated['status'], [JadwalTelepon::STATUS_MENUNGGU, JadwalTelepon::STATUS_DIJADWALKAN_ULANG])) {
            $updateData['waktu_dihubungi'] = null;
        }

        $jadwal->update($updateData);

        AdminActivityLogger::log(
            'update_status_telepon',
            "Mengubah status sambungan telepon ananda '{$jadwal->santri?->nama_lengkap}' menjadi '{$jadwal->status_label}'",
            'JadwalTelepon',
            $jadwal->id
        );

        return back()->with('success', "Status sambungan telepon ananda {$jadwal->santri?->nama_lengkap} berhasil dicatat sebagai '{$jadwal->status_label}'.");
    }

    public function sendReminder(JadwalTelepon $jadwal)
    {
        $result = JadwalTeleponService::sendReminder($jadwal, true);

        if ($result['success']) {
            AdminActivityLogger::log(
                'send_wa_reminder_telepon',
                "Mengirim ulang reminder WhatsApp jadwal telepon ananda '{$jadwal->santri?->nama_lengkap}'",
                'JadwalTelepon',
                $jadwal->id
            );

            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function broadcastToday()
    {
        $result = JadwalTeleponService::broadcastHariIni();

        if ($result['total'] === 0) {
            return back()->with('info', 'Tidak ada jadwal telepon hari ini yang belum dikirimi pengingat.');
        }

        AdminActivityLogger::log(
            'broadcast_today_telepon',
            "Melakukan broadcast pengingat jadwal telepon hari ini: {$result['success']} berhasil, {$result['failed']} gagal dari total {$result['total']} jadwal.",
            'JadwalTelepon'
        );

        return back()->with('success', "Broadcast pengingat selesai. Berhasil: {$result['success']}, Gagal/Pending: {$result['failed']} dari {$result['total']} jadwal hari ini.");
    }

    public function destroy(JadwalTelepon $jadwal)
    {
        $namaSantri = $jadwal->santri?->nama_lengkap ?? 'Santri';
        $jadwal->delete();

        AdminActivityLogger::log(
            'delete_jadwal_telepon',
            "Menghapus jadwal telepon ananda '{$namaSantri}'",
            'JadwalTelepon'
        );

        return back()->with('success', "Jadwal telepon ananda {$namaSantri} berhasil dihapus.");
    }
}
