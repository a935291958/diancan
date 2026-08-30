<?php

declare(strict_types=1);
/**
 * 伪静态：去掉 .html / .htm / 多余斜杠，统一走 REST 路由。
 * 实际对外隐藏端口与脚本名依赖 deploy/nginx.conf 反向代理。
 */

namespace App\Middleware;

use Hyperf\Context\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RewriteMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        $rewritten = $this->rewrite($path);
        if ($rewritten !== $path) {
            $request = $request->withUri($uri->withPath($rewritten));
            Context::set(ServerRequestInterface::class, $request);
        }

        return $handler->handle($request);
    }

    private function rewrite(string $path): string
    {
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }
        if (str_ends_with(strtolower($path), '.html')) {
            $path = substr($path, 0, -5);
        } elseif (str_ends_with(strtolower($path), '.htm')) {
            $path = substr($path, 0, -4);
        }
        if ($path === '') {
            $path = '/';
        }

        return $path;
    }
}
