<?php

declare(strict_types=1);
/**
 * 家庭数据隔离。
 * 加载当前用户所属家庭 ID，写入 AuthContext；
 * 请求中带 family_id 时立即校验，防止越权。
 * 具体表查询由 Mini 模型 FamilyIsolate Trait 自动追加 whereIn(family_id)。
 */

namespace App\Middleware;

use App\Context\AuthContext;
use App\Model\Mini\FamilyMember;
use App\Support\SqlSafe;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FamilyIsolateMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (AuthContext::isGuest()) {
            AuthContext::setFamilyIds([]);

            return $handler->handle($request);
        }

        $familyIds = FamilyMember::query()
            ->where('uid', AuthContext::id())
            ->pluck('family_id')
            ->map(static fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
        AuthContext::setFamilyIds($familyIds);

        $familyId = $this->extractFamilyId($request);
        if ($familyId > 0) {
            AuthContext::assertFamily($familyId);
        }

        return $handler->handle($request);
    }

    private function extractFamilyId(ServerRequestInterface $request): int
    {
        $parsed = $request->getParsedBody();
        $fromBody = is_array($parsed) ? ($parsed['family_id'] ?? 0) : 0;
        $fromQuery = $request->getQueryParams()['family_id'] ?? 0;

        $id = SqlSafe::uint($fromBody ?: $fromQuery);
        if ($id > 0) {
            return $id;
        }

        // 仅当路径为 /v1/family/{id} 时把路由 id 当作 family_id
        $path = $request->getUri()->getPath();
        if (preg_match('#^/v1/family/(\d+)$#', $path, $matches) === 1) {
            return SqlSafe::uint($matches[1]);
        }

        return 0;
    }
}
