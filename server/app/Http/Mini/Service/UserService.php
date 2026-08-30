<?php

declare(strict_types=1);
/**
 * 当前登录用户资料。phone 前端会提交，用户表无该字段，不落库.
 */

namespace App\Http\Mini\Service;

use App\Http\Mini\Context\AuthContext;
use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Http\Mini\Model\User;
use App\Http\Mini\Support\Formatter;

class UserService extends AbstractMiniService
{
    /**
     * @return array<string, mixed>
     */
    public function info(): array
    {
        $user = AuthContext::mustUser();
        $data = Formatter::row($user, ['token']);
        $data['family_ids'] = AuthContext::familyIds() ?? [];
        $data['family_id'] = AuthContext::currentFamilyId();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function updateProfile(array $payload): array
    {
        $user = AuthContext::mustUser();
        if (isset($payload['nickname'])) {
            $nickname = trim((string) $payload['nickname']);
            if ($nickname === '') {
                throw new BusinessException(ResultCode::BAD_REQUEST, '请输入昵称');
            }
            $user->nickname = $nickname;
        }
        if (isset($payload['avatar'])) {
            $user->avatar = (string) $payload['avatar'];
        }
        $user->save();

        return Formatter::row($user, ['token']);
    }

    public function find(int $id): ?User
    {
        return User::query()->find($id);
    }
}
