<?php

declare(strict_types=1);
/**
 * 小程序 API 全局函数。Composer autoload files 加载。
 *
 * 控制器请优先用 $this->success() / $this->error()；
 * Service 层抛错请用 api_abort()，由异常处理器统一包装为 {code,message,data}。
 */

use App\Exception\BusinessException;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Support\Api;

if (! function_exists('api_success')) {
    /**
     * 成功响应：{"code":200,"message":"...","data":{}}.
     */
    function api_success(mixed $data = null, ?string $message = null): Result
    {
        return Api::success($data, $message);
    }
}

if (! function_exists('api_error')) {
    /**
     * 失败响应（不抛异常，直接返回给客户端）.
     */
    function api_error(?string $message = null, ResultCode $code = ResultCode::FAIL, mixed $data = null): Result
    {
        return Api::error($code, $message, $data);
    }
}

if (! function_exists('api_abort')) {
    /**
     * 中断请求并抛出业务异常，最终仍返回统一 JSON。
     *
     * @throws BusinessException
     */
    function api_abort(ResultCode $code = ResultCode::FAIL, ?string $message = null, mixed $data = []): never
    {
        throw new BusinessException($code, $message, $data);
    }
}

if (! function_exists('mini_table')) {
    /**
     * 拼接小程序业务表名，并去掉全局 DB_PREFIX，避免重复前缀。
     *
     * 例：MINI_TABLE_PREFIX=jt_jiating_ + DB_PREFIX=jt_ + user
     *     => 逻辑全名 jt_jiating_user，模型 table=jiating_user，SQL 最终 jt_jiating_user.
     */
    function mini_table(string $name): string
    {
        $full = (string) config('mini.table_prefix', 'jt_jiating_') . $name;
        $global = (string) config('databases.default.prefix', '');
        if ($global !== '' && str_starts_with($full, $global)) {
            return substr($full, strlen($global));
        }

        return $full;
    }
}
