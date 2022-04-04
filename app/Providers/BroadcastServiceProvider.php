<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Routing\Router;

class BroadcastServiceProvider extends ServiceProvider {

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        $this->app['router']->group([
            'middleware' => []
        ], function (Router $router) {
            require_once base_path('routes/channels.php');
        });
    }
}
