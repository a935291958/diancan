<?php

declare(strict_types=1);
/**
 * 小程序 Service 基类。
 * 业务逻辑、家庭隔离断言写在这里；Controller 禁止直接操作 Model。
 */

namespace App\Service\Mini;

use App\Context\AuthContext;
use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Mini\Family;
use App\Model\Mini\FamilyMember;
use App\Support\SqlSafe;

abstract class AbstractMiniService
{
    protected function uid(): int
    {
        return AuthContext::id();
    }

    protected function requireFamilyId(?int $prefer = null): int
    {
        $familyId = AuthContext::currentFamilyId($prefer);
        if ($familyId <= 0) {
            throw new BusinessException(ResultCode::FORBIDDEN, '请先加入家庭');
        }

        return $familyId;
    }

    /**
     * 按主键取家庭，且必须是当前用户所属家庭（绕过邀请码查询场景请用 withoutGlobal...）.
     */
    protected function mustFamily(int $familyId): Family
    {
        $familyId = SqlSafe::uint($familyId);
        AuthContext::assertFamily($familyId);
        $family = Family::query()->find($familyId);
        if (! $family instanceof Family) {
            throw new BusinessException(ResultCode::NOT_FOUND, '家庭不存在');
        }

        return $family;
    }

    protected function assertAdmin(Family $family): void
    {
        if (! $family->isAdmin($this->uid())) {
            throw new BusinessException(ResultCode::FORBIDDEN, '仅家庭管理员可执行该操作');
        }
    }

    protected function assertMember(int $familyId, int $uid): void
    {
        $exists = FamilyMember::query()
            ->where('family_id', $familyId)
            ->where('uid', $uid)
            ->exists();
        if (! $exists) {
            throw new BusinessException(ResultCode::FORBIDDEN, '该用户不是本家庭成员');
        }
    }

    /**
     * 小程序列表默认一次拉全量（前端未分页）；显式传 page 时再分页。
     *
     * @param  array<string, mixed>  $params
     * @return array{0: int, 1: int}
     */
    protected function pagePair(array $params, int $defaultSize = 200): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $pageSize = (int) ($params['page_size'] ?? $params['pageSize'] ?? $defaultSize);
        $pageSize = min(500, max(1, $pageSize));

        return [$page, $pageSize];
    }
}
