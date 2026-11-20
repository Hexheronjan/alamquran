<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SantriExport;
use App\Exports\SantriTemplateExport;
use App\Imports\SantriImport;
use App\Models\Angkatan;
use App\Models\Dokumen;
use App\Models\OrangTua;
use App\Models\Santri;
use App\Models\SantriCorrectionNote;
use App\Services\AkunWaliService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SantriController extends Controller
{
    public function index(Request $request)
    {
        $applyFilters = function ($query) use ($request) {
            // Filter hanya santri aktif (bukan alumni / lulus)
            $query->where(function ($q) {
                $q->whereNull('kelas')->orWhere('kelas', '!=', 'alumni');
            })->where(function ($q) {
                $q->whereNull('status_santri')->orWhere('status_santri', '!=', 'lulus');
            });

            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('nama_lengkap', 'like', '%' . $request->search . '%')
                      ->orWhere('nik', 'like', '%' . $request->search . '%')
                      ->orWhere('nisn', 'like', '%' . $request->search . '%')
                      ->orWhere('nis', 'like', '%' . $request->search . '%')
                      ->orWhereHas('orangTua', function ($oq) use ($request) {
                          $oq->where('nama_ayah', 'like', '%' . $request->search . '%');
                      });
                });
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->filled('status_santri') && $request->status_santri !== 'all') {
                if ($request->status_santri === 'aktif') {
                    $query->where(function ($q) {
                        $q->whereNull('status_santri')->orWhere('status_santri', 'aktif');
                    });
                } elseif ($request->status_santri === 'mutasi') {
                    $query->where('status_santri', 'mutasi');
                }
            }

            if ($request->kelas) {
                $query->where('kelas', $request->kelas);
            }

            if ($request->tahun_masuk) {
                $query->where('tahun_masuk', $request->tahun_masuk);
            }

            if ($request->angkatan_id) {
                $query->where('angkatan_id', $request->angkatan_id);
            }

            return $query;
        };

        $query = $applyFilters(Santri::with(['orangTua', 'dokumen', 'user', 'angkatan']));
        $query->orderBy('created_at', 'desc');

        $allSantri = $query->get();
        $statuses = AkunWaliService::statusesFor($allSantri);
        foreach ($allSantri as $item) {
            $item->setAttribute('wali_account_status', $statuses[$item->id]);
        }

        $grouped = $allSantri->groupBy(function ($s) {
            return $s->tahun_masuk ?: 'Belum Ditentukan';
        })->sortKeysDesc();

        $tahunList = Santri::distinct()
            ->pluck('tahun_masuk')
            ->filter()
            ->sortDesc()
            ->values();

        $angkatanList = Angkatan::orderByDesc('tahun_ajaran')->get();
        $selectedStatusSantri = $request->get('status_santri', 'all');

        $santri = $applyFilters(Santri::with(['orangTua', 'dokumen', 'user', 'angkatan']))
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();
        $pageStatuses = AkunWaliService::statusesFor($santri->getCollection());
        foreach ($santri as $item) {
            $item->setAttribute('wali_account_status', $pageStatuses[$item->id]);
        }

        return view('admin.santri.index', compact('santri', 'grouped', 'tahunList', 'angkatanList', 'selectedStatusSantri'));
    }

    /**
     * Form khusus data santri yang dimasukkan langsung oleh admin.
     * Daftar angkatan sengaja tidak dibatasi status pendaftaran PPDB agar
     * admin dapat memasukkan data ke tahun mana pun.
     */
    public function create()
    {
        $angkatanList = Angkatan::orderByDesc('tahun_angkatan')
            ->orderByDesc('tahun_ajaran')
            ->get();

        return view('admin.santri.create', compact('angkatanList'));
    }

    /**
     * Simpan data santri dari input admin. Akun wali dan pesan WhatsApp
     * tidak dibuat di sini; keduanya tetap dilakukan lewat tombol Generate Akun.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'angkatan_id'       => ['required', 'exists:angkatan,id'],
            'nama_lengkap'      => ['required', 'string', 'max:255'],
            'tempat_lahir'      => ['nullable', 'string', 'max:255'],
            'tanggal_lahir'     => ['nullable', 'date'],
            'nik'               => ['nullable', 'digits:16'],
            'nisn'              => ['nullable', 'digits:10'],
            'sekolah_asal'      => ['nullable', 'string', 'max:255'],
            'npsn'              => ['nullable', 'string', 'max:20'],
            'kelas'             => ['required', 'in:1,2,3'],
            'nama_ayah'         => ['required', 'string', 'max:255'],
            'no_wa_ayah'        => ['required', 'string', 'max:20'],
            'alamat_domisili_ayah' => ['nullable', 'string'],
            'catatan'           => ['nullable', 'string', 'max:1000'],
        ]);

        $noWa = \App\Services\WhatsappService::formatPhoneNumber($validated['no_wa_ayah']);
        if (!$noWa) {
            return back()->withInput()->withErrors([
                'no_wa_ayah' => 'Format nomor WhatsApp tidak valid. Gunakan 08xxxxxxxxxx atau 628xxxxxxxxxx.',
            ]);
        }

        $angkatan = Angkatan::findOrFail($validated['angkatan_id']);
        $tahunMasuk = $angkatan->tahun_angkatan
            ?? (int) explode('/', (string) $angkatan->tahun_ajaran)[0];
        $tahunAjaran = $angkatan->tahun_ajaran ?: $tahunMasuk . '/' . ($tahunMasuk + 1);

        $santri = DB::transaction(function () use ($validated, $noWa, $angkatan, $tahunMasuk, $tahunAjaran) {
            $orangTua = OrangTua::create([
                'nama_ayah' => $validated['nama_ayah'],
                'no_wa_ayah' => $noWa,
                'alamat_domisili_ayah' => $validated['alamat_domisili_ayah'] ?? null,
            ]);

            return Santri::create([
                'nama_lengkap' => $validated['nama_lengkap'],
                'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'nik' => $validated['nik'] ?? null,
                'nisn' => $validated['nisn'] ?? null,
                'sekolah_asal' => $validated['sekolah_asal'] ?? null,
                'npsn' => $validated['npsn'] ?? null,
                'anak_ke' => 1,
                'dari_bersaudara' => 1,
                'tujuan_mendaftar' => 'Input langsung oleh admin' . (!empty($validated['catatan']) ? ': ' . $validated['catatan'] : ''),
                'status' => 'diterima',
                'kelas' => $validated['kelas'],
                'status_santri' => 'aktif',
                'tahun_masuk' => (string) $tahunMasuk,
                'tahun_ajaran' => $tahunAjaran,
                'angkatan_id' => $angkatan->id,
                'orang_tua_id' => $orangTua->id,
            ]);
        });

        // Bila nomor tersebut memang sudah memiliki akun wali, hanya tautkan
        // santrinya. Tidak ada akun baru dan tidak ada pesan WA yang dikirim.
        $existingUser = \App\Models\User::whereIn('no_wa', AkunWaliService::phoneLookupVariants($noWa))->first();
        if ($existingUser) {
            AkunWaliService::linkAllSantriByPhone($noWa, $existingUser->id, $validated['no_wa_ayah']);
        }

        \App\Services\AdminActivityLogger::log(
            'tambah_santri_manual',
            "Menambahkan santri '{$santri->nama_lengkap}' melalui input admin untuk tahun masuk {$tahunMasuk}",
            'Santri',
            $santri->id
        );

        return redirect()->route('admin.santri.index', ['tahun_masuk' => $tahunMasuk])
            ->with('success', 'Data santri berhasil ditambahkan. Akun wali belum dibuat; gunakan tombol Generate Akun jika diperlukan.');
    }
    
    public function show($id)
    {
        $santri = Santri::with(['orangTua', 'dokumen', 'user', 'correctionNotes'])->findOrFail($id);
        return view('admin.santri.show', compact('santri'));
    }

    public function storeCorrectionNote(Request $request, $id)
    {
        $request->validate(['catatan' => ['required', 'string', 'max:2000']]);
        $santri = Santri::findOrFail($id);

        SantriCorrectionNote::where('santri_id', $santri->id)
            ->whereNull('diselesaikan_pada')
            ->update(['diselesaikan_pada' => now()]);

        SantriCorrectionNote::create([
            'santri_id' => $santri->id,
            'admin_id' => auth('admin')->id(),
            'catatan' => $request->catatan,
            'diberikan_pada' => now(),
        ]);

        \App\Services\AdminActivityLogger::log(
            'catatan_perbaikan_santri',
            "Memberikan catatan perbaikan data untuk '{$santri->nama_lengkap}'",
            'Santri', $santri->id
        );

        $santri->load('orangTua');
        \App\Services\NotificationService::send([
            'recipient_number' => $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa,
            'recipient_name' => $santri->orangTua?->nama_ayah,
            'user_id' => $santri->user_id,
            'santri_id' => $santri->id,
            'modul' => 'PPDB',
            'reference_id' => 'PPDB-PERBAIKAN-' . $santri->id . '-' . now()->timestamp,
            'message' => "Assalamu'alaikum. Data pendaftaran {$santri->nama_lengkap} perlu diperbaiki. Catatan panitia: {$request->catatan}",
        ]);

        return back()->with('success', 'Catatan perbaikan telah dikirim ke akun wali santri.');
    }
    
    public function edit($id)
    {
        $santri = Santri::with(['orangTua', 'dokumen'])->findOrFail($id);
        return view('admin.santri.edit', compact('santri'));
    }
    
    public function update(Request $request, $id)
    {
        $santri = Santri::with('orangTua')->findOrFail($id);

        $request->validate([
            // Santri
            'nama_lengkap'       => 'required|string|max:255',
            'tempat_lahir'       => 'nullable|string|max:255',
            'tanggal_lahir'      => 'nullable|date',
            'nik'                => 'nullable|digits:16',
            'sekolah_asal'       => 'nullable|string|max:255',
            'npsn'               => 'nullable|numeric',
            'nsm'                => 'nullable|string|max:20',
            'nisn'               => 'nullable|digits:10',
            'anak_ke'            => 'nullable|integer|min:1',
            'dari_bersaudara'    => 'nullable|integer|min:1',
            'prestasi'           => 'nullable|string',
            'riwayat_penyakit'   => 'nullable|string',
            'hobi'               => 'nullable|string|max:255',
            'ekstrakurikuler'    => 'nullable|array',
            'tujuan_mendaftar'   => 'nullable|string',
            'status'             => 'required|in:menunggu_verifikasi,diverifikasi,diterima,ditolak',
            // Orang Tua - Ayah
            'nama_ayah'              => 'nullable|string|max:255',
            'no_wa_ayah'             => 'nullable|string|max:20',
            'no_kk'                  => 'nullable|digits:16',
            'nik_ayah'               => 'nullable|digits:16',
            'tempat_lahir_ayah'      => 'nullable|string|max:255',
            'tanggal_lahir_ayah'     => 'nullable|date',
            'pekerjaan_ayah'         => 'nullable|string|max:255',
            'penghasilan_ayah'       => 'nullable|string|max:100',
            'alamat_domisili_ayah'   => 'nullable|string',
            // Orang Tua - Ibu
            'nama_ibu'               => 'nullable|string|max:255',
            'nik_ibu'                => 'nullable|digits:16',
            'tempat_lahir_ibu'       => 'nullable|string|max:255',
            'tanggal_lahir_ibu'      => 'nullable|date',
            'pekerjaan_ibu'          => 'nullable|string|max:255',
            'penghasilan_ibu'        => 'nullable|string|max:100',
            'alamat_domisili_ibu'    => 'nullable|string',
            'foto_santri'            => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        // Handle update foto santri jika ada
        if ($request->hasFile('foto_santri')) {
            $path = $request->file('foto_santri')->store('santri/foto', 'public');
            $santri->foto_santri = $path;
            $santri->save();
        }

        // Simpan nomor baru dalam format canonical agar lookup akun konsisten.
        $noWa = \App\Services\WhatsappService::formatPhoneNumber($request->no_wa_ayah);
        if ($request->filled('no_wa_ayah') && !$noWa) {
            return back()->withInput()->withErrors(['no_wa_ayah' => 'Format nomor WhatsApp tidak valid. Gunakan 08xxxxxxxxxx atau 628xxxxxxxxxx.']);
        }

        // Update Santri fields
        $santri->update([
            'nama_lengkap'     => $request->nama_lengkap,
            'tempat_lahir'     => $request->tempat_lahir,
            'tanggal_lahir'    => $request->tanggal_lahir,
            'nik'              => $request->nik,
            'sekolah_asal'     => $request->sekolah_asal,
            'npsn'             => $request->npsn,
            'nsm'              => $request->nsm,
            'nisn'             => $request->nisn,
            'anak_ke'          => $request->anak_ke,
            'dari_bersaudara'  => $request->dari_bersaudara,
            'prestasi'         => $request->prestasi,
            'riwayat_penyakit' => $request->riwayat_penyakit,
            'hobi'             => $request->hobi,
            'ekstrakurikuler'  => $request->ekstrakurikuler ?? [],
            'tujuan_mendaftar' => $request->tujuan_mendaftar,
            'status'           => $request->status,
        ]);

        // Update OrangTua fields
        if ($santri->orangTua) {
            $santri->orangTua->update([
                'nama_ayah'            => $request->nama_ayah,
                'no_wa_ayah'           => $noWa,
                'no_kk'                => $request->no_kk,
                'nik_ayah'             => $request->nik_ayah,
                'tempat_lahir_ayah'    => $request->tempat_lahir_ayah,
                'tanggal_lahir_ayah'   => $request->tanggal_lahir_ayah,
                'pekerjaan_ayah'       => $request->pekerjaan_ayah,
                'penghasilan_ayah'     => $request->penghasilan_ayah,
                'alamat_domisili_ayah' => $request->alamat_domisili_ayah,
                'nama_ibu'             => $request->nama_ibu,
                'nik_ibu'              => $request->nik_ibu,
                'tempat_lahir_ibu'     => $request->tempat_lahir_ibu,
                'tanggal_lahir_ibu'    => $request->tanggal_lahir_ibu,
                'pekerjaan_ibu'        => $request->pekerjaan_ibu,
                'penghasilan_ibu'      => $request->penghasilan_ibu,
                'alamat_domisili_ibu'  => $request->alamat_domisili_ibu,
            ]);
        }

        // Sinkronisasi otomatis ke Akun Login Wali (User) jika nomor WA diubah
        if ($noWa) {
            if ($santri->user) {
                if ($santri->user->no_wa !== $noWa) {
                    // Cek apakah nomor baru sudah dipakai akun user lain
                    $otherUser = \App\Models\User::where('id', '!=', $santri->user_id)
                        ->whereIn('no_wa', \App\Services\AkunWaliService::phoneLookupVariants($noWa))
                        ->first();

                    if ($otherUser) {
                        // Tautkan santri ke akun user yang sudah ada
                        $santri->user_id = $otherUser->id;
                        $santri->save();
                    } else {
                        // Perbarui nomor di akun user saat ini agar wali bisa langsung login & terima OTP di nomor baru
                        $santri->user->update(['no_wa' => $noWa]);
                    }

                    // Sinkronkan ke seluruh data santri saudara kandung yang satu akun wali
                    \App\Models\Santri::where('user_id', $santri->user_id)->get()->each(function ($sibling) use ($noWa) {
                        if ($sibling->orangTua && $sibling->orangTua->no_wa_ayah !== $noWa) {
                            $sibling->orangTua->update(['no_wa_ayah' => $noWa]);
                        }
                    });

                    \App\Services\AdminActivityLogger::log(
                        'sync_santri_user_wa',
                        "Memperbarui nomor WA akun login wali ananda '{$santri->nama_lengkap}' menjadi {$noWa}",
                        'Santri',
                        $santri->id
                    );
                }
            } else {
                // Tautkan otomatis ke akun wali jika nomor ini sudah memiliki user
                $existingUser = \App\Models\User::whereIn('no_wa', \App\Services\AkunWaliService::phoneLookupVariants($noWa))->first();
                if ($existingUser) {
                    \App\Services\AkunWaliService::linkAllSantriByPhone($noWa, $existingUser->id, $request->no_wa_ayah);
                }
            }
        }

        return redirect()->route('admin.santri.index')
            ->with('success', 'Data santri berhasil diperbarui dan nomor WhatsApp akun wali telah disinkronkan.');
    }
    
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:menunggu_verifikasi,diverifikasi,diterima,ditolak'],
        ]);

        $santri = Santri::with('angkatan')->findOrFail($id);
        $oldStatus = $santri->status;
        $updates = ['status' => $validated['status']];

        // Penerimaan adalah titik aktivasi santri. Lengkapi data akademik untuk
        // pendaftaran lama yang masih kosong/masih memakai skema Kelas 7-9.
        if ($validated['status'] === 'diterima') {
            $tahunMasuk = $santri->angkatan?->tahun_angkatan
                ?? ($santri->angkatan?->tahun_ajaran ? (int) explode('/', $santri->angkatan->tahun_ajaran)[0] : (int) date('Y'));

            if (blank($santri->tahun_masuk)) {
                $updates['tahun_masuk'] = (string) $tahunMasuk;
            }

            if (blank($santri->tahun_ajaran)) {
                $updates['tahun_ajaran'] = $tahunMasuk . '/' . ($tahunMasuk + 1);
            }

            if (blank($santri->kelas) || in_array((string) $santri->kelas, ['7', '8', '9'], true)) {
                $updates['kelas'] = '1';
            }

            if (blank($santri->status_santri)) {
                $updates['status_santri'] = 'aktif';
            }
        }

        $santri->update($updates);
        
        \App\Services\AdminActivityLogger::log(
            'update_santri_status',
            "Mengubah status pendaftaran santri '{$santri->nama_lengkap}' dari '{$oldStatus}' menjadi '{$validated['status']}'",
            'Santri',
            $santri->id
        );

        $santri->load('orangTua');
        \App\Services\NotificationService::send([
            'recipient_number' => $santri->orangTua?->no_wa_ayah ?: $santri->user?->no_wa,
            'recipient_name' => $santri->orangTua?->nama_ayah,
            'user_id' => $santri->user_id,
            'santri_id' => $santri->id,
            'modul' => 'PPDB',
            'reference_id' => 'PPDB-STATUS-SANTRI-' . $santri->id . '-' . $validated['status'] . '-' . now()->timestamp,
            'sync' => true,
            'message' => match ($validated['status']) {
                'diterima' => "Alhamdulillah, {$santri->nama_lengkap} dinyatakan diterima sebagai santri. Informasi lanjutan akan disampaikan oleh pondok.",
                'ditolak' => "Mohon maaf, pendaftaran {$santri->nama_lengkap} belum dapat diterima. Silakan hubungi panitia PPDB untuk informasi lebih lanjut.",
                'diverifikasi' => "Data pendaftaran {$santri->nama_lengkap} telah diverifikasi oleh panitia PPDB.",
                default => "Data pendaftaran {$santri->nama_lengkap} sedang dalam proses verifikasi.",
            },
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui'
        ]);
    }

    /**
     * Ubah status santri menjadi Mutasi (keluar/pindah pondok) atau aktifkan kembali.
     */
    public function updateMutasi(Request $request, $id)
    {
        $validated = $request->validate([
            'status_santri'     => ['required', 'in:aktif,mutasi'],
            'keterangan_mutasi' => ['nullable', 'string', 'max:500'],
            'tanggal_mutasi'    => ['nullable', 'date'],
        ]);

        $santri = Santri::findOrFail($id);
        $isMutasi = $validated['status_santri'] === 'mutasi';

        if ($isMutasi && $santri->status !== 'diterima') {
            return back()->with('error', 'Status mutasi hanya dapat diberikan kepada santri yang sudah berstatus Diterima.');
        }

        if ($isMutasi && empty(trim($validated['keterangan_mutasi'] ?? ''))) {
            return back()->with('error', 'Keterangan atau alasan mutasi wajib diisi.');
        }

        $santri->update([
            'status_santri'     => $validated['status_santri'],
            'keterangan_mutasi' => $isMutasi ? trim($validated['keterangan_mutasi']) : null,
            'tanggal_mutasi'    => $isMutasi ? ($validated['tanggal_mutasi'] ?? now()->toDateString()) : null,
        ]);

        \App\Services\AdminActivityLogger::log(
            'update_mutasi_santri',
            $isMutasi 
                ? "Mengubah status santri '{$santri->nama_lengkap}' menjadi MUTASI. Alasan: {$santri->keterangan_mutasi}"
                : "Mengembalikan status santri '{$santri->nama_lengkap}' menjadi AKTIF",
            'Santri',
            $santri->id
        );

        $pesan = $isMutasi 
            ? "Santri {$santri->nama_lengkap} berhasil diubah statusnya menjadi MUTASI (Alasan: {$santri->keterangan_mutasi}). Tagihan SPP bulan berikutnya tidak akan diterbitkan."
            : "Santri {$santri->nama_lengkap} berhasil dikembalikan ke status AKTIF.";

        return back()->with('success', $pesan);
    }
    
    public function destroy($id)
    {
        if (!auth('admin')->user()?->isSuperAdmin()) {
            return redirect()->route('admin.santri.index')
                ->with('error', 'Akses ditolak: Hanya Super Admin yang berwenang menghapus data santri.');
        }

        $santri = Santri::findOrFail($id);
        $santriNama = $santri->nama_lengkap;

        $santri->delete();

        \App\Services\AdminActivityLogger::log(
            'soft_delete_santri',
            "Memindahkan santri '{$santriNama}' ke Tempat Sampah",
            'Santri',
            $id
        );
        
        return redirect()->route('admin.santri.index')
            ->with('success', "Santri '{$santriNama}' berhasil dipindahkan ke Tempat Sampah. Anda dapat memulihkannya kapan saja di menu Tempat Sampah.");
    }

    /**
     * Halaman Tempat Sampah (Recycle Bin) Santri
     */
    public function trash(Request $request)
    {
        if (!auth('admin')->user()?->isSuperAdmin()) {
            abort(403, 'Akses khusus Super Admin.');
        }

        $query = Santri::onlyTrashed()->with(['orangTua', 'angkatan']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhereHas('orangTua', function ($oq) use ($search) {
                      $oq->where('nama_ayah', 'like', "%{$search}%");
                  });
            });
        }

        $trashedSantri = $query->orderByDesc('deleted_at')->paginate(20)->withQueryString();

        return view('admin.santri.trash', compact('trashedSantri'));
    }

    /**
     * Pulihkan santri dari Tempat Sampah ke data aktif
     */
    public function restore($id)
    {
        if (!auth('admin')->user()?->isSuperAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $santri = Santri::onlyTrashed()->findOrFail($id);
        $santriNama = $santri->nama_lengkap;
        $santri->restore();

        \App\Services\AdminActivityLogger::log(
            'restore_santri',
            "Memulihkan santri '{$santriNama}' dari Tempat Sampah",
            'Santri',
            $id
        );

        return redirect()->route('admin.santri.trash')
            ->with('success', "Data santri '{$santriNama}' berhasil dipulihkan kembali ke data aktif!");
    }

    /**
     * Pulihkan SEMUA santri di Tempat Sampah
     */
    public function restoreAll()
    {
        if (!auth('admin')->user()?->isSuperAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $count = Santri::onlyTrashed()->count();
        Santri::onlyTrashed()->restore();

        \App\Services\AdminActivityLogger::log(
            'restore_all_santri',
            "Memulihkan seluruh ({$count}) santri dari Tempat Sampah",
            'Santri'
        );

        return redirect()->route('admin.santri.trash')
            ->with('success', "Berhasil memulihkan seluruh {$count} data santri dari Tempat Sampah!");
    }

    /**
     * Hapus permanen santri dari database
     */
    public function forceDelete($id)
    {
        if (!auth('admin')->user()?->isSuperAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $santri = Santri::onlyTrashed()->findOrFail($id);
        $santriNama = $santri->nama_lengkap;
        $orangTuaId = $santri->orang_tua_id;

        if ($santri->dokumen) {
            $santri->dokumen->delete();
        }
        $santri->uangSaku()->delete();

        $santri->forceDelete();

        if ($orangTuaId && !Santri::withTrashed()->where('orang_tua_id', $orangTuaId)->exists()) {
            OrangTua::where('id', $orangTuaId)->delete();
        }

        \App\Services\AdminActivityLogger::log(
            'force_delete_santri',
            "Menghapus permanen santri '{$santriNama}' dari database",
            'Santri',
            $id
        );

        return redirect()->route('admin.santri.trash')
            ->with('success', "Data santri '{$santriNama}' berhasil dihapus permanen dari database.");
    }

    /**
     * Kosongkan Tempat Sampah (Hapus permanen semua data di tempat sampah)
     */
    public function emptyTrash()
    {
        if (!auth('admin')->user()?->isSuperAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $trashed = Santri::onlyTrashed()->get();
        $count = $trashed->count();

        foreach ($trashed as $santri) {
            $orangTuaId = $santri->orang_tua_id;
            if ($santri->dokumen) {
                $santri->dokumen->delete();
            }
            $santri->uangSaku()->delete();
            $santri->forceDelete();

            if ($orangTuaId && !Santri::withTrashed()->where('orang_tua_id', $orangTuaId)->exists()) {
                OrangTua::where('id', $orangTuaId)->delete();
            }
        }

        \App\Services\AdminActivityLogger::log(
            'empty_trash_santri',
            "Mengosongkan Tempat Sampah ({$count} santri dihapus permanen)",
            'Santri'
        );

        return redirect()->route('admin.santri.trash')
            ->with('success', "Tempat Sampah berhasil dikosongkan ({$count} data santri telah dimusnahkan secara permanen).");
    }
    
    public function pollLatest()
    {
        $latest = \App\Models\Santri::orderBy('id', 'desc')->first(['id', 'created_at']);
        $total  = \App\Models\Santri::count();

        return response()->json([
            'latest_id' => $latest ? $latest->id : 0,
            'total'     => $total,
        ]);
    }

    public function downloadTemplate()
    {
        return Excel::download(new SantriTemplateExport(), 'template_import_santri.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ], [
            'file_excel.required' => 'Pilih file Excel terlebih dahulu.',
            'file_excel.mimes'    => 'Format file harus berupa XLSX, XLS, atau CSV.',
        ]);

        $import = new SantriImport();
        
        try {
            Excel::import($import, $request->file('file_excel'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }

        $successCount = $import->getSuccessCount();
        $skippedCount = $import->getSkippedCount();
        $errors       = $import->getErrors();

        \App\Services\AdminActivityLogger::log(
            'import_santri',
            "Mengimpor {$successCount} data santri secara massal (Dilewati/Gagal: {$skippedCount})",
            'Santri'
        );

        $message = "Import selesai! {$successCount} data santri berhasil diimport, {$skippedCount} data gagal/dilewati.";

        if (!empty($errors)) {
            return back()
                ->with('success', $message)
                ->with('import_errors', $errors);
        }

        return back()->with('success', $message);
    }

    public function exportExcel(Request $request)
    {
        $filename = 'data_santri_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new SantriExport(
            $request->input('kelas'),
            $request->input('angkatan_id'),
            $request->input('tahun_ajaran'),
            $request->input('status_santri', 'aktif'),
            $request->input('tahun_masuk'),
        ), $filename);
    }
    
    public function exportPDF(Request $request)
    {
        $query = Santri::with(['orangTua', 'dokumen']);

        $tahun = $request->input('tahun_masuk') ?: $request->input('tahun_ajaran');

        if ($tahun === '__belum_ditentukan__') {
            $query->whereNull('tahun_masuk');
            $labelTahun = 'Belum Ditentukan';
        } elseif ($tahun) {
            $query->where(function ($q) use ($tahun) {
                $q->where('tahun_masuk', $tahun)
                  ->orWhere('tahun_ajaran', $tahun)
                  ->orWhere('tahun_ajaran', 'like', "{$tahun}%");
            });
            $labelTahun = $tahun;
        } else {
            $labelTahun = 'Semua Santri';
        }

        if ($request->filled('kelas') && $request->kelas !== 'all') {
            $query->where('kelas', $request->kelas);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $santri = $query->orderBy('kelas')->orderBy('nama_lengkap')->get();
        $tahunAjaran = $labelTahun;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.santri.pdf', compact('santri', 'tahunAjaran'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('santri_' . str_replace(['/', ' '], '-', $labelTahun) . '_' . date('Y-m-d') . '.pdf');
    }

    public function exportSinglePDF($id)
    {
        $santri = Santri::with(['orangTua', 'dokumen'])->findOrFail($id);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.santri.single_pdf', compact('santri'));
        
        $fileName = 'Formulir_' . \Str::slug($santri->nama_lengkap) . '_' . date('Ymd') . '.pdf';
        
        return $pdf->download($fileName);
    }

    public function exportSingleExcel($id)
    {
        $santri = Santri::findOrFail($id);
        $fileName = 'Data_Santri_' . \Str::slug($santri->nama_lengkap) . '_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new SantriExport(null, null, null, null, null, $santri->id), $fileName);
    }

    public function cetakKartu($id)
    {
        $santri = Santri::with(['orangTua', 'angkatan'])->findOrFail($id);
        $santris = collect([$santri]);
        $isMassal = false;

        return view('admin.santri.kartu', compact('santri', 'santris', 'isMassal'));
    }

    public function cetakKartuMassal(Request $request)
    {
        $query = Santri::aktif()->with(['orangTua', 'angkatan']);

        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        if ($request->filled('tahun_masuk')) {
            if ($request->tahun_masuk === '__belum_ditentukan__') {
                $query->whereNull('tahun_masuk');
            } else {
                $query->where('tahun_masuk', $request->tahun_masuk);
            }
        }

        if ($request->filled('angkatan_id')) {
            $query->where('angkatan_id', $request->angkatan_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $santris = $query->orderBy('kelas')->orderBy('nama_lengkap')->get();
        $isMassal = true;
        $santri = $santris->first();
        $filters = [
            'kelas'       => $request->kelas,
            'tahun_masuk' => $request->tahun_masuk,
        ];

        return view('admin.santri.kartu', compact('santri', 'santris', 'isMassal', 'filters'));
    }
}
