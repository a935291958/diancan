<?php

declare(strict_types=1);
/**
 * 家庭成员 jt_jiating_family_member.
 * 关联：family、user。带 FamilyIsolate，只能看到自己所属家庭的成员行。
 *
 * @property int $id
 * @property int $family_id
 * @property int $uid
 * @property int $create_time
 */

namespace App\Http\Mini\Model;

use App\Http\Mini\Model\Concern\FamilyIsolate;
use Hyperf\Database\Model\Relations\BelongsTo;

class FamilyMember extends AbstractMiniModel
{
    use FamilyIsolate;

    public const UPDATED_AT = null;

    protected string $baseTable = 'family_member';

    protected array $fillable = [
        'family_id',
        'uid',
        'create_time',
    ];

    protected array $casts = [
        'id' => 'integer',
        'family_id' => 'integer',
        'uid' => 'integer',
        'create_time' => 'integer',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'family_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uid', 'id');
    }
}
