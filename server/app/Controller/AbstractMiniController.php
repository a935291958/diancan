<?php

declare(strict_types=1);
/**
 * 小程序控制器基类。
 *
 * 分层约定：只接收参数、调用 Service、返回 Result。
 * 禁止在此写 SQL、禁止绕过 AuthContext 做家庭校验。
 */

namespace App\Controller;

use App\Http\Common\Controller\AbstractController as Base;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Support\Api;
use App\Support\SqlSafe;
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Contract\RequestInterface;

abstract class AbstractMiniController extends Base
{
    protected function success(mixed $data = null, ?string $message = null): Result
    {
        return Api::success($data, $message);
    }

    protected function error(?string $message = null, mixed $data = null): Result
    {
        return Api::error(ResultCode::FAIL, $message, $data);
    }

    protected function request(): RequestInterface
    {
        return ApplicationContext::getContainer()->get(RequestInterface::class);
    }

    /**
     * 路由或请求中的正整数 ID.
     */
    protected function routeId(string $name = 'id'): int
    {
        $request = $this->request();
        $value = $request->route($name);
        if ($value === null || $value === '') {
            $value = $request->input($name, 0);
        }

        return SqlSafe::uint($value);
    }
}
