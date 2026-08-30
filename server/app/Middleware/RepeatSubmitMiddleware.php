<?php

declare(strict_types=1);
/**
 * 写接口防重复提交。GET/OPTIONS 跳过。
 */

namespace App\Middleware;

use App\Support\RepeatSubmit;
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
        $method = strtoupper($request->getMethod());
        $methods = config('mini.repeat_submit.methods', ['POST', 'PUT', 'PATCH', 'DELETE']);
        if (in_array($method, $methods, true)) {
            $this->repeatSubmit->assertOnce($request);
        }

        return $handler->handle($request);
    }
}
