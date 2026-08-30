<?php

declare(strict_types=1);
/**
 * 家庭点餐小程序（Mini）全局配置。
 *
 * Cursor 迭代约定：
 * - 业务表前缀仅作用于 Mini 模型，禁止写入全局 DB_PREFIX（会破坏后台 MineAdmin 表）。
 * - 鉴权白名单、防重复提交、微信登录开关都从本文件读取，避免散落魔法值。
 */
return [
    // 小程序业务表前缀，对应 jt_jiating_* 
    'table_prefix' => env('MINI_TABLE_PREFIX', 'jt_jiating_'),

    // 登录 Token 有效期（秒）。表字段为 token 字符串，过期后重新签发。
    'token_ttl' => (int) env('MINI_TOKEN_TTL', 86400 * 30),

    // Token 请求头：优先 Authorization: Bearer {token}，其次 Token / token
    'token_header' => env('MINI_TOKEN_HEADER', 'Authorization'),

    // 无需登录即可访问的路径（精确匹配，不含 query）
    'auth_whitelist' => [
        '/auth/wx-login',
        '/favicon.ico',
        '/',
    ],

    // 微信小程序
    'wechat' => [
        'app_id' => env('WECHAT_APPID', ''),
        'secret' => env('WECHAT_SECRET', ''),
        // 本地无 AppSecret 时用 code 派生 mock openid，生产必须关闭
        'mock' => (bool) env('WECHAT_MOCK', true),
    ],

    // 接口防重复提交（POST/PUT/PATCH/DELETE）
    'repeat_submit' => [
        'enable' => (bool) env('MINI_REPEAT_SUBMIT', true),
        'ttl' => (int) env('MINI_REPEAT_SUBMIT_TTL', 2),
        'methods' => ['POST', 'PUT', 'PATCH', 'DELETE'],
        'redis_prefix' => 'mini:repeat:',
    ],

    // 参数过滤 / SQL 注入特征拦截
    'param_filter' => [
        'enable' => true,
        // 命中则直接拒绝请求（不改写业务字段，避免误伤菜名等）
        'block_sql_inject' => true,
    ],
];
