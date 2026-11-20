<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convert the previous 7-9 class scheme to the new 1-3 scheme without
     * altering alumni records.
     */
    public function up(): void
    {
        $this->convertColumn('santri', 'kelas', ['7' => '1', '8' => '2', '9' => '3']);
        $this->convertColumn('kelas_histories', 'kelas_dari', ['7' => '1', '8' => '2', '9' => '3']);
        $this->convertColumn('kelas_histories', 'kelas_ke', ['7' => '1', '8' => '2', '9' => '3']);
        $this->convertColumn('rekening_kelas', 'kelas', ['7' => '1', '8' => '2', '9' => '3']);
        $this->convertColumn('admins', 'assigned_kelas', ['7' => '1', '8' => '2', '9' => '3']);
        $this->convertColumn('absensi_santri', 'kelas', ['7' => '1', '8' => '2', '9' => '3']);
        $this->convertColumn('uang_saku', 'kelas', ['7' => '1', '8' => '2', '9' => '3']);
        $this->convertColumn('uang_saku_manual', 'kelas', ['7' => '1', '8' => '2', '9' => '3']);
    }

    public function down(): void
    {
        $this->convertColumn('santri', 'kelas', ['1' => '7', '2' => '8', '3' => '9']);
        $this->convertColumn('kelas_histories', 'kelas_dari', ['1' => '7', '2' => '8', '3' => '9']);
        $this->convertColumn('kelas_histories', 'kelas_ke', ['1' => '7', '2' => '8', '3' => '9']);
        $this->convertColumn('rekening_kelas', 'kelas', ['1' => '7', '2' => '8', '3' => '9']);
        $this->convertColumn('admins', 'assigned_kelas', ['1' => '7', '2' => '8', '3' => '9']);
        $this->convertColumn('absensi_santri', 'kelas', ['1' => '7', '2' => '8', '3' => '9']);
        $this->convertColumn('uang_saku', 'kelas', ['1' => '7', '2' => '8', '3' => '9']);
        $this->convertColumn('uang_saku_manual', 'kelas', ['1' => '7', '2' => '8', '3' => '9']);
    }

    private function convertColumn(string $table, string $column, array $mapping): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $cases = collect($mapping)
            ->map(fn ($to, $from) => "WHEN '{$from}' THEN '{$to}'")
            ->implode(' ');

        $fromValues = array_map(static fn ($from) => (string) $from, array_keys($mapping));

        DB::table($table)
            ->whereIn($column, $fromValues)
            ->update([$column => DB::raw("CASE {$column} {$cases} ELSE {$column} END")]);
    }
};
