<?php

namespace App\Providers;

use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider {

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot() {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function (Request $request) {
            // get token
            $token = $request->bearerToken();

            // TODO: token verify

            $tokenVerified = ['user_id' => 123];

            if ($tokenVerified) {
                return app(UserService::class)->getUserById($tokenVerified['user_id']);
            }

            return null;
        });
    }
}
