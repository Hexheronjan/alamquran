<?php

return [
    'rekening_deposit' => [
        'bank' => env('DEPOSIT_BANK', 'Bank BSI'),
        'nomor' => env('DEPOSIT_NOMOR_REKENING', '0000000000'),
        'atas_nama' => env('DEPOSIT_ATAS_NAMA', 'Pesantren Tahfizh Alam Qur’an'),
    ],
];
