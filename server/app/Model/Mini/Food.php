<?php

declare(strict_types=1);
/**
 * 菜品 jt_jiating_food.
 *
 * 关联：specs 一对多、family、creator、orders。
 * FamilyIsolate 自动限制 family_id。
 *
 * @property int $id
 * @property int $family_id
 * @property string $food_name
 * @property string $food_img
 * @property string $category
 * @property string $cook_uids
 * @property int $create_uid
 * @property int $create_time
 * @property int $update_time
 */

namespace App\Model\Mini;

use App\Model\Mini\Concern\FamilyIsolate;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\Relations\HasMany;

class Food extends AbstractMiniModel
{
    use FamilyIsolate;

    protected string $baseTable = 'food';

    protected array $fillable = [
        'family_id',
        'food_name',
        'food_img',
        'category',
        'cook_uids',
        'create_uid',
        'create_time',
        'update_time',
    ];

    protected array $casts = [
        'id' => 'integer',
        'family_id' => 'integer',
        'create_uid' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function specs(): HasMany
    {
        return $this->hasMany(FoodSpec::class, 'food_id', 'id');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'family_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'create_uid', 'id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'food_id', 'id');
    }
}
