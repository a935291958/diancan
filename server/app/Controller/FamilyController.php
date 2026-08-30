<?php

declare(strict_types=1);
/**
 * 家庭模块，路径前缀 /v1/family.
 */

namespace App\Controller;

use App\Http\Common\Result;
use App\Service\Mini\FamilyService;
use App\Support\SqlSafe;
use App\Validate\FamilyValidate;

class FamilyController extends AbstractMiniController
{
    public function __construct(
        private readonly FamilyService $familyService
    ) {}

    public function list(): Result
    {
        return $this->success($this->familyService->list());
    }

    public function current(): Result
    {
        return $this->success($this->familyService->current());
    }

    public function detail(): Result
    {
        return $this->success($this->familyService->detail($this->routeId()));
    }

    public function create(FamilyValidate $validate): Result
    {
        return $this->success($this->familyService->create($validate->validated()));
    }

    public function join(FamilyValidate $validate): Result
    {
        return $this->success($this->familyService->join($validate->validated()));
    }

    public function update(FamilyValidate $validate): Result
    {
        return $this->success($this->familyService->update($this->routeId(), $validate->validated()));
    }

    public function delete(): Result
    {
        $this->familyService->delete($this->routeId());

        return $this->success();
    }

    public function members(): Result
    {
        $familyId = SqlSafe::uint($this->request()->input('family_id', 0));

        return $this->success($this->familyService->members($familyId > 0 ? $familyId : null));
    }

    public function removeMember(): Result
    {
        $this->familyService->removeMember($this->routeId());

        return $this->success();
    }

    public function leave(FamilyValidate $validate): Result
    {
        $familyId = SqlSafe::uint($validate->validated()['family_id'] ?? 0);
        $this->familyService->leave($familyId > 0 ? $familyId : null);

        return $this->success();
    }
}
