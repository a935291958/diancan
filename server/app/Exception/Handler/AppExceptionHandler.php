<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace App\Exception\Handler;

use App\Http\Common\Result;
use App\Http\Common\ResultCode;

final class AppExceptionHandler extends AbstractHandler
{
    public function handleResponse(\Throwable $throwable): Result
    {
        $this->stopPropagation();
        $message = $this->isDebug()
            ? $throwable->getMessage()
            : '服务器内部错误';

        return new Result(
            code: ResultCode::FAIL,
            message: $message !== '' ? $message : '服务器内部错误',
        );
    }

    public function isValid(\Throwable $throwable): bool
    {
        return true;
    }
}
