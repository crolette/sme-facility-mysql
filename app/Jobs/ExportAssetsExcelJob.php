<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Tenants\User;
use App\Exports\AssetsExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ExportAssetsExcelJob implements ShouldQueue
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
        Log::info('BEGIN EXPORT ASSETS EXCEL JOB : ' . $this->user->email);
        
        $directory = tenancy()->tenant->id . '/exports/' . Carbon::now()->isoFormat('YYYYMMDDhhmm') . '_assets.xlsx';

        Excel::store(new AssetsExport(), $directory , 'tenants');
        
        Log::info('EXPORT ASSETS EXCEL JOB DONE');

        // Mail
        Log::info('SENDING MAIL EXPORT SUCCESS');
        if (env('APP_ENV') === 'local') {
            Mail::to('crolweb@gmail.com')->send(
                new \App\Mail\ExportSuccessMail($this->user, $directory)
            );
            Log::info("Mail sent to : crolweb@gmail.com");
        } else {
            Mail::to($this->user->email)->send(
                new \App\Mail\ExportSuccessMail($this->user, $directory)
            );
            Log::info("Mail sent to : {$this->user->email}");
        }
        Log::info('SUCCESS SENDING MAIL EXPORT');
    }

    public function failed($exception): void
    {
        Log::error('!!! FAILED EXPORT ASSETS EXCEL');
        Log::error($exception);
    }
}
