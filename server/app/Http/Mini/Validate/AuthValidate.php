<?php

declare(strict_types=1);
/**
 * 微信登录参数：code 必填；昵称/头像可选（授权后回传）.
 */

namespace App\Http\Mini\Validate;

class AuthValidate extends AbstractValidate
{
    protected function wxLoginRules(): array
    {
        return [
            'code' => 'required|string|min:1|max:128',
            'nickname' => 'nullable|string|max:50',
            'avatar' => 'nullable|string|max:255',
        ];
    }

    protected function commonMessages(): array
    {
        return [
            'code.required' => '缺少微信登录 code',
            'code.max' => '登录凭证无效',
            'nickname.max' => '昵称最多 50 个字',
            'avatar.max' => '头像地址过长',
        ];
    }
}
