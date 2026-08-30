<?php

declare(strict_types=1);
/**
 * 点餐记录对外结构：规格 JSON 解析 + 菜品/点餐人/烹饪人关联。
 */

namespace App\Http\Mini\Service\Concern;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Http\Mini\Model\Order;
use App\Http\Mini\Support\Formatter;

trait FormatsOrder
{
    protected function mustOrder(int $id): Order
    {
        $order = Order::query()->with(['food', 'orderUser', 'cook'])->find($id);
        if (! $order instanceof Order) {
            throw new BusinessException(ResultCode::NOT_FOUND, '点餐记录不存在');
        }

        return $order;
    }

    /**
     * 对象或 JSON 字符串写入 select_spec 字段.
     */
    protected function encodeSpec(mixed $value): string
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
    protected function formatOrder(Order $order): array
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
