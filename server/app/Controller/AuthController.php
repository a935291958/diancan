<?php

declare(strict_types=1);
/**
 * 登录：POST /auth/wx-login（白名单，无需 Token）.
 */

namespace App\Controller;

use App\Http\Common\Result;
use App\Service\Mini\AuthService;
use App\Validate\AuthValidate;

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
