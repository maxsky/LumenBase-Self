<?php

namespace App\Console;

use Illuminate\Console\KeyGenerateCommand;
use Illuminate\Console\Scheduling\Schedule;
use JK\Dingo\Api\Console\Commands\RouteListCommand;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        KeyGenerateCommand::class,
        RouteListCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        //
    }
}
