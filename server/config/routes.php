<?php

declare(strict_types=1);
/**
 * 路由定义。
 *
 * 后台 MineAdmin 接口由 Controller 注解扫描注册（/admin/*）。
 * 小程序接口集中写在此处，便于 Cursor 对照 client/src/api 迭代。
 *
 * 中间件顺序：Token 鉴权 -> 家庭隔离 -> 防重复提交。
 */
use App\Controller\AuthController;
use App\Controller\FamilyController;
use App\Controller\FoodController;
use App\Controller\OrderController;
use App\Controller\UploadController;
use App\Controller\UserController;
use App\Middleware\ApiLogMiddleware;
use App\Middleware\FamilyIsolateMiddleware;
use App\Middleware\RepeatSubmitMiddleware;
use App\Middleware\TokenAuthMiddleware;
use Hyperf\HttpServer\Router\Router;

Router::get('/', static function () {
    return 'welcome use mineAdmin';
});

Router::get('/favicon.ico', static function () {
    return '';
});

// 微信登录：白名单，不走 Token 中间件
Router::post('/auth/wx-login', [AuthController::class, 'wxLogin'], [
    'middleware' => [ApiLogMiddleware::class],
]);
// 上传文件回显，无需登录
Router::get('/uploads/{path:.+}', [UploadController::class, 'show']);

$miniMiddleware = [
    TokenAuthMiddleware::class,
    FamilyIsolateMiddleware::class,
    RepeatSubmitMiddleware::class,
    ApiLogMiddleware::class,
];

Router::addGroup('', static function (): void {
    Router::get('/user/info', [UserController::class, 'info']);
    Router::put('/user/profile', [UserController::class, 'profile']);
    Router::post('/upload', [UploadController::class, 'upload']);
    Router::post('/v1/upload', [UploadController::class, 'upload']);

    // 家庭：静态路径必须写在 {id} 之前
    Router::get('/v1/family/list', [FamilyController::class, 'list']);
    Router::get('/v1/family/current', [FamilyController::class, 'current']);
    Router::get('/v1/family/members', [FamilyController::class, 'members']);
    Router::post('/v1/family/join', [FamilyController::class, 'join']);
    Router::post('/v1/family/leave', [FamilyController::class, 'leave']);
    Router::post('/v1/family', [FamilyController::class, 'create']);
    Router::delete('/v1/family/member/{id}', [FamilyController::class, 'removeMember']);
    Router::get('/v1/family/{id:\d+}', [FamilyController::class, 'detail']);
    Router::put('/v1/family/{id:\d+}', [FamilyController::class, 'update']);
    Router::delete('/v1/family/{id:\d+}', [FamilyController::class, 'delete']);

    // 菜品
    Router::get('/v1/food/list', [FoodController::class, 'list']);
    Router::get('/v1/food/{id:\d+}/specs', [FoodController::class, 'specs']);
    Router::post('/v1/food/{id:\d+}/specs', [FoodController::class, 'createSpec']);
    Router::get('/v1/food/{id:\d+}', [FoodController::class, 'detail']);
    Router::post('/v1/food', [FoodController::class, 'create']);
    Router::put('/v1/food/{id:\d+}', [FoodController::class, 'update']);
    Router::delete('/v1/food/{id:\d+}', [FoodController::class, 'delete']);
    Router::put('/v1/food-spec/{id:\d+}', [FoodController::class, 'updateSpec']);
    Router::delete('/v1/food-spec/{id:\d+}', [FoodController::class, 'deleteSpec']);

    // 点餐
    Router::get('/v1/order/list', [OrderController::class, 'list']);
    Router::get('/v1/order/today', [OrderController::class, 'today']);
    Router::post('/v1/order/batch', [OrderController::class, 'batch']);
    Router::put('/v1/order/{id:\d+}/status', [OrderController::class, 'updateStatus']);
    Router::put('/v1/order/{id:\d+}/cook', [OrderController::class, 'assignCook']);
    Router::get('/v1/order/{id:\d+}', [OrderController::class, 'detail']);
    Router::post('/v1/order', [OrderController::class, 'create']);
    Router::put('/v1/order/{id:\d+}', [OrderController::class, 'update']);
    Router::delete('/v1/order/{id:\d+}', [OrderController::class, 'delete']);
}, ['middleware' => $miniMiddleware]);
