<?php

declare(strict_types=1);
/**
 * 点餐：提交/取消、烹饪指派、状态流转。select_spec 入库 JSON，出库对象.
 */

namespace App\Service\Mini;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Mini\Food;
use App\Model\Mini\Order;
use App\Support\Formatter;
use App\Support\Time;
use Hyperf\DbConnection\Db;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class OrderService extends AbstractMiniService
{
    private LoggerInterface $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('api');
    }

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
     * 当日清单，返回数组供 unwrapList 直接使用.
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
        $order = $this->storeOne($payload);
        $this->logger->info('mini.order.create', ['id' => $order->id, 'uid' => $this->uid()]);

        return $this->formatOrder($this->mustOrder((int) $order->id));
    }

    /**
     * 批量提交（事务），与前端 createOrders 的 items 结构一致.
     *
     * @param  array<string, mixed>  $payload
     * @return array<int, array<string, mixed>>
     */
    public function createBatch(array $payload): array
    {
        $items = $payload['items'] ?? [];
        if (! is_array($items) || $items === []) {
            throw new BusinessException(ResultCode::BAD_REQUEST, '请选择要提交的菜品');
        }

        $orders = Db::transaction(function () use ($payload, $items) {
            $created = [];
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $created[] = $this->storeOne(array_merge($payload, $item));
            }

            return $created;
        });

        $this->logger->info('mini.order.batch', ['count' => count($orders), 'uid' => $this->uid()]);

        return array_map(fn (Order $order) => $this->formatOrder($this->mustOrder((int) $order->id)), $orders);
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
            $this->applyStatus($order, (int) $payload['status']);
        }
        $order->save();

        return $this->formatOrder($order->refresh()->load(['food', 'orderUser', 'cook']));
    }

    /**
     * @return array<string, mixed>
     */
    public function updateStatus(int $id, int $status): array
    {
        $order = $this->mustOrder($id);
        $this->applyStatus($order, $status);
        $order->save();
        $this->logger->info('mini.order.status', ['id' => $id, 'status' => $status, 'uid' => $this->uid()]);

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
        $this->logger->info('mini.order.cook', ['id' => $id, 'cook_uid' => $cookUid, 'uid' => $this->uid()]);

        return $this->formatOrder($order->load(['food', 'orderUser', 'cook']));
    }

    public function delete(int $id): void
    {
        $this->mustOrder($id)->delete();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function storeOne(array $payload): Order
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

        return $order;
    }

    private function applyStatus(Order $order, int $status): void
    {
        if (! Order::canTransit((int) $order->status, $status)) {
            throw new BusinessException(ResultCode::BAD_REQUEST, '当前状态不允许该操作');
        }
        $order->status = $status;
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

    /**
     * 对象 / JSON 字符串统一写成 JSON 文本入库.
     */
    private function encodeSpec(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '{}';
        }
        if (is_array($value)) {
            $json = json_encode($value, JSON_UNESCAPED_UNICODE);

            return is_string($json) ? $json : '{}';
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $value : '{}';
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

        $food = $order->food;
        $data['food_name'] = $food?->food_name ?? '';
        $data['food_img'] = $food?->food_img ?? '';
        $data['cook_uids'] = $food?->cook_uids ?? '';
        $data['food'] = $food ? [
            'id' => (int) $food->id,
            'food_name' => $food->food_name,
            'food_img' => $food->food_img,
            'cook_uids' => $food->cook_uids,
            'category' => $food->category,
        ] : null;

        $orderUser = $order->orderUser;
        $data['order_nickname'] = $orderUser?->nickname ?? '';
        $data['order_user'] = $orderUser ? [
            'id' => (int) $orderUser->id,
            'nickname' => $orderUser->nickname,
            'avatar' => $orderUser->avatar,
        ] : null;

        $cook = $order->cook;
        $data['cook_nickname'] = $cook?->nickname ?? '';
        $data['cook'] = $cook ? [
            'id' => (int) $cook->id,
            'nickname' => $cook->nickname,
            'avatar' => $cook->avatar,
        ] : null;

        return $data;
    }
}
