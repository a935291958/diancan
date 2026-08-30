<?php

declare(strict_types=1);
/**
 * 微信登录：code 换 openid，签发 token；已加入家庭时带回 family 供前端落库.
 */

namespace App\Http\Mini\Service;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Http\Mini\Model\Family;
use App\Http\Mini\Model\FamilyMember;
use App\Http\Mini\Model\User;
use App\Http\Mini\Support\Formatter;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class AuthService extends AbstractMiniService
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly ClientFactory $clientFactory,
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->get('api');
    }

    /**
     * @param  array{code: string, nickname?: string, avatar?: string}  $payload
     * @return array<string, mixed>
     */
    public function wxLogin(array $payload): array
    {
        $code = trim((string) ($payload['code'] ?? ''));
        if ($code === '') {
            throw new BusinessException(ResultCode::BAD_REQUEST, '缺少微信登录 code');
        }

        $openid = $this->code2openid($code);
        $user = User::query()->where('openid', $openid)->first();
        $isNew = ! $user instanceof User;
        if ($isNew) {
            $user = new User();
            $user->openid = $openid;
            $user->nickname = (string) ($payload['nickname'] ?? '微信用户');
            $user->avatar = (string) ($payload['avatar'] ?? '');
        } else {
            if (! empty($payload['nickname'])) {
                $user->nickname = (string) $payload['nickname'];
            }
            if (! empty($payload['avatar'])) {
                $user->avatar = (string) $payload['avatar'];
            }
        }

        $token = bin2hex(random_bytes(32));
        $user->token = $token;
        $user->save();

        $data = Formatter::row($user, ['token']);
        $family = $this->firstFamilyOf((int) $user->id);

        $this->logger->info('mini.wx_login', [
            'uid' => $user->id,
            'new' => $isNew,
            'family_id' => $family['id'] ?? 0,
        ]);

        return [
            'token' => $token,
            'userInfo' => $data,
            'user' => $data,
            'family' => $family,
        ];
    }

    /**
     * @return null|array<string, mixed>
     */
    private function firstFamilyOf(int $uid): ?array
    {
        $member = FamilyMember::query()->where('uid', $uid)->orderBy('id')->first();
        if (! $member instanceof FamilyMember) {
            return null;
        }
        $family = Family::query()->find($member->family_id);
        if (! $family instanceof Family) {
            return null;
        }
        $row = Formatter::row($family);
        $row['is_admin'] = $family->isAdmin($uid);
        $row['member_count'] = FamilyMember::query()->where('family_id', $family->id)->count();

        return $row;
    }

    private function code2openid(string $code): string
    {
        if (config('mini.wechat.mock', true)) {
            return 'mock_' . substr(hash('sha256', $code), 0, 28);
        }

        $appId = (string) config('mini.wechat.app_id');
        $secret = (string) config('mini.wechat.secret');
        if ($appId === '' || $secret === '') {
            throw new BusinessException(ResultCode::FAIL, '未配置微信小程序 AppID');
        }

        $response = $this->clientFactory->create()->get('https://api.weixin.qq.com/sns/jscode2session', [
            'query' => [
                'appid' => $appId,
                'secret' => $secret,
                'js_code' => $code,
                'grant_type' => 'authorization_code',
            ],
            'timeout' => 5,
        ]);
        $json = json_decode((string) $response->getBody(), true);
        if (! is_array($json) || empty($json['openid'])) {
            $err = is_array($json) ? (string) ($json['errmsg'] ?? '微信登录失败') : '微信登录失败';
            throw new BusinessException(ResultCode::FAIL, $err);
        }

        return (string) $json['openid'];
    }
}
