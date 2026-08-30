<?php

declare(strict_types=1);
/**
 * 模块：分工 — RESTful /v1/duty
 * UniApp 现有路径 /v1/order/today|/status|/cook 在路由中指向本控制器，保证前端零改动.
 */

namespace App\Http\Mini\Controller;

use App\Http\Common\Result;
use App\Http\Mini\Service\DutyService;
use App\Support\SqlSafe;
use App\Http\Mini\Validate\DutyValidate;

class DutyController extends AbstractMiniController
{
    public function __construct(
        private readonly DutyService $dutyService
    ) {}

    public function list(): Result
    {
        return $this->success($this->dutyService->list($this->request()->all()));
    }

    public function today(): Result
    {
        return $this->success($this->dutyService->today($this->request()->all()));
    }

    public function mine(): Result
    {
        return $this->success($this->dutyService->mine($this->request()->all()));
    }

    public function summary(): Result
    {
        return $this->success($this->dutyService->summary($this->request()->all()));
    }

    public function detail(): Result
    {
        return $this->success($this->dutyService->detail($this->routeId()));
    }

    public function assignCook(DutyValidate $validate): Result
    {
        $cookUid = SqlSafe::uint($validate->validated()['cook_uid'] ?? 0);

        return $this->success($this->dutyService->assignCook($this->routeId(), $cookUid));
    }

    public function updateStatus(DutyValidate $validate): Result
    {
        $status = SqlSafe::uint($validate->validated()['status'] ?? 0);

        return $this->success($this->dutyService->updateStatus($this->routeId(), $status));
    }
}
