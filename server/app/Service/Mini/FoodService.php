<?php

declare(strict_types=1);
/**
 * 菜品与规格。查询自动带家庭隔离作用域。
 */

namespace App\Service\Mini;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Mini\Food;
use App\Model\Mini\FoodSpec;
use App\Support\Formatter;
use Hyperf\DbConnection\Db;

class FoodService extends AbstractMiniService
{
    /**
     * @param  array<string, mixed>  $params
     * @return array{list: array<int, array<string, mixed>>, total: int, page: int, page_size: int}
     */
    public function list(array $params): array
    {
        $familyId = $this->requireFamilyId(isset($params['family_id']) ? (int) $params['family_id'] : null);
        [$page, $pageSize] = $this->pagePair($params);

        $query = Food::query()->where('family_id', $familyId)->orderByDesc('id');
        if (! empty($params['category'])) {
            $query->where('category', (string) $params['category']);
        }
        if (! empty($params['keyword'])) {
            $kw = '%' . \App\Support\SqlSafe::escapeLike((string) $params['keyword']) . '%';
            $query->where('food_name', 'like', $kw);
        }

        $total = (int) $query->count();
        $rows = $query->forPage($page, $pageSize)->get();

        return Formatter::page(
            $rows->map(fn (Food $food) => $this->formatFood($food, false)),
            $total,
            $page,
            $pageSize
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(int $id): array
    {
        return $this->formatFood($this->mustFood($id), true);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        $familyId = $this->requireFamilyId(isset($payload['family_id']) ? (int) $payload['family_id'] : null);

        $food = Db::transaction(function () use ($payload, $familyId) {
            $food = new Food();
            $this->fillFood($food, $payload, $familyId);
            $food->create_uid = $this->uid();
            $food->save();
            $this->syncSpecs((int) $food->id, $payload['specs'] ?? null);

            return $food;
        });

        return $this->formatFood($food->refresh(), true);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function update(int $id, array $payload): array
    {
        $food = $this->mustFood($id);
        Db::transaction(function () use ($food, $payload): void {
            $this->fillFood($food, $payload, (int) $food->family_id);
            $food->save();
            if (array_key_exists('specs', $payload)) {
                $this->syncSpecs((int) $food->id, $payload['specs']);
            }
        });

        return $this->formatFood($food->refresh(), true);
    }

    public function delete(int $id): void
    {
        $food = $this->mustFood($id);
        Db::transaction(function () use ($food): void {
            FoodSpec::query()->where('food_id', $food->id)->delete();
            $food->delete();
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function specs(int $foodId): array
    {
        $this->mustFood($foodId);

        return Formatter::list(FoodSpec::query()->where('food_id', $foodId)->orderBy('id')->get());
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createSpec(int $foodId, array $payload): array
    {
        $this->mustFood($foodId);
        $spec = new FoodSpec();
        $spec->food_id = $foodId;
        $spec->spec_name = (string) ($payload['spec_name'] ?? '');
        $spec->spec_value = (string) ($payload['spec_value'] ?? '');
        $spec->save();

        return Formatter::row($spec);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function updateSpec(int $id, array $payload): array
    {
        $spec = $this->mustSpec($id);
        if (isset($payload['spec_name'])) {
            $spec->spec_name = (string) $payload['spec_name'];
        }
        if (isset($payload['spec_value'])) {
            $spec->spec_value = (string) $payload['spec_value'];
        }
        $spec->save();

        return Formatter::row($spec);
    }

    public function deleteSpec(int $id): void
    {
        $this->mustSpec($id)->delete();
    }

    private function mustFood(int $id): Food
    {
        $food = Food::query()->find($id);
        if (! $food instanceof Food) {
            throw new BusinessException(ResultCode::NOT_FOUND, '菜品不存在');
        }

        return $food;
    }

    private function mustSpec(int $id): FoodSpec
    {
        $spec = FoodSpec::query()->find($id);
        if (! $spec instanceof FoodSpec) {
            throw new BusinessException(ResultCode::NOT_FOUND, '规格不存在');
        }

        return $spec;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function fillFood(Food $food, array $payload, int $familyId): void
    {
        $food->family_id = $familyId;
        if (isset($payload['food_name'])) {
            $food->food_name = (string) $payload['food_name'];
        }
        if (array_key_exists('food_img', $payload)) {
            $food->food_img = (string) ($payload['food_img'] ?? '');
        }
        if (array_key_exists('category', $payload)) {
            $food->category = (string) ($payload['category'] ?? '');
        }
        if (array_key_exists('cook_uids', $payload)) {
            $food->cook_uids = Formatter::joinIds(Formatter::splitIds($payload['cook_uids']));
        }
    }

    /**
     * @param  null|array<int, array<string, mixed>>  $specs
     */
    private function syncSpecs(int $foodId, mixed $specs): void
    {
        if (! is_array($specs)) {
            return;
        }
        FoodSpec::query()->where('food_id', $foodId)->delete();
        foreach ($specs as $item) {
            if (! is_array($item) || empty($item['spec_name'])) {
                continue;
            }
            $spec = new FoodSpec();
            $spec->food_id = $foodId;
            $spec->spec_name = (string) $item['spec_name'];
            $spec->spec_value = (string) ($item['spec_value'] ?? '');
            $spec->save();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formatFood(Food $food, bool $withSpecs): array
    {
        $data = Formatter::row($food);
        $data['cook_uid_list'] = Formatter::splitIds($food->cook_uids);
        if ($withSpecs) {
            $data['specs'] = Formatter::list($food->specs()->orderBy('id')->get());
        }

        return $data;
    }
}
