<?php

declare(strict_types=1);
/**
 * 统一时间戳处理。业务表 create_time / update_time 为 Unix 秒级整数。
 */

namespace App\Http\Mini\Support;

final class Time
{
    /**
     * 当前 Unix 秒.
     */
    public static function now(): int
    {
        return time();
    }

    /**
     * 任意值转为 Unix 秒，无法解析时返回 0.
     */
    public static function toTimestamp(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }
        if (is_numeric($value)) {
            $int = (int) $value;
            // 毫秒时间戳兼容
            if ($int > 9999999999) {
                return (int) floor($int / 1000);
            }

            return $int;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }
        $parsed = strtotime((string) $value);

        return $parsed === false ? 0 : $parsed;
    }

    /**
     * 格式化为日期时间字符串.
     */
    public static function format(mixed $value, string $pattern = 'Y-m-d H:i:s'): string
    {
        $ts = self::toTimestamp($value);

        return $ts > 0 ? date($pattern, $ts) : '';
    }

    /**
     * 今日日期 Y-m-d，供点餐 order_date 默认值.
     */
    public static function today(): string
    {
        return date('Y-m-d');
    }
}
