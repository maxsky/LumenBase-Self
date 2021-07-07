<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

class TrimStrings {

    /**
     * The additional attributes passed to the middleware.
     */
    protected array $attributes = [

    ];

    /**
     * The attributes that should not be trimmed.
     */
    protected array $except = [
        'password',
        'password_confirmation'
    ];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @param array   $attributes
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$attributes) {
        $this->attributes = $attributes;

        $this->clean($request);

        return $next($request);
    }

    /**
     * Clean the request's data.
     *
     * @param Request $request
     *
     * @return void
     */
    protected function clean(Request $request) {
        $this->cleanParameterBag($request->query);

        if ($request->isJson()) {
            $this->cleanParameterBag($request->json());
        } else {
            $this->cleanParameterBag($request->request);
        }
    }

    /**
     * Clean the data in the parameter bag.
     *
     * @param ParameterBag $bag
     *
     * @return void
     */
    protected function cleanParameterBag(ParameterBag $bag) {
        $bag->replace($this->cleanArray($bag->all()));
    }

    /**
     * Clean the data in the given array.
     *
     * @param array $data
     *
     * @return array
     */
    protected function cleanArray(array $data): array {
        return collect($data)->map(function ($value, $key) {
            return $this->cleanValue($key, $value);
        })->all();
    }

    /**
     * Clean the given value.
     *
     * @param string       $key
     * @param array|string $value
     *
     * @return array|string
     */
    protected function cleanValue(string $key, $value) {
        if (is_array($value)) {
            return $this->cleanArray($value);
        }

        return $this->transform($key, $value);
    }

    /**
     * Transform the given value.
     *
     * @param string       $key
     * @param array|string $value
     *
     * @return array|string
     */
    protected function transform(string $key, $value) {
        if (in_array($key, $this->except, true)) {
            return $value;
        }

        return is_string($value) ? trim($value) : $value;
    }
}
