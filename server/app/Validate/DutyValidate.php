<?php

declare(strict_types=1);
/**
 * 模块：分工 — 指派烹饪人、状态流转、当日看板。
 */

namespace App\Validate;

use App\Model\Mini\Order;

class DutyValidate extends AbstractValidate
{
    protected function assignCookRules(): array
    {
        return [
            'cook_uid' => 'required|integer|min:1',
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

    protected function commonMessages(): array
    {
        return [
            'cook_uid.required' => '请选择烹饪人',
            'cook_uid.min' => '烹饪人无效',
            'status.required' => '请选择制作状态',
            'status.in' => '状态仅支持 1待制作 2制作中 3已完成 4已取消',
        ];
    }
}
