<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        School::updateOrCreate(
            ['name' => 'MTS TAHFIZH ALAM QURAN'],
            [
                'phone' => '087756563507 / 085853410025',
                'address' => 'JL.PANDANARUM-WINONG-JETIS-PONOROGO',
                'principal_name' => 'AKHMAD FAUZI, S.Ag. M.Pd',
                'email' => null,
            ]
        );
    }
}
