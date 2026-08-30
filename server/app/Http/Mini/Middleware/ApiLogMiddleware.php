<?php

declare(strict_types=1);
/**
 * 小程序接口访问日志：方法、路径、用户、耗时、业务结果码。
 */

namespace App\Http\Mini\Middleware;

use App\Http\Mini\Context\AuthContext;
use Hyperf\Logger\LoggerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiLogMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly LoggerFactory $loggerFactory
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $start = microtime(true);
        $response = $handler->handle($request);
        $ms = (int) round((microtime(true) - $start) * 1000);

        $this->loggerFactory->get('api')->info('mini.api', [
            'method' => $request->getMethod(),
            'path' => $request->getUri()->getPath(),
            'uid' => AuthContext::id(),
            'family_ids' => AuthContext::familyIds(),
            'status' => $response->getStatusCode(),
            'cost_ms' => $ms,
        ]);

        return $response;
    }
}
