<?php

namespace App\Jobs;

class ExampleJob extends Job {

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        print_r($this->data);
        echo "done\n";
        $this->delete();
    }
}
