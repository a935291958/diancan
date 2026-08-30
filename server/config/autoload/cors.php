<?php

declare(strict_types=1);
/**
 * 全局跨域配置。由 App\Middleware\CorsMiddleware 读取。
 *
 * 生产环境建议将 allow_origin 配成小程序/H5 实际域名，而不是 *。
 */
return [
    'enable' => (bool) env('CORS_ENABLE', true),
    // * 或逗号分隔的 Origin 列表
    'allow_origin' => env('CORS_ALLOW_ORIGIN', '*'),
    'allow_credentials' => (bool) env('CORS_ALLOW_CREDENTIALS', true),
    'allow_methods' => env('CORS_ALLOW_METHODS', 'GET, POST, PATCH, PUT, DELETE, OPTIONS'),
    'allow_headers' => env(
        'CORS_ALLOW_HEADERS',
        'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authorization,Accept-Language,Token,X-Requested-With'
    ),
    'expose_headers' => env('CORS_EXPOSE_HEADERS', 'Request-Id'),
    'max_age' => (int) env('CORS_MAX_AGE', 86400),
];
