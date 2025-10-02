<?php

namespace App\Jobs;

use Exception;
use App\Models\Tenant;
use App\Models\Tenants\User;
use App\Services\UserService;
use App\Models\Tenants\Picture;
use App\Services\PictureService;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Intervention\Image\Exceptions\DecoderException;

class CompressUserAvatarJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 15;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(public User $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('HANDLE COMPRESS AVATAR JOB');
        app(UserService::class)->compressAvatar($this->user);
    }

    public function failed($exception): void
    {
        Log::error('!!! FAILED COMPRESS AVATAR JOB : ' . $this->user->avatar . ' - ' . $this->user->id);
        Log::error($exception);
    }
}
