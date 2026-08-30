<?php

declare(strict_types=1);
/**
 * 点餐记录。所有查询带家庭隔离。
 */

namespace App\Service\Mini;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Mini\Food;
use App\Model\Mini\Order;
use App\Support\Formatter;
use App\Support\Time;

class OrderService extends AbstractMiniService
{
    /**
     * @param  array<string, mixed>  $params
     * @return array{list: array<int, array<string, mixed>>, total: int, page: int, page_size: int}
     */
    public function list(array $params): array
    {
        $familyId = $this->requireFamilyId(isset($params['family_id']) ? (int) $params['family_id'] : null);
        [$page, $pageSize] = $this->pagePair($params);
        $query = $this->buildQuery($familyId, $params);
        $total = (int) $query->count();
        $rows = $query->forPage($page, $pageSize)->get();

        return Formatter::page(
            $rows->map(fn (Order $order) => $this->formatOrder($order)),
            $total,
            $page,
            $pageSize
        );
    }

    /**
     * 当日清单.
     *
     * @param  array<string, mixed>  $params
     * @return array<int, array<string, mixed>>
     */
    public function today(array $params): array
    {
        $familyId = $this->requireFamilyId(isset($params['family_id']) ? (int) $params['family_id'] : null);
        $params['order_date'] = (string) ($params['order_date'] ?? Time::today());
        $rows = $this->buildQuery($familyId, $params)->get();

        return $rows->map(fn (Order $order) => $this->formatOrder($order))->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(int $id): array
    {
        return $this->formatOrder($this->mustOrder($id));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        $familyId = $this->requireFamilyId(isset($payload['family_id']) ? (int) $payload['family_id'] : null);
        $food = Food::query()->find((int) ($payload['food_id'] ?? 0));
        if (! $food instanceof Food) {
            throw new BusinessException(ResultCode::NOT_FOUND, '菜品不存在');
        }
        if ((int) $food->family_id !== $familyId) {
            throw new BusinessException(ResultCode::FORBIDDEN, '无权点该家庭的菜品');
        }

        $cookUid = (int) ($payload['cook_uid'] ?? 0);
        if ($cookUid > 0) {
            $this->assertMember($familyId, $cookUid);
        }

        $order = new Order();
        $order->family_id = $familyId;
        $order->food_id = (int) $food->id;
        $order->select_spec = $this->encodeSpec($payload['select_spec'] ?? null);
        $order->order_uid = $this->uid();
        $order->cook_uid = $cookUid;
        $order->meal_type = (string) ($payload['meal_type'] ?? '');
        $order->order_date = (string) ($payload['order_date'] ?? Time::today());
        $order->status = Order::STATUS_PENDING;
        $order->save();

        return $this->formatOrder($order->refresh());
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function update(int $id, array $payload): array
    {
        $order = $this->mustOrder($id);
        if (array_key_exists('select_spec', $payload)) {
            $order->select_spec = $this->encodeSpec($payload['select_spec']);
        }
        if (isset($payload['meal_type'])) {
            $order->meal_type = (string) $payload['meal_type'];
        }
        if (isset($payload['order_date'])) {
            $order->order_date = (string) $payload['order_date'];
        }
        if (isset($payload['cook_uid'])) {
            $cookUid = (int) $payload['cook_uid'];
            if ($cookUid > 0) {
                $this->assertMember((int) $order->family_id, $cookUid);
            }
            $order->cook_uid = $cookUid;
        }
        if (isset($payload['status'])) {
            $order->status = (int) $payload['status'];
        }
        $order->save();

        return $this->formatOrder($order);
    }

    /**
     * @return array<string, mixed>
     */
    public function updateStatus(int $id, int $status): array
    {
        $order = $this->mustOrder($id);
        $order->status = $status;
        $order->save();

        return $this->formatOrder($order);
    }

    /**
     * @return array<string, mixed>
     */
    public function assignCook(int $id, int $cookUid): array
    {
        $order = $this->mustOrder($id);
        $this->assertMember((int) $order->family_id, $cookUid);
        $order->cook_uid = $cookUid;
        $order->save();

        return $this->formatOrder($order);
    }

    public function delete(int $id): void
    {
        $this->mustOrder($id)->delete();
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function buildQuery(int $familyId, array $params)
    {
        $query = Order::query()
            ->with(['food', 'orderUser', 'cook'])
            ->where('family_id', $familyId)
            ->orderByDesc('id');

        if (! empty($params['order_date'])) {
            $query->where('order_date', (string) $params['order_date']);
        }
        if (! empty($params['meal_type'])) {
            $query->where('meal_type', (string) $params['meal_type']);
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', (int) $params['status']);
        }

        return $query;
    }

    private function mustOrder(int $id): Order
    {
        $order = Order::query()->with(['food', 'orderUser', 'cook'])->find($id);
        if (! $order instanceof Order) {
            throw new BusinessException(ResultCode::NOT_FOUND, '点餐记录不存在');
        }

        return $order;
    }

    private function encodeSpec(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '{}';
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '{}';
        }
        if (is_string($value)) {
            json_decode($value, true);

            return json_last_error() === JSON_ERROR_NONE ? $value : '{}';
        }

        return '{}';
    }

    /**
     * @return array<string, mixed>
     */
    private function formatOrder(Order $order): array
    {
        $data = Formatter::row($order);
        $spec = json_decode((string) $order->select_spec, true);
        $data['select_spec'] = is_array($spec) ? $spec : [];
        $data['food_name'] = $order->food?->food_name ?? '';
        $data['food_img'] = $order->food?->food_img ?? '';
        $data['order_nickname'] = $order->orderUser?->nickname ?? '';
        $data['cook_nickname'] = $order->cook?->nickname ?? '';

        return $data;
    }
}
