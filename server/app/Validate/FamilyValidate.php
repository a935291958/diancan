<?php

declare(strict_types=1);
/**
 * 家庭模块校验.
 */

namespace App\Validate;

class FamilyValidate extends AbstractValidate
{
    protected function createRules(): array
    {
        return [
            'family_name' => 'required|string|max:50',
        ];
    }

    protected function joinRules(): array
    {
        return [
            'invite_code' => 'required|string|size:6',
        ];
    }

    protected function updateRules(): array
    {
        return [
            'family_name' => 'required|string|max:50',
        ];
    }

    protected function commonMessages(): array
    {
        return [
            'family_name.required' => '请填写家庭名称',
            'invite_code.required' => '请填写邀请码',
            'invite_code.size' => '邀请码为 6 位',
        ];
    }
}
