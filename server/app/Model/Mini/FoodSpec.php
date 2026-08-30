<?php

declare(strict_types=1);
/**
 * 菜品规格 jt_jiating_food_spec（无 family_id，经 food 关联隔离）.
 *
 * @property int $id
 * @property int $food_id
 * @property string $spec_name
 * @property string $spec_value
 * @property int $create_time
 */

namespace App\Model\Mini;

use App\Context\AuthContext;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\BelongsTo;

class FoodSpec extends AbstractMiniModel
{
    public const UPDATED_AT = null;

    protected string $baseTable = 'food_spec';

    protected array $fillable = [
        'food_id',
        'spec_name',
        'spec_value',
        'create_time',
    ];

    protected array $casts = [
        'id' => 'integer',
        'food_id' => 'integer',
        'create_time' => 'integer',
    ];

    public static function boot(): void
    {
        parent::boot();
        static::addGlobalScope('family_isolate', static function (Builder $builder): void {
            $ids = AuthContext::familyIds();
            if ($ids === null) {
                return;
            }
            if ($ids === []) {
                $builder->whereRaw('1 = 0');

                return;
            }
            $foodTable = mini_table('food');
            $specTable = mini_table('food_spec');
            $builder->whereIn($specTable . '.food_id', static function ($query) use ($foodTable, $ids): void {
                $query->from($foodTable)->select('id')->whereIn('family_id', $ids);
            });
        });
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class, 'food_id', 'id');
    }
}
