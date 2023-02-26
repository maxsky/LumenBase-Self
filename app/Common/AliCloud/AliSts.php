<?php

/**
 * Created by IntelliJ IDEA.
 * User: maxsky
 * Date: 2021/10/19
 * Time: 4:47 PM
 */

namespace App\Common\AliCloud;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\{ClientException, ServerException};

class AliSts extends AliClient {

    /**
     * @param string|null $access_key
     * @param string|null $access_secret
     * @param string|null $region_id
     *
     * @return AliSts
     * @throws ClientException
     */
    public static function getInstance(?string $access_key = null,
                                       ?string $access_secret = null, ?string $region_id = null): AliSts {
        if (!self::$instance) {
            self::$instance = new self($access_key, $access_secret, $region_id);
        }

        return self::$instance;
    }

    /**
     * @return array|string
     */
    public function getCallerIdentity(): array|string {
        return $this->getRequestResult('GetCallerIdentity');
    }

    /**
     * @param string $role_arn          from `getCallerIdentity` method `Arn` key
     * @param string $role_session_name custom param, generally set to the username
     * @param int    $expiration
     *
     * @return array|string
     */
    public function assumeRole(string $role_arn, string $role_session_name, int $expiration = 7200): array|string {
        return $this->getRequestResult('AssumeRole', 'POST', [
            'query' => [
                'RoleArn' => $role_arn,
                'RoleSessionName' => $role_session_name,
                'DurationSeconds' => $expiration
            ]
        ]);
    }

    /**
     * @param string $action
     * @param string $method
     * @param array  $options
     *
     * @return array|string
     */
    private function getRequestResult(string $action, string $method = 'GET', array $options = []): array|string {
        try {
            return AlibabaCloud::rpc()
                ->method($method)
                ->scheme('https')
                ->host('sts.aliyuncs.com')
                ->product('Sts')
                ->action($action)
                ->version('2015-04-01')
                ->options($options)
                ->request()
                ->all();
        } catch (ClientException|ServerException $e) {
            return $e->getErrorMessage();
        }
    }
}
