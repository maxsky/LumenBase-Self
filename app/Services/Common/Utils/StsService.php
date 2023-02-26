<?php

/**
 * Created by IntelliJ IDEA.
 * User: maxsky
 * Date: 2021/10/20
 * Time: 2:13 PM
 */

namespace App\Services\Common\Utils;

use AlibabaCloud\Client\Exception\ClientException;
use App\Common\AliCloud\AliSts;

class StsService {

    private AliSts $sts;

    /**
     * @throws ClientException
     */
    public function __construct() {
        $this->sts = AliSts::getInstance(env('ALI_CLOUD_ACCESS_KEYID'), env('ALI_CLOUD_ACCESS_SECRET'));
    }

    /**
     * @param string $role_session_name
     * @param int    $expiration
     *
     * @return array|null
     */
    public function getCredential(string $role_session_name, int $expiration = 7200): ?array {
        return getSetCache('Ali:STS:Credential', function () use ($expiration, $role_session_name): ?array {
            $identity = $this->sts->getCallerIdentity();

            if ($identity['Arn'] ?? null) {
                $result = $this->sts->assumeRole($identity['Arn'], $role_session_name, $expiration);

                if ($result['Credentials'] ?? null) {
                    return $result['Credentials'];
                }
            }

            return null;
        }, $expiration);
    }
}
