/**
 * 用户相关接口
 */
import { get, post, put } from '@/utils/request'

/** 微信登录：用 code 换 token */
export function wxLogin(data, options = {}) {
  return post('/auth/wx-login', data, options)
}

export function getUserInfo(options = {}) {
  return get('/user/info', {}, options)
}

export function updateUserProfile(data, options = {}) {
  return put('/user/profile', data, options)
}
