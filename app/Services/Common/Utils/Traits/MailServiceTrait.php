<?php

namespace App\Services\Common\Utils\Traits;

use Illuminate\Mail\MailServiceProvider;

trait MailServiceTrait {

    /**
     * @param string $type
     *
     * @return array
     */
    private function getSenderAccountConfig(string $type): array {
        $username = env("MAIL_{$type}_USERNAME");
        $password = env("MAIL_{$type}_PASSWORD");
        $from = env("MAIL_{$type}_FROM_ADDRESS");
        $fromName = env("MAIL_{$type}_FROM_NAME");

        $config = config('mail');

        $config['username'] = $username;
        $config['password'] = $password;
        $config['from']['address'] = $from;
        $config['from']['name'] = $fromName;

        return $config;
    }

    /**
     * @param array $config
     *
     * @return void
     */
    private function registerProvider(array $config): void {
        // set new mail config
        config(['mail' => $config]);
        // register provider
        app()->register(MailServiceProvider::class);
    }
}
