<?php

namespace Database\Seeders\tenant;

use App\Models\Tenants\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\tenant\PermissionsSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            PermissionsSeeder::class,
        ]);

        if (!User::where('email', 'super@sme-facility.com')->first()) {

            $user = User::create([
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'email' => 'super@sme-facility.com',
                'password' => Hash::make('SME_2025!fwebxp'),
                'can_login' => true
            ]);
        } else {
            $user = User::where('email', 'super@sme-facility.com')->first();
            $user->assignRole('Super Admin');
        }
    }
}
