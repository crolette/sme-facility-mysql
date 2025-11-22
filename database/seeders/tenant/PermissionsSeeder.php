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

            $permissions = [
                'roles' => ['assign'],
                'permissions' => ['assign'],
                'company' => ['view', 'update'],
                'excel' => ['export', 'import'],
                'users' => ['view any', 'view', 'create', 'update', 'delete'],
                'locations' => ['view any', 'view', 'create', 'update', 'delete'],
                'assets' => ['view any', 'view', 'create', 'update', 'delete', 'force delete', 'restore'],
                'tickets' => ['view any', 'view', 'create', 'update', 'delete'],
                'interventions' => ['view any', 'view', 'create', 'update', 'delete'],
                'actions' => ['view any', 'view', 'create', 'update', 'delete'],
                'documents' => ['view any', 'view', 'create', 'update', 'delete'],
                'pictures' => ['view any', 'view', 'create', 'update', 'delete'],
                'statistics' => ['view', 'export']
            ];

            foreach ($permissions as $entity => $actions) {
                foreach ($actions as $action) {
                    Permission::create(['guard_name' => 'tenant', 'name' => "$action $entity"]);
                }
            }

            // // create permissions
            // Permission::create(['guard_name' => 'tenant', 'name' => 'assign roles']);

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $superAdminRole = Role::create(['guard_name' => 'tenant', 'name' => 'Super Admin']);
            $superAdminRole->syncPermissions(Permission::all());

            $adminRole = Role::create(['guard_name' => 'tenant', 'name' => 'Admin']);
            $adminRole->syncPermissions(Permission::all());

            Role::create(['guard_name' => 'tenant', 'name' => 'Maintenance Manager'])->givePermissionTo([
                'view company',

                'view users',
                'update users',

                'view any locations',
                'view locations',
                'update locations',

                'view any assets',
                'view assets',
                'update assets',
                'delete assets',

                'view any tickets',
                'view tickets',
                'create tickets',
                'update tickets',

                'view any interventions',
                'view interventions',
                'create interventions',
                'update interventions',

                'view actions',
                'create actions',
                'update actions',
                'delete actions',

                'view documents',
                'create documents',
                'update documents',
                'delete documents',

                'view pictures',
                'create pictures',
                'update pictures',
                'delete pictures',

                'view statistics'
            ]);

            Role::create(['guard_name' => 'tenant', 'name' => 'Provider'])->givePermissionTo([
                'view users',
                'update users',

                'view locations',

                'view assets',

                'view tickets',
                'create tickets',
                'update tickets',

                'view interventions',

                'view actions',
                'create actions',
                'update actions',

                'view documents',

                'view pictures',
            ]);


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
    }
}
