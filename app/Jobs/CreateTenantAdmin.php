<?php

namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Support\Str;
use App\Models\Tenants\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Events\NewTenantCreatedEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Session;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use App\Services\UserNotificationPreferenceService;

class CreateTenantAdmin implements ShouldQueue
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

        $admin = User::create([
            'email' => $this->tenant->email,
            'first_name' => $this->tenant->first_name,
            'last_name' => $this->tenant->last_name,
            'can_login' => true,
        ]);

        $admin->assignRole('Admin');

        app(UserNotificationPreferenceService::class)->createDefaultUserNotificationPreferences($admin);

             tenancy()->end();
    }
}
