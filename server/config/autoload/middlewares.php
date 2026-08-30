<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */
use App\Middleware\CorsMiddleware;
use App\Middleware\ParamFilterMiddleware;
use App\Middleware\RewriteMiddleware;
use Hyperf\Validation\Middleware\ValidationMiddleware;
use Mine\Support\Middleware\RequestIdMiddleware;
use Mine\Support\Middleware\TranslationMiddleware;

return [
    'http' => [
        // 请求ID中间件
        RequestIdMiddleware::class,
        // 多语言识别中间件
        TranslationMiddleware::class,
        // 全局跨域（配置见 config/autoload/cors.php）
        CorsMiddleware::class,
        // 伪静态：去掉 .html 与尾斜杠
        RewriteMiddleware::class,
        // 参数清洗 + SQL 注入特征拦截
        ParamFilterMiddleware::class,
        // 验证器中间件,处理 formRequest 验证器
        ValidationMiddleware::class,
    ],
];
