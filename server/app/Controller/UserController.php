<?php

declare(strict_types=1);
/**
 * 模块：用户 — GET/PUT /user/info|/profile，REST 别名 /v1/user
 */

namespace App\Controller;

use App\Http\Common\Result;
use App\Service\Mini\UserService;
use App\Validate\UserValidate;

class UserController extends AbstractMiniController
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function info(): Result
    {
        return $this->success($this->userService->info());
    }

    public function profile(UserValidate $validate): Result
    {
        return $this->success($this->userService->updateProfile($validate->validated()));
    }
}
