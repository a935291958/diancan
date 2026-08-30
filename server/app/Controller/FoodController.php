<?php

declare(strict_types=1);
/**
 * 模块：菜品 — /v1/food 增删改查 + /v1/food/{id}/specs 一对多规格.
 */

namespace App\Controller;

use App\Http\Common\Result;
use App\Service\Mini\FoodService;
use App\Validate\FoodValidate;

class FoodController extends AbstractMiniController
{
    public function __construct(
        private readonly FoodService $foodService
    ) {}

    public function list(): Result
    {
        return $this->success($this->foodService->list($this->request()->all()));
    }

    public function detail(): Result
    {
        return $this->success($this->foodService->detail($this->routeId()));
    }

    public function create(FoodValidate $validate): Result
    {
        return $this->success($this->foodService->create($validate->validated()));
    }

    public function update(FoodValidate $validate): Result
    {
        return $this->success($this->foodService->update($this->routeId(), $validate->validated()));
    }

    public function delete(): Result
    {
        $this->foodService->delete($this->routeId());

        return $this->success();
    }

    public function specs(): Result
    {
        return $this->success($this->foodService->specs($this->routeId()));
    }

    public function createSpec(FoodValidate $validate): Result
    {
        return $this->success($this->foodService->createSpec($this->routeId(), $validate->validated()));
    }

    public function updateSpec(FoodValidate $validate): Result
    {
        return $this->success($this->foodService->updateSpec($this->routeId(), $validate->validated()));
    }

    public function deleteSpec(): Result
    {
        $this->foodService->deleteSpec($this->routeId());

        return $this->success();
    }
}
