<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InfaqProgram;
use App\Models\InfaqTransaksi;
use App\Models\User;
use App\Services\AdminActivityLogger;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InfaqController extends Controller
{
    public function index(Request $request)
    {
        $programs = InfaqProgram::withCount('transaksis')
            ->withSum(['transaksis as total_terkumpul' => function ($q) {
                $q->where('status', 'berhasil');
            }], 'nominal')
            ->latest()
            ->get();

        $transaksiTerbaru = InfaqTransaksi::with(['program', 'user', 'santri', 'verifiedBy'])
            ->latest()
            ->paginate(15);

        $totalTerkumpul = InfaqTransaksi::where('status', 'berhasil')->sum('nominal');
        $totalMenunggu  = InfaqTransaksi::where('status', 'menunggu_konfirmasi')->count();

        return view('admin.infaq.index', compact('programs', 'transaksiTerbaru', 'totalTerkumpul', 'totalMenunggu'));
    }

    // =================== PROGRAM ===================
    public function storeProgram(Request $request)
    {
        $request->validate([
            'nama_program' => 'required|string|max:200',
            'deskripsi'    => 'required|string',
            'target_dana'  => 'required|numeric|min:0',
            'foto_banner'  => 'nullable|image|max:5120',
        ]);

        $data = $request->only(['nama_program', 'deskripsi', 'target_dana']);
        if ($request->hasFile('foto_banner')) {
            $data['foto_banner'] = $request->file('foto_banner')->store('infaq-banner', 'public');
        }

        $program = InfaqProgram::create($data);
        AdminActivityLogger::log('buat_program_infaq', "Buat program infaq: {$program->nama_program} target Rp " . number_format($program->target_dana));

        return back()->with('success', "Program infaq '{$program->nama_program}' berhasil dibuat.");
    }

    public function updateProgram(Request $request, $id)
    {
        $program = InfaqProgram::findOrFail($id);

        $request->validate([
            'nama_program' => 'required|string|max:200',
            'deskripsi'    => 'nullable|string',
            'target_dana'  => 'required|numeric|min:0',
            'foto_banner'  => 'nullable|image|max:5120',
        ]);

        $data = $request->only(['nama_program', 'deskripsi', 'target_dana']);
        if ($request->hasFile('foto_banner')) {
            if ($program->foto_banner) {
                Storage::disk('public')->delete($program->foto_banner);
            }
            $data['foto_banner'] = $request->file('foto_banner')->store('infaq-banner', 'public');
        }

        $program->update($data);
        AdminActivityLogger::log('edit_program_infaq', "Edit program infaq: {$program->nama_program}");

        return back()->with('success', "Program infaq '{$program->nama_program}' berhasil diperbarui.");
    }

    public function toggleProgram($id)
    {
        $program = InfaqProgram::findOrFail($id);
        $program->update(['is_active' => !$program->is_active]);
        $status = $program->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Program '{$program->nama_program}' berhasil {$status}.");
    }

    public function destroyProgram($id)
    {
        $program = InfaqProgram::findOrFail($id);
        if ($program->foto_banner) {
            Storage::disk('public')->delete($program->foto_banner);
        }
        AdminActivityLogger::log('hapus_program_infaq', "Hapus program infaq: {$program->nama_program}");
        $program->delete();
        return back()->with('success', 'Program infaq berhasil dihapus.');
    }

    // =================== TRANSAKSI ===================
    public function storeTransaksi(Request $request)
    {
        $request->validate([
            'infaq_program_id'  => 'required|exists:infaq_programs,id',
            'nama_donatur'      => 'required|string|max:200',
            'no_wa_donatur'     => 'nullable|string',
            'nominal'           => 'required|numeric|min:1000',
            'metode_pembayaran' => 'required|in:potong_saldo,transfer',
            'doa_harapan'       => 'nullable|string',
            'bukti_transfer'    => 'nullable|file|image|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $status = 'menunggu_konfirmasi';
            $buktiPath = null;

            if ($request->hasFile('bukti_transfer')) {
                $buktiPath = $request->file('bukti_transfer')->store('infaq-bukti', 'public');
            }

            // Jika potong saldo langsung konfirmasi
            if ($request->metode_pembayaran === 'potong_saldo') {
                $status = 'berhasil';
            }

            $transaksi = InfaqTransaksi::create([
                'infaq_program_id'  => $request->infaq_program_id,
                'user_id'           => $request->user_id ?? null,
                'santri_id'         => $request->santri_id ?? null,
                'nama_donatur'      => $request->nama_donatur,
                'no_wa_donatur'     => $request->no_wa_donatur,
                'nominal'           => $request->nominal,
                'metode_pembayaran' => $request->metode_pembayaran,
                'bukti_transfer'    => $buktiPath,
                'status'            => $status,
                'doa_harapan'       => $request->doa_harapan,
                'verified_by_id'    => ($status === 'berhasil') ? auth('admin')->id() : null,
            ]);

            if ($status === 'berhasil') {
                InfaqProgram::find($request->infaq_program_id)->increment('dana_terkumpul', $request->nominal);
            }

            DB::commit();
            AdminActivityLogger::log('tambah_infaq', "Donasi {$request->nama_donatur} Rp " . number_format($request->nominal));
            return back()->with('success', "Donasi dari {$request->nama_donatur} berhasil dicatat.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    public function konfirmasiTransaksi(Request $request, $id)
    {
        $transaksi = InfaqTransaksi::findOrFail($id);

        if ($transaksi->status !== 'menunggu_konfirmasi') {
            return back()->with('error', 'Transaksi ini sudah dikonfirmasi sebelumnya.');
        }

        DB::beginTransaction();
        try {
            $transaksi->update([
                'status'         => 'berhasil',
                'verified_by_id' => auth('admin')->id(),
            ]);

            InfaqProgram::find($transaksi->infaq_program_id)->increment('dana_terkumpul', $transaksi->nominal);

            // Kirim notif WA jika ada no WA donatur
            if ($transaksi->no_wa_donatur) {
                try {
                    NotificationService::send([
                        'recipient_number' => $transaksi->no_wa_donatur,
                        'recipient_name' => $transaksi->nama_donatur,
                        'user_id' => $transaksi->user_id,
                        'modul' => 'Infaq',
                        'reference_id' => 'INFAQ-KONFIRMASI-' . $transaksi->id,
                        'message' =>
                        "🤲 *Konfirmasi Donasi Diterima*\n\n" .
                        "Jazakumullah khairan, {$transaksi->nama_donatur}.\n" .
                        "Donasi Anda sebesar *Rp " . number_format($transaksi->nominal, 0, ',', '.') . "* untuk program *{$transaksi->program->nama_program}* telah kami terima.\n\n" .
                        "Semoga menjadi amal jariyah yang berkah. Aamiin.\n\n" .
                        "_Pesantren Al-Qur'an_",
                    ]);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Gagal kirim notif WA infaq: ' . $e->getMessage());
                }
            }

            DB::commit();
            AdminActivityLogger::log('konfirmasi_infaq', "Konfirmasi donasi {$transaksi->nama_donatur} Rp " . number_format($transaksi->nominal));
            return back()->with('success', "Donasi Rp " . number_format($transaksi->nominal, 0, ',', '.') . " berhasil dikonfirmasi.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal konfirmasi: ' . $e->getMessage());
        }
    }

    public function tolakTransaksi($id)
    {
        $transaksi = InfaqTransaksi::with('program')->findOrFail($id);
        $transaksi->update(['status' => 'ditolak', 'verified_by_id' => auth('admin')->id()]);

        if ($transaksi->no_wa_donatur) {
            try {
                \App\Services\NotificationService::send([
                    'recipient_number' => $transaksi->no_wa_donatur,
                    'recipient_name'   => $transaksi->nama_donatur,
                    'user_id'          => $transaksi->user_id,
                    'modul'            => 'Infaq',
                    'priority'         => 'normal',
                    'reference_id'     => 'INFAQ-TOLAK-' . $transaksi->id,
                    'message'          => "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\n" .
                        "Yth. Bapak/Ibu *{$transaksi->nama_donatur}*,\n\n" .
                        "Mohon maaf, bukti konfirmasi donasi infaq sebesar *Rp " . number_format($transaksi->nominal, 0, ',', '.') . "* untuk program *{$transaksi->program?->nama_program}* belum dapat diverifikasi.\n\n" .
                        "Silakan hubungi pengurus pondok untuk informasi lebih lanjut. Terima kasih.\n\n" .
                        "_Pesantren Tahfizh Alam Qur'an_",
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal kirim notif tolak infaq: ' . $e->getMessage());
            }
        }

        AdminActivityLogger::log('tolak_infaq', "Tolak donasi ID {$id}");
        return back()->with('success', 'Transaksi infaq berhasil ditolak.');
    }
}
