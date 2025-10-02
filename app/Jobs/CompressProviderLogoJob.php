<?php

namespace App\Jobs;

use Exception;
use App\Services\LogoService;
use App\Models\Tenants\Provider;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Intervention\Image\Exceptions\DecoderException;

class CompressProviderLogoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 15;
    public $timeout = 120;

    
    /**
     * Create a new job instance.
     */
    public function __construct(public Provider $provider)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('HANDLE COMPRESS LOGO JOB');
        app(LogoService::class)->compressLogo($this->provider);
    }

    public function failed(DecoderException|Exception $exception): void
    {
        Log::error('!!! FAILED COMPRESS LOGO JOB : ' . $this->provider->logo . ' - ' . $this->provider->name);
        Log::error($exception);
    }
}
