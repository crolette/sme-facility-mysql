<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\Tenants\Company;
use App\Models\Tenants\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use App\Services\UserNotificationPreferenceService;

class CreateCompany implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /** @var TenantWithDatabase */
    protected $tenant;

    /**
     * Create a new job instance.
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        tenancy()->initialize($this->tenant);

        Company::create(
            [
                'last_ticket_number' => 0,
                'last_asset_number' => 0,
                'disk_size' => 0,
                'address' => $this->tenant->fullCompanyAddress ?? '',
                'vat_number' => $this->tenant->vat_number,
                'name' => $this->tenant->company_name,
            ]
        );

        tenancy()->end();
    }
}
