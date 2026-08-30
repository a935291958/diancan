<?php

declare(strict_types=1);
/**
 * Unix 秒级时间戳。无 update_time 的表将 UPDATED_AT 置为 null。
 */

namespace App\Http\Mini\Model\Concern;

use App\Http\Mini\Support\Time;

trait UnixTimestamp
{
    /** 关闭框架默认 datetime 时间戳，改由本 Trait 写入 Unix 秒. */
    public bool $timestamps = false;

    public const CREATED_AT = 'create_time';

    public const UPDATED_AT = 'update_time';

    public static function bootUnixTimestamp(): void
    {
        static::creating(static function ($model): void {
            $now = Time::now();
            $createdAt = $model::CREATED_AT;
            if ($createdAt && empty($model->{$createdAt})) {
                $model->{$createdAt} = $now;
            }
            $updatedAt = $model::UPDATED_AT;
            if ($updatedAt) {
                $model->{$updatedAt} = $now;
            }
        });

        static::updating(static function ($model): void {
            $updatedAt = $model::UPDATED_AT;
            if ($updatedAt) {
                $model->{$updatedAt} = Time::now();
            }
        });
    }
}
