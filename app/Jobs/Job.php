<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Queue\Jobs\{Job as BaseJob, JobName};

abstract class Job implements ShouldQueue {
    /*
    |--------------------------------------------------------------------------
    | Queueable Jobs
    |--------------------------------------------------------------------------
    |
    | This job base class provides a central location to place any logic that
    | is shared across all of your jobs. The trait included with the class
    | provides access to the "queueOn" and "delay" queue helper methods.
    |
    */

    use InteractsWithQueue, Queueable, SerializesModels;

    /** @var mixed */
    protected mixed $data;

    /**
     * @param BaseJob $job
     * @param mixed   $data
     */
    public function fire(BaseJob $job, mixed $data = null) {
        $payload = $job->payload();

        $class = JobName::resolve($payload['job'], $payload);

        $jobClass = new $class();

        $jobClass->job = $job;
        $jobClass->data = $data;

        $jobClass->handle();
    }
}
