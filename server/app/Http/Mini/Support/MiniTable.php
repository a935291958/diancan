<?php

declare(strict_types=1);
/**
 * 小程序表名：逻辑前缀 jt_jiating_*，自动去掉全局 DB_PREFIX 避免重复拼接。
 */

namespace App\Http\Mini\Support;

final class MiniTable
{
    public static function name(string $base): string
    {
        $full = (string) env('MINI_TABLE_PREFIX', 'jt_jiating_') . $base;
        $global = (string) env('DB_PREFIX', '');
        if ($global !== '' && str_starts_with($full, $global)) {
            return substr($full, strlen($global));
        }

        return $full;
    }
}
