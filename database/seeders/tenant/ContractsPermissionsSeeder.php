<?php

namespace Database\Seeders\tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ContractsPermissionsSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        try {
            DB::beginTransaction();

            $permissions = [
                'contracts' => ['view any', 'view', 'create', 'update', 'delete'],
            ];

            foreach ($permissions as $entity => $actions) {
                foreach ($actions as $action) {
                    Permission::create(['guard_name' => 'tenant', 'name' => "$action $entity"]);
                }
            }

            // // create permissions
            // Permission::create(['guard_name' => 'tenant', 'name' => 'assign roles']);

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $superAdminRole = Role::where('name', 'Super Admin')->first();
            $superAdminRole->givePermissionTo(
                [
                    'view any contracts',
                    'view contracts',
                    'create contracts',
                    'update contracts',
                    'delete contracts',
                ]
            );

            $adminRole = Role::where('name', 'Admin')->first();
            $adminRole->givePermissionTo(
                [
                    'view any contracts',
                    'view contracts',
                    'create contracts',
                    'update contracts',
                    'delete contracts',
                ]
            );;

            $maintenanceManagerRole = Role::where('name', 'Maintenance Manager')->first();
            $maintenanceManagerRole->givePermissionTo(
                [
                    'view any contracts',
                    'view contracts',
                    'create contracts',
                    'update contracts',
                    
            ]);

           


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
    }
}
