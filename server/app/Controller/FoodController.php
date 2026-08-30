<?php

declare(strict_types=1);
/**
 * 菜品 / 规格，路径 /v1/food 、 /v1/food-spec.
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
