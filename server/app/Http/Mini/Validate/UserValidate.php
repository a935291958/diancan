<?php

declare(strict_types=1);
/**
 * 用户资料：昵称必填，头像/手机号选填（手机号仅校验，表结构无 phone 字段不落库）.
 */

namespace App\Http\Mini\Validate;

class UserValidate extends AbstractValidate
{
    protected function profileRules(): array
    {
        return [
            'nickname' => 'required|string|min:1|max:50',
            'avatar' => 'nullable|string|max:255',
            'phone' => 'nullable|string|regex:/^1[3-9]\\d{9}$/',
        ];
    }

    protected function commonMessages(): array
    {
        return [
            'nickname.required' => '请输入昵称',
            'nickname.max' => '昵称最多 50 个字',
            'phone.regex' => '手机号格式不正确',
        ];
    }
}
