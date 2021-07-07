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
                                $files[] = '/' . $file;
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
