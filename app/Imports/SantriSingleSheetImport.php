<?php

namespace App\Imports;

use App\Models\OrangTua;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;

class SantriSingleSheetImport implements ToCollection
{
    public int $successCount   = 0;
    public int $skippedCount   = 0;
    public int $duplicateCount = 0;
    public array $errors       = [];

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        // 1. Cari baris header yang berisi nama kolom (misal: NAMA / NAMA SANTRI / NAMA LENGKAP)
        $headerRowIndex = null;
        $colMap = [];

        foreach ($rows as $index => $row) {
            $rowArray = $row->toArray();
            $rowLower = array_map(function ($val) {
                return strtolower(trim(preg_replace('/\s+/', ' ', (string) $val)));
            }, $rowArray);

            foreach ($rowLower as $cIdx => $cVal) {
                if (in_array($cVal, ['nama', 'nama santri', 'nama lengkap', 'nama_santri', 'nama_lengkap'])) {
                    $headerRowIndex = $index;
                    break;
                }
            }

            if ($headerRowIndex !== null) {
                foreach ($rowLower as $cIdx => $cVal) {
                    $cleanVal = strtolower(trim(preg_replace('/\s+/', ' ', (string) $cVal)));

                    if (in_array($cleanVal, ['nama', 'nama santri', 'nama lengkap', 'nama_santri', 'nama_lengkap'])) {
                        $colMap['nama'] = $cIdx;
                    } elseif (in_array($cleanVal, ['tahun masuk', 'tahun_masuk', 'tahun angkatan', 'tahun_angkatan', 'thn masuk', 'thn_masuk'])) {
                        $colMap['tahun_masuk'] = $cIdx;
                    } elseif (in_array($cleanVal, ['ttl', 'tempat lahir', 'tempat_lahir', 'tempat tanggal lahir'])) {
                        $colMap['tempat_lahir'] = $cIdx;
                        if (!isset($colMap['tanggal_lahir'])) {
                            $colMap['tanggal_lahir'] = $cIdx + 1;
                        }
                    } elseif (in_array($cleanVal, ['tanggal lahir', 'tanggal_lahir', 'tgl lahir', 'tgl_lahir'])) {
                        $colMap['tanggal_lahir'] = $cIdx;
                    } elseif ($cleanVal === 'nik') {
                        $colMap['nik'] = $cIdx;
                    } elseif ($cleanVal === 'nisn') {
                        $colMap['nisn'] = $cIdx;
                    } elseif (in_array($cleanVal, ['no induk', 'no_induk', 'nis', 'no. induk', 'no induk/nis', 'no_induk_nis'])) {
                        $colMap['nis'] = $cIdx;
                    } elseif (in_array($cleanVal, ['ayah', 'nama ayah', 'nama_ayah'])) {
                        $colMap['nama_ayah'] = $cIdx;
                    } elseif (in_array($cleanVal, ['ibu', 'nama ibu', 'nama_ibu'])) {
                        $colMap['nama_ibu'] = $cIdx;
                    } elseif (in_array($cleanVal, ['wali', 'nama wali', 'nama_wali'])) {
                        $colMap['nama_wali'] = $cIdx;
                    } elseif (in_array($cleanVal, ['no. hp', 'no hp', 'no_hp', 'no wa', 'no_wa', 'no wa wali', 'no_wa_wali', 'no wa ayah', 'no_wa_ayah', 'telepon'])) {
                        $colMap['no_wa'] = $cIdx;
                    } elseif (in_array($cleanVal, ['alamat', 'alamat domisili', 'alamat_domisili'])) {
                        $colMap['alamat'] = $cIdx;
                    } elseif ($cleanVal === 'kelas') {
                        $colMap['kelas'] = $cIdx;
                    } elseif (in_array($cleanVal, ['tahun ajaran', 'tahun_ajaran'])) {
                        $colMap['tahun_ajaran'] = $cIdx;
                    }
                }
                break;
            }
        }

