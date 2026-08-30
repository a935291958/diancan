<?php

declare(strict_types=1);
/**
 * 家庭数据隔离全局作用域。
 * AuthContext::familyIds() 为 null 时不生效（后台/CLI）；
 * 已登录则强制 whereIn(family_id, 所属家庭)。
 */

namespace App\Model\Mini\Concern;

use App\Context\AuthContext;
use Hyperf\Database\Model\Builder;

trait FamilyIsolate
{
    /**
     * 覆盖此方法可改隔离字段，默认 family_id.
     */
    public function familyIsolateColumn(): string
    {
        return 'family_id';
    }

    public static function bootFamilyIsolate(): void
    {
        static::addGlobalScope('family_isolate', static function (Builder $builder): void {
            $ids = AuthContext::familyIds();
            if ($ids === null) {
                return;
            }
            $model = $builder->getModel();
            $column = method_exists($model, 'familyIsolateColumn')
                ? $model->familyIsolateColumn()
                : 'family_id';
            $qualified = $model->getTable() . '.' . $column;
            if ($ids === []) {
                $builder->whereRaw('1 = 0');

                return;
            }
            $builder->whereIn($qualified, $ids);
        });
    }
}
