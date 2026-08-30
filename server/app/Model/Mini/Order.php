<?php

declare(strict_types=1);
/**
 * 点餐记录 jt_jiating_order.
 *
 * @property int $id
 * @property int $family_id
 * @property int $food_id
 * @property string $select_spec
 * @property int $order_uid
 * @property int $cook_uid
 * @property string $meal_type
 * @property string $order_date
 * @property int $status
 * @property int $create_time
 */

namespace App\Model\Mini;

use App\Model\Mini\Concern\FamilyIsolate;
use Hyperf\Database\Model\Relations\BelongsTo;

class Order extends AbstractMiniModel
{
    use FamilyIsolate;

    public const UPDATED_AT = null;

    /** 待制作 */
    public const STATUS_PENDING = 1;

    /** 制作中 */
    public const STATUS_COOKING = 2;

    /** 已完成 */
    public const STATUS_DONE = 3;

    /** 已取消 */
    public const STATUS_CANCELLED = 4;

    protected string $baseTable = 'order';

    protected array $fillable = [
        'family_id',
        'food_id',
        'select_spec',
        'order_uid',
        'cook_uid',
        'meal_type',
        'order_date',
        'status',
        'create_time',
    ];

    protected array $casts = [
        'id' => 'integer',
        'family_id' => 'integer',
        'food_id' => 'integer',
        'order_uid' => 'integer',
        'cook_uid' => 'integer',
        'status' => 'integer',
        'create_time' => 'integer',
    ];

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class, 'food_id', 'id');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'family_id', 'id');
    }

    public function orderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'order_uid', 'id');
    }

    public function cook(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cook_uid', 'id');
    }
}
