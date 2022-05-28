<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\{Exceptions\ThrottleRequestsException, JsonResponse, Request, Response};
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use RedisException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler {
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        //SuspiciousOperationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param Throwable $e
     *
     * @return void
     *
     * @throws Exception
     */
    public function report(Throwable $e): void {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request   $request
     * @param Throwable $e
     *
     * @return JsonResponse|Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $e): JsonResponse|Response {
        // 404 & 405
        if ($e instanceof NotFoundHttpException | $e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'message' => '请求不可用',
                'result' => 0,
                'error_code' => $e->getStatusCode()
            ], $e->getStatusCode());
        }

        if ($e instanceof ThrottleRequestsException) {
            return response()->json([
                'message' => '操作太快，休息一会儿',
                'result' => 0
            ], $e->getStatusCode(), $e->getHeaders());
        }

        // request params validate result
        if ($e instanceof ValidationException) {
            return failed(getFirstInvalidMsg($e->errors()), 400);
        }

        if ($e instanceof RedisException) {
            return failed('系统缓存服务异常', 500);
        }

        // base message
        if ($e instanceof BaseMessageException) {
            return failed($e->getMessage(), $e->getCode());
        }

        //if ($e instanceof ContainerExceptionInterface) {
        //    return failed('请求失败，请重试', 400);
        //}

        return parent::render($request, $e);
    }
}
