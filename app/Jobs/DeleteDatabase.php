<?php

namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Stancl\Tenancy\Events\DatabaseDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Stancl\Tenancy\Events\DeletingDatabase;
use Stancl\Tenancy\Contracts\TenantWithDatabase;

class DeleteDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var TenantWithDatabase */
    protected $tenant;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle()
    {

        //  $this->tenant->database()->manager()->deleteDatabase($this->tenant);
        // event(new DeletingDatabase($this->tenant));
        DB::listen(function ($query) {
            if (str_contains(strtolower($query->sql), 'drop database')) {
                Log::channel('single')->info('🚨 DROP DATABASE called from Job: ' . $query->sql);
            }
        });

        logger()->info('Test avec logger()');

        try {

            Log::info('DeleteDatabaseJob');
            // Fermer toute connexion existante à la base du tenant
            DB::purge('tenant');
            DB::disconnect('tenant');

            // Récupérer le nom de la base (tu peux le stocker dans les données custom du tenant)
            $dbName = $this->tenant->tenancy_db_name;
            logger()->info('Database name ' . $dbName);

            if (!$dbName) {
                logger()->warning("DeleteDatabase: no database name found for tenant ID {$this->tenant->id}");
                return;
            }

            Log::info('existing DB');
            DB::statement("DROP DATABASE IF EXISTS `$dbName`");
            logger()->info("Deleted tenant DB: $dbName");

            Log::info('fin suppression DB');

            event(new DatabaseDeleted($this->tenant));
        } catch (Throwable $e) {
            logger()->error("Failed to delete tenant DB: " . $e->getMessage());
            // Re-throw if you want the job to fail
            throw $e;
        }
    }
}
