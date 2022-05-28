<?php

namespace App\Services\Common\Utils;

use App\Common\Utils\{JWT, Snowflake};
use App\Exceptions\Auth\LoginException;
use Cache;
use Carbon\Carbon;
use DateTimeInterface;
use DomainException;
use Exception;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use InvalidArgumentException;
use Log;
use Redis;
use UnexpectedValueException;

class JWTService {

    private JWT $jwt;
    private Redis $redis;
    private string $prefix;

    public function __construct() {
        $priKey = file_get_contents(storage_path('app/ecc_pri_key.pem'));
        $pubKey = file_get_contents(storage_path('app/ecc_pub_key.pem'));

        $this->jwt = app(JWT::class)->setPrivateKey($priKey)->setPublicKey($pubKey);
        $this->redis = Cache::connection()->client();

        $this->prefix = config('cache.prefix');
        //$this->audience = substr(md5(env('APP_NAME')), 8, 16);
    }

    /**
     * 根据 UID 生成 Token
     *
     * @param string                 $audience 受众，目标应用 ID
     * @param string                 $subject  用户 UNo
     * @param string                 $source
     * @param DateTimeInterface|null $expires  Token 有效期，默认为 30 天
     *
     * @return bool|string
     * @throws LoginException
     */
    public function generate(string $audience,
                             string $subject,
                             string $source = 'App', ?DateTimeInterface $expires = null): bool|string {
        $key = "$this->prefix:$audience:JWT:$source:$subject";

        // clean expired Tokens, since snowflake.epoch in config file
        $this->redis->zRemRangeByScore($key, Carbon::parse(config('snowflake.epoch'))->timestamp, time());

        if (!$expires) {
            $expires = Carbon::now()->addDays(30);
        }

        $jti = Snowflake::generate();

        // generate Token
        $token = $this->jwt->generate($audience, $subject, $jti, $source, $expires->timestamp);

        if ($token) {
            $this->handleNumLimit($key);

            /**
             * options:
             *     XX - only update elements that already exist, don’t add new elements.
             *     NX - only add new elements, don’t update already existing elements.
             *     LT - only update existing elements if the new score is less than the current score.
             *              this flag does not prevent adding new elements.
             *     GT - only update existing elements if the new score is greater than the current score.
             *              this flag does not prevent adding new elements.
             *     CH - modify the return value from the number of new elements added,
             *              to the total number of elements changed (CH is an abbreviation of changed).
             *              changed elements are new elements added and elements already existing for which the score was updated.
             *              so elements specified in the command line having the same score as they had in the past are not counted.
             *              Note: normally the return value of ZADD only counts the number of new elements added.
             *     INCR - when this option is specified ZADD acts like ZINCRBY. only one score-element pair can be specified in this mode.
             *
             * Note: The GT, LT and NX options are mutually exclusive.
             */
            // add to the zSet
            $this->redis->zAdd($key, ['NX'], $expires->timestamp, $jti);

            // reset expiration time to 30 days each time a new token is added
            $this->redis->expire($key, $expires->diffInSeconds(Carbon::now()));

            return $token;
        }

        Log::error("生成用户 Token 失败，用户身份：$subject");

        throw new LoginException('登录服务异常');
    }

    /**
     * Token 验证，超过 n 分钟换取新 Token
     *
     * @param string $token         请求 Token
     * @param int    $renew_minutes 更新时间（分钟）
     *
     * @return array|null 返回数组时结构为 ['token' => ’New token‘, 'subject' => 'UNo']，token 无需更新时不存在
     * @throws LoginException
     */
    public function verify(string $token, int $renew_minutes = 30): ?array {
        // decode Token
        $decodeToken = $this->decode($token);

        if (!$decodeToken) {
            return null;
        }

        $key = "$this->prefix:$decodeToken->aud:JWT:$decodeToken->src:$decodeToken->sub";

        if (!in_array($decodeToken->jti, $this->redis->zRange($key, 0, -1))) {
            return null;
        }

        $ret['subject'] = $decodeToken->sub;

        if (env('APP_ENV') !== 'production') {
            $renew_minutes = 1440; // 1 day
        }

        // renew Token
        if (Carbon::createFromTimestamp($decodeToken->iat)->diffInMinutes(Carbon::now()) >= $renew_minutes) {
            // delete old jti make old token invalid, must do this before generate new token
            $this->redis->zRem($key, $decodeToken->jti);

            // refresh new token through old
            $ret['token'] = $this->generate($decodeToken->aud, $decodeToken->sub);
        }

        return $ret;
    }

    /**
     * 解码 Token
     *
     * @param string $token
     *
     * @return object|null 成功返回解码对象
     * @throws BeforeValidException
     * @throws DomainException
     * @throws ExpiredException
     * @throws InvalidArgumentException
     * @throws SignatureInvalidException
     * @throws UnexpectedValueException
     */
    private function decode(string $token): ?object {
        try {
            $decoded = $this->jwt->decode($token);

            /** @noinspection PhpExpressionResultUnusedInspection */
            $decoded->aud && $decoded->sub && $decoded->jti && $decoded->src;

            return $decoded;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param string $key
     *
     * @return void
     * @throws LoginException
     */
    private function handleNumLimit(string $key): void {
        // limit max login num
        $maxLoginNum = max((int)env('APP_MAX_LOGIN_NUM'), 0);

        // not allow login
        if (!$maxLoginNum) {
            throw new LoginException('当前不允许登录');
        }

        $loginCount = $this->redis->zCount($key, 0, -1);

        if ($loginCount >= $maxLoginNum) {
            $remLen = $loginCount - $maxLoginNum + 1;

            $this->redis->zRemRangeByScore($key, 0, $remLen);
        }
    }
}
