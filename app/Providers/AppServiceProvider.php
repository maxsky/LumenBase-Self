<?php

namespace App\Providers;

use App\Exceptions\DingoExceptionHandler;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Dingo\Api\Provider\LumenServiceProvider as DingoAPI;
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

        // Dingo API
        $this->app->register(DingoAPI::class);

        // Overwrite exception render
        $this->app->singleton('api.exception', function () {
            return new DingoExceptionHandler(
                $this->app['Illuminate\Contracts\Debug\ExceptionHandler'],
                config('api.errorFormat'),
                config('api.debug')
            );
        });

        // Redis
        $this->app->register(RedisServiceProvider::class);

        // Phone validator
        $this->app->register(PhoneValidator::class);

        // SnowFlake
        $this->app->register(SnowFlake::class);

        // IDE Helper
        if ($this->app->environment() !== 'production') {
            $this->app->register(IdeHelperServiceProvider::class);
            $this->app->register(StatsServiceProvider::class);
        }
    }
}
