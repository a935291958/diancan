<?php

declare(strict_types=1);
/**
 * 点餐，路径前缀 /v1/order.
 */

namespace App\Controller;

use App\Http\Common\Result;
use App\Service\Mini\OrderService;
use App\Support\SqlSafe;
use App\Validate\OrderValidate;

class OrderController extends AbstractMiniController
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function list(): Result
    {
        return $this->success($this->orderService->list($this->request()->all()));
    }

    public function today(): Result
    {
        return $this->success($this->orderService->today($this->request()->all()));
    }

    public function detail(): Result
    {
        return $this->success($this->orderService->detail($this->routeId()));
    }

    public function create(OrderValidate $validate): Result
    {
        return $this->success($this->orderService->create($validate->validated()));
    }

    public function update(OrderValidate $validate): Result
    {
        return $this->success($this->orderService->update($this->routeId(), $validate->validated()));
    }

    public function updateStatus(OrderValidate $validate): Result
    {
        $status = SqlSafe::uint($validate->validated()['status'] ?? 0);

        return $this->success($this->orderService->updateStatus($this->routeId(), $status));
    }

    public function assignCook(OrderValidate $validate): Result
    {
        $cookUid = SqlSafe::uint($validate->validated()['cook_uid'] ?? 0);

        return $this->success($this->orderService->assignCook($this->routeId(), $cookUid));
    }

    public function delete(): Result
    {
        $this->orderService->delete($this->routeId());

        return $this->success();
    }
}
