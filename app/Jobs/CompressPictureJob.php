<?php

namespace App\Jobs;

use Exception;
use App\Models\Tenant;
use App\Models\Tenants\Picture;
use App\Services\PictureService;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use App\Services\CompressPictureService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Intervention\Image\Exceptions\DecoderException;

class CompressPictureJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 15;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(public Model $picture)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('HANDLE COMPRESSPICTURE JOB');
        app(CompressPictureService::class)->compressPicture($this->picture);
    }

    public function failed($exception): void
    {
        Log::error('!!! FAILED COMPRESSPICTURE JOB : ' . $this->picture->path . ' - ' . $this->picture->id);
        Log::error($exception);
    }
}
