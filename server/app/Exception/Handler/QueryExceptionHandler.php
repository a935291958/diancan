<?php

declare(strict_types=1);
/**
 * 数据库异常不向客户端泄露 SQL / 表结构。
 */

namespace App\Exception\Handler;

use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use Hyperf\Database\Exception\QueryException;
use PDOException;

final class QueryExceptionHandler extends AbstractHandler
{
    public function handleResponse(\Throwable $throwable): Result
    {
        $this->stopPropagation();
        $message = $this->isDebug() ? $throwable->getMessage() : '数据操作失败';

        return new Result(ResultCode::FAIL, $message);
    }

    public function isValid(\Throwable $throwable): bool
    {
        return $throwable instanceof QueryException || $throwable instanceof PDOException;
    }
}
