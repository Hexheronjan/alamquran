<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SantriImport implements WithMultipleSheets
{
    private SantriSingleSheetImport $sheetImport;

    public function __construct()
    {
        $this->sheetImport = new SantriSingleSheetImport();
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
