<?php

namespace App\Jobs;

use App\Models\Tenants\User;
use App\Imports\AssetsImport;
use App\Mail\ImportSuccessMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportExcelAssetsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 15;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public string $path
        )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('BEGIN IMPORT ASSETS EXCEL JOB : ' . $this->user->email);
        Excel::import(new AssetsImport, $this->path, 'tenants');

        Log::info('IMPORT ASSETS EXCEL JOB DONE');

        Log::info('SENDING MAIL IMPORT SUCCESS');
        if (env('APP_ENV') === 'local') {
            Mail::to('crolweb@gmail.com')->send(
                new ImportSuccessMail($this->user)
            );
            Log::info("Mail sent to : crolweb@gmail.com");
        } else {
            Mail::to($this->user->email)->send(
                new ImportSuccessMail($this->user)
            );
            Log::info("Mail sent to : {$this->user->email}");
        }
        Log::info('SUCCESS SENDING MAIL IMPORT');

        Storage::disk('tenants')->delete($this->path);


    }

    public function failed($exception): void
    {
        Log::error('!!! FAILED EXPORT ASSETS EXCEL');
        Log::error($exception);
    }
}
