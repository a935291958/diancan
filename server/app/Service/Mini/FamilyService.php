<?php

declare(strict_types=1);
/**
 * 家庭与成员。邀请码查询必须 without 家庭作用域（Family 本身无全局隔离）.
 */

namespace App\Service\Mini;

use App\Context\AuthContext;
use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Mini\Family;
use App\Model\Mini\FamilyMember;
use App\Model\Mini\Food;
use App\Model\Mini\FoodSpec;
use App\Model\Mini\Order;
use App\Support\Formatter;
use Hyperf\DbConnection\Db;

class FamilyService extends AbstractMiniService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function list(): array
    {
        $ids = AuthContext::familyIds() ?? [];
        if ($ids === []) {
            return [];
        }
        $families = Family::query()->whereIn('id', $ids)->orderByDesc('id')->get();

        return $families->map(fn (Family $family) => $this->formatFamily($family))->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function current(): array
    {
        $familyId = AuthContext::currentFamilyId();
        if ($familyId <= 0) {
            return [];
        }

        return $this->detail($familyId);
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(int $id): array
    {
        return $this->formatFamily($this->mustFamily($id));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        $uid = $this->uid();
        $name = trim((string) ($payload['family_name'] ?? ''));
        if ($name === '') {
            throw new BusinessException(ResultCode::BAD_REQUEST, '请填写家庭名称');
        }

        $family = Db::transaction(function () use ($name, $uid) {
            $family = new Family();
            $family->family_name = $name;
            $family->invite_code = $this->uniqueInviteCode();
            $family->admin_uid = $uid;
            $family->save();

            $member = new FamilyMember();
            $member->family_id = (int) $family->id;
            $member->uid = $uid;
            $member->save();

            return $family;
        });

        AuthContext::rememberFamily((int) $family->id);

        return $this->formatFamily($family);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function join(array $payload): array
    {
        $code = strtoupper(trim((string) ($payload['invite_code'] ?? '')));
        $family = Family::query()->where('invite_code', $code)->first();
        if (! $family instanceof Family) {
            throw new BusinessException(ResultCode::NOT_FOUND, '邀请码无效');
        }

        $uid = $this->uid();
        $exists = FamilyMember::withoutGlobalScope('family_isolate')
            ->where('family_id', $family->id)
            ->where('uid', $uid)
            ->exists();
        if ($exists) {
            AuthContext::rememberFamily((int) $family->id);

            return $this->formatFamily($family);
        }

        $member = new FamilyMember();
        $member->family_id = (int) $family->id;
        $member->uid = $uid;
        $member->save();
        AuthContext::rememberFamily((int) $family->id);

        return $this->formatFamily($family);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function update(int $id, array $payload): array
    {
        $family = $this->mustFamily($id);
        $this->assertAdmin($family);
        if (isset($payload['family_name'])) {
            $name = trim((string) $payload['family_name']);
            if ($name === '') {
                throw new BusinessException(ResultCode::BAD_REQUEST, '请填写家庭名称');
            }
            $family->family_name = $name;
            $family->save();
        }

        return $this->formatFamily($family);
    }

    public function delete(int $id): void
    {
        $family = $this->mustFamily($id);
        $this->assertAdmin($family);

        Db::transaction(function () use ($family): void {
            $foodIds = Food::query()->where('family_id', $family->id)->pluck('id')->all();
            if ($foodIds !== []) {
                FoodSpec::query()->whereIn('food_id', $foodIds)->delete();
            }
            Food::query()->where('family_id', $family->id)->delete();
            Order::query()->where('family_id', $family->id)->delete();
            FamilyMember::query()->where('family_id', $family->id)->delete();
            $family->delete();
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function members(?int $familyId = null): array
    {
        $familyId = $this->requireFamilyId($familyId);
        $rows = FamilyMember::query()
            ->with('user')
            ->where('family_id', $familyId)
            ->orderBy('id')
            ->get();

        $family = $this->mustFamily($familyId);

        return $rows->map(function (FamilyMember $member) use ($family) {
            $user = $member->user;
            $row = Formatter::row($member);
            $row['nickname'] = $user?->nickname ?? '';
            $row['avatar'] = $user?->avatar ?? '';
            $row['is_admin'] = $family->isAdmin((int) $member->uid);
            $row['user'] = $user ? [
                'id' => (int) $user->id,
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
            ] : null;

            return $row;
        })->all();
    }

    public function removeMember(int $memberId): void
    {
        $member = FamilyMember::query()->find($memberId);
        if (! $member instanceof FamilyMember) {
            throw new BusinessException(ResultCode::NOT_FOUND, '成员不存在');
        }
        $family = $this->mustFamily((int) $member->family_id);
        $this->assertAdmin($family);
        if ((int) $member->uid === $this->uid()) {
            throw new BusinessException(ResultCode::FORBIDDEN, '管理员不能移除自己，请先转让或解散家庭');
        }
        $member->delete();
    }

    public function leave(?int $familyId = null): void
    {
        $familyId = $this->requireFamilyId($familyId);
        $family = $this->mustFamily($familyId);
        if ($family->isAdmin($this->uid())) {
            throw new BusinessException(ResultCode::FORBIDDEN, '管理员请先解散家庭或转让管理权');
        }
        FamilyMember::query()
            ->where('family_id', $familyId)
            ->where('uid', $this->uid())
            ->delete();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatFamily(Family $family): array
    {
        $data = Formatter::row($family);
        $data['is_admin'] = $family->isAdmin($this->uid());
        $data['member_count'] = FamilyMember::query()->where('family_id', $family->id)->count();

        return $data;
    }

    private function uniqueInviteCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        for ($i = 0; $i < 20; ++$i) {
            $code = '';
            for ($j = 0; $j < 6; ++$j) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
            if (! Family::query()->where('invite_code', $code)->exists()) {
                return $code;
            }
        }
        throw new BusinessException(ResultCode::FAIL, '邀请码生成失败，请重试');
    }
}
