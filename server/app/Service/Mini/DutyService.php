<?php

declare(strict_types=1);
/**
 * 模块：分工 — 看板、指派烹饪、状态更新（数据源为 order 表，家庭隔离）.
 */

namespace App\Service\Mini;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Mini\Order;
use App\Service\Mini\Concern\FormatsOrder;
use App\Support\Time;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class DutyService extends AbstractMiniService
{
    use FormatsOrder;

    private LoggerInterface $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('api');
    }

    /**
     * 分工列表（日期/餐段/烹饪人/状态），结构与 UniApp unwrapList 兼容.
     *
     * @param  array<string, mixed>  $params
     * @return array{list: array<int, array<string, mixed>>, total: int, page: int, page_size: int}
     */
    public function list(array $params): array
    {
        $familyId = $this->requireFamilyId(isset($params['family_id']) ? (int) $params['family_id'] : null);
        [$page, $pageSize] = $this->pagePair($params);
        $query = $this->boardQuery($familyId, $params);
        $total = (int) $query->count();
        $rows = $query->forPage($page, $pageSize)->get();

        return \App\Support\Formatter::page(
            $rows->map(fn (Order $order) => $this->formatOrder($order)),
            $total,
            $page,
            $pageSize
        );
    }

    /**
     * 当日分工看板，默认今天；meal_type 空表示全部餐段.
     *
     * @param  array<string, mixed>  $params
     * @return array<int, array<string, mixed>>
     */
    public function today(array $params): array
    {
        $familyId = $this->requireFamilyId(isset($params['family_id']) ? (int) $params['family_id'] : null);
        $params['order_date'] = (string) ($params['order_date'] ?? Time::today());
        $rows = $this->boardQuery($familyId, $params)->get();

        return $rows->map(fn (Order $order) => $this->formatOrder($order))->all();
    }

    /**
     * 当前用户被指派的烹饪任务.
     *
     * @param  array<string, mixed>  $params
     * @return array<int, array<string, mixed>>
     */
    public function mine(array $params): array
    {
        $params['cook_uid'] = $this->uid();

        return $this->today($params);
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(int $id): array
    {
        return $this->formatOrder($this->mustOrder($id));
    }

    /**
     * 按烹饪人汇总当日任务数.
     *
     * @param  array<string, mixed>  $params
     * @return array<int, array<string, mixed>>
     */
    public function summary(array $params): array
    {
        $rows = $this->today($params);
        $map = [];
        foreach ($rows as $row) {
            $cookUid = (int) ($row['cook_uid'] ?? 0);
            $key = (string) $cookUid;
            if (! isset($map[$key])) {
                $map[$key] = [
                    'cook_uid' => $cookUid,
                    'cook_nickname' => $cookUid > 0 ? (string) ($row['cook_nickname'] ?: '未命名') : '未指派',
                    'pending' => 0,
                    'cooking' => 0,
                    'done' => 0,
                    'cancelled' => 0,
                    'total' => 0,
                ];
            }
            ++$map[$key]['total'];
            $status = (int) ($row['status'] ?? 0);
            match ($status) {
                Order::STATUS_PENDING => ++$map[$key]['pending'],
                Order::STATUS_COOKING => ++$map[$key]['cooking'],
                Order::STATUS_DONE => ++$map[$key]['done'],
                Order::STATUS_CANCELLED => ++$map[$key]['cancelled'],
                default => null,
            };
        }

        return array_values($map);
    }

    /**
     * 指派烹饪人（须为本家庭成员）.
     *
     * @return array<string, mixed>
     */
    public function assignCook(int $id, int $cookUid): array
    {
        $order = $this->mustOrder($id);
        $this->assertMember((int) $order->family_id, $cookUid);
        $order->cook_uid = $cookUid;
        $order->save();
        $this->logger->info('mini.duty.cook', ['id' => $id, 'cook_uid' => $cookUid, 'uid' => $this->uid()]);

        return $this->formatOrder($order->load(['food', 'orderUser', 'cook']));
    }

    /**
     * 更新制作状态：待制作→制作中/取消，制作中→完成/取消.
     *
     * @return array<string, mixed>
     */
    public function updateStatus(int $id, int $status): array
    {
        $order = $this->mustOrder($id);
        if (! Order::canTransit((int) $order->status, $status)) {
            throw new BusinessException(ResultCode::BAD_REQUEST, '当前状态不允许该操作');
        }
        $order->status = $status;
        $order->save();
        $this->logger->info('mini.duty.status', ['id' => $id, 'status' => $status, 'uid' => $this->uid()]);

        return $this->formatOrder($order);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function boardQuery(int $familyId, array $params)
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
        if (isset($params['cook_uid']) && $params['cook_uid'] !== '') {
            $query->where('cook_uid', (int) $params['cook_uid']);
        }

        return $query;
    }
}
