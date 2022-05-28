<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ResponseHeader {

    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed {
        $response = $next($request);

        $attrs = $request->attributes->all();

        if ($attrs) {
            $response->withHeaders($attrs);
        }

        return $response;
    }
}
