<?php

declare(strict_types=1);
/**
 * 小程序业务模型基类。
 *
 * 分层约定（Cursor 迭代请遵守）：
 * - Model：表映射、关联、作用域，禁止写 HTTP / 鉴权分支。
 * - 时间字段为 Unix 秒 create_time / update_time。
 * - 带 family_id 的表使用 FamilyIsolate Trait，自动隔离家庭数据。
 */

namespace App\Http\Mini\Model;

use App\Http\Mini\Model\Concern\UnixTimestamp;
use Hyperf\DbConnection\Model\Model;

abstract class AbstractMiniModel extends Model
{
    use UnixTimestamp;

    /**
     * 不含前缀的表名，例如 user => jt_jiating_user.
     */
    protected string $baseTable = '';

    public function __construct(array $attributes = [])
    {
        if ($this->baseTable !== '') {
            $this->table = mini_table($this->baseTable);
        }
        parent::__construct($attributes);
    }
}
