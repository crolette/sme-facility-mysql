<?php

namespace Tests\Concerns;

use Exception;
use App\Models\Tenant;
use App\Models\Tenants\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

trait ManagesTenantDatabases
{
    protected array $createdTenants = [];

    protected function createTestTenant(string $id = null): Tenant
    {
        $userTenant = User::factory()->raw();
        Session::put([...$userTenant]);
        $tenant = Tenant::create(['id' => $id ?? 'test-tenant-' . uniqid(), 'email' => 'test@sme-facility.com']);
        $this->createdTenants[] = $tenant;

        return $tenant;
    }

    protected function initializeTestTenancy(?Tenant $tenant = null): Tenant
    {
        $tenant = $tenant ?? $this->createTestTenant();
        tenancy()->initialize($tenant);

        return $tenant;
    }

    protected function cleanupAllTenants(): void
    {
        foreach ($this->createdTenants as $tenant) {
            $this->cleanupTenant($tenant);
        }

        $this->createdTenants = [];
    }

    public function cleanupTenant(Tenant $tenant): void
    {
        try {
            // End tenancy if this tenant is currently active
            if (tenancy()->initialized && tenancy()->tenant->id === $tenant->id) {
                tenancy()->end();
            }

            // Get tenant database name (adjust based on your Stancl config)
            $tenantDbName = config('tenancy.database.prefix') . $tenant->id . config('tenancy.database.suffix');

            // For PostgreSQL
            DB::statement("DROP DATABASE IF EXISTS `$tenantDbName`");

            // Delete tenant addresses
            $tenant->companyAddress()->delete();
            $tenant->invoiceAddress ? $tenant->invoiceAddress()->delete() : '';

            // Delete tenant record
            $tenant->delete();
        } catch (Exception $e) {
            // Log but don't fail tests
            error_log("Failed to cleanup tenant {$tenant->id}: " . $e->getMessage());
        }
    }

    protected function tearDownTenants(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        $this->cleanupAllTenants();
    }
}
