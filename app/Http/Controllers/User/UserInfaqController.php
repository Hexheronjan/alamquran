<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\InfaqProgram;
use App\Models\InfaqTransaksi;
use App\Models\Santri;
use App\Models\UangSaku;
use App\Models\UangSakuSetting;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserInfaqController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Active programs
        $programs = InfaqProgram::where('is_active', true)
            ->withCount('transaksis')
            ->withSum(['transaksis as total_terkumpul' => function ($q) {
                $q->where('status', 'berhasil');
            }], 'nominal')
            ->latest()
            ->get();

        // User's santri (for potong saldo option)
        $santris = Santri::where('user_id', $user->id)
            ->where('status', 'diterima')
            ->get();

        // User's donation history
        $myDonations = InfaqTransaksi::with('program')
            ->where('user_id', $user->id)
            ->orWhere('no_wa_donatur', $user->no_hp ?? $user->no_wa ?? '')
            ->latest()
            ->take(20)
            ->get();

        $rekening = UangSakuSetting::first();

        return view('user.infaq.index', compact('programs', 'santris', 'myDonations', 'rekening'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'infaq_program_id'  => 'required|exists:infaq_programs,id',
            'nominal'           => 'required|numeric|min:1000',
            'metode_pembayaran' => 'required|in:potong_saldo,transfer',
            'santri_id'         => 'required_if:metode_pembayaran,potong_saldo|nullable|exists:santri,id',
            'doa_harapan'       => 'nullable|string|max:500',
            'bukti_transfer'    => 'required_if:metode_pembayaran,transfer|nullable|file|image|max:5120',
        ]);

        $program = InfaqProgram::findOrFail($request->infaq_program_id);

        if (!$program->is_active) {
            return back()->with('error', 'Program donasi ini sedang tidak aktif.');
        }

        DB::beginTransaction();
        try {
            $buktiPath = null;
            $status = 'menunggu_konfirmasi';

            if ($request->metode_pembayaran === 'potong_saldo') {
                $santri = Santri::where('id', $request->santri_id)
                    ->where('user_id', $user->id)
                    ->firstOrFail();

                $saldoSaatIni = $santri->saldo_uang_saku ?? 0;

                if ($saldoSaatIni < $request->nominal) {
                    return back()->with('error', 'Saldo uang saku santri (' . $santri->nama_lengkap . ') tidak mencukupi. Saldo saat ini: Rp ' . number_format($saldoSaatIni, 0, ',', '.'));
                }

                $saldoBaru = $saldoSaatIni - $request->nominal;

                // Catat mutasi uang saku keluar
                UangSaku::create([
                    'santri_id'   => $santri->id,
                    'jenis'       => 'keluar',
                    'jumlah'      => $request->nominal,
                    'saldo_akhir' => $saldoBaru,
                    'keterangan'  => 'Donasi Infaq: ' . $program->nama_program,
                    'kelas'       => $santri->kelas,
                    'tahun_ajaran'=> $santri->tahun_ajaran ?? (date('Y') . '/' . (date('Y') + 1)),
                ]);

                $status = 'berhasil';
            } else {
                if ($request->hasFile('bukti_transfer')) {
                    $buktiPath = $request->file('bukti_transfer')->store('infaq-bukti', 'public');
                }
            }

            $transaksi = InfaqTransaksi::create([
                'infaq_program_id'  => $program->id,
                'user_id'           => $user->id,
                'santri_id'         => $request->santri_id ?? null,
                'nama_donatur'      => $user->name,
                'no_wa_donatur'     => $user->no_hp ?? $user->no_wa ?? '',
                'nominal'           => $request->nominal,
                'metode_pembayaran' => $request->metode_pembayaran,
                'bukti_transfer'    => $buktiPath,
                'status'            => $status,
                'doa_harapan'       => $request->doa_harapan,
            ]);

            if ($status === 'berhasil') {
                $program->increment('dana_terkumpul', $request->nominal);
            }

            DB::commit();

            if ($status === 'berhasil') {
                NotificationService::sendInfaq($transaksi->fresh('program'));
            }

            $msg = ($status === 'berhasil')
                ? 'Jazakumullah khairan! Donasi Anda sebesar Rp ' . number_format($request->nominal, 0, ',', '.') . ' berhasil disalurkan melalui potong saldo.'
                : 'Terima kasih! Donasi Anda telah kami terima dan sedang menunggu konfirmasi admin.';

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses donasi: ' . $e->getMessage());
        }
    }
}
