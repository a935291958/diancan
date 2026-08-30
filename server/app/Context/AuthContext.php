<?php

declare(strict_types=1);
/**
 * 当前请求的登录用户与家庭范围。
 * 由 TokenAuthMiddleware / FamilyIsolateMiddleware 写入 Coroutine Context，
 * 请求结束自动销毁，禁止跨请求缓存。
 */

namespace App\Context;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Mini\User;
use Hyperf\Context\Context;

final class AuthContext
{
    private const USER_KEY = 'mini.auth.user';

    private const FAMILY_IDS_KEY = 'mini.auth.family_ids';

    public static function setUser(User $user): void
    {
        Context::set(self::USER_KEY, $user);
    }

    public static function user(): ?User
    {
        $user = Context::get(self::USER_KEY);

        return $user instanceof User ? $user : null;
    }

    /**
     * 必须已登录，否则 401.
     */
    public static function mustUser(): User
    {
        $user = self::user();
        if (! $user instanceof User) {
            throw new BusinessException(ResultCode::UNAUTHORIZED);
        }

        return $user;
    }

    public static function id(): int
    {
        return (int) (self::user()?->id ?? 0);
    }

    public static function isGuest(): bool
    {
        return ! self::user() instanceof User;
    }

    /**
     * @param  array<int, int>  $familyIds
     */
    public static function setFamilyIds(array $familyIds): void
    {
        Context::set(self::FAMILY_IDS_KEY, array_values(array_unique(array_map('intval', $familyIds))));
    }

    /**
     * null 表示尚未进入小程序鉴权上下文（后台请求 / CLI），模型全局作用域应跳过。
     *
     * @return null|array<int, int>
     */
    public static function familyIds(): ?array
    {
        if (! Context::has(self::FAMILY_IDS_KEY)) {
            return null;
        }
        $ids = Context::get(self::FAMILY_IDS_KEY);

        return is_array($ids) ? $ids : [];
    }

    /**
     * 当前操作家庭：请求参数 family_id，否则取用户加入的第一个家庭.
     */
    public static function currentFamilyId(?int $prefer = null): int
    {
        if ($prefer && $prefer > 0) {
            self::assertFamily($prefer);

            return $prefer;
        }
        $ids = self::familyIds() ?? [];

        return (int) ($ids[0] ?? 0);
    }

    public static function belongsToFamily(int $familyId): bool
    {
        if ($familyId <= 0) {
            return false;
        }
        $ids = self::familyIds() ?? [];

        return in_array($familyId, $ids, true);
    }

    /**
     * 越权访问家庭数据时抛 403.
     */
    public static function assertFamily(int $familyId): void
    {
        if (! self::belongsToFamily($familyId)) {
            throw new BusinessException(ResultCode::FORBIDDEN, '无权访问该家庭数据');
        }
    }

    /**
     * 用户加入新家庭后刷新内存中的家庭 ID 列表.
     */
    public static function rememberFamily(int $familyId): void
    {
        $ids = self::familyIds() ?? [];
        $ids[] = $familyId;
        self::setFamilyIds($ids);
    }
}
