<?php

namespace Tests;

use App\Models\Tenant;
use App\Models\Address;
use App\Enums\AddressTypes;
use App\Models\Tenants\User;
use App\Services\TenantLimits;
use App\Models\Tenants\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

// use Stancl\Tenancy\Database\Models\Tenant;

abstract class TenantTestCase extends BaseTestCase
{
    protected Tenant $tenant;
    protected $tenancy = false;

    protected $preservedTables = [
        'migrations',
        'company',
        'permissions',
        'roles',
        'role_has_permissions',
        'countries',
        'country_translations'

    ];

    protected $preservedCentralTables = [
        'migrations',
        'countries',
        'addresses',
        'tenants',
        'domains',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->initializeTenancy();
    }

    protected function initializeTenancy()
    {
        // Create test tenant

        if (!Tenant::find('test')?->first()) {

            $userTenant = User::factory()->raw();
            Session::put([...$userTenant]);

            $this->tenant = Tenant::factory()->create([
                'id' => 'test',
                'company_name' => 'test',
                'email' => 'test@sme-facility.com',
                'vat_number' => 'BE1239874560',
                'phone_number' => '+32123856479',
                'company_code' => 'test'
            ]);


            $this->tenant->domain()->create(['domain' => $this->tenant->id]);

            $this->tenant->addresses()->create(Address::factory()->raw());
            $this->tenant->addresses()->create(Address::factory()->raw(['address_type' => AddressTypes::INVOICE->value]));
            $this->tenant->save();
        } else {
            $this->tenant = Tenant::find('test')->first();
        }




        // Initialize tenant context
        tenancy()->initialize($this->tenant);

        if ($this->tenant) {
            Cache::remember(
                "tenant:{$this->tenant->id}:limits",
                now()->addDay(),
                fn() => TenantLimits::loadLimitsFromDatabase($this->tenant)
            );
        }

        $company = Company::first();
        if (!$company) {
            Company::create(['last_ticket_number' => 0, 'last_asset_number' => 0]);
        }

        app('router')->getRoutes()->refreshNameLookups();


        return $this->tenant;
    }

    protected function tenantRoute($name, $parameters = [])
    {
        $originalRoute = route($name, $parameters);

        // Remplacer complètement le domaine
        return preg_replace('/^https?:\/\/[^\/]+/', "http://{$this->tenant->id}.localhost:8000", $originalRoute);
    }

    // Helpers pour les requêtes courantes
    protected function getFromTenant($route, $parameters = [], $headers = [])
    {
        return $this->get($this->tenantRoute($route, $parameters), $headers);
    }

    protected function postToTenant($route, $data = [], $parameters = [], $headers = [])
    {
        return $this->post($this->tenantRoute($route, $parameters), $data, $headers);
    }

    protected function putToTenant($route, $data = [], $parameters = [], $headers = [])
    {
        return $this->put($this->tenantRoute($route, $parameters), $data, $headers);
    }

    protected function patchToTenant($route, $data = [], $parameters = [], $headers = [])
    {
        return $this->patch($this->tenantRoute($route, $parameters), $data, $headers);
    }

    protected function deleteFromTenant($route, $parameters = [], $data = [],  $headers = [])
    {
        return $this->delete($this->tenantRoute($route, $parameters), $data, $headers);
    }

    protected function assertRedirectToTenant($response, $route, $parameters = [])
    {
        $response->assertRedirect($this->tenantRoute($route, $parameters));
    }

    protected function truncateAllTablesExcept(array $preservedTables = [])
    {
        // Désactiver les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

        // Récupérer toutes les tables de la base courante
        $database = DB::getDatabaseName();
        $tables = DB::select("SHOW TABLES");
        // Nom de la colonne dynamique (ex: "Tables_in_nom_de_ta_base")
        $tableKey = 'Tables_in_' . $database;

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;

            if (!in_array($tableName, $preservedTables)) {
                DB::statement("TRUNCATE TABLE `$tableName`");
            }
        }

        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
    }

    protected function truncateAllCentralTablesExcept(array $preservedCentralTables = [])
    {
        $centralConnection = DB::connection('central');

        $centralConnection->statement('SET FOREIGN_KEY_CHECKS = 0;');

        // Récupérer toutes les tables de la base centrale
        $database = $centralConnection->getDatabaseName();
        $tables = $centralConnection->select("SHOW TABLES");
        $tableKey = 'Tables_in_' . $database;

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            if (!in_array($tableName, $preservedCentralTables)) {
                $centralConnection->statement("TRUNCATE TABLE `$tableName`");
            }
        }

        $centralConnection->statement('SET FOREIGN_KEY_CHECKS = 1;');
    }

    protected function tearDown(): void
    {
        $this->cleanupTenantData();

        // End tenant context
        if (tenancy()->initialized && tenancy()->tenant->id === $this->tenant->id) {
            tenancy()->end();
        }

        Storage::disk('tenants')->deleteDirectory('test');

        parent::tearDown();
    }

    protected function cleanupTenantData()
    {
        // S'assurer qu'on est dans le bon contexte tenant
        if (tenancy()->initialized) {

            // Remettre à 0 les compteurs assets et tickets
            $company = Company::first();
            if ($company)
                $company->update(['last_ticket_number' => 0, 'last_asset_number' => 0, 'disk_size' => 0]);



            $this->truncateAllTablesExcept($this->preservedTables);
            $this->truncateAllCentralTablesExcept($this->preservedCentralTables);
        }
    }
}
