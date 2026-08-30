<?php

declare(strict_types=1);
/**
 * 防 SQL 注入辅助。
 *
 * 查询一律走 Query Builder / Eloquent 绑定参数；本类负责：
 * 1. 检测字符串是否像注入 payload，供参数过滤器拒绝请求；
 * 2. LIKE 查询转义 % _ \，避免通配符被当成条件。
 *
 * 禁止拼接：Db::select("... {$id}")、whereRaw 拼用户输入。
 */

namespace App\Support;

final class SqlSafe
{
    /**
     * 高危 SQL 特征（大小写不敏感）。命中即视为注入尝试。
     */
    private const PATTERNS = [
        '/\b(sleep|benchmark|extractvalue|updatexml|load_file|outfile|dumpfile)\s*\(/i',
        '/\bunion\s+all\s+select\b/i',
        '/\bunion\s+select\b/i',
        '/\binformation_schema\b/i',
        '/\binto\s+(out|dump)file\b/i',
        '/;\s*(drop|alter|truncate|rename|create)\s+(table|database|index)\b/i',
        '/\bor\s+1\s*=\s*1\b/i',
        '/\band\s+1\s*=\s*1\b/i',
        '/\/\*|\*\//',
        '/\bxp_cmdshell\b/i',
    ];

    /**
     * 是否像 SQL 注入字符串.
     */
    public static function looksLikeInject(mixed $value): bool
    {
        if (! is_string($value) || $value === '') {
            return false;
        }
        // 过短或纯中文/数字不检测，降低误杀
        if (strlen($value) < 6 && ! preg_match('/[;\'"]/', $value)) {
            return false;
        }
        foreach (self::PATTERNS as $pattern) {
            if (preg_match($pattern, $value) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * 递归检测数组/标量.
     */
    public static function containsInject(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if (self::containsInject($item)) {
                    return true;
                }
            }

            return false;
        }

        return self::looksLikeInject($value);
    }

    /**
     * LIKE 模糊查询转义，配合 where('name', 'like', '%'.$kw.'%').
     */
    public static function escapeLike(string $keyword): string
    {
        return addcslashes($keyword, '%_\\');
    }

    /**
     * 白名单整型 ID，非法则返回 0.
     */
    public static function uint(mixed $value): int
    {
        if (! is_numeric($value)) {
            return 0;
        }
        $id = (int) $value;

        return $id > 0 ? $id : 0;
    }
}
