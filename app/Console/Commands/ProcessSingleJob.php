<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessSingleJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-single-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process jobs from Redis queue in production';

    /**
     * Execute the console command.
     */	
    public function handle()
    {
        $redis = app('redis')->connection('queue');
        $redis->client()->select(env('REDIS_QUEUE_DB'));

        $processed = 0;

        while (true) {
            $jobData = $redis->lpop('queues:default');

            if ($jobData) {
                try {
                    $this->info('Processing job...');
                    $job = json_decode($jobData, true);
                    $command = unserialize($job['data']['command']);
                    $command->handle();
                    $this->info('Job completed successfully');
                    $processed++;
                } catch (\Exception $e) {
                    $this->error('Job failed: ' . $e->getMessage());
                }
            } else {
                // Plus de jobs disponibles, arrêter
                break;
            }
        }

        $this->info("Processed {$processed} jobs");
    }
}
