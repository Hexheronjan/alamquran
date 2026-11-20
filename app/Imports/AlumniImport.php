<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AlumniImport implements WithMultipleSheets
{
    private AlumniSingleSheetImport $sheetImport;

    public function __construct()
    {
        $this->sheetImport = new AlumniSingleSheetImport();
    }

    public function sheets(): array
    {
        return [
            0 => $this->sheetImport,
        ];
    }

    public function getSuccessCount(): int
    {
        return $this->sheetImport->getSuccessCount();
    }

    public function getSkippedCount(): int
    {
        return $this->sheetImport->getSkippedCount();
    }

    public function getErrors(): array
    {
        return $this->sheetImport->getErrors();
    }
}
