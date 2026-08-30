/**
 * 今日菜单 / 购物车相关接口
 */
import { get, post } from '@/utils/request'

export function getTodayMenu(params = {}, options = {}) {
  return get('/menu/today', params, options)
}

export function saveTodayMenu(data, options = {}) {
  return post('/menu/today/save', data, options)
}

export function getCartList(params = {}, options = {}) {
  return get('/cart/list', params, options)
}

export function addCartItem(data, options = {}) {
  return post('/cart/add', data, options)
}

export function updateCartItem(data, options = {}) {
  return post('/cart/update', data, options)
}

export function clearCart(data = {}, options = {}) {
  return post('/cart/clear', data, options)
}
