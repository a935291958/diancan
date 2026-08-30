<?php

declare(strict_types=1);
/**
 * 小程序用户 jt_jiating_user.
 *
 * 关联：
 * - families() 多对多，经 family_member
 * - memberships() 成员记录
 * - orders() 作为点餐人
 * - cookOrders() 作为烹饪人
 *
 * @property int $id
 * @property string $openid
 * @property string $nickname
 * @property string $avatar
 * @property string $token
 * @property int $create_time
 * @property int $update_time
 */

namespace App\Http\Mini\Model;

use Hyperf\Database\Model\Relations\BelongsToMany;
use Hyperf\Database\Model\Relations\HasMany;

class User extends AbstractMiniModel
{
    protected string $baseTable = 'user';

    protected array $fillable = [
        'openid',
        'nickname',
        'avatar',
        'token',
        'create_time',
        'update_time',
    ];

    protected array $hidden = [
        'token',
    ];

    protected array $casts = [
        'id' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function families(): BelongsToMany
    {
        return $this->belongsToMany(
            Family::class,
            mini_table('family_member'),
            'uid',
            'family_id'
        );
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(FamilyMember::class, 'uid', 'id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'order_uid', 'id');
    }

    public function cookOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'cook_uid', 'id');
    }
}
