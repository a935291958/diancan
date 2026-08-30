<?php

declare(strict_types=1);
/**
 * 小程序 Token 登录校验。
 * 白名单外请求必须携带有效 Bearer Token，并写入 AuthContext。
 */

namespace App\Http\Mini\Middleware;

use App\Http\Mini\Context\AuthContext;
use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Http\Mini\Model\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TokenAuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        if ($this->inWhitelist($path)) {
            return $handler->handle($request);
        }

        $token = $this->extractToken($request);
        if ($token === '') {
            throw new BusinessException(ResultCode::UNAUTHORIZED, '未登录');
        }

        $user = User::query()->where('token', $token)->first();
        if (! $user instanceof User) {
            throw new BusinessException(ResultCode::UNAUTHORIZED, '登录已过期，请重新登录');
        }

        AuthContext::setUser($user);

        return $handler->handle($request);
    }

    private function inWhitelist(string $path): bool
    {
        $list = config('mini.auth_whitelist', []);
        foreach ($list as $item) {
            if ($path === $item) {
                return true;
            }
        }
        foreach (config('mini.auth_whitelist_prefix', []) as $prefix) {
            if ($prefix !== '' && str_starts_with($path, (string) $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function extractToken(ServerRequestInterface $request): string
    {
        $authorization = $request->getHeaderLine('Authorization');
        if ($authorization !== '' && preg_match('/Bearer\s+(\S+)/i', $authorization, $matches) === 1) {
            return trim($matches[1]);
        }

        foreach (['Token', 'token'] as $header) {
            $value = trim($request->getHeaderLine($header));
            if ($value !== '') {
                return $value;
            }
        }

        $queryToken = $request->getQueryParams()['token'] ?? '';

        return is_string($queryToken) ? trim($queryToken) : '';
    }
}
