<?php

declare(strict_types=1);
/**
 * 全局参数过滤 + SQL 注入特征拦截。
 * 清洗 query / parsedBody，命中注入特征则 400。
 */

namespace App\Middleware;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Support\ParamFilter;
use App\Support\SqlSafe;
use Hyperf\Context\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ParamFilterMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! config('mini.param_filter.enable', true)) {
            return $handler->handle($request);
        }

        $query = ParamFilter::filter($request->getQueryParams());
        $parsed = $request->getParsedBody();
        $body = is_array($parsed) ? ParamFilter::filter($parsed) : $parsed;

        if (config('mini.param_filter.block_sql_inject', true)) {
            if (SqlSafe::containsInject($query) || (is_array($body) && SqlSafe::containsInject($body))) {
                throw new BusinessException(ResultCode::BAD_REQUEST, '请求包含非法字符');
            }
        }

        $request = $request->withQueryParams($query);
        if (is_array($body)) {
            $request = $request->withParsedBody($body);
        }
        Context::set(ServerRequestInterface::class, $request);

        return $handler->handle($request);
    }
}
