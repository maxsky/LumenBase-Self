<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrimStrings extends TransformsRequest {

    /**
     * All of the registered skip callbacks.
     *
     * @var array
     */
    protected static array $skipCallbacks = [];

    /**
     * The attributes that should not be trimmed.
     *
     * @var array
     */
    protected array $except = [
        //
    ];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed {
        foreach (static::$skipCallbacks as $callback) {
            if ($callback($request)) {
                return $next($request);
            }
        }

        return parent::handle($request, $next);
    }

    /**
     * Transform the given value.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function transform(string $key, mixed $value): mixed {
        if (in_array($key, $this->except, true)) {
            return $value;
        }

        return is_string($value) ? preg_replace('~^\s+|\s+$~iu', '', $value) : $value;
    }

    /**
     * Register a callback that instructs the middleware to be skipped.
     *
     * @param Closure $callback
     *
     * @return void
     */
    public static function skipWhen(Closure $callback) {
        static::$skipCallbacks[] = $callback;
    }
}
