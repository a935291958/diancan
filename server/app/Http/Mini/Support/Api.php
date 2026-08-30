<?php

declare(strict_types=1);
/**
 * 统一 API 返回封装。固定结构：{"code":200,"message":"","data":{}}.
 *
 * 状态码与文案集中在 ResultCode + storage/languages/{locale}/result.php，
 * 新增业务码时两处一起改，便于 Cursor 迭代时保持前后端约定一致。
 */

namespace App\Http\Mini\Support;

use App\Http\Common\Result;
use App\Http\Common\ResultCode;

final class Api
{
    /**
     * 成功。未传 data 时输出空对象 {}，避免前端拿到 [] 误判。
     */
    public static function success(mixed $data = null, ?string $message = null): Result
    {
        return new Result(
            ResultCode::SUCCESS,
            $message,
            self::normalizeData($data)
        );
    }

    /**
     * 失败。code 使用 ResultCode，message 缺省走语言包。
     */
    public static function error(ResultCode $code = ResultCode::FAIL, ?string $message = null, mixed $data = null): Result
    {
        return new Result($code, $message, self::normalizeData($data));
    }

    /**
     * 空 data 统一为对象，列表/关联数组保持原样。
     */
    public static function normalizeData(mixed $data): mixed
    {
        if ($data === null) {
            return new \stdClass();
        }

        return $data;
    }
}
