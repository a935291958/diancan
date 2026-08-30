<?php

declare(strict_types=1);
/**
 * 全局跨域中间件。OPTIONS 预检直接返回，正式请求附加 CORS 头。
 */

namespace App\Middleware;

use Hyperf\Context\ResponseContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! config('cors.enable', true)) {
            return $handler->handle($request);
        }

        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            return $this->withCors($request, ResponseContext::get());
        }

        return $this->withCors($request, $handler->handle($request));
    }

    private function withCors(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $allowOrigin = (string) config('cors.allow_origin', '*');
        $requestOrigin = $request->getHeaderLine('Origin');
        $origin = '*';
        if ($allowOrigin === '*') {
            $origin = $requestOrigin !== '' ? $requestOrigin : '*';
        } else {
            $list = array_map('trim', explode(',', $allowOrigin));
            if ($requestOrigin !== '' && in_array($requestOrigin, $list, true)) {
                $origin = $requestOrigin;
            } elseif (isset($list[0]) && $list[0] !== '') {
                $origin = $list[0];
            }
        }

        return $response
            ->withHeader('Access-Control-Allow-Origin', $origin)
            ->withHeader('Access-Control-Allow-Credentials', config('cors.allow_credentials', true) ? 'true' : 'false')
            ->withHeader('Access-Control-Allow-Methods', (string) config('cors.allow_methods'))
            ->withHeader('Access-Control-Allow-Headers', (string) config('cors.allow_headers'))
            ->withHeader('Access-Control-Expose-Headers', (string) config('cors.expose_headers'))
            ->withHeader('Access-Control-Max-Age', (string) config('cors.max_age', 86400));
    }
}
