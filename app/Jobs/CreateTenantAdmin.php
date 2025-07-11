<?php

namespace App\Jobs;

use App\Models\Tenants\User;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Session;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Stancl\Tenancy\Contracts\TenantWithDatabase;

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
        $email = Session::get('email');
        $pwd = Session::get('password');
        $first_name = Session::get('first_name');
        $last_name = Session::get('last_name');
        $username = Str::lower(preg_replace('/\s+/', '', $first_name)) . '.' . Str::lower(preg_replace('/\s+/', '', $last_name));

        tenancy()->initialize($this->tenant);

        $admin = User::create([
            'email' => $email,
            'password' => Hash::make($pwd),
            'first_name' => $first_name,
            'last_name' => $last_name,
            'username' => $username
        ]);

        Log::info($admin);

        tenancy()->end();
    }
}
