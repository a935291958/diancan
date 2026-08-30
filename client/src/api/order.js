/**
 * 订单相关接口
 */
import { get, post } from '@/utils/request'

export function getOrderList(params = {}, options = {}) {
  return get('/order/list', params, options)
}

export function getOrderDetail(id, options = {}) {
  return get(`/order/${id}`, {}, options)
}

export function createOrder(data, options = {}) {
  return post('/order/create', data, options)
}

export function cancelOrder(id, options = {}) {
  return post('/order/cancel', { id }, options)
}

export function confirmOrder(id, options = {}) {
  return post('/order/confirm', { id }, options)
}
