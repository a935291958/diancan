<?php

declare(strict_types=1);
/**
 * 当前用户：GET /user/info 、 PUT /user/profile.
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
