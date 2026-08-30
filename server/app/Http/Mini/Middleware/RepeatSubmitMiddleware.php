<?php

declare(strict_types=1);
/**
 * 写接口防重复提交。GET/OPTIONS 跳过；上传接口跳过（multipart 体为空会误伤）.
 */

namespace App\Http\Mini\Middleware;

use App\Http\Mini\Support\RepeatSubmit;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RepeatSubmitMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly RepeatSubmit $repeatSubmit
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        if (str_contains($path, '/upload')) {
            return $handler->handle($request);
        }

        $method = strtoupper($request->getMethod());
        $methods = config('mini.repeat_submit.methods', ['POST', 'PUT', 'PATCH', 'DELETE']);
        if (in_array($method, $methods, true)) {
            $this->repeatSubmit->assertOnce($request);
        }

        return $handler->handle($request);
    }
}
