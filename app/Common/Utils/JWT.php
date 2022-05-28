<?php

/**
 * Created by IntelliJ IDEA.
 * User: maxsky
 * Date: 2020/3/22
 * Time: 01:06
 */

namespace App\Common\Utils;

use DomainException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use InvalidArgumentException;
use UnexpectedValueException;

class JWT {

    /**
     * @var array 载荷由如下键组成：
     *                         aud - 接收者，同 iss，由请求方提供
     *                         iss - 颁发者，为 APP_NAME 16 位 MD5 值
     *                         sub - 主题，即 UID
     *                         jti - JWT 唯一 ID
     *                         iat - 颁发时间，不可晚于生效时间及过期时间
     *                         nbf - 生效时间，不可早于颁发时间、晚于过期时间
     *                         exp - 过期时间，不可早于颁发时间及生效时间
     */
    private array $payload;

    private string $priKey;

    private string $pubKey;

    /**
     * JWT constructor.
     */
    public function __construct() {
        $this->payload = [
            'aud' => null,
            'iss' => '1a294f40812c9a36', // LumenProject-API
            'sub' => null,
            'jti' => null,
            'src' => null,
            'iat' => null,
            'nbf' => null,
            'exp' => null
        ];
    }

    /**
     * @param string $private_key
     *
     * @return JWT
     */
    public function setPrivateKey(string $private_key): JWT {
        $this->priKey = $private_key;

        return $this;
    }

    /**
     * @param string $public_key
     *
     * @return JWT
     */
    public function setPublicKey(string $public_key): JWT {
        $this->pubKey = $public_key;

        return $this;
    }

    /**
     * @param string $audience 受众，目标应用 ID
     * @param string $subject  主题，用户唯一编号
     * @param string $jti      JWT 唯一校验 ID
     * @param string $source   来源
     * @param int    $expires  Token 过期时间
     * @param string $algo     算法
     *
     * @return string
     */
    public function generate(string $audience, string $subject,
                             string $jti, string $source, int $expires, string $algo = 'ES256'): string {
        $this->payload['aud'] = $audience;
        $this->payload['sub'] = $subject;
        $this->payload['jti'] = $jti;
        $this->payload['src'] = $source;
        $this->payload['iat'] = $this->payload['nbf'] = time();
        $this->payload['exp'] = $expires;

        return FirebaseJWT::encode($this->payload, $this->priKey, $algo);
    }

    /**
     * @param string $token
     * @param string $algo
     *
     * @return object
     * @throws BeforeValidException
     * @throws DomainException
     * @throws ExpiredException
     * @throws InvalidArgumentException
     * @throws SignatureInvalidException
     * @throws UnexpectedValueException
     */
    public function decode(string $token, string $algo = 'ES256'): object {
        return FirebaseJWT::decode($token, new Key($this->pubKey, $algo));
    }
}
