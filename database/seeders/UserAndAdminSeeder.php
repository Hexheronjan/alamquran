<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin;

class UserAndAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seeder untuk User
        User::create([
            'name' => 'John Doe',
            'email' => 'user@example.com',
            'password' => Hash::make('password'), // Password: password
        ]);

        // Seeder untuk Admin
        Admin::create([
            'name' => 'Admin Master',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // Password: password
        ]);
    }
}
