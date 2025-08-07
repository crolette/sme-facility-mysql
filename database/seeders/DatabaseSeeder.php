<?php

namespace Database\Seeders;

use App\Models\Central\CentralUser;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        CentralUser::factory()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'username' => 'super.admin',
            'email' => 'super@sme-facility.com',
            'password' => Hash::make('SME_2025!fwebxp')
        ]);
    }
}
