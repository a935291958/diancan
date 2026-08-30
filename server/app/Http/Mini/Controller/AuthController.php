<?php

declare(strict_types=1);
/**
 * 模块：用户登录 — POST /auth/wx-login（白名单，无需 Token）.
 */

namespace App\Http\Mini\Controller;

use App\Http\Common\Result;
use App\Http\Mini\Service\AuthService;
use App\Http\Mini\Validate\AuthValidate;

class AuthController extends AbstractMiniController
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function wxLogin(AuthValidate $validate): Result
    {
        return $this->success($this->authService->wxLogin($validate->validated()));
    }
}
