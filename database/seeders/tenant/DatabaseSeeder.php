<?php

namespace Database\Seeders;

use App\Models\Tenants\User;
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
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'super@sme-facility.com',
            'password' => Hash::make('SME_2025!fwebxp')
        ]);
    }
}
