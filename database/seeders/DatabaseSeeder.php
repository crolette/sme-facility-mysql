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

        if (!CentralUser::where('email', 'super@sme-facility.com')->first()) {

            CentralUser::factory()->create([
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'email' => 'super@sme-facility.com',
                'password' => Hash::make('SME_2025!fwebxp')
            ]);
        }

        if (!CentralUser::where('email', 'alain.delahaut@gmail.com')->first()) {

            CentralUser::factory()->create([
                'first_name' => 'Alain',
                'last_name' => 'Delahaut',
                'email' => 'alain.delahaut@gmail.com',
                'password' => Hash::make('SME_2025!fwebxp')
            ]);
        }

        if (!CentralUser::where('email', 'crolweb@gmail.com')->first()) {

            CentralUser::factory()->create([
                'first_name' => 'Jonathan',
                'last_name' => 'De Dijcker',
                'email' => 'crolweb@gmail.com',
                'password' => Hash::make('SME_2025!fwebxp')
            ]);
        }
    }
}
