<?php

declare(strict_types=1);
/**
 * 家庭模块校验：创建/加入/改名/退出.
 */

namespace App\Validate;

class FamilyValidate extends AbstractValidate
{
    protected function createRules(): array
    {
        return [
            'family_name' => 'required|string|min:1|max:50',
        ];
    }

    protected function joinRules(): array
    {
        return [
            'invite_code' => 'required|string|size:6|regex:/^[A-Za-z0-9]{6}$/',
        ];
    }

    protected function updateRules(): array
    {
        return [
            'family_name' => 'required|string|min:1|max:50',
        ];
    }

    protected function leaveRules(): array
    {
        return [
            'family_id' => 'nullable|integer|min:1',
        ];
    }

    protected function commonMessages(): array
    {
        return [
            'family_name.required' => '请填写家庭名称',
            'family_name.max' => '家庭名称最多 50 个字',
            'invite_code.required' => '请填写邀请码',
            'invite_code.size' => '邀请码为 6 位',
            'invite_code.regex' => '邀请码仅为字母或数字',
        ];
    }
}
