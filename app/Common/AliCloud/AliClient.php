<?php

/**
 * Created by IntelliJ IDEA.
 * User: maxsky
 * Date: 2021/10/19
 * Time: 4:43 PM
 */

namespace App\Common\AliCloud;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;

abstract class AliClient {

    protected static ?AliClient $instance = null;

    /**
     * @param string|null $access_key
     * @param string|null $access_secret
     * @param string|null $region_id
     *
     * @throws ClientException
     */
    public function __construct(?string $access_key = null, ?string $access_secret = null, ?string $region_id = null) {
        AlibabaCloud::accessKeyClient(
            $access_key ?: env('ALI_CLOUD_ACCESS_KEYID'),
            $access_secret ?: env('ALI_CLOUD_ACCESS_SECRET'))
            ->regionId($region_id ?: env('ALI_CLOUD_REGION_ID'))
            ->connectTimeout(30)->timeout(30)->asDefaultClient();
    }

    /**
     * @param string|null $access_key
     * @param string|null $access_secret
     * @param string|null $region_id
     *
     * @return AliClient
     * @throws ClientException
     */
    abstract public static function getInstance(?string $access_key = null,
                                                ?string $access_secret = null, ?string $region_id = null): AliClient;
}
