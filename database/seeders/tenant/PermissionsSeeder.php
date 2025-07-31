<?php

namespace Database\Seeders\tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
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


            // create permissions
            Permission::create(['guard_name' => 'tenant', 'name' => 'assign roles']);



            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $superAdminRole = Role::create(['guard_name' => 'tenant', 'name' => 'Super Admin']);
            $superAdminRole->syncPermissions(Permission::all());

            Role::create(['guard_name' => 'tenant', 'name' => 'Admin'])->givePermissionTo([
                'assign roles',

            ]);


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
    }
}
