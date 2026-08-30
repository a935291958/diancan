<?php

declare(strict_types=1);
/**
 * 当前登录用户资料.
 */

namespace App\Service\Mini;

use App\Context\AuthContext;
use App\Model\Mini\User;
use App\Support\Formatter;

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
            $user->nickname = (string) $payload['nickname'];
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
