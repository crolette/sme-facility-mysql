<?php

namespace Database\Seeders\tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class NewPermissions extends Seeder
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
                'statistics' => ['view', 'export'],
            ];

            foreach ($permissions as $entity => $actions) {
                foreach ($actions as $action) {
                    Permission::create(['guard_name' => 'tenant', 'name' => "$action $entity"]);
                }
            }

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $superAdminRole = Role::where('name', 'Super Admin')->first();
            $superAdminRole->givePermissionTo(
                [
                    'view statistics',
                    'export statistics',
                ]
            );

            $adminRole = Role::where('name', 'Admin')->first();
            $adminRole->givePermissionTo(
                [
                    'view statistics',
                    'export statistics',
                ]
            );

            $adminRole = Role::where('name', 'Maintenance Manager')->first();
            $adminRole->givePermissionTo(
                [
                    'view statistics',
                ]
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
    }
}
