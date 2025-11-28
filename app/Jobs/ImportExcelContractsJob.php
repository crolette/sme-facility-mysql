<?php

namespace App\Jobs;

use App\Models\Tenants\User;
use App\Imports\AssetsImport;
use App\Imports\ContractsImport;
use App\Mail\ImportErrorMail;
use App\Mail\ImportSuccessMail;
use App\Imports\ProvidersImport;
use App\Imports\UsersImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportExcelContractsJob implements ShouldQueue
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
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('BEGIN IMPORT CONTRACTS EXCEL JOB : ' . $this->user->email);

        try {
            Excel::import(new ContractsImport, $this->path, 'tenants');

            Log::info('IMPORT PROVIDERS EXCEL JOB DONE');

            Log::info('SENDING MAIL IMPORT CONTRACTS SUCCESS');
            if (env('APP_ENV') === 'local') {
                Mail::to('crolweb@gmail.com')->send(
                    new ImportSuccessMail($this->user, 'contracts')
                );
                Log::info("Mail sent to : crolweb@gmail.com");
            } else {
                Mail::to($this->user->email)->send(
                    new ImportSuccessMail($this->user, 'contracts')
                );
                Log::info("Mail sent to : {$this->user->email}");
            }
            Log::info('SUCCESS SENDING MAIL CONTRACTS IMPORT');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            foreach ($failures as $failure) {
                Log::error('Row validation failed', [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                ]);
            }

            if (env('APP_ENV') === 'local') {
                Mail::to('crolweb@gmail.com')->send(
                    new ImportErrorMail('contracts', $failures)
                );
                Log::info("Mail sent to : crolweb@gmail.com");
            } else {
                Mail::to($this->user->email)->send(
                    new ImportErrorMail('contracts', $failures)
                );
            }
        } catch (\Exception $e) {
            Log::error('Error during export');
            Log::error($e->getMessage());

            if (env('APP_ENV') === 'local') {
                Mail::to('crolweb@gmail.com')->send(
                    new \App\Mail\ImportErrorMail('contracts')
                );
                Log::info("Mail sent to : crolweb@gmail.com");
            } else {
                Mail::to($this->user->email)->send(
                    new \App\Mail\ImportErrorMail('contracts')
                );
                Log::info("Mail sent to : {$this->user->email}");
            }
        } catch (\Error $e) {
            Log::error('Error during export');
            Log::error($e->getMessage());

            if (env('APP_ENV') === 'local') {
                Mail::to('crolweb@gmail.com')->send(
                    new \App\Mail\ImportErrorMail('contracts')
                );
                Log::info("Mail sent to : crolweb@gmail.com");
            } else {
                Mail::to($this->user->email)->send(
                    new \App\Mail\ImportErrorMail('contracts')
                );
                Log::info("Mail sent to : {$this->user->email}");
            }
        }

        Storage::disk('tenants')->delete($this->path);
    }

    public function failed($exception): void
    {
        Log::error('!!! FAILED IMPORT USERS EXCEL');
        Log::error($exception);
    }
}
