<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrimResponseData {

    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed {
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $response->setEncodingOptions(DEFAULT_JSON_ENCODING_OPTIONS);

            $content = json_decode($response->getContent());

            if (empty($content->data ?? null)) {
                unset($content->data);

                $response->setData($content);
            }
        }

        return $response;
    }
}
