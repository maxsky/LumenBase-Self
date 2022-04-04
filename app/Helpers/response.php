<?php

/**
 * Created by IntelliJ IDEA.
 * User: maxsky
 * Date: 2021/7/7
 * Time: 17:43
 */

use Illuminate\Http\{JsonResponse, Response};

if (!function_exists('success')) {
    /**
     * @param string     $msg  返回消息
     * @param mixed|null $data 额外数据
     *
     * @return JsonResponse
     */
    function success(string $msg = 'OK', mixed $data = null): JsonResponse {
        return response()->json([
            'message' => $msg,
            'result' => 1,
            'data' => $data
        ]);
    }
}

if (!function_exists('failed')) {
    /**
     * @param string $msg        返回消息
     * @param int    $error_code 错误码
     *
     * @return JsonResponse
     */
    function failed(string $msg = 'Failed', int $error_code = 0): JsonResponse {
        return response()->json([
            'message' => $msg,
            'result' => 0,
            'error_code' => $error_code
        ]);
    }
}

if (!function_exists('xml')) {
    /**
     * @param string $contents
     *
     * @return Response
     */
    function xml(string $contents): Response {
        return response($contents, 200, [
            'content-type' => 'application/xml,text/xml'
        ]);
    }
}

if (!function_exists('image')) {
    /**
     * @param string      $contents
     * @param string|null $format
     *
     * @return Response
     */
    function image(string $contents, ?string $format = null): Response {
        if ($format) {
            $contentType = "image/$format";
        } else {
            $contentType = getFileMimeType($contents, false, true);
        }

        return response($contents, 200, [
            'content-type' => $contentType
        ]);
    }
}
