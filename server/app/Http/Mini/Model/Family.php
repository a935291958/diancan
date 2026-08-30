<?php

declare(strict_types=1);
/**
 * 家庭 jt_jiating_family.
 *
 * 关联：members / admin / foods / orders。
 * 本表无 family_id，越权校验在 Service + AuthContext::assertFamily。
 *
 * @property int $id
 * @property string $family_name
 * @property string $invite_code
 * @property int $admin_uid
 * @property int $create_time
 * @property int $update_time
 */

namespace App\Http\Mini\Model;

use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasManyThrough;

class Family extends AbstractMiniModel
{
    protected string $baseTable = 'family';

    protected array $fillable = [
        'family_name',
        'invite_code',
        'admin_uid',
        'create_time',
        'update_time',
    ];

    protected array $casts = [
        'id' => 'integer',
        'admin_uid' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(FamilyMember::class, 'family_id', 'id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_uid', 'id');
    }

    public function foods(): HasMany
    {
        return $this->hasMany(Food::class, 'family_id', 'id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'family_id', 'id');
    }

    public function specs(): HasManyThrough
    {
        return $this->hasManyThrough(FoodSpec::class, Food::class, 'family_id', 'food_id', 'id', 'id');
    }

    public function isAdmin(int $uid): bool
    {
        return (int) $this->admin_uid === $uid;
    }
}
