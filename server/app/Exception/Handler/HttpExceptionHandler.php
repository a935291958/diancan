<?php

declare(strict_types=1);
/**
 * HTTP 异常（404/405 等）统一为 {code,message,data}.
 */

namespace App\Exception\Handler;

use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Exception\MethodNotAllowedHttpException;
use Hyperf\HttpMessage\Exception\NotFoundHttpException;

final class HttpExceptionHandler extends AbstractHandler
{
    public function handleResponse(\Throwable $throwable): Result
    {
        $this->stopPropagation();

        if ($throwable instanceof NotFoundHttpException) {
            return new Result(ResultCode::NOT_FOUND, '接口不存在');
        }
        if ($throwable instanceof MethodNotAllowedHttpException) {
            return new Result(ResultCode::METHOD_NOT_ALLOWED);
        }

        $status = $throwable instanceof HttpException ? $throwable->getStatusCode() : 500;
        $code = ResultCode::tryFrom($status) ?? ResultCode::FAIL;

        return new Result($code, $throwable->getMessage() ?: null);
    }

    public function isValid(\Throwable $throwable): bool
    {
        return $throwable instanceof HttpException;
    }
}
