<?php

namespace App\Jobs;

use Exception;
use App\Services\LogoService;
use App\Models\Tenants\Company;
use App\Models\Tenants\Provider;
use Illuminate\Support\Facades\Log;
use App\Services\CompanyLogoService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Intervention\Image\Exceptions\DecoderException;

class CompressCompanyLogoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 15;
    public $timeout = 120;


    /**
     * Create a new job instance.
     */
    public function __construct(public Company $company)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('HANDLE COMPANY COMPRESS LOGO JOB');
        app(CompanyLogoService::class)->compressLogo($this->company);
    }

    public function failed($exception): void
    {
        Log::error('!!! FAILED COMPANY COMPRESS LOGO JOB : ' . $this->company->logo . ' - ' . $this->company->name);
        Log::error($exception);
    }
}
