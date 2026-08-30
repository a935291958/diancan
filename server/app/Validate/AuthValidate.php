<?php

declare(strict_types=1);
/**
 * 微信登录参数.
 */

namespace App\Validate;

class AuthValidate extends AbstractValidate
{
    protected function wxLoginRules(): array
    {
        return [
            'code' => 'required|string|max:128',
            'nickname' => 'nullable|string|max:50',
            'avatar' => 'nullable|string|max:255',
        ];
    }

    protected function commonMessages(): array
    {
        return [
            'code.required' => '缺少微信登录 code',
        ];
    }
}
