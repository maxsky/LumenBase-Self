<?php

/**
 * [!!!]DO NOT MODIFY THIS FUNCTION[!!!]
 */
if (!function_exists('read_dir_queue')) {
    /**
     * 队列读目录内文件
     *
     * @param string $dir  目录
     * @param int    $type 返回类型。0 仅返回无后缀格式文件名；1 仅返回完整文件名；2 返回带路径的完整文件名
     *
     * @return array
     */
    function read_dir_queue(string $dir, int $type = 0): array {
        $files = [];
        $queue = [$dir];

        while ($path = current($queue)) {
            next($queue);

            if (is_dir($path) && $handle = opendir($path)) {

                while ($file = readdir($handle)) {
                    if ($file === '.' || $file === '..' || $file === '.DS_Store') {
                        continue;
                    }

                    $real_path = $path . '/' . $file;

                    if (is_dir($real_path)) {
                        $queue[] = $real_path;
                        continue;
                    }

                    switch ($type) {
                        case 1:
                            if ($path == $dir) {
                                $files[] = $file;
                            } else {
                                $files[] = substr($path, strripos($path, '/') + 1) . '/' . $file;
                            }

                            break;
                        case 2:
                            $files[] = $real_path;

                            break;
                        default:
                            $files[] = current(explode('.', $file));
                    }
                }

                closedir($handle);
            }
        }

        return $files;
    }
}

if (!function_exists('getFileMimeType')) {
    /**
     * @param string $contents
     * @param bool   $is_path
     * @param bool   $full_type
     * @param bool   $include_dot
     *
     * @return false|string
     */
    function getFileMimeType(string $contents, bool $is_path = true, bool $full_type = false, bool $include_dot = true): bool|string {
        if (!$is_path) {
            $temp = tmpfile();
            fwrite($temp, $contents);

            $contents = stream_get_meta_data($temp)['uri']; // eg: /tmp/phpFx0513a
        }

        if ($full_type) {
            return mime_content_type($contents);
        }

        return image_type_to_extension(exif_imagetype($contents), $include_dot);
    }
}

if (!function_exists('getFirstInvalidMsg')) {
    /**
     * 取第一个验证失败消息
     *
     * @param array $errors 验证错误数组
     *
     * @return string 成功返回第一个验证失败消息，否则返回空字符串
     */
    function getFirstInvalidMsg(array $errors): string {
        return $errors && isset(current($errors)[0]) ? current($errors)[0] : '未知错误';
    }
}

if (!function_exists('ex_mt_rand')) {
    /**
     * 生成前部可为零的大范围随机数并以字符串形式返回
     *
     * @param int $length 长度
     *
     * @return string
     */
    function ex_mt_rand(int $length): string {
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }

        return $result;
    }
}

if (!function_exists('ex_str_rand')) {
    /**
     * 生成随机字符串
     *
     * @param int    $length 长度，默认 16
     * @param string $prefix 前缀，默认为空字符串。前缀不影响随机长度
     *
     * @return string
     */
    function ex_str_rand(int $length = 16, string $prefix = ''): string {
        return $prefix . Illuminate\Support\Str::random($length);
    }
}

if (!function_exists('msectime')) {
    /**
     * 取毫秒级时间戳，默认返回普通秒级时间戳 time() 及 3 位长度毫秒字符串
     *
     * @param int $msec_length 毫秒长度，默认 3
     *
     * @return string
     */
    function msectime(int $msec_length = 3): string {
        [$msec, $sec] = explode(' ', microtime());

        return sprintf('%.0f', (floatval($msec) + floatval($sec)) * pow(10, $msec_length));
    }
}

if (!function_exists('getSetCache')) {
    /**
     * @param string                             $key
     * @param mixed|null                         $value
     * @param DateInterval|DateTimeInterface|int $ttl
     *
     * @return mixed
     */
    function getSetCache(string $key, mixed $value = null, DateInterval|DateTimeInterface|int $ttl = 86400): mixed {
        $content = Illuminate\Support\Facades\Cache::get($key);

        if (!$content) {
            if ($value instanceof Closure) {
                $value = call_user_func($value);
            }

            if ($value) {
                Illuminate\Support\Facades\Cache::put($key, $value, $ttl);
                return $value;
            }

            $content = $value;
        }

        return $content;
    }
}

if (!function_exists('formatPrice')) {
    /**
     * Will be divide 100
     *
     * @param float|int $price
     *
     * @return string
     */
    function formatPrice(float|int $price): string {
        return number_format($price / 100, '2', '.', '');
    }
}

if (!function_exists('trimDomainFromHTML')) {
    /**
     * @param string $html
     *
     * @return string
     */
    function trimDomainFromHTML(string $html): string {
        return preg_replace('/https?:\/\/.*?\//im', '/', $html);
    }
}

if (!function_exists('addDomainToHTML')) {
    /**
     * @param string      $domain
     * @param string|null $html
     *
     * @return string
     */
    function addDomainToHTML(string $domain, ?string $html = ''): string {
        return preg_replace_callback('/src=\"\/.*?\"/', function ($item) use ($domain) {
            return "src=\"$domain" . substr($item[0], 5);
        }, (string)$html);
    }
}

if (!function_exists('parseQrSceneValue')) {
    /**
     * @param string $scene_value
     *
     * @return string|null
     */
    function parseQrSceneValue(string $scene_value): ?string {
        return str_replace('qrscene_', '', $scene_value) ?: null;
    }
}

if (!function_exists('getResLink')) {
    /**
     * @param string|null $source
     *
     * @return string
     */
    function getResLink(?string $source = null): string {
        if (!$source) {
            return '';
        }

        return str_starts_with($source, '/') ? env('APP_RES_DOMAIN') . $source : $source;
    }
}
