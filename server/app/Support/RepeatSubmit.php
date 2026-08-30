<?php

declare(strict_types=1);
/**
 * 接口防重复提交：同一用户 + 方法 + 路径 + 请求体，在 ttl 秒内只允许一次。
 */

namespace App\Support;

use App\Context\AuthContext;
use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use Hyperf\Redis\Redis;
use Psr\Http\Message\ServerRequestInterface;

final class RepeatSubmit
{
    public function __construct(
        private readonly Redis $redis
    ) {}

    /**
     * 占用锁，失败则抛 409.
     */
    public function assertOnce(ServerRequestInterface $request): void
    {
        $config = config('mini.repeat_submit', []);
        if (! ($config['enable'] ?? true)) {
            return;
        }

        $ttl = max(1, (int) ($config['ttl'] ?? 2));
        $prefix = (string) ($config['redis_prefix'] ?? 'mini:repeat:');
        $key = $prefix . $this->buildHash($request);

        // SET key 1 EX ttl NX —— 已存在则视为重复提交
        $ok = $this->redis->set($key, '1', ['NX', 'EX' => $ttl]);
        if ($ok === false || $ok === null) {
            throw new BusinessException(ResultCode::REPEAT_SUBMIT);
        }
    }

    private function buildHash(ServerRequestInterface $request): string
    {
        $uid = AuthContext::id();
        $method = strtoupper($request->getMethod());
        $path = $request->getUri()->getPath();
        $body = $request->getParsedBody();
        $payload = is_array($body) ? json_encode($body, JSON_UNESCAPED_UNICODE) : (string) $request->getBody();

        return md5($uid . '|' . $method . '|' . $path . '|' . $payload);
    }
}
