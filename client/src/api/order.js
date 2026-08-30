/**
 * 点餐 / 分工模块接口
 * 资源：jt_jiating_order
 */
import { del, get, post, put } from '@/utils/request'

export function getOrderList(params = {}, options = {}) {
  return get('/v1/order/list', params, options)
}

/** 当日清单，默认按 order_date 过滤 */
export function getTodayOrders(params = {}, options = {}) {
  return get('/v1/order/today', params, options)
}

export function getOrderDetail(id, options = {}) {
  return get(`/v1/order/${id}`, {}, options)
}

/**
 * 创建一条点餐记录
 * family_id, food_id, select_spec, cook_uid, meal_type, order_date
 */
export function createOrder(data, options = {}) {
  return post('/v1/order', data, options)
}

/** 批量提交（按菜品逐条写入 order 表） */
export async function createOrders(payload, options = {}) {
  const { items = [], ...base } = payload
  const results = []
  for (let i = 0; i < items.length; i += 1) {
    const data = await createOrder({ ...base, ...items[i] }, { ...options, loading: i === 0 })
    results.push(data)
  }
  return results
}

export function updateOrder(id, data, options = {}) {
  return put(`/v1/order/${id}`, data, options)
}

export function updateOrderStatus(id, status, options = {}) {
  return put(`/v1/order/${id}/status`, { status }, options)
}

export function assignOrderCook(id, cookUid, options = {}) {
  return put(`/v1/order/${id}/cook`, { cook_uid: cookUid }, options)
}

export function cancelOrder(id, options = {}) {
  return updateOrderStatus(id, 4, options)
}

export function deleteOrder(id, options = {}) {
  return del(`/v1/order/${id}`, {}, options)
}
