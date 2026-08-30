<?php

declare(strict_types=1);
/**
 * 数据格式化：模型转数组、分页结构、隐藏敏感字段。
 * 控制器/Service 对外输出前走这里，保证小程序字段为 snake_case。
 */

namespace App\Support;

use Hyperf\Contract\Arrayable;
use Hyperf\Database\Model\Model;

final class Formatter
{
    /**
     * 模型或数组转为纯数组，并规范化时间戳字段为 int.
     *
     * @param  array<string, mixed>|Arrayable|Model|null  $row
     * @return array<string, mixed>
     */
    public static function row(mixed $row, array $hidden = []): array
    {
        if ($row === null) {
            return [];
        }
        if ($row instanceof Model) {
            $data = $row->toArray();
        } elseif ($row instanceof Arrayable) {
            $data = $row->toArray();
        } elseif (is_array($row)) {
            $data = $row;
        } else {
            return [];
        }

        foreach (['create_time', 'update_time'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = Time::toTimestamp($data[$field]);
            }
        }

        foreach ($hidden as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    /**
     * 列表格式化.
     *
     * @param  iterable<mixed>  $rows
     * @return array<int, array<string, mixed>>
     */
    public static function list(iterable $rows, array $hidden = []): array
    {
        $result = [];
        foreach ($rows as $row) {
            $result[] = self::row($row, $hidden);
        }

        return $result;
    }

    /**
     * 分页结构，前端 unwrapList 可识别 list.
     *
     * @return array{list: array<int, array<string, mixed>>, total: int, page: int, page_size: int}
     */
    public static function page(iterable $rows, int $total, int $page = 1, int $pageSize = 20, array $hidden = []): array
    {
        return [
            'list' => self::list($rows, $hidden),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * cook_uids 逗号串 <-> int 数组.
     *
     * @return array<int, int>
     */
    public static function splitIds(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_unique(array_filter(array_map('intval', $value))));
        }

        if ($value === null || $value === '') {
            return [];
        }

        $parts = preg_split('/[,\s]+/', (string) $value) ?: [];

        return array_values(array_unique(array_filter(array_map('intval', $parts))));
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    public static function joinIds(array $ids): string
    {
        return implode(',', array_values(array_unique(array_filter(array_map('intval', $ids)))));
    }
}