        // Fallback jika tidak ditemukan header jelas
        if (!isset($colMap['nama']))          $colMap['nama']          = 2;
        if (!isset($colMap['tahun_masuk']))   $colMap['tahun_masuk']   = 1;
        if (!isset($colMap['tempat_lahir']))  $colMap['tempat_lahir']  = 3;
        if (!isset($colMap['tanggal_lahir'])) $colMap['tanggal_lahir'] = 4;
        if (!isset($colMap['nik']))           $colMap['nik']           = 5;
        if (!isset($colMap['nisn']))          $colMap['nisn']          = 6;
        if (!isset($colMap['nis']))           $colMap['nis']           = 7;
        if (!isset($colMap['nama_ayah']))     $colMap['nama_ayah']     = 8;
        if (!isset($colMap['nama_ibu']))      $colMap['nama_ibu']      = 9;
        if (!isset($colMap['alamat']))        $colMap['alamat']        = 10;
        if (!isset($colMap['no_wa']))         $colMap['no_wa']         = 11;

        if ($headerRowIndex === null) {
            $headerRowIndex = 0;
        }

        $lastTahunMasuk = (string) date('Y');
        $seenNiks  = [];
        $seenNisns = [];
        $seenNis   = [];

        // 2. Iterasi baris-baris data
        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i]->toArray();
            $excelRowNumber = $i + 1;

            $namaLengkap = isset($colMap['nama']) && isset($row[$colMap['nama']]) ? trim((string) $row[$colMap['nama']]) : '';

            // Abaikan baris kosong, header terulang, atau baris TOTAL/JUMLAH di bagian bawah
            if (
                empty($namaLengkap) ||
                in_array(strtoupper($namaLengkap), ['NAMA', 'NAMA SANTRI', 'NAMA LENGKAP', 'TOTAL', 'JUMLAH', 'SUBTOTAL', 'REKAPITULASI'])
            ) {
                continue;
            }

            // --- TAHUN MASUK (Carry Forward) ---
            $tahunMasukRaw = isset($colMap['tahun_masuk']) && isset($row[$colMap['tahun_masuk']]) ? trim((string) $row[$colMap['tahun_masuk']]) : '';
            
            if (preg_match('/^(19|20)\d{2}$/', $tahunMasukRaw)) {
                $lastTahunMasuk = $tahunMasukRaw;
                $tahunMasuk     = $tahunMasukRaw;
            } else {
                $tahunMasuk = $lastTahunMasuk;
            }

            // --- NIK, NISN, NIS ---
            $nikRaw  = isset($colMap['nik'])  && isset($row[$colMap['nik']])  ? trim((string) $row[$colMap['nik']])  : null;
            $nisnRaw = isset($colMap['nisn']) && isset($row[$colMap['nisn']]) ? trim((string) $row[$colMap['nisn']]) : null;
            $nisRaw  = isset($colMap['nis'])  && isset($row[$colMap['nis']])  ? trim((string) $row[$colMap['nis']])  : null;

            $nik  = $nikRaw  ? $this->cleanNumber($nikRaw)  : null;
            $nisn = $nisnRaw ? $this->cleanNumber($nisnRaw) : null;
            $nis  = $nisRaw  ? $this->cleanNumber($nisRaw)  : null;

            // Data ganda di dalam file tetap dilewati. Jika data sudah ada di
            // database, data akademiknya akan diperbarui agar tahun masuk dari
            // Excel tetap menjadi sumber yang benar.

            // --- TEMPAT & TANGGAL LAHIR ---
            $tempatLahir = isset($colMap['tempat_lahir']) && isset($row[$colMap['tempat_lahir']]) ? trim((string) $row[$colMap['tempat_lahir']]) : null;
            $tglLahirRaw = isset($colMap['tanggal_lahir']) && isset($row[$colMap['tanggal_lahir']]) ? $row[$colMap['tanggal_lahir']] : null;

            // Jika Tempat Lahir berisi gabungan "Ponorogo 19/10/2003"
            if ($tempatLahir && preg_match('/^(.*?)\s*,?\s*(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4})$/', $tempatLahir, $matches)) {
                $tempatLahir = trim($matches[1]);
                $tglLahirRaw = trim($matches[2]);
            }

            $parsedDate = $this->parseDate($tglLahirRaw);
            $existingSantri = $this->findExistingSantri($nik, $nisn, $nis, $namaLengkap);

            // --- DATA ORANG TUA ---
            $namaAyah = isset($colMap['nama_ayah']) && isset($row[$colMap['nama_ayah']]) ? trim((string) $row[$colMap['nama_ayah']]) : null;
            $namaIbu  = isset($colMap['nama_ibu'])  && isset($row[$colMap['nama_ibu']])  ? trim((string) $row[$colMap['nama_ibu']])  : null;
            $namaWali = isset($colMap['nama_wali']) && isset($row[$colMap['nama_wali']]) ? trim((string) $row[$colMap['nama_wali']]) : null;
            $noWaRaw  = isset($colMap['no_wa'])    && isset($row[$colMap['no_wa']])    ? trim((string) $row[$colMap['no_wa']])    : null;
            $alamat   = isset($colMap['alamat'])   && isset($row[$colMap['alamat']])   ? trim((string) $row[$colMap['alamat']])   : null;

            $noWa = $noWaRaw ? $this->normalizePhone($noWaRaw) : null;

            $orangTua = $existingSantri?->orangTua;
            $user     = $existingSantri?->user;

            try {
                if (! $orangTua && ($namaAyah || $namaIbu || $namaWali || $noWa || $alamat)) {
                    $orangTua = OrangTua::create([
                        'nama_ayah'            => $namaAyah ?: ($namaWali ?: 'Wali ' . $namaLengkap),
                        'nama_ibu'             => $namaIbu ?: null,
                        'no_wa_ayah'           => $noWa ?: '-',
                        'alamat_domisili_ayah' => $alamat,
                    ]);

                    if ($noWa) {
                        $user = User::where('no_wa', $noWa)->first();
                        if (!$user) {
                            $email = 'santri_' . ($nisn ?: ($nis ?: uniqid())) . '@alamquran.sch.id';
                            if (User::where('email', $email)->exists()) {
                                $email = 'santri_' . uniqid() . '@alamquran.sch.id';
                            }
                            $user = User::create([
                                'name'     => $namaAyah ?: ($namaWali ?: 'Wali ' . $namaLengkap),
                                'email'    => $email,
                                'no_wa'    => $noWa,
                                'password' => Hash::make('santri' . date('Y')),
                            ]);
                        }
                    }
                }

                // --- OTOMATISASI KELAS, STATUS SANTRI, DAN ALUMNI BERDASARKAN TAHUN MASUK ---
                $explicitKelas = isset($colMap['kelas']) && isset($row[$colMap['kelas']]) ? strtolower(trim((string) $row[$colMap['kelas']])) : null;
                $currentYear   = (int) date('Y');
                $entryYear     = (int) $tahunMasuk;

                $kelas        = '1';
                $statusSantri = 'aktif';
                $tahunLulus   = null;

                if ($explicitKelas === 'alumni' || $explicitKelas === 'lulus') {
                    $kelas        = 'alumni';
                    $statusSantri = 'lulus';
                    $tahunLulus   = (string) ($entryYear + 3);
                } elseif (in_array($explicitKelas, ['1', '2', '3'])) {
                    $kelas        = $explicitKelas;
                    $statusSantri = 'aktif';
                } else {
                    // Kelas dihitung berdasarkan tahun ajaran berjalan:
                    // tahun masuk = Kelas 1, +1 tahun = Kelas 2, +2 tahun = Kelas 3.
                    // Contoh pada 2026: 2024 = Kelas 3, 2025 = Kelas 2,
                    // 2026 = Kelas 1, dan 2023 ke bawah sudah alumni.
                    if ($entryYear <= ($currentYear - 3)) {
                        $kelas        = 'alumni';
                        $statusSantri = 'lulus';
                        $tahunLulus   = (string) ($entryYear + 3);
                    } elseif ($entryYear === ($currentYear - 2)) {
                        $kelas        = '3';
                        $statusSantri = 'aktif';
                    } elseif ($entryYear === ($currentYear - 1)) {
                        $kelas        = '2';
                        $statusSantri = 'aktif';
                    } else {
                        $kelas        = '1';
                        $statusSantri = 'aktif';
                    }
                }

                $tahunAjaran = isset($colMap['tahun_ajaran']) && isset($row[$colMap['tahun_ajaran']]) ? trim((string) $row[$colMap['tahun_ajaran']]) : ($tahunMasuk . '/' . ((int)$tahunMasuk + 1));

                $santriData = [
                    'nama_lengkap'  => $namaLengkap,
                    'nik'           => $nik,
                    'nisn'          => $nisn,
                    'nis'           => $nis,
                    'tempat_lahir'  => $tempatLahir,
                    'tanggal_lahir' => $parsedDate,
                    'kelas'         => $kelas,
                    'status_santri' => $statusSantri,
                    'tahun_lulus'   => $tahunLulus,
                    'status'        => 'diterima',
                    'tahun_masuk'   => $tahunMasuk,
                    'tahun_ajaran'  => $tahunAjaran,
                    'orang_tua_id'  => $orangTua?->id,
                    'user_id'       => $user?->id,
                ];

                if ($existingSantri) {
                    // Sinkronkan kolom yang memang tersedia di Excel, tanpa
                    // menghapus data tambahan apabila sel Excel-nya kosong.
                    $existingSantri->update(array_filter(
                        $santriData,
                        static fn ($value) => $value !== null && $value !== ''
                    ));
                } else {
                    Santri::create($santriData);
                }

                if ($nik)  $seenNiks[]  = $nik;
                if ($nisn) $seenNisns[] = $nisn;
                if ($nis)  $seenNis[]   = $nis;

                $this->successCount++;
            } catch (\Throwable $e) {
                $this->skippedCount++;
                $this->errors[] = [
                    'row'   => $excelRowNumber,
                    'nama'  => $namaLengkap,
                    'error' => 'Gagal menyimpan: ' . $e->getMessage(),
                ];
            }
        }
    }

    private function cleanNumber(string $val): string
    {
        if (str_contains(strtoupper($val), 'E+')) {
            $val = sprintf('%.0f', (float) $val);
        }
        return preg_replace('/[^0-9]/', '', $val);
    }

    private function findExistingSantri(?string $nik, ?string $nisn, ?string $nis, string $namaLengkap): ?Santri
    {
        // NIS adalah nomor induk per santri dalam file sumber dan menjadi
        // pencocokan utama saat impor ulang. NIK/NISN dapat berulang di file.
        foreach ([['nis', $nis], ['nik', $nik], ['nisn', $nisn]] as [$column, $value]) {
            if ($value && ($santri = Santri::where($column, $value)->first())) {
                return $santri;
            }
        }

        // Data pendaftaran yang telah dibuat lebih dahulu kadang hanya berisi
        // nama. Cocokkan nama persis sebagai fallback agar data Excel melengkapi
        // profil yang sama, bukan membuat santri ganda.
        return Santri::query()
            ->whereRaw('LOWER(TRIM(nama_lengkap)) = ?', [mb_strtolower(trim($namaLengkap))])
            ->whereNull('nik')
            ->whereNull('nisn')
            ->whereNull('nis')
            ->first();
    }

    private function parseDate($val): ?string
    {
        if (empty($val)) return null;

        if ($val instanceof \DateTimeInterface) {
            return $val->format('Y-m-d');
        }

        $valStr = trim((string) $val);
        if (empty($valStr)) return null;

        if (is_numeric($valStr) && (float)$valStr > 10000 && (float)$valStr < 60000) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$valStr)->format('Y-m-d');
            } catch (\Throwable $e) {
                // abaikan jika gagal
            }
        }

        $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'd.m.Y', 'Y/m/d'];
        foreach ($formats as $fmt) {
            $d = \DateTime::createFromFormat($fmt, $valStr);
            if ($d && $d->format($fmt) === $valStr) {
                return $d->format('Y-m-d');
            }
        }

        if (strtotime($valStr)) {
            return date('Y-m-d', strtotime($valStr));
        }

        return null;
    }

    private function normalizePhone(string $phone): ?string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (empty($cleaned)) return null;
        if (str_starts_with($cleaned, '08')) {
            return '628' . substr($cleaned, 2);
        }
        if (str_starts_with($cleaned, '8') && strlen($cleaned) >= 10) {
            return '628' . substr($cleaned, 1);
        }
        return $cleaned;
    }
}
