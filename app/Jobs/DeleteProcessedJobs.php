<?php

namespace App\Jobs;

use App\Job;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;

class DeleteProcessedJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $jobs = Job::whereDate('processed_at', '<', Carbon::now()->subDays(30))->get();

        foreach ($jobs as $job) {
            $jobFolderPath = $job->getUploadPath();
            $jobOutputPath = $job->getOutputPath();

            # Delete the uploads folder
            if (Storage::exists($jobFolderPath)) {
                Storage::deleteDirectory($jobFolderPath);
            }

            # Delete the output folder
            if (Storage::exists($jobOutputPath)) {
                Storage::deleteDirectory($jobOutputPath);
            }

            $job->presets()->detach();
            $job->files()->delete();
            $job->delete();
        };
    }
}
