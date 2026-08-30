<?php

declare(strict_types=1);
/**
 * 点餐校验.
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
            'meal_type' => 'required|string|in:早,中,晚',
            'order_date' => 'nullable|string|max:20',
        ];
    }

    protected function updateRules(): array
    {
        return [
            'select_spec' => 'nullable',
            'cook_uid' => 'nullable|integer|min:0',
            'meal_type' => 'nullable|string|in:早,中,晚',
            'order_date' => 'nullable|string|max:20',
            'status' => 'nullable|integer|in:' . implode(',', [
                Order::STATUS_PENDING,
                Order::STATUS_COOKING,
                Order::STATUS_DONE,
                Order::STATUS_CANCELLED,
            ]),
        ];
    }

    protected function updateStatusRules(): array
    {
        return [
            'status' => 'required|integer|in:' . implode(',', [
                Order::STATUS_PENDING,
                Order::STATUS_COOKING,
                Order::STATUS_DONE,
                Order::STATUS_CANCELLED,
            ]),
        ];
    }

    protected function assignCookRules(): array
    {
        return [
            'cook_uid' => 'required|integer|min:1',
        ];
    }

    protected function commonMessages(): array
    {
        return [
            'food_id.required' => '请选择菜品',
            'meal_type.required' => '请选择用餐时段',
            'meal_type.in' => '用餐时段仅支持 早/中/晚',
            'status.required' => '请选择点餐状态',
            'cook_uid.required' => '请选择烹饪人',
        ];
    }
}
