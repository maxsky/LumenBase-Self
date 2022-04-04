<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 2/22/2022
 * Time: 8:03 PM
 */

namespace App\Http\Middleware\Route;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Unlimited;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Exceptions\{HttpResponseException, ThrottleRequestsException};
use Illuminate\Http\Request;
use Illuminate\Support\{Arr, InteractsWithTime};
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRequests {

    use InteractsWithTime;

    /**
     * The rate limiter instance.
     *
     * @var RateLimiter
     */
    protected RateLimiter $limiter;

    /**
     * Create a new request throttler.
     *
     * @param RateLimiter $limiter
     *
     * @return void
     */
    public function __construct(RateLimiter $limiter) {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request    $request
     * @param Closure    $next
     * @param int|string $maxAttempts
     * @param float|int  $decayMinutes
     * @param string     $prefix
     *
     * @return Response
     *
     * @throws ThrottleRequestsException
     */
    public function handle(Request    $request, Closure $next,
                           int|string $maxAttempts = 60, float|int $decayMinutes = 1, string $prefix = ''): Response {
        if (is_string($maxAttempts)
            && func_num_args() === 3
            && !is_null($limiter = $this->limiter->limiter($maxAttempts))) {
            return $this->handleRequestUsingNamedLimiter($request, $next, $maxAttempts, $limiter);
        }

        return $this->handleRequest(
            $request,
            $next,
            [
                (object)[
                    'key' => $prefix . $this->resolveRequestSignature($request),
                    'maxAttempts' => $this->resolveMaxAttempts($request, $maxAttempts),
                    'decayMinutes' => $decayMinutes,
                    'responseCallback' => null,
                ],
            ]
        );
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string  $limiterName
     * @param Closure $limiter
     *
     * @return Response
     *
     * @throws ThrottleRequestsException
     */
    protected function handleRequestUsingNamedLimiter(Request $request, Closure $next,
                                                      string  $limiterName, Closure $limiter): Response {
        $limiterResponse = $limiter($request);

        if ($limiterResponse instanceof Response) {
            return $limiterResponse;
        } elseif ($limiterResponse instanceof Unlimited) {
            return $next($request);
        }

        return $this->handleRequest(
            $request,
            $next,
            collect(Arr::wrap($limiterResponse))->map(function ($limit) use ($limiterName) {
                return (object)[
                    'key' => md5($limiterName . $limit->key),
                    'maxAttempts' => $limit->maxAttempts,
                    'decayMinutes' => $limit->decayMinutes,
                    'responseCallback' => $limit->responseCallback,
                ];
            })->all()
        );
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param array   $limits
     *
     * @return Response
     *
     * @throws ThrottleRequestsException
     */
    protected function handleRequest(Request $request, Closure $next, array $limits): Response {
        foreach ($limits as $limit) {
            if ($this->limiter->tooManyAttempts($limit->key, $limit->maxAttempts)) {
                throw $this->buildException($request, $limit->key, $limit->maxAttempts, $limit->responseCallback);
            }

            $this->limiter->hit($limit->key, $limit->decayMinutes * 60);
        }

        $response = $next($request);

        foreach ($limits as $limit) {
            $response = $this->addHeaders(
                $response,
                $limit->maxAttempts,
                $this->calculateRemainingAttempts($limit->key, $limit->maxAttempts)
            );
        }

        return $response;
    }

    /**
     * Resolve the number of attempts if the user is authenticated or not.
     *
     * @param Request    $request
     * @param int|string $maxAttempts
     *
     * @return int
     */
    protected function resolveMaxAttempts(Request $request, int|string $maxAttempts): int {
        if (str_contains($maxAttempts, '|')) {
            $maxAttempts = explode('|', $maxAttempts, 2)[$request->user() ? 1 : 0];
        }

        if (!is_numeric($maxAttempts) && $request->user()) {
            $maxAttempts = $request->user()->{$maxAttempts};
        }

        return (int)$maxAttempts;
    }

    /**
     * Resolve request signature.
     *
     * @param Request $request
     *
     * @return string
     *
     * @throws RuntimeException
     */
    protected function resolveRequestSignature(Request $request): string {
        /** @var Authenticatable|null $user */
        if ($user = $request->user()) {
            return sha1($user->getAuthIdentifier());
        }

        return $request->fingerprint();
    }

    /**
     * Create a 'too many attempts' exception.
     *
     * @param Request       $request
     * @param string        $key
     * @param int           $maxAttempts
     * @param callable|null $responseCallback
     *
     * @return HttpResponseException|ThrottleRequestsException
     */
    protected function buildException(Request  $request,
                                      string   $key,
                                      int      $maxAttempts,
                                      callable $responseCallback = null): HttpResponseException|ThrottleRequestsException {
        $retryAfter = $this->getTimeUntilNextRetry($key);

        $headers = $this->getHeaders(
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts, $retryAfter),
            $retryAfter
        );

        return is_callable($responseCallback)
            ? new HttpResponseException($responseCallback($request, $headers))
            : new ThrottleRequestsException('Too Many Attempts.', null, $headers);
    }

    /**
     * Get the number of seconds until the next retry.
     *
     * @param string $key
     *
     * @return int
     */
    protected function getTimeUntilNextRetry(string $key): int {
        return $this->limiter->availableIn($key);
    }

    /**
     * Add the limit header information to the given response.
     *
     * @param Response $response
     * @param int      $maxAttempts
     * @param int      $remainingAttempts
     * @param int|null $retryAfter
     *
     * @return Response
     */
    protected function addHeaders(Response $response,
                                  int      $maxAttempts, int $remainingAttempts, int $retryAfter = null): Response {
        $response->headers->add(
            $this->getHeaders($maxAttempts, $remainingAttempts, $retryAfter, $response)
        );

        return $response;
    }

    /**
     * Get the limit headers information.
     *
     * @param int           $maxAttempts
     * @param int           $remainingAttempts
     * @param int|null      $retryAfter
     * @param Response|null $response
     *
     * @return array
     */
    protected function getHeaders(int       $maxAttempts,
                                  int       $remainingAttempts,
                                  int       $retryAfter = null,
                                  ?Response $response = null): array {
        if ($response &&
            !is_null($response->headers->get('X-RateLimit-Remaining')) &&
            (int)$response->headers->get('X-RateLimit-Remaining') <= $remainingAttempts) {
            return [];
        }

        $headers = [
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ];

        if (!is_null($retryAfter)) {
            $headers['Retry-After'] = $retryAfter;
            $headers['X-RateLimit-Reset'] = $this->availableAt($retryAfter);
        }

        return $headers;
    }

    /**
     * Calculate the number of remaining attempts.
     *
     * @param string   $key
     * @param int      $maxAttempts
     * @param int|null $retryAfter
     *
     * @return int
     */
    protected function calculateRemainingAttempts(string $key, int $maxAttempts, int $retryAfter = null): int {
        return is_null($retryAfter) ? $this->limiter->retriesLeft($key, $maxAttempts) : 0;
    }
}
