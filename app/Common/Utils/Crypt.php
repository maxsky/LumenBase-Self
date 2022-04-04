<?php

namespace App\Common\Utils;

use phpseclib3\Crypt\{AES, DES, Hash, PublicKeyLoader, RC2, RC4, RSA, RSA\PrivateKey};
use SodiumException;

/**
 * Class Crypt
 *
 * @package App\Common\Utils
 */
class Crypt {

    /**
     * @param string|null $key
     * @param int         $keyLength
     * @param string|null $iv
     * @param string      $mode AES 模式，默认 ctr
     * @param bool        $disable_padding
     *
     * @return AES
     */
    public static function AES(?string $key = null,
                               int     $keyLength = 128,
                               ?string $iv = null,
                               string  $mode = 'ctr',
                               bool    $disable_padding = false): AES {
        $aes = new AES($mode);

        $key = $key ?: env('APP_KEY');
        $aes->setKey($key);
        $aes->setKeyLength($keyLength);

        if ($iv) {
            $aes->setIV($iv);
        }

        if ($disable_padding) {
            $aes->disablePadding();
        }

        return $aes;
    }

    /**
     * @return DES
     */
    public static function DES(): DES {
        return app(DES::class);
    }

    /**
     * @param string $mode
     *
     * @return RC2
     */
    public static function RC2(string $mode): RC2 {
        return new RC2($mode);
    }

    /**
     * @return RC4
     */
    public static function RC4(): RC4 {
        return app(RC4::class);
    }

    /**
     * @param string      $key
     * @param string|null $password
     * @param int         $padding
     * @param string|null $hash
     * @param string|null $mgf_hash
     *
     * @return RSA|PrivateKey
     */
    public static function RSA(string  $key, ?string $password = null, int $padding = RSA::ENCRYPTION_PKCS1,
                               ?string $hash = null, ?string $mgf_hash = null): RSA|PrivateKey {
        /** @var PrivateKey $loaded */
        $loaded = PublicKeyLoader::load($key, $password);

        $loaded = $loaded->withPadding($padding);

        if ($hash) {
            $loaded = $loaded->withHash($hash);
        }

        if ($mgf_hash) {
            $loaded = $loaded->withMGFHash($mgf_hash);
        }

        return $loaded;
    }

    /**
     * Hash 字符串处理
     *
     * @param string      $str  需处理的字符串
     * @param string      $algo 加密方式
     * @param string|null $key  加密 Key，默认使用框架环境配置 APP_KEY 值
     * @param bool        $hex  是否返回 Hex，默认为 true，否则返回二进制值
     *
     * @return string 返回处理后的 Hash 值
     * @throws SodiumException
     */
    public static function hash(string $str, string $algo = 'sha256', ?string $key = null, bool $hex = true): string {
        $hash = new Hash($algo);
        $hash->setKey($key ?: env('APP_KEY'));
        $hashed = $hash->hash($str);

        if ($hex) {
            if (function_exists('sodium_bin2hex')) {
                return sodium_bin2hex($hashed);
            }

            return bin2hex($hashed);
        }

        return $hashed;
    }
}
