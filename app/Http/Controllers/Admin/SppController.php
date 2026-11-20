<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\SantriAddonTemplate;
use App\Models\SppPembayaran;
use App\Models\SppSetting;
use App\Models\SppTagihan;
use App\Models\SppTagihanAddon;
use App\Services\AdminActivityLogger;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SppController extends Controller
{
    public function index(Request $request)
    {
        $bulan = (int) $request->get('bulan', date('n'));
        $tahun = (int) $request->get('tahun', date('Y'));
        $selectedKelas = $request->get('kelas', 'all');
        $selectedStatus = $request->get('status', 'all');

        $query = SppTagihan::with(['santri.orangTua', 'pembayarans.verifiedBy', 'addons'])
            ->whereHas('santri')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun);

        if ($selectedKelas !== 'all') {
            $query->whereHas('santri', function ($q) use ($selectedKelas) {
                $q->where('kelas', $selectedKelas);
            });
        }

        if ($selectedStatus !== 'all') {
            $query->where('status', $selectedStatus);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('santri', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        // Summary metrics
        $totalTagihan = (clone $query)->sum('nominal_tagihan');
        $totalTerbayar = (clone $query)->sum('nominal_terbayar');
        $totalSisa = (clone $query)->sum('sisa_tagihan');
        $countLunas = (clone $query)->where('status', 'lunas')->count();
        $countCicilan = (clone $query)->where('status', 'cicilan')->count();
        $countBelumLunas = (clone $query)->where('status', 'belum_lunas')->count();

        $tagihans = $query->orderByDesc('sisa_tagihan')->paginate(25, ['*'], 'tagihan_page')->withQueryString();

        $sppSetting = SppSetting::getActive();
        $pendingPembayarans = SppPembayaran::with(['tagihan.santri', 'santri', 'submittedBy'])
            ->whereHas('santri')
            ->whereHas('tagihan')
            ->where('status', 'menunggu_konfirmasi')
            ->latest()
            ->get();

        // Data untuk Tab Pengaturan Rekening Santri
        $activeTab = $request->get('tab', 'tagihan');
        $santriSearch = trim($request->get('search_santri', ''));
        $santriKelas = $request->get('kelas_santri', 'all');

        $santriQuery = Santri::aktif()->with(['orangTua', 'addonTemplates']);

        if ($santriSearch !== '') {
            $santriQuery->where(function ($q) use ($santriSearch) {
                $q->where('nama_lengkap', 'like', "%{$santriSearch}%")
                  ->orWhere('nis', 'like', "%{$santriSearch}%")
                  ->orWhere('nama_bank_spp', 'like', "%{$santriSearch}%")
                  ->orWhere('nomor_rekening_spp', 'like', "%{$santriSearch}%")
                  ->orWhere('atas_nama_rekening_spp', 'like', "%{$santriSearch}%");
            });
        }

        if ($santriKelas !== 'all') {
            $santriQuery->where('kelas', $santriKelas);
        }

        $santris = $santriQuery->orderBy('kelas')->orderBy('nama_lengkap')->paginate(25, ['*'], 'santri_page')->withQueryString();
        $totalSantriAktif = Santri::aktif()->count();
        $countSantriRekeningKhusus = Santri::aktif()->whereNotNull('nomor_rekening_spp')->where('nomor_rekening_spp', '!=', '')->count();
        $allSantriAktif = Santri::aktif()->with('orangTua')->orderBy('kelas')->orderBy('nama_lengkap')->get(['id', 'nama_lengkap', 'kelas', 'nominal_spp', 'is_beasiswa', 'tahun_masuk', 'status_santri', 'tahun_lulus', 'orang_tua_id', 'nama_bank_spp', 'nomor_rekening_spp', 'atas_nama_rekening_spp']);

        // Map tagihan periode aktif per santri_id (beserta addons) untuk ditampilkan di Pengaturan Rekening Santri
        $santriTagihanMap = SppTagihan::with('addons')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get()
            ->keyBy('santri_id');

        // Data untuk Tab Baru: Rekap Semua Wali Santri yang Menunggak
        $tunggakanSearch = trim($request->get('search_tunggakan', ''));
        $tunggakanKelas = $request->get('kelas_tunggakan', 'all');
        $tunggakanBulan = $request->get('bulan_tunggakan', 'all');
        $tunggakanTahun = $request->get('tahun_tunggakan', 'all');
        $tunggakanTipe  = $request->get('tipe_tunggakan', 'all');
        $biayaAdminDefault = $sppSetting?->biaya_admin ? (float) $sppSetting->biaya_admin : 2500.0;

        $allUnpaidQueryBuilder = SppTagihan::with(['santri.orangTua', 'santri.user', 'addons'])
            ->where('status', '!=', 'lunas')
            ->where('sisa_tagihan', '>', 0);

        if ($tunggakanBulan !== 'all' && is_numeric($tunggakanBulan)) {
            $allUnpaidQueryBuilder->where('bulan', (int)$tunggakanBulan);
        }
        if ($tunggakanTahun !== 'all' && is_numeric($tunggakanTahun)) {
            $allUnpaidQueryBuilder->where('tahun', (int)$tunggakanTahun);
        }

        $allUnpaidQuery = $allUnpaidQueryBuilder
            ->whereHas('santri', function ($q) use ($tunggakanKelas, $tunggakanSearch) {
                $q->where('status', 'diterima')
                  ->where(function ($sq) {
                      $sq->whereNull('kelas')->orWhere('kelas', '!=', 'alumni');
                  });
                if ($tunggakanKelas !== 'all') {
                    $q->where('kelas', $tunggakanKelas);
                }
                if ($tunggakanSearch !== '') {
                    $q->where(function ($sq) use ($tunggakanSearch) {
                        $sq->where('nama_lengkap', 'like', "%{$tunggakanSearch}%")
                           ->orWhere('nis', 'like', "%{$tunggakanSearch}%")
                           ->orWhereHas('orangTua', function ($oq) use ($tunggakanSearch) {
                               $oq->where('nama_ayah', 'like', "%{$tunggakanSearch}%")
                                  ->orWhere('no_wa_ayah', 'like', "%{$tunggakanSearch}%");
                           });
                    });
                }
            })
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc')
            ->get();

        $rekapTunggakan = $allUnpaidQuery
            ->groupBy('santri_id')
            ->map(function ($items) use ($biayaAdminDefault) {
                $first = $items->first();
                $s = $first->santri;
                $totalPokok = (float) $items->sum('nominal_spp');
                $totalAddon = (float) $items->sum('nominal_addon');
                $totalSisa  = (float) $items->sum('sisa_tagihan');

                return (object) [
                    'santri'              => $s,
                    'nama_wali'           => $s?->orangTua?->nama_ayah ?: ($s?->user?->name ?? '-'),
                    'no_wa_wali'          => $s?->orangTua?->no_wa_ayah ?: ($s?->user?->no_wa ?? '-'),
                    'jumlah_bulan'        => $items->count(),
                    'tagihans'            => $items->values(),
                    'bulan_labels'        => $items->pluck('bulan_label')->implode(', '),
                    'total_pokok'         => $totalPokok,
                    'total_addon'         => $totalAddon,
                    'total_sisa_tagihan'  => $totalSisa,
                    'biaya_admin'         => $biayaAdminDefault,
                    'total_harus_dibayar' => $totalSisa + $biayaAdminDefault,
                ];
            })
            ->sortByDesc('total_sisa_tagihan')
            ->values();

        if ($tunggakanTipe === 'multi') {
            $rekapTunggakan = $rekapTunggakan->filter(fn($item) => $item->jumlah_bulan >= 2)->values();
        } elseif ($tunggakanTipe === 'single') {
            $rekapTunggakan = $rekapTunggakan->filter(fn($item) => $item->jumlah_bulan === 1)->values();
        } elseif ($tunggakanTipe === 'future') {
            $currM = (int) date('n');
            $currY = (int) date('Y');
            $rekapTunggakan = $rekapTunggakan->filter(function ($item) use ($currM, $currY) {
                return $item->tagihans->contains(function ($t) use ($currM, $currY) {
                    return ((int)$t->tahun > $currY) || ((int)$t->tahun === $currY && (int)$t->bulan > $currM);
                });
            })->values();
        }

        $totalWaliMenunggak = $rekapTunggakan->count();
        $totalBulanMenunggak = $rekapTunggakan->sum('jumlah_bulan');
        $grandTotalTunggakan = $rekapTunggakan->sum('total_sisa_tagihan');

        return view('admin.spp.index', compact(
            'tagihans',
            'bulan',
            'tahun',
            'selectedKelas',
            'selectedStatus',
            'totalTagihan',
            'totalTerbayar',
            'totalSisa',
            'countLunas',
            'countCicilan',
            'countBelumLunas',
            'sppSetting',
            'pendingPembayarans',
            'activeTab',
            'santris',
            'santriSearch',
            'santriKelas',
            'totalSantriAktif',
            'countSantriRekeningKhusus',
            'allSantriAktif',
            'santriTagihanMap',
            'rekapTunggakan',
            'totalWaliMenunggak',
            'totalBulanMenunggak',
            'grandTotalTunggakan',
            'tunggakanSearch',
            'tunggakanKelas',
            'tunggakanBulan',
            'tunggakanTahun',
            'tunggakanTipe'
        ));
    }

    public function generateMonthly(Request $request)
    {
        $request->validate([
            'bulan'           => ['required', 'integer', 'between:1,12'],
            'tahun'           => ['required', 'integer', 'between:2020,2099'],
            'nominal_default' => ['required', 'numeric', 'min:1000'],
        ]);

        $bulan = (int) $request->bulan;
        $tahun = (int) $request->tahun;
        $nominalDefault = (float) $request->nominal_default;

        Santri::syncClassesForActiveYear();
        $santris = Santri::aktif()->with('addonTemplates')->get();

        $createdCount = 0;
        $namaBulan = SppTagihan::getNamaBulan($bulan);

        foreach ($santris as $santri) {
            // Syarat mutlak: Lewati santri yang SPP & rekeningnya belum diatur oleh admin
            if (empty($santri->nomor_rekening_spp) && !$santri->is_beasiswa) {
                continue;
            }

            // Prioritas utama: Beasiswa Full (0), atau nominal khusus santri, atau fallback default
            if ($santri->is_beasiswa) {
                $nominalSantri = 0.0;
            } elseif ($santri->nominal_spp !== null && (float) $santri->nominal_spp >= 0) {
                $nominalSantri = (float) $santri->nominal_spp;
            } else {
                $nominalSantri = $nominalDefault;
            }

            $rekeningSantri = $santri->nomor_rekening_spp ?: null;
            $isBeasiswa = ($nominalSantri == 0.0);

            $tagihan = SppTagihan::firstOrCreate(
                [
                    'santri_id' => $santri->id,
                    'bulan'     => $bulan,
                    'tahun'     => $tahun,
                ],
                [
                    'nominal_spp'      => $nominalSantri,
                    'nominal_addon'    => 0,
                    'nominal_tagihan'  => $nominalSantri,
                    'nominal_terbayar' => 0,
                    'sisa_tagihan'     => $nominalSantri,
                    'status'           => $isBeasiswa ? 'lunas' : 'belum_lunas',
                    'nomor_va'         => $rekeningSantri,
                    'keterangan'       => $isBeasiswa ? "SPP {$namaBulan} {$tahun} (Beasiswa Full)" : "SPP {$namaBulan} {$tahun}",
                ]
            );

            if ($tagihan->wasRecentlyCreated) {
                $createdCount++;

                // Otomatis masukkan template add-on santri (e.g. Laundry, Kitab) jika ada
                if ($santri->addonTemplates && $santri->addonTemplates->isNotEmpty()) {
                    foreach ($santri->addonTemplates->where('is_active', true) as $tpl) {
                        $tagihan->addons()->create([
                            'nama_biaya' => $tpl->nama_biaya,
                            'nominal'    => (float) $tpl->nominal,
                            'keterangan' => $tpl->keterangan,
                        ]);
                    }
                    $tagihan->syncTotals();
                }
            }
        }

        AdminActivityLogger::log(
            'generate_spp',
            "Membuat {$createdCount} tagihan SPP {$namaBulan} {$tahun} (Default: Rp " . number_format($nominalDefault, 0, ',', '.') . ")"
        );

        return redirect()->route('admin.spp.index', ['bulan' => $bulan, 'tahun' => $tahun])
            ->with('success', "Berhasil membuat {$createdCount} tagihan SPP untuk santri aktif Kelas 1, 2, dan 3.");
    }

    /**
     * Buat tagihan SPP untuk 1 santri tertentu secara spesifik (input satu per satu).
     * Nominal dan keterangan terisi otomatis 100% dari tarif santri/kelas yang berlaku.
     */
    public function storeSingleTagihan(Request $request)
    {
        $validated = $request->validate([
            'santri_id'   => ['required', 'exists:santri,id'],
            'bulan'       => ['required', 'integer', 'between:1,12'],
            'tahun'       => ['required', 'integer', 'between:2020,2099'],
            'status_awal' => ['nullable', 'in:belum_lunas,lunas'],
        ], [
            'santri_id.required' => 'Pilih santri terlebih dahulu.',
        ]);

        $santri = Santri::findOrFail($validated['santri_id']);
        $namaBulan = SppTagihan::getNamaBulan((int) $validated['bulan']);
        $rekeningSantri = $santri->nomor_rekening_spp ?: null;
        $statusInput = $validated['status_awal'] ?? 'belum_lunas';

        $existing = SppTagihan::where('santri_id', $santri->id)
            ->where('bulan', $validated['bulan'])
            ->where('tahun', $validated['tahun'])
            ->first();

        if ($existing) {
            if ($statusInput === 'lunas' && $existing->status !== 'lunas') {
                $sisaBayar = (float) $existing->sisa_tagihan;
                if ($sisaBayar > 0) {
                    SppPembayaran::create([
                        'spp_tagihan_id'       => $existing->id,
                        'santri_id'            => $santri->id,
                        'jumlah_bayar'         => $sisaBayar,
                        'metode_bayar'         => 'transfer',
                        'catatan'              => "Input Lunas Per Santri ({$namaBulan} {$validated['tahun']})",
                        'status'               => 'diterima',
                        'verified_by_admin_id' => auth('admin')->id(),
                        'verified_at'          => now(),
                    ]);
                }
                $existing->nominal_terbayar = $existing->nominal_tagihan;
                $existing->sisa_tagihan = 0;
                $existing->status = 'lunas';
                $existing->save();

                return back()->with('success', "Tagihan SPP {$santri->nama_lengkap} ({$namaBulan} {$validated['tahun']}) berhasil diubah menjadi SUDAH LUNAS (tidak akan menagih WA lagi).");
            }
            return back()->with('error', "Tagihan SPP {$santri->nama_lengkap} untuk periode {$namaBulan} {$validated['tahun']} sudah ada.");
        }

        $nominalTagihan = (float) $santri->tarif_spp;
        $isBeasiswa = ($nominalTagihan == 0.0);
        $isLunasLangsung = ($isBeasiswa || $statusInput === 'lunas');

        $tagihan = SppTagihan::create([
            'santri_id'        => $santri->id,
            'bulan'            => $validated['bulan'],
            'tahun'            => $validated['tahun'],
            'nominal_spp'      => $nominalTagihan,
            'nominal_addon'    => 0,
            'nominal_tagihan'  => $nominalTagihan,
            'nominal_terbayar' => 0,
            'sisa_tagihan'     => $nominalTagihan,
            'status'           => $isBeasiswa ? 'lunas' : 'belum_lunas',
            'nomor_va'         => $rekeningSantri,
            'keterangan'       => $isBeasiswa ? "SPP {$namaBulan} {$validated['tahun']} (Beasiswa Full)" : "SPP {$namaBulan} {$validated['tahun']}",
        ]);

        // Otomatis masukkan template add-on santri jika ada
        foreach ($santri->addonTemplates()->where('is_active', true)->get() as $tpl) {
            $tagihan->addons()->create([
                'nama_biaya' => $tpl->nama_biaya,
                'nominal'    => (float) $tpl->nominal,
                'keterangan' => $tpl->keterangan,
            ]);
        }
        $tagihan->syncTotals();

        if ($statusInput === 'lunas' && !$isBeasiswa && (float)$tagihan->nominal_tagihan > 0) {
            $bayarTotal = (float) $tagihan->nominal_tagihan;
            SppPembayaran::create([
                'spp_tagihan_id'       => $tagihan->id,
                'santri_id'            => $santri->id,
                'jumlah_bayar'         => $bayarTotal,
                'metode_bayar'         => 'transfer',
                'catatan'              => "Input Lunas Per Santri ({$namaBulan} {$validated['tahun']})",
                'status'               => 'diterima',
                'verified_by_admin_id' => auth('admin')->id(),
                'verified_at'          => now(),
            ]);
            $tagihan->nominal_terbayar = $bayarTotal;
            $tagihan->sisa_tagihan = 0;
            $tagihan->status = 'lunas';
            $tagihan->save();
        }

        $ketStatus = $isLunasLangsung ? 'SUDAH LUNAS (Tidak Menagih WA)' : 'BELUM LUNAS';
        AdminActivityLogger::log(
            'create_spp_single',
            "Membuat tagihan SPP perorangan ({$ketStatus}): {$santri->nama_lengkap} ({$namaBulan} {$validated['tahun']}) Rp " . number_format($nominalTagihan, 0, ',', '.'),
            'SppTagihan',
            $tagihan->id
        );

        return back()->with('success', "Tagihan SPP untuk {$santri->nama_lengkap} ({$namaBulan} {$validated['tahun']}) sebesar Rp " . number_format($nominalTagihan, 0, ',', '.') . " berhasil dibuat dengan status {$ketStatus}.");
    }

    /**
     * Buat / Input Tagihan SPP dengan Menu Centang (Multi-Bulan & Multi-Santri).
     * Mendukung status awal 'belum_lunas' (untuk dibayar via aplikasi) atau langsung 'lunas'
     * (jika sudah dibayar 3 bulan / lebih sehingga tidak ditagih WA lagi).
     */
    public function storeMultiTagihan(Request $request)
    {
        if ($request->has('bulans') && !$request->has('bulan_list')) {
            $request->merge(['bulan_list' => $request->input('bulans')]);
        }
        if ($request->has('status_set') && !$request->has('status_awal')) {
            $request->merge(['status_awal' => $request->input('status_set')]);
        }
        if ($request->has('keterangan') && !$request->has('catatan')) {
            $request->merge(['catatan' => $request->input('keterangan')]);
        }

        $validated = $request->validate([
            'santri_ids'   => ['required', 'array', 'min:1'],
            'santri_ids.*' => ['integer', 'exists:santri,id'],
            'bulan_list'   => ['required', 'array', 'min:1'],
            'bulan_list.*' => ['integer', 'between:1,12'],
            'tahun'        => ['required', 'integer', 'between:2020,2099'],
            'status_awal'  => ['required', 'in:belum_lunas,lunas'],
            'metode_bayar' => ['nullable', 'in:transfer,tunai'],
            'catatan'      => ['nullable', 'string', 'max:255'],
        ], [
            'santri_ids.required' => 'Centang minimal 1 nama santri.',
            'bulan_list.required' => 'Centang minimal 1 bulan tagihan.',
        ]);

        $tahun = (int) $validated['tahun'];
        $statusInput = $validated['status_awal'];
        $metodeBayar = $validated['metode_bayar'] ?? 'transfer';
        $catatan = $validated['catatan'] ?? null;
        $kirimWa = $request->boolean('kirim_wa');

        $santris = Santri::with(['orangTua', 'user', 'addonTemplates'])->whereIn('id', $validated['santri_ids'])->get();
        $bulanList = collect($validated['bulan_list'])->map(fn($b) => (int) $b)->sort()->unique()->values();

        $createdOrUpdatedCount = 0;
        $lunasCount = 0;
        $waSentCount = 0;

        $defaultRekening = SppSetting::getActive();
        $biayaAdmin = $defaultRekening?->biaya_admin ? (float) $defaultRekening->biaya_admin : 2500.0;

        DB::transaction(function () use ($santris, $bulanList, $tahun, $statusInput, $metodeBayar, $catatan, &$createdOrUpdatedCount, &$lunasCount) {
            foreach ($santris as $santri) {
                $nominalTagihan = (float) $santri->tarif_spp;
                $isBeasiswa = ($nominalTagihan == 0.0);
                $rekeningSantri = $santri->nomor_rekening_spp ?: null;

                foreach ($bulanList as $bulan) {
                    $namaBulan = SppTagihan::getNamaBulan($bulan);

                    $tagihan = SppTagihan::firstOrCreate(
                        [
                            'santri_id' => $santri->id,
                            'bulan'     => $bulan,
                            'tahun'     => $tahun,
                        ],
                        [
                            'nominal_spp'      => $nominalTagihan,
                            'nominal_addon'    => 0,
                            'nominal_tagihan'  => $nominalTagihan,
                            'nominal_terbayar' => 0,
                            'sisa_tagihan'     => $nominalTagihan,
                            'status'           => $isBeasiswa ? 'lunas' : 'belum_lunas',
                            'nomor_va'         => $rekeningSantri,
                            'keterangan'       => $catatan ?: ($isBeasiswa ? "SPP {$namaBulan} {$tahun} (Beasiswa Full)" : "SPP {$namaBulan} {$tahun}"),
                        ]
                    );

                    if ($tagihan->wasRecentlyCreated) {
                        $createdOrUpdatedCount++;

                        if ($santri->addonTemplates && $santri->addonTemplates->isNotEmpty()) {
                            foreach ($santri->addonTemplates->where('is_active', true) as $tpl) {
                                $tagihan->addons()->create([
                                    'nama_biaya' => $tpl->nama_biaya,
                                    'nominal'    => (float) $tpl->nominal,
                                    'keterangan' => $tpl->keterangan,
                                ]);
                            }
                            $tagihan->syncTotals();
                        }
                    }

                    // Jika dipilih langsung SUDAH LUNAS (misal bayar 3 bulan atau lebih)
                    if ($statusInput === 'lunas' && $tagihan->status !== 'lunas') {
                        $sisaBayar = (float) $tagihan->sisa_tagihan;
                        if ($sisaBayar > 0) {
                            SppPembayaran::create([
                                'spp_tagihan_id'       => $tagihan->id,
                                'santri_id'            => $santri->id,
                                'jumlah_bayar'         => $sisaBayar,
                                'metode_bayar'         => $metodeBayar,
                                'catatan'              => $catatan ?: "Input Lunas Multi-Bulan ({$namaBulan} {$tahun})",
                                'status'               => 'diterima',
                                'verified_by_admin_id' => auth('admin')->id(),
                                'verified_at'          => now(),
                            ]);
                        }
                        $tagihan->nominal_terbayar = $tagihan->nominal_tagihan;
                        $tagihan->sisa_tagihan = 0;
                        $tagihan->status = 'lunas';
                        $tagihan->save();
                        $lunasCount++;
                    }
                }
            }
        });

        if ($kirimWa && $statusInput === 'belum_lunas') {
            $lastBulan = (int) $bulanList->last();
            foreach ($santris as $santri) {
                $tagihanTerakhir = SppTagihan::where('santri_id', $santri->id)
                    ->where('bulan', $lastBulan)
                    ->where('tahun', $tahun)
                    ->first();
                if ($tagihanTerakhir && $tagihanTerakhir->status !== 'lunas') {
                    $rekSantri = $santri->info_rekening_spp;
                    $rekeningTujuan = !empty($santri->nomor_rekening_spp)
                        ? "{$rekSantri['bank']} {$rekSantri['nomor_rekening']} ({$rekSantri['atas_nama']})"
                        : ($defaultRekening ? trim("{$defaultRekening->nama_bank} {$defaultRekening->nomor_rekening} a.n. {$defaultRekening->atas_nama_rekening}") : 'Rekening Pondok');
                    $msg = NotificationService::sendSppTagihan(
                        $santri,
                        $lastBulan,
                        $tahun,
                        (float) $tagihanTerakhir->nominal_tagihan,
                        $rekeningTujuan,
                        'MULTI-' . $tagihanTerakhir->id . '-' . now()->format('YmdHis'),
                        $biayaAdmin
                    );
                    if ($msg) {
                        $tagihanTerakhir->update(['broadcast_wa_at' => now()]);
                        $waSentCount++;
                    }
                }
            }
        }

        $namaBulanJoined = $bulanList->map(fn($b) => SppTagihan::getNamaBulan($b))->implode(', ');
        $statusLabel = $statusInput === 'lunas' ? 'SUDAH LUNAS (Tidak Menagih WA Lagi)' : 'BELUM LUNAS (Siap Dibayar via Aplikasi)';

        AdminActivityLogger::log(
            'create_spp_multi',
            "Input SPP Multi-Bulan ({$namaBulanJoined} {$tahun}) untuk {$santris->count()} santri dengan status {$statusLabel}"
        );

        return redirect()->route('admin.spp.index', [
            'bulan' => $bulanList->first(),
            'tahun' => $tahun,
        ])->with('success', "Berhasil memproses {$createdOrUpdatedCount} tagihan SPP ({$namaBulanJoined} {$tahun}) untuk {$santris->count()} santri dengan status: {$statusLabel}." . ($waSentCount > 0 ? " Notifikasi WA terkirim ke {$waSentCount} wali." : ""));
    }

    public function updateTagihan(Request $request, $id)
    {
        $validated = $request->validate([
            'nominal_tagihan' => ['required', 'numeric', 'min:0'],
            'keterangan'      => ['nullable', 'string', 'max:255'],
        ]);

        $tagihan = SppTagihan::with(['santri', 'pembayarans', 'addons'])->findOrFail($id);
        $sudahDibayar = (float) $tagihan->pembayarans
            ->where('status', 'diterima')
            ->sum('jumlah_bayar');
        $nominalBaruSpp = (float) $validated['nominal_tagihan'];

        if (($nominalBaruSpp + (float) $tagihan->nominal_addon) < $sudahDibayar) {
            return back()->with('error', 'Nominal tagihan tidak boleh lebih kecil dari pembayaran yang sudah diterima (Rp ' . number_format($sudahDibayar, 0, ',', '.') . ').');
        }

        $nominalLama = (float) $tagihan->nominal_spp;

        DB::transaction(function () use ($tagihan, $nominalBaruSpp, $validated): void {
            $tagihan->nominal_spp = $nominalBaruSpp;
            $tagihan->keterangan = $validated['keterangan'];
            $tagihan->syncTotals();
        });

        AdminActivityLogger::log(
            'update_spp_tagihan',
            "Mengubah SPP pokok {$tagihan->santri->nama_lengkap} ({$tagihan->bulan_label}) dari Rp " . number_format($nominalLama, 0, ',', '.') . ' menjadi Rp ' . number_format($nominalBaruSpp, 0, ',', '.'),
            'SppTagihan',
            $tagihan->id
        );

        return back()->with('success', 'Tagihan SPP berhasil diperbarui.');
    }

    public function destroyPeriod(Request $request)
    {
        $validated = $request->validate([
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'between:2020,2099'],
        ]);

        $query = SppTagihan::where('bulan', $validated['bulan'])
            ->where('tahun', $validated['tahun']);

        // Riwayat pembayaran adalah data keuangan; tagihan yang sudah mempunyai
        // pembayaran tidak dihapus melalui penghapusan massal ini.
        $totalPeriode = (clone $query)->count();
        $tagihanAmanDihapus = (clone $query)->doesntHave('pembayarans');
        $dihapus = $tagihanAmanDihapus->count();
        $tagihanAmanDihapus->delete();
        $dilewati = $totalPeriode - $dihapus;
        $namaBulan = SppTagihan::getNamaBulan((int) $validated['bulan']);

        AdminActivityLogger::log(
            'delete_spp_period',
            "Menghapus {$dihapus} tagihan SPP tanpa pembayaran untuk {$namaBulan} {$validated['tahun']}" . ($dilewati ? "; {$dilewati} tagihan berbayar dipertahankan" : '')
        );

        $pesan = "Berhasil menghapus {$dihapus} tagihan SPP {$namaBulan} {$validated['tahun']}.";
        if ($dilewati) {
            $pesan .= " {$dilewati} tagihan yang sudah memiliki riwayat pembayaran tidak dihapus.";
        }

        return redirect()->route('admin.spp.index', [
            'bulan' => $validated['bulan'],
            'tahun' => $validated['tahun'],
        ])->with('success', $pesan);
    }

    /**
     * Hapus massal seluruh tagihan yang berstatus menunggak (semua wali santri).
     */
    public function destroyAllTunggakan(Request $request)
    {
        $allUnpaid = SppTagihan::where('status', '!=', 'lunas')
            ->where('sisa_tagihan', '>', 0)
            ->get();

        if ($allUnpaid->isEmpty()) {
            return back()->with('info', 'Tidak ada data tagihan menunggak yang dapat dihapus.');
        }

        $totalCount = $allUnpaid->count();
        $dihapus = 0;

        DB::transaction(function () use ($allUnpaid, &$dihapus) {
            foreach ($allUnpaid as $tagihan) {
                if ($tagihan->pembayarans()->where('status', 'diterima')->exists()) {
                    continue;
                }
                $tagihan->addons()->delete();
                $tagihan->pembayarans()->delete();
                $tagihan->delete();
                $dihapus++;
            }
        });

        AdminActivityLogger::log(
            'delete_all_spp_tunggakan_global',
            "Menghapus massal {$dihapus} dari {$totalCount} tagihan SPP yang menunggak"
        );

        return back()->with('success', "Berhasil menghapus seluruh {$dihapus} tagihan SPP yang menunggak.");
    }

    public function destroyTagihan($id)
    {
        $tagihan = SppTagihan::with(['santri', 'pembayarans'])->findOrFail($id);

        if ($tagihan->pembayarans->where('status', 'diterima')->count() > 0) {
            return back()->with('error', 'Tagihan tidak dapat dihapus karena sudah memiliki riwayat pembayaran yang diterima.');
        }

        $namaSantri = $tagihan->santri->nama_lengkap ?? 'Santri';
        $bulanLabel = $tagihan->bulan_label;

        DB::transaction(function () use ($tagihan) {
            $tagihan->pembayarans()->delete();
            $tagihan->delete();
        });

        AdminActivityLogger::log(
            'delete_spp_tagihan',
            "Menghapus tagihan SPP {$namaSantri} periode {$bulanLabel}"
        );

        return back()->with('success', "Tagihan SPP {$namaSantri} ({$bulanLabel}) berhasil dihapus dan otomatis hilang dari akun wali.");
    }

    /**
     * Batalkan status Lunas / Cicilan pada tagihan SPP:
     * Menghapus riwayat pembayaran yang diterima dan mengembalikan sisa tagihan secara penuh.
     */
    public function batalkanLunas($id)
    {
        $tagihan = SppTagihan::with(['santri', 'pembayarans'])->findOrFail($id);
        $namaSantri = $tagihan->santri->nama_lengkap ?? 'Santri';
        $bulanLabel = $tagihan->bulan_label;

        DB::transaction(function () use ($tagihan) {
            // Hapus semua pembayaran yang tercatat pada tagihan ini
            $tagihan->pembayarans()->delete();

            // Kembalikan saldo tagihan
            $tagihan->nominal_terbayar = 0;
            $tagihan->sisa_tagihan = $tagihan->nominal_tagihan;
            $tagihan->status = 'belum_lunas';
            $tagihan->save();
        });

        AdminActivityLogger::log(
            'cancel_spp_lunas',
            "Membatalkan status pembayaran/lunas SPP {$namaSantri} periode {$bulanLabel}"
        );

        return back()->with('success', "Status lunas SPP {$namaSantri} ({$bulanLabel}) berhasil dibatalkan. Tagihan kembali menjadi Belum Lunas.");
    }

    /**
     * Tambah biaya add-on (misal Laundry, Kegiatan, Buku) pada tagihan SPP tertentu.
     */
    public function storeAddon(Request $request, SppTagihan $tagihan)
    {
        $validated = $request->validate([
            'nama_biaya' => ['required', 'string', 'max:100'],
            'nominal'    => ['required', 'numeric', 'min:500'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'nama_biaya.required' => 'Nama biaya add-on wajib diisi (misal: Laundry).',
            'nama_biaya.max'      => 'Nama biaya add-on maksimal 100 karakter.',
            'nominal.required'    => 'Nominal biaya wajib diisi.',
            'nominal.numeric'     => 'Nominal biaya harus berupa angka.',
            'nominal.min'         => 'Nominal biaya minimal Rp 500.',
        ]);

        $addon = $tagihan->addons()->create([
            'nama_biaya' => trim($validated['nama_biaya']),
            'nominal'    => (float) $validated['nominal'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        $tagihan->syncTotals();
        $tagihan->load(['santri.orangTua', 'santri.user', 'addons']);

        AdminActivityLogger::log(
            'add_spp_addon',
            "Menambahkan add-on '{$addon->nama_biaya}' Rp " . number_format($addon->nominal, 0, ',', '.') . " pada tagihan SPP {$tagihan->santri?->nama_lengkap} ({$tagihan->bulan_label})",
            'SppTagihan',
            $tagihan->id
        );

        // Kirim notifikasi WA otomatis ke wali santri dengan rincian + Add-on terbaru
        if ($request->boolean('kirim_wa') && $tagihan->santri) {
            try {
                NotificationService::sendSppAddonNotif($tagihan->santri, $tagihan, $addon, 'ditambahkan');
            } catch (\Throwable $e) {
                \Log::warning('Gagal kirim notif WA add-on SPP: ' . $e->getMessage());
            }
        }

        return back()->with('success', "Biaya tambahan '+ {$addon->nama_biaya}' sebesar Rp " . number_format($addon->nominal, 0, ',', '.') . " berhasil ditambahkan ke tagihan" . ($request->boolean('kirim_wa') ? " dan notifikasi WA terkirim." : "."));
    }

    /**
     * Edit biaya add-on pada tagihan SPP.
     */
    public function updateAddon(Request $request, SppTagihanAddon $addon)
    {
        $validated = $request->validate([
            'nama_biaya' => ['required', 'string', 'max:100'],
            'nominal'    => ['required', 'numeric', 'min:500'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'nama_biaya.required' => 'Nama biaya add-on wajib diisi.',
            'nominal.required'    => 'Nominal biaya wajib diisi.',
            'nominal.min'         => 'Nominal biaya minimal Rp 500.',
        ]);

        $addon->update([
            'nama_biaya' => trim($validated['nama_biaya']),
            'nominal'    => (float) $validated['nominal'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        $tagihan = $addon->tagihan;
        if ($tagihan) {
            $tagihan->syncTotals();
            $tagihan->load(['santri.orangTua', 'santri.user', 'addons']);

            AdminActivityLogger::log(
                'update_spp_addon',
                "Mengubah add-on '{$addon->nama_biaya}' menjadi Rp " . number_format($addon->nominal, 0, ',', '.') . " pada tagihan SPP {$tagihan->santri?->nama_lengkap} ({$tagihan->bulan_label})",
                'SppTagihan',
                $tagihan->id
            );

            if ($request->boolean('kirim_wa') && $tagihan->santri) {
                try {
                    NotificationService::sendSppAddonNotif($tagihan->santri, $tagihan, $addon, 'diperbarui');
                } catch (\Throwable $e) {
                    \Log::warning('Gagal kirim notif WA edit add-on SPP: ' . $e->getMessage());
                }
            }
        }

        return back()->with('success', "Biaya tambahan '{$addon->nama_biaya}' berhasil diperbarui menjadi Rp " . number_format($addon->nominal, 0, ',', '.') . ".");
    }

    /**
     * Simpan template add-on rutin santri (e.g. Laundry 60rb).
     * Template ini TIDAK menerbitkan tagihan seketika & TIDAK menagih WA saat ini,
     * melainkan otomatis disalin ke tagihan SPP saat periode tagihan bulanannya diterbitkan.
     */
    public function storeSantriAddonTemplate(Request $request, Santri $santri)
    {
        $validated = $request->validate([
            'nama_biaya' => ['required', 'string', 'max:100'],
            'nominal'    => ['required', 'numeric', 'min:500'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'nama_biaya.required' => 'Nama biaya add-on rutin wajib diisi.',
            'nominal.required'    => 'Nominal biaya wajib diisi.',
            'nominal.numeric'     => 'Nominal biaya harus berupa angka.',
            'nominal.min'         => 'Nominal biaya minimal Rp 500.',
        ]);

        $template = $santri->addonTemplates()->create([
            'nama_biaya' => trim($validated['nama_biaya']),
            'nominal'    => (float) $validated['nominal'],
            'keterangan' => $validated['keterangan'] ?? null,
            'is_active'  => true,
        ]);

        AdminActivityLogger::log(
            'create_santri_addon_template',
            "Menambahkan template add-on rutin '{$template->nama_biaya}' Rp " . number_format($template->nominal, 0, ',', '.') . " untuk santri {$santri->nama_lengkap}",
            'SantriAddonTemplate',
            $template->id
        );

        return redirect()->route('admin.spp.index', ['tab' => 'rekening'])
            ->with('success', "Template Add-on Rutin '+ {$template->nama_biaya}' (Rp " . number_format($template->nominal, 0, ',', '.') . ") untuk {$santri->nama_lengkap} berhasil disimpan. Biaya ini akan otomatis masuk setiap tagihan SPP bulanan diterbitkan.");
    }

    /**
     * Hapus template add-on rutin santri.
     */
    public function destroySantriAddonTemplate(SantriAddonTemplate $template)
    {
        $santri = $template->santri;
        $namaBiaya = $template->nama_biaya;
        $nominal = (float) $template->nominal;

        $template->delete();

        AdminActivityLogger::log(
            'delete_santri_addon_template',
            "Menghapus template add-on rutin '{$namaBiaya}' Rp " . number_format($nominal, 0, ',', '.') . " santri " . ($santri?->nama_lengkap ?? '-'),
            'SantriAddonTemplate',
            $template->id
        );

        return redirect()->route('admin.spp.index', ['tab' => 'rekening'])
            ->with('success', "Template Add-on Rutin '{$namaBiaya}' berhasil dihapus.");
    }

    /**
     * Update template add-on rutin santri.
     */
    public function updateSantriAddonTemplate(Request $request, SantriAddonTemplate $template)
    {
        $validated = $request->validate([
            'nama_biaya' => ['required', 'string', 'max:100'],
            'nominal'    => ['required', 'numeric', 'min:500'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'nama_biaya.required' => 'Nama biaya add-on rutin wajib diisi.',
            'nominal.required'    => 'Nominal biaya wajib diisi.',
            'nominal.numeric'     => 'Nominal biaya harus berupa angka.',
            'nominal.min'         => 'Nominal biaya minimal Rp 500.',
        ]);

        $santri = $template->santri;
        $oldNama = $template->nama_biaya;
        $oldNominal = (float) $template->nominal;

        $template->update([
            'nama_biaya' => trim($validated['nama_biaya']),
            'nominal'    => (float) $validated['nominal'],
            'keterangan' => !empty($validated['keterangan']) ? trim($validated['keterangan']) : null,
        ]);

        AdminActivityLogger::log(
            'update_santri_addon_template',
            "Memperbarui template add-on rutin '{$oldNama}' (Rp " . number_format($oldNominal, 0, ',', '.') . ") menjadi '{$template->nama_biaya}' (Rp " . number_format($template->nominal, 0, ',', '.') . ") santri " . ($santri?->nama_lengkap ?? '-'),
            'SantriAddonTemplate',
            $template->id
        );

        return redirect()->route('admin.spp.index', ['tab' => 'rekening'])
            ->with('success', "Template Add-on Rutin '{$template->nama_biaya}' berhasil diperbarui.");
    }

    /**
     * Tambah biaya Add-on langsung dari menu Pengaturan Rekening Santri
     * (Otomatis membuat tagihan bulan berjalan jika belum ada, menampilkan + di daftar tagihan, & mengirim WA).
     */
    public function storeAddonSantri(Request $request, Santri $santri)
    {
        $validated = $request->validate([
            'bulan'      => ['required', 'integer', 'between:1,12'],
            'tahun'      => ['required', 'integer', 'between:2020,2099'],
            'nama_biaya' => ['required', 'string', 'max:100'],
            'nominal'    => ['required', 'numeric', 'min:500'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $bulan = (int) $validated['bulan'];
        $tahun = (int) $validated['tahun'];
        $namaBulan = SppTagihan::getNamaBulan($bulan);
        $nominalSpp = (float) $santri->tarif_spp;
        $isBeasiswa = ($nominalSpp == 0.0);

        $tagihan = SppTagihan::firstOrCreate(
            [
                'santri_id' => $santri->id,
                'bulan'     => $bulan,
                'tahun'     => $tahun,
            ],
            [
                'nominal_spp'      => $nominalSpp,
                'nominal_addon'    => 0,
                'nominal_tagihan'  => $nominalSpp,
                'nominal_terbayar' => 0,
                'sisa_tagihan'     => $nominalSpp,
                'status'           => $isBeasiswa ? 'lunas' : 'belum_lunas',
                'nomor_va'         => $santri->nomor_rekening_spp ?: null,
                'keterangan'       => $isBeasiswa ? "SPP {$namaBulan} {$tahun} (Beasiswa Full)" : "SPP {$namaBulan} {$tahun}",
            ]
        );

        $addon = $tagihan->addons()->create([
            'nama_biaya' => trim($validated['nama_biaya']),
            'nominal'    => (float) $validated['nominal'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        $tagihan->syncTotals();
        $tagihan->load(['santri.orangTua', 'santri.user', 'addons']);

        AdminActivityLogger::log(
            'add_spp_addon_from_rekening',
            "Menambahkan add-on '{$addon->nama_biaya}' Rp " . number_format($addon->nominal, 0, ',', '.') . " dari Pengaturan Rekening Santri {$santri->nama_lengkap} ({$tagihan->bulan_label})",
            'SppTagihan',
            $tagihan->id
        );

        if ($request->boolean('kirim_wa')) {
            try {
                NotificationService::sendSppAddonNotif($santri, $tagihan, $addon, 'ditambahkan');
            } catch (\Throwable $e) {
                \Log::warning('Gagal kirim notif WA add-on santri: ' . $e->getMessage());
            }
        }

        return back()->with('success', "Add-on '+ {$addon->nama_biaya}' (Rp " . number_format($addon->nominal, 0, ',', '.') . ") untuk {$santri->nama_lengkap} ({$namaBulan} {$tahun}) berhasil disimpan, tampil di daftar tagihan, dan dikirim ke WA wali.");
    }

    /**
     * Hapus biaya add-on dari tagihan SPP.
     */
    public function destroyAddon(SppTagihanAddon $addon)
    {
        $tagihan = $addon->tagihan;
        $namaBiaya = $addon->nama_biaya;
        $nominal = (float) $addon->nominal;

        $addon->delete();

        if ($tagihan) {
            $tagihan->syncTotals();
            AdminActivityLogger::log(
                'delete_spp_addon',
                "Menghapus add-on '{$namaBiaya}' Rp " . number_format($nominal, 0, ',', '.') . " dari tagihan SPP {$tagihan->santri?->nama_lengkap} ({$tagihan->bulan_label})",
                'SppTagihan',
                $tagihan->id
            );
        }

        return back()->with('success', "Biaya tambahan '{$namaBiaya}' berhasil dihapus.");
    }

    public function storePembayaran(Request $request, $tagihanId)
    {
        $request->validate([
            'jumlah_bayar' => ['required', 'numeric', 'min:1000'],
            'metode_bayar' => ['required', 'in:transfer,tunai'],
            'catatan'      => ['nullable', 'string', 'max:255'],
        ]);

        $tagihan = SppTagihan::with('santri.orangTua')->findOrFail($tagihanId);
        $jumlahBayar = (float) $request->jumlah_bayar;

        if ($jumlahBayar > $tagihan->sisa_tagihan) {
            return back()->with('error', "Nominal pembayaran melebihi sisa tagihan (Maksimal: Rp " . number_format($tagihan->sisa_tagihan, 0, ',', '.') . ")");
        }

        DB::beginTransaction();
        try {
            $pembayaran = SppPembayaran::create([
                'spp_tagihan_id'       => $tagihan->id,
                'santri_id'            => $tagihan->santri_id,
                'jumlah_bayar'         => $jumlahBayar,
                'metode_bayar'         => $request->metode_bayar,
                'catatan'              => $request->catatan,
                'status'               => 'diterima',
                'verified_by_admin_id' => auth('admin')->id(),
                'verified_at'          => now(),
            ]);

            $tagihan->updateStatusAndBalance();

            AdminActivityLogger::log(
                'payment_spp',
                "Mencatat pembayaran SPP santri '{$tagihan->santri->nama_lengkap}' sebesar Rp " . number_format($jumlahBayar, 0, ',', '.') . " (Status: {$tagihan->status})",
                'SppPembayaran',
                $pembayaran->id
            );

            DB::commit();

            // Notifikasi WA Otomatis ke Wali
            try {
                $noWaOrtu = $tagihan->santri->orangTua?->no_wa_ayah;
                if ($noWaOrtu) {
                    NotificationService::sendSppLunas(
                        $tagihan->santri,
                        $tagihan->bulan,
                        $tagihan->tahun,
                        (float) $jumlahBayar,
                        $tagihan->status,
                        (float) $tagihan->sisa_tagihan,
                        (string) $pembayaran->id
                    );
                }
            } catch (\Throwable $e) {
                \Log::warning('Gagal kirim notif WA pembayaran SPP: ' . $e->getMessage());
            }

            return back()->with('success', "Pembayaran SPP sebesar Rp " . number_format($jumlahBayar, 0, ',', '.') . " berhasil dicatat.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    /** Terima bukti transfer yang sudah diunggah wali (mendukung 1 bulan maupun multi-bulan sekaligus). */
    public function verifyPembayaran(Request $request, SppPembayaran $pembayaran)
    {
        $verifiedTagihans = collect();
        $pembayaranId = null;
        $totalJumlahBayar = 0.0;

        DB::transaction(function () use ($pembayaran, &$verifiedTagihans, &$pembayaranId, &$totalJumlahBayar) {
            $pembayaran = SppPembayaran::lockForUpdate()->with('tagihan.santri.orangTua')->findOrFail($pembayaran->id);
            if ($pembayaran->status !== 'menunggu_konfirmasi') {
                abort(422, 'Pembayaran ini sudah diproses.');
            }

            // Cari semua pembayaran menunggu_konfirmasi yang diunggah bersama-sama (bukti_bayar & santri_id sama)
            $relatedPembayarans = SppPembayaran::lockForUpdate()
                ->where('santri_id', $pembayaran->santri_id)
                ->where('status', 'menunggu_konfirmasi')
                ->when($pembayaran->bukti_bayar, function ($q) use ($pembayaran) {
                    $q->where('bukti_bayar', $pembayaran->bukti_bayar);
                }, function ($q) use ($pembayaran) {
                    $q->where('id', $pembayaran->id);
                })
                ->get();

            foreach ($relatedPembayarans as $item) {
                $tagihan = SppTagihan::lockForUpdate()->findOrFail($item->spp_tagihan_id);
                $item->update([
                    'status'               => 'diterima',
                    'verified_by_admin_id' => auth('admin')->id(),
                    'verified_at'          => now(),
                    'alasan_penolakan'     => null,
                ]);
                $tagihan->updateStatusAndBalance();
                $tagihan->refresh()->load('santri.orangTua');

                $verifiedTagihans->push($tagihan);
                $totalJumlahBayar += (float) $item->jumlah_bayar;
            }

            $pembayaranId = $pembayaran->id;
            $firstTagihan = $verifiedTagihans->first();
            $periodeJoined = $verifiedTagihans->pluck('bulan_label')->implode(', ');

            if ($firstTagihan) {
                AdminActivityLogger::log(
                    'verifikasi_pembayaran_spp',
                    "Menerima pembayaran SPP {$firstTagihan->santri->nama_lengkap} periode ({$periodeJoined}) sebesar Rp " . number_format($totalJumlahBayar, 0, ',', '.'),
                    'SppPembayaran',
                    $pembayaran->id
                );
            }
        });

        // Kirim WhatsApp di luar transaksi DB
        if ($verifiedTagihans->isNotEmpty()) {
            $firstTagihan = $verifiedTagihans->first();
            try {
                if ($verifiedTagihans->count() === 1) {
                    NotificationService::sendSppLunas(
                        $firstTagihan->santri,
                        $firstTagihan->bulan,
                        $firstTagihan->tahun,
                        (float) $totalJumlahBayar,
                        $firstTagihan->status,
                        (float) $firstTagihan->sisa_tagihan,
                        (string) $pembayaranId
                    );
                } else {
                    $santri = $firstTagihan->santri;
                    $noWa = $santri?->orangTua?->no_wa_ayah ?: $santri?->user?->no_wa;
                    $periodeJoined = $verifiedTagihans->pluck('bulan_label')->implode(', ');
                    if ($noWa) {
                        NotificationService::send([
                            'recipient_number' => $noWa,
                            'recipient_name'   => $santri->orangTua?->nama_ayah ?? 'Wali Santri',
                            'user_id'          => $santri->user_id,
                            'santri_id'        => $santri->id,
                            'modul'            => 'SPP',
                            'priority'         => 'normal',
                            'reference_id'     => 'SPP-BAYAR-MULTI-' . $pembayaranId,
                            'message'          => "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\n"
                                . "Alhamdulillah, pembayaran SPP ananda *{$santri->nama_lengkap}* ({$santri->kelas_label}) untuk *{$verifiedTagihans->count()} bulan ({$periodeJoined})* sebesar *Rp " . number_format($totalJumlahBayar, 0, ',', '.') . "* telah kami terima dan diverifikasi.\n\n"
                                . "📊 *Status Tagihan:* *LUNAS*\n"
                                . "💰 *Sisa Tagihan:* *Rp 0*\n\n"
                                . "Jazakumullah khairan katsiran atas kerja samanya.\n"
                                . "_Bagian Administrasi & Keuangan SPP_",
                            'sync'             => true,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Gagal kirim notif WA verifikasi SPP: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Bukti transfer diterima. Seluruh bulan terkait telah berstatus LUNAS dan notifikasi wali telah diperbarui.');
    }

    /** Tolak bukti transfer dan simpan alasan agar wali dapat mengunggah ulang. */
    public function rejectPembayaran(Request $request, SppPembayaran $pembayaran)
    {
        $validated = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'max:500'],
        ], ['alasan_penolakan.required' => 'Alasan penolakan wajib diisi.']);

        $pembayaran = SppPembayaran::with('tagihan.santri.orangTua')->findOrFail($pembayaran->id);
        if ($pembayaran->status !== 'menunggu_konfirmasi') {
            return back()->with('error', 'Pembayaran ini sudah diproses.');
        }

        $relatedPembayarans = SppPembayaran::with('tagihan')
            ->where('santri_id', $pembayaran->santri_id)
            ->where('status', 'menunggu_konfirmasi')
            ->when($pembayaran->bukti_bayar, function ($q) use ($pembayaran) {
                $q->where('bukti_bayar', $pembayaran->bukti_bayar);
            }, function ($q) use ($pembayaran) {
                $q->where('id', $pembayaran->id);
            })
            ->get();

        foreach ($relatedPembayarans as $item) {
            $item->update([
                'status'               => 'ditolak',
                'alasan_penolakan'     => $validated['alasan_penolakan'],
                'verified_by_admin_id' => auth('admin')->id(),
                'verified_at'          => now(),
            ]);
        }

        $tagihan = $pembayaran->tagihan;
        $periodeJoined = $relatedPembayarans->map(fn($p) => $p->tagihan?->bulan_label)->filter()->implode(', ');

        AdminActivityLogger::log(
            'tolak_pembayaran_spp',
            "Menolak bukti pembayaran SPP {$tagihan->santri->nama_lengkap} periode ({$periodeJoined}): {$validated['alasan_penolakan']}",
            'SppPembayaran',
            $pembayaran->id
        );

        try {
            NotificationService::send([
                'recipient_number' => $tagihan->santri->orangTua?->no_wa_ayah ?: $tagihan->santri->user?->no_wa,
                'recipient_name' => $tagihan->santri->orangTua?->nama_ayah,
                'user_id' => $tagihan->santri->user_id,
                'santri_id' => $tagihan->santri_id,
                'modul' => 'SPP',
                'priority' => 'high',
                'reference_id' => 'SPP-TOLAK-' . $pembayaran->id,
                'message' => "Pembayaran SPP {$tagihan->santri->nama_lengkap} untuk periode ({$periodeJoined}) belum dapat diverifikasi. Alasan: {$validated['alasan_penolakan']}. Silakan periksa dan upload kembali bukti pembayaran.",
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Gagal kirim notif WA penolakan SPP: ' . $e->getMessage());
        }

        return back()->with('success', 'Bukti transfer ditolak. Alasan telah dikirim kepada wali.');
    }

    public function broadcastWa(Request $request)
    {
        $bulan = (int) $request->get('bulan', date('n'));
        $tahun = (int) $request->get('tahun', date('Y'));

        $tagihans = SppTagihan::with(['santri.orangTua', 'santri.user', 'addons'])
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->where('status', '!=', 'lunas')
            ->get();

        $defaultRekening = SppSetting::getActive();
        $defaultRekeningTujuan = $defaultRekening
            ? trim("{$defaultRekening->nama_bank} {$defaultRekening->nomor_rekening} a.n. {$defaultRekening->atas_nama_rekening}")
            : 'Rekening pondok';

        $sentCount = 0;
        $skippedCount = 0;
        foreach ($tagihans as $tagihan) {
            $santri = $tagihan->santri;
            if (!$santri || $tagihan->status === 'lunas') {
                continue;
            }

            // Hanya kirim notifikasi jika nomor rekening/VA santri sudah diatur
            if (empty($santri->nomor_rekening_spp)) {
                $skippedCount++;
                continue;
            }

            $noWa = $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa;
            if (!$noWa || $noWa === '-') {
                continue;
            }

            // Tentukan rekening tujuan spesifik santri dengan fallback rekening pondok
            $rekSantri = $santri->info_rekening_spp;
            if (!empty($santri->nomor_rekening_spp)) {
                $rekeningTujuan = "{$rekSantri['bank']} {$rekSantri['nomor_rekening']} ({$rekSantri['atas_nama']})";
            } elseif (!empty($tagihan->nomor_va) && $tagihan->nomor_va !== '-') {
                $rekeningTujuan = "{$rekSantri['bank']} {$tagihan->nomor_va} ({$rekSantri['atas_nama']})";
            } else {
                $rekeningTujuan = $defaultRekeningTujuan;
            }

            $biayaAdmin = $defaultRekening?->biaya_admin ? (float) $defaultRekening->biaya_admin : 2500.0;
            $message = NotificationService::sendSppTagihan(
                $santri,
                $tagihan->bulan,
                $tagihan->tahun,
                (float) $tagihan->nominal_tagihan,
                $rekeningTujuan,
                $tagihan->id . '-' . now()->format('YmdHi'),
                $biayaAdmin
            );
            if ($message) {
                $tagihan->update(['broadcast_wa_at' => now()]);
                $sentCount++;
            }
        }

        $logMsg = "Broadcast tagihan SPP {$bulan}/{$tahun} terkirim ke {$sentCount} wali santri";
        if ($skippedCount > 0) {
            $logMsg .= " ({$skippedCount} santri dilewati karena rekening/VA belum diatur)";
        }
        AdminActivityLogger::log('broadcast_spp', $logMsg);

        $successMsg = "Broadcast tagihan SPP berhasil dikirimkan ke {$sentCount} wali santri.";
        if ($skippedCount > 0) {
            $successMsg .= " ({$skippedCount} santri dilewati karena rekening/VA belum diatur).";
        }

        return back()->with('success', $successMsg);
    }

    /**
     * Kirim WA Rincian Tunggakan & Total Keseluruhan ke 1 Wali Santri dari Tab Rekap Tunggakan.
     */
    public function kirimWaTunggakanWali(Request $request, Santri $santri)
    {
        $unpaidTagihans = SppTagihan::with('addons')
            ->where('santri_id', $santri->id)
            ->where('status', '!=', 'lunas')
            ->where('sisa_tagihan', '>', 0)
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc')
            ->get();

        if ($unpaidTagihans->isEmpty()) {
            return back()->with('error', "Santri {$santri->nama_lengkap} tidak memiliki tunggakan.");
        }

        $latestUnpaid = $unpaidTagihans->last();

        if (empty($santri->nomor_rekening_spp)) {
            return back()->with('error', "Gagal mengirim WA: Rekening / Virtual Account santri {$santri->nama_lengkap} belum diatur. Silakan atur rekening terlebih dahulu di tab Kelola Rekening.");
        }

        $defaultRekening = SppSetting::getActive();
        $biayaAdmin = $defaultRekening?->biaya_admin ? (float) $defaultRekening->biaya_admin : 2500.0;

        $rekSantri = $santri->info_rekening_spp;
        $rekeningTujuan = "{$rekSantri['bank']} {$rekSantri['nomor_rekening']} ({$rekSantri['atas_nama']})";

        $msg = NotificationService::sendSppTotalTunggakan(
            $santri,
            $unpaidTagihans,
            $rekeningTujuan,
            $biayaAdmin,
            true
        );

        if (!$msg) {
            return back()->with('error', "Gagal mengirim WA ke wali {$santri->nama_lengkap}. Pastikan nomor WA wali sudah terisi.");
        }

        return back()->with('success', "Notifikasi WA rincian total tunggakan berhasil dikirim ke wali santri {$santri->nama_lengkap}.");
    }

    /**
     * Lunasi seluruh bulan tunggakan milik 1 santri sekaligus dari Tab Rekap Tunggakan Wali Santri.
     */
    public function lunasiSemuaTunggakanSantri(Request $request, Santri $santri)
    {
        $unpaidTagihans = SppTagihan::where('santri_id', $santri->id)
            ->where('status', '!=', 'lunas')
            ->where('sisa_tagihan', '>', 0)
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc')
            ->get();

        if ($unpaidTagihans->isEmpty()) {
            return back()->with('error', "Seluruh tagihan {$santri->nama_lengkap} sudah lunas.");
        }

        $metodeBayar = $request->input('metode_bayar', 'transfer');
        $periodeLabels = $unpaidTagihans->pluck('bulan_label')->implode(', ');
        $totalDiterima = (float) $unpaidTagihans->sum('sisa_tagihan');

        DB::transaction(function () use ($unpaidTagihans, $santri, $metodeBayar, $periodeLabels) {
            foreach ($unpaidTagihans as $tagihan) {
                $sisa = (float) $tagihan->sisa_tagihan;
                SppPembayaran::create([
                    'spp_tagihan_id'       => $tagihan->id,
                    'santri_id'            => $santri->id,
                    'jumlah_bayar'         => $sisa,
                    'metode_bayar'         => $metodeBayar,
                    'catatan'              => "Pelunasan menyeluruh ({$periodeLabels})",
                    'status'               => 'diterima',
                    'verified_by_admin_id' => auth('admin')->id(),
                    'verified_at'          => now(),
                ]);
                $tagihan->updateStatusAndBalance();
            }
        });

        AdminActivityLogger::log(
            'lunasi_semua_tunggakan_spp',
            "Melunasi seluruh tunggakan SPP {$santri->nama_lengkap} ({$periodeLabels}) total Rp " . number_format($totalDiterima, 0, ',', '.')
        );

        return back()->with('success', "Seluruh tunggakan {$santri->nama_lengkap} ({$periodeLabels}) sebesar Rp " . number_format($totalDiterima, 0, ',', '.') . " berhasil dilunasi.");
    }

    /**
     * Hapus seluruh tagihan SPP menunggak untuk satu santri tertentu.
     */
    public function hapusSemuaTunggakanSantri(Santri $santri)
    {
        $unpaidTagihans = SppTagihan::where('santri_id', $santri->id)
            ->where('status', '!=', 'lunas')
            ->where('sisa_tagihan', '>', 0)
            ->get();

        if ($unpaidTagihans->isEmpty()) {
            return back()->with('error', "Tidak ada tagihan menunggak untuk {$santri->nama_lengkap}.");
        }

        $count = $unpaidTagihans->count();
        $periodeLabels = $unpaidTagihans->pluck('bulan_label')->implode(', ');

        DB::transaction(function () use ($unpaidTagihans) {
            foreach ($unpaidTagihans as $tagihan) {
                // Jika sudah ada pembayaran diterima, lewati agar riwayat keuangan riil tidak terhapus
                if ($tagihan->pembayarans()->where('status', 'diterima')->exists()) {
                    continue;
                }
                $tagihan->addons()->delete();
                $tagihan->pembayarans()->delete();
                $tagihan->delete();
            }
        });

        AdminActivityLogger::log(
            'delete_all_spp_tunggakan',
            "Menghapus seluruh tagihan SPP menunggak {$santri->nama_lengkap} ({$periodeLabels}) sebanyak {$count} tagihan",
            'Santri',
            $santri->id
        );

        return back()->with('success', "Seluruh tagihan SPP menunggak ({$periodeLabels}) untuk {$santri->nama_lengkap} berhasil dihapus.");
    }

    public function updateSetting(Request $request)
    {
        $request->validate([
            'nama_bank'          => ['nullable', 'string', 'max:100'],
            'nomor_rekening'     => ['nullable', 'string', 'max:50'],
            'atas_nama_rekening' => ['nullable', 'string', 'max:255'],
            'no_wa_bendahara'    => ['nullable', 'string', 'max:50'],
            'biaya_admin'        => ['nullable', 'numeric', 'min:0'],
            'keterangan'         => ['nullable', 'string', 'max:500'],
        ], [
            'nama_bank.max'          => 'Nama bank maksimal 100 karakter.',
            'nomor_rekening.max'     => 'Nomor rekening maksimal 50 karakter.',
            'atas_nama_rekening.max' => 'Atas nama rekening maksimal 255 karakter.',
            'keterangan.max'         => 'Keterangan maksimal 500 karakter.',
        ]);

        $noWaBendahara = $request->filled('no_wa_bendahara')
            ? \App\Services\WhatsappService::formatPhoneNumber($request->no_wa_bendahara)
            : null;

        $data = [
            'nama_bank'          => $request->nama_bank,
            'nomor_rekening'     => $request->nomor_rekening,
            'atas_nama_rekening' => $request->atas_nama_rekening,
            'no_wa_bendahara'    => $noWaBendahara,
            'biaya_admin'        => $request->filled('biaya_admin') ? (float) $request->biaya_admin : 2500,
            'keterangan'         => $request->keterangan,
        ];

        $setting = SppSetting::first();

        if ($setting) {
            $setting->update($data);
        } else {
            $data['is_active'] = true;
            SppSetting::create($data);
        }

        AdminActivityLogger::log(
            'update_spp_setting',
            "Memperbarui pengaturan SPP: Bank {$request->nama_bank}, Rek {$request->nomor_rekening}, WA Bendahara {$noWaBendahara}, Admin Rp " . number_format($data['biaya_admin'], 0, ',', '.')
        );

        return redirect()->route('admin.spp.index', ['tab' => 'rekening'])
            ->with('success', 'Pengaturan nomor bendahara dan SPP berhasil disimpan.');
    }

    public function updateSantriRekening(Request $request, Santri $santri)
    {
        $request->validate([
            'nominal_spp'            => ['nullable', 'numeric', 'min:0'],
            'is_beasiswa'            => ['nullable'],
            'nama_bank_spp'          => ['nullable', 'string', 'max:100'],
            'nomor_rekening_spp'     => ['nullable', 'string', 'max:50'],
            'atas_nama_rekening_spp' => ['nullable', 'string', 'max:255'],
            'addon_nama_biaya'       => ['nullable', 'string', 'max:100'],
            'addon_nominal'          => ['nullable', 'numeric', 'min:500'],
            'addon_keterangan'       => ['nullable', 'string', 'max:255'],
            'addon_bulan'            => ['nullable', 'integer', 'between:1,12'],
            'addon_tahun'            => ['nullable', 'integer', 'between:2020,2099'],
        ], [
            'nominal_spp.numeric'        => 'Nominal SPP harus berupa angka.',
            'nama_bank_spp.max'          => 'Nama bank maksimal 100 karakter.',
            'nomor_rekening_spp.max'     => 'Nomor rekening maksimal 50 karakter.',
            'atas_nama_rekening_spp.max' => 'Atas nama rekening maksimal 255 karakter.',
        ]);

        $isBeasiswa = $request->boolean('is_beasiswa');
        $nominalSpp = $isBeasiswa ? 0.0 : ($request->filled('nominal_spp') ? (float) $request->nominal_spp : null);

        $santri->update([
            'is_beasiswa'            => $isBeasiswa,
            'nominal_spp'            => $nominalSpp,
            'nama_bank_spp'          => $request->filled('nama_bank_spp') ? trim($request->nama_bank_spp) : null,
            'nomor_rekening_spp'     => $request->filled('nomor_rekening_spp') ? trim($request->nomor_rekening_spp) : null,
            'atas_nama_rekening_spp' => $request->filled('atas_nama_rekening_spp') ? trim($request->atas_nama_rekening_spp) : null,
        ]);

        if ($santri->nomor_rekening_spp) {
            SppTagihan::where('santri_id', $santri->id)
                ->where('status', '!=', 'lunas')
                ->where(function ($q) {
                    $q->whereNull('nomor_va')->orWhere('nomor_va', '-');
                })
                ->update(['nomor_va' => $santri->nomor_rekening_spp]);
        }

        // Jika di modal Pengaturan Rekening Santri juga diisi fitur Add-on, simpan sebagai Template Add-on Rutin Santri (tidak langsung membuat tagihan)
        $addonMsg = '';
        if ($request->filled('addon_nama_biaya') && $request->filled('addon_nominal')) {
            $template = $santri->addonTemplates()->create([
                'nama_biaya' => trim($request->addon_nama_biaya),
                'nominal'    => (float) $request->addon_nominal,
                'keterangan' => $request->addon_keterangan ?? null,
                'is_active'  => true,
            ]);

            $addonMsg = " serta Add-on Rutin '+ {$template->nama_biaya}' (Rp " . number_format($template->nominal, 0, ',', '.') . ") berhasil disimpan sebagai template rutin.";
        }

        $infoMsg = $isBeasiswa ? "Beasiswa Full (SPP Rp 0)" : "Nominal Rp " . number_format($santri->nominal_spp ?? 0, 0, ',', '.');
        AdminActivityLogger::log(
            'update_rekening_spp_santri',
            "Memperbarui data SPP santri {$santri->nama_lengkap}: {$infoMsg}, Rek {$santri->nama_bank_spp} - {$santri->nomor_rekening_spp}{$addonMsg}"
        );

        return redirect()->route('admin.spp.index', ['tab' => 'rekening'])
            ->with('success', "Pengaturan SPP dan rekening santri {$santri->nama_lengkap} berhasil diperbarui{$addonMsg}");
    }

    public function cetakInvoice($id)
    {
        $tagihan = SppTagihan::with(['santri.orangTua', 'addons', 'pembayarans.verifiedBy'])->findOrFail($id);
        $setting = \App\Models\InvoiceSetting::getSetting();
        $fileName = 'Invoice_SPP_' . \Illuminate\Support\Str::slug($tagihan->santri->nama_lengkap) . '_' . $tagihan->bulan . '_' . $tagihan->tahun . '.pdf';

        if (request('download') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice_spp', [
                'tagihan' => $tagihan,
                'setting' => $setting,
                'isPdf' => true,
            ])->setPaper('a4', 'portrait');
            return $pdf->download($fileName);
        }

        return view('pdf.invoice_spp', [
            'tagihan' => $tagihan,
            'setting' => $setting,
            'fileName' => $fileName,
            'isPdf' => false,
        ]);
    }
}

