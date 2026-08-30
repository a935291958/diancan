/**
 * 菜品模块接口
 * 资源：jt_jiating_food / jt_jiating_food_spec
 */
import { unwrapList } from '@/utils/biz'
import { del, get, post, put } from '@/utils/request'

export function getFoodList(params = {}, options = {}) {
  return get('/v1/food/list', params, options)
}

export function getFoodSpecs(foodId, options = {}) {
  return get(`/v1/food/${foodId}/specs`, {}, options)
}

export async function getFoodDetail(id, options = {}) {
  const data = await get(`/v1/food/${id}`, {}, options)
  if (!data) return data
  if (!data.specs) {
    try {
      data.specs = unwrapList(await getFoodSpecs(id, { loading: false, showError: false }))
    } catch (error) {
      data.specs = []
    }
  }
  return data
}

export function createFood(data, options = {}) {
  return post('/v1/food', data, options)
}

export function updateFood(id, data, options = {}) {
  return put(`/v1/food/${id}`, data, options)
}

export function saveFood(data, options = {}) {
  return data.id ? updateFood(data.id, data, options) : createFood(data, options)
}

export function deleteFood(id, options = {}) {
  return del(`/v1/food/${id}`, {}, options)
}

export function createFoodSpec(foodId, data, options = {}) {
  return post(`/v1/food/${foodId}/specs`, data, options)
}

export function updateFoodSpec(id, data, options = {}) {
  return put(`/v1/food-spec/${id}`, data, options)
}

export function deleteFoodSpec(id, options = {}) {
  return del(`/v1/food-spec/${id}`, {}, options)
}
