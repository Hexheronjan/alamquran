<?php

namespace App\Imports;

use App\Models\OrangTua;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;

class AlumniSingleSheetImport implements ToCollection
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

        $headerRowIndex = null;
        $colMap = [];

        foreach ($rows as $index => $row) {
            $rowArray = $row->toArray();
            $rowLower = array_map(function ($val) {
                return strtolower(trim(preg_replace('/\s+/', ' ', (string) $val)));
            }, $rowArray);

            foreach ($rowLower as $cIdx => $cVal) {
                if (in_array($cVal, ['nama', 'nama alumni', 'nama santri', 'nama lengkap', 'nama_santri', 'nama_lengkap'])) {
                    $headerRowIndex = $index;
                    break;
                }
            }

            if ($headerRowIndex !== null) {
                foreach ($rowLower as $cIdx => $cVal) {
                    $cleanVal = strtolower(trim(preg_replace('/\s+/', ' ', (string) $cVal)));

                    if (in_array($cleanVal, ['nama', 'nama alumni', 'nama santri', 'nama lengkap', 'nama_santri', 'nama_lengkap'])) {
                        $colMap['nama'] = $cIdx;
                    } elseif (in_array($cleanVal, ['tahun masuk', 'tahun_masuk', 'thn masuk', 'thn_masuk'])) {
                        $colMap['tahun_masuk'] = $cIdx;
                    } elseif (in_array($cleanVal, ['tahun lulus', 'tahun_lulus', 'thn lulus', 'thn_lulus'])) {
                        $colMap['tahun_lulus'] = $cIdx;
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
                    } elseif (in_array($cleanVal, ['no induk', 'no_induk', 'nis', 'no. induk', 'no induk/nis'])) {
                        $colMap['nis'] = $cIdx;
                    } elseif (in_array($cleanVal, ['ayah', 'nama ayah', 'nama_ayah'])) {
                        $colMap['nama_ayah'] = $cIdx;
                    } elseif (in_array($cleanVal, ['ibu', 'nama ibu', 'nama_ibu'])) {
                        $colMap['nama_ibu'] = $cIdx;
                    } elseif (in_array($cleanVal, ['no. hp', 'no hp', 'no_hp', 'no wa', 'no_wa', 'no wa wali', 'no_wa_wali', 'no wa ayah', 'no_wa_ayah'])) {
                        $colMap['no_wa'] = $cIdx;
                    } elseif (in_array($cleanVal, ['alamat', 'alamat domisili', 'alamat_domisili'])) {
                        $colMap['alamat'] = $cIdx;
                    }
                }
                break;
            }
        }

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

        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i]->toArray();
            $excelRowNumber = $i + 1;

            $namaLengkap = isset($colMap['nama']) && isset($row[$colMap['nama']]) ? trim((string) $row[$colMap['nama']]) : '';

            if (
                empty($namaLengkap) ||
                in_array(strtoupper($namaLengkap), ['NAMA', 'NAMA ALUMNI', 'NAMA SANTRI', 'NAMA LENGKAP', 'TOTAL', 'JUMLAH', 'SUBTOTAL', 'REKAPITULASI'])
            ) {
                continue;
            }

            // --- TAHUN MASUK & LULUS ---
            $tahunMasukRaw = isset($colMap['tahun_masuk']) && isset($row[$colMap['tahun_masuk']]) ? trim((string) $row[$colMap['tahun_masuk']]) : '';
            if (preg_match('/^(19|20)\d{2}$/', $tahunMasukRaw)) {
                $lastTahunMasuk = $tahunMasukRaw;
                $tahunMasuk     = $tahunMasukRaw;
            } else {
                $tahunMasuk = $lastTahunMasuk;
            }

            $tahunLulus = isset($colMap['tahun_lulus']) && isset($row[$colMap['tahun_lulus']]) ? trim((string) $row[$colMap['tahun_lulus']]) : ((string)((int)$tahunMasuk + 3));

            // --- NIK, NISN, NIS ---
            $nikRaw  = isset($colMap['nik'])  && isset($row[$colMap['nik']])  ? trim((string) $row[$colMap['nik']])  : null;
            $nisnRaw = isset($colMap['nisn']) && isset($row[$colMap['nisn']]) ? trim((string) $row[$colMap['nisn']]) : null;
            $nisRaw  = isset($colMap['nis'])  && isset($row[$colMap['nis']])  ? trim((string) $row[$colMap['nis']])  : null;

            $nik  = $nikRaw  ? $this->cleanNumber($nikRaw)  : null;
            $nisn = $nisnRaw ? $this->cleanNumber($nisnRaw) : null;
            $nis  = $nisRaw  ? $this->cleanNumber($nisRaw)  : null;

            if ($nik && (in_array($nik, $seenNiks) || Santri::where('nik', $nik)->exists())) {
                $this->duplicateCount++;
                $this->skippedCount++;
                $this->errors[] = [
                    'row'   => $excelRowNumber,
                    'nama'  => $namaLengkap,
                    'error' => "NIK '{$nik}' sudah terdaftar (duplikat).",
                ];
                continue;
            }

            if ($nisn && (in_array($nisn, $seenNisns) || Santri::where('nisn', $nisn)->exists())) {
                $this->duplicateCount++;
                $this->skippedCount++;
                $this->errors[] = [
                    'row'   => $excelRowNumber,
                    'nama'  => $namaLengkap,
                    'error' => "NISN '{$nisn}' sudah terdaftar (duplikat).",
                ];
                continue;
            }

            if ($nis && (in_array($nis, $seenNis) || Santri::where('nis', $nis)->exists())) {
                $this->duplicateCount++;
                $this->skippedCount++;
                $this->errors[] = [
                    'row'   => $excelRowNumber,
                    'nama'  => $namaLengkap,
                    'error' => "NIS '{$nis}' sudah terdaftar (duplikat).",
                ];
                continue;
            }

            // --- TEMPAT & TANGGAL LAHIR ---
            $tempatLahir = isset($colMap['tempat_lahir']) && isset($row[$colMap['tempat_lahir']]) ? trim((string) $row[$colMap['tempat_lahir']]) : null;
            $tglLahirRaw = isset($colMap['tanggal_lahir']) && isset($row[$colMap['tanggal_lahir']]) ? $row[$colMap['tanggal_lahir']] : null;

            if ($tempatLahir && preg_match('/^(.*?)\s*,?\s*(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4})$/', $tempatLahir, $matches)) {
                $tempatLahir = trim($matches[1]);
                $tglLahirRaw = trim($matches[2]);
            }

            $parsedDate = $this->parseDate($tglLahirRaw);

            // --- DATA ORANG TUA ---
            $namaAyah = isset($colMap['nama_ayah']) && isset($row[$colMap['nama_ayah']]) ? trim((string) $row[$colMap['nama_ayah']]) : null;
            $namaIbu  = isset($colMap['nama_ibu'])  && isset($row[$colMap['nama_ibu']])  ? trim((string) $row[$colMap['nama_ibu']])  : null;
            $noWaRaw  = isset($colMap['no_wa'])    && isset($row[$colMap['no_wa']])    ? trim((string) $row[$colMap['no_wa']])    : null;
            $alamat   = isset($colMap['alamat'])   && isset($row[$colMap['alamat']])   ? trim((string) $row[$colMap['alamat']])   : null;

            $noWa = $noWaRaw ? $this->normalizePhone($noWaRaw) : null;

            $orangTua = null;
            $user     = null;

            try {
                if ($namaAyah || $namaIbu || $noWa || $alamat) {
                    $orangTua = OrangTua::create([
                        'nama_ayah'            => $namaAyah ?: ('Wali ' . $namaLengkap),
                        'nama_ibu'             => $namaIbu ?: null,
                        'no_wa_ayah'           => $noWa ?: '-',
                        'alamat_domisili_ayah' => $alamat,
                    ]);

                    if ($noWa) {
                        $user = User::where('no_wa', $noWa)->first();
                        if (!$user) {
                            $email = 'alumni_' . ($nisn ?: ($nis ?: uniqid())) . '@alamquran.sch.id';
                            if (User::where('email', $email)->exists()) {
                                $email = 'alumni_' . uniqid() . '@alamquran.sch.id';
                            }
                            $user = User::create([
                                'name'     => $namaAyah ?: ('Wali ' . $namaLengkap),
                                'email'    => $email,
                                'no_wa'    => $noWa,
                                'password' => Hash::make('alumni' . $tahunLulus),
                            ]);
                        }
                    }
                }

                Santri::create([
                    'nama_lengkap'  => $namaLengkap,
                    'nik'           => $nik,
                    'nisn'          => $nisn,
                    'nis'           => $nis,
                    'tempat_lahir'  => $tempatLahir,
                    'tanggal_lahir' => $parsedDate,
                    'kelas'         => 'alumni',
                    'status_santri' => 'lulus',
                    'status'        => 'diterima',
                    'tahun_masuk'   => $tahunMasuk,
                    'tahun_lulus'   => $tahunLulus,
                    'orang_tua_id'  => $orangTua?->id,
                    'user_id'       => $user?->id,
                ]);

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
                // ignore
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
