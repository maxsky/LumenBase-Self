<?php

namespace App\Exceptions;

use Dingo\Api\Contract\Debug\ExceptionHandler;
use Dingo\Api\Exception\{Handler as DingoHandler, RateLimitExceededException};
use Exception;
use Illuminate\Validation\ValidationException;

class DingoExceptionHandler extends DingoHandler implements ExceptionHandler {

    public function handle(Exception $exception) {
        if ($exception instanceof RateLimitExceededException) {
            return response([
                'message' => '当前操作太过频繁',
                'result' => 0,
                'status_code' => 429
            ])->withHeaders($exception->getHeaders());
        }

        if ($exception instanceof ValidationException) {
            return response([
                'message' =>  getFirstInvalidMsg($exception->errors()),
                'result' => 0,
                'status_code' => 400
            ]);
        }

        return parent::handle($exception);
    }
}
