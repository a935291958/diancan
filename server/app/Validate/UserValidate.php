<?php

declare(strict_types=1);
/**
 * 用户资料.
 */

namespace App\Validate;

class UserValidate extends AbstractValidate
{
    protected function profileRules(): array
    {
        return [
            'nickname' => 'nullable|string|max:50',
            'avatar' => 'nullable|string|max:255',
        ];
    }
}
