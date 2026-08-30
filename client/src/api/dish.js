/**
 * 菜品相关接口
 */
import { get, post, put, del } from '@/utils/request'

export function getDishList(params = {}, options = {}) {
  return get('/dish/list', params, options)
}

export function getDishDetail(id, options = {}) {
  return get(`/dish/${id}`, {}, options)
}

export function saveDish(data, options = {}) {
  return data.id ? put('/dish/update', data, options) : post('/dish/create', data, options)
}

export function deleteDish(id, options = {}) {
  return del('/dish/delete', { id }, options)
}

export function getCategoryList(params = {}, options = {}) {
  return get('/category/list', params, options)
}

export function saveCategory(data, options = {}) {
  return data.id
    ? put('/category/update', data, options)
    : post('/category/create', data, options)
}

export function deleteCategory(id, options = {}) {
  return del('/category/delete', { id }, options)
}
