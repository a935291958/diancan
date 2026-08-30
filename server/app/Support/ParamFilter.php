<?php

declare(strict_types=1);
/**
 * 全局参数清洗：去空字节、修剪空白、限制异常层级。
 * 不做 htmlspecialchars，避免破坏昵称/菜名中的合法字符。
 */

namespace App\Support;

final class ParamFilter
{
    /**
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    public static function filter(array $data, int $depth = 0): array
    {
        if ($depth > 8) {
            return [];
        }
        $clean = [];
        foreach ($data as $key => $value) {
            $safeKey = is_string($key) ? self::cleanString($key) : $key;
            if (is_array($value)) {
                $clean[$safeKey] = self::filter($value, $depth + 1);
                continue;
            }
            if (is_string($value)) {
                $clean[$safeKey] = self::cleanString($value);
                continue;
            }
            $clean[$safeKey] = $value;
        }

        return $clean;
    }

    public static function cleanString(string $value): string
    {
        $value = str_replace("\0", '', $value);
        // 去掉 ASCII 控制符，保留 TAB/LF/CR
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? $value;

        return trim($value);
    }
}
