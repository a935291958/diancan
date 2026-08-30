<?php

declare(strict_types=1);
/**
 * 模块：点餐 — /v1/order CRUD；当日/状态/指派见 DutyController.
 */

namespace App\Http\Mini\Controller;

use App\Http\Common\Result;
use App\Http\Mini\Service\OrderService;
use App\Http\Mini\Validate\OrderValidate;

class OrderController extends AbstractMiniController
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function list(): Result
    {
        return $this->success($this->orderService->list($this->request()->all()));
    }

    public function detail(): Result
    {
        return $this->success($this->orderService->detail($this->routeId()));
    }

    public function create(OrderValidate $validate): Result
    {
        return $this->success($this->orderService->create($validate->validated()));
    }

    public function batch(OrderValidate $validate): Result
    {
        return $this->success($this->orderService->createBatch($validate->validated()));
    }

    public function update(OrderValidate $validate): Result
    {
        return $this->success($this->orderService->update($this->routeId(), $validate->validated()));
    }

    public function delete(): Result
    {
        $this->orderService->delete($this->routeId());

        return $this->success();
    }
}
