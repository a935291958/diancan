<?php

declare(strict_types=1);
/**
 * 点餐校验：单条提交、状态、指派烹饪、批量 items.
 */

namespace App\Validate;

use App\Model\Mini\Order;

class OrderValidate extends AbstractValidate
{
    protected function createRules(): array
    {
        return [
            'family_id' => 'nullable|integer|min:1',
            'food_id' => 'required|integer|min:1',
            'select_spec' => 'nullable',
            'cook_uid' => 'nullable|integer|min:0',
            'order_uid' => 'nullable|integer|min:1',
            'meal_type' => 'required|string|in:早,中,晚',
            'order_date' => 'nullable|date_format:Y-m-d',
            'status' => 'nullable|integer|in:' . $this->statusList(),
        ];
    }

    protected function batchRules(): array
    {
        return [
            'family_id' => 'nullable|integer|min:1',
            'meal_type' => 'required|string|in:早,中,晚',
            'order_date' => 'nullable|date_format:Y-m-d',
            'order_uid' => 'nullable|integer|min:1',
            'items' => 'required|array|min:1|max:50',
            'items.*.food_id' => 'required|integer|min:1',
            'items.*.select_spec' => 'nullable',
            'items.*.cook_uid' => 'nullable|integer|min:0',
            'items.*.status' => 'nullable|integer|in:' . $this->statusList(),
        ];
    }

    protected function updateRules(): array
    {
        return [
            'select_spec' => 'nullable',
            'cook_uid' => 'nullable|integer|min:0',
            'meal_type' => 'nullable|string|in:早,中,晚',
            'order_date' => 'nullable|date_format:Y-m-d',
            'status' => 'nullable|integer|in:' . $this->statusList(),
        ];
    }

    protected function updateStatusRules(): array
    {
        return [
            'status' => 'required|integer|in:' . $this->statusList(),
        ];
    }

    protected function assignCookRules(): array
    {
        return [
            'cook_uid' => 'required|integer|min:1',
        ];
    }

    private function statusList(): string
    {
        return implode(',', [
            Order::STATUS_PENDING,
            Order::STATUS_COOKING,
            Order::STATUS_DONE,
            Order::STATUS_CANCELLED,
        ]);
    }

    protected function commonMessages(): array
    {
        return [
            'food_id.required' => '请选择菜品',
            'meal_type.required' => '请选择用餐时段',
            'meal_type.in' => '用餐时段仅支持 早/中/晚',
            'order_date.date_format' => '点餐日期格式为 YYYY-MM-DD',
            'status.required' => '请选择点餐状态',
            'cook_uid.required' => '请选择烹饪人',
            'items.required' => '请选择要提交的菜品',
            'items.min' => '请至少选择一道菜',
        ];
    }
}
