<?php

namespace App\Providers;

use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Dcat\Laravel\Database\WhereHasInServiceProvider;
use Illuminate\Broadcasting\BroadcastServiceProvider;
use Illuminate\Redis\RedisServiceProvider;
use Illuminate\Support\ServiceProvider;
use Kra8\Snowflake\Providers\LumenServiceProvider as SnowFlake;
use Overtrue\LaravelLang\TranslationServiceProvider as I18N;
use Propaganistas\LaravelPhone\PhoneServiceProvider as PhoneValidator;
use Wnx\LaravelStats\StatsServiceProvider;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        // I18N
        $this->app->register(I18N::class);

        // WhereHasIn method
        $this->app->register(WhereHasInServiceProvider::class);

        // Redis
        $this->app->register(RedisServiceProvider::class);

        // Broadcast
        $this->app->register(BroadcastServiceProvider::class);

        // Phone validator
        $this->app->register(PhoneValidator::class);

        // SnowFlake
        $this->app->register(SnowFlake::class);

        if ($this->app->environment() !== 'production') {
            $this->app->register(IdeHelperServiceProvider::class);
            $this->app->register(StatsServiceProvider::class);
        }
    }
}
