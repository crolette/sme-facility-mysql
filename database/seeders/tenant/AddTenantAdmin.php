<?php

namespace Database\Seeders\tenant;

use App\Models\Tenants\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Tenants\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\tenant\NewPermissions;
use Database\Seeders\tenant\CountriesSeeder;
use Database\Seeders\tenant\PermissionsSeeder;
use App\Services\UserNotificationPreferenceService;
use Database\Seeders\tenant\ContractsPermissionsSeeder;
use Database\Seeders\tenant\ProvidersPermissionsSeeder;

class AddTenantAdmin extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $tenant = tenancy()->tenant;

        $admin = User::create([
            'email' => $tenant->email,
            'first_name' => $tenant->first_name,
            'last_name' => $tenant->last_name,
            'can_login' => true,
        ]);

        $admin->assignRole('Admin');

        app(UserNotificationPreferenceService::class)->createDefaultUserNotificationPreferences($admin);

        Company::create(
            [
                'last_ticket_number' => 0,
                'last_asset_number' => 0,
                'disk_size' => 0,
                'address' => $tenant->fullCompanyAddress ?? '',
                'vat_number' => $tenant->vat_number,
                'name' => $tenant->company_name,
            ]
        );
    }
}
