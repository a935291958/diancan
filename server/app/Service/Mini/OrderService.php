<?php

declare(strict_types=1);
/**
 * 模块：点餐 — 提交/批量/改删查。规格 JSON 解析与存储。
 * 当日看板、指派、状态流转见 DutyService。
 */

namespace App\Service\Mini;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Mini\Food;
use App\Model\Mini\Order;
use App\Service\Mini\Concern\FormatsOrder;
use App\Support\Formatter;
use App\Support\Time;
use Hyperf\DbConnection\Db;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class OrderService extends AbstractMiniService
{
    use FormatsOrder;

    private LoggerInterface $logger;

    public function __construct(
        LoggerFactory $loggerFactory
    ) {
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
            $status = (int) $payload['status'];
            if (! Order::canTransit((int) $order->status, $status)) {
                throw new BusinessException(ResultCode::BAD_REQUEST, '当前状态不允许该操作');
            }
            $order->status = $status;
        }
        $order->save();

        return $this->formatOrder($order->refresh()->load(['food', 'orderUser', 'cook']));
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
}
