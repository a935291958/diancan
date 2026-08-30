/**
 * 家庭模块接口
 * 资源：jt_jiating_family / jt_jiating_family_member
 */
import { del, get, post, put } from '@/utils/request'

export function getFamilyList(params = {}, options = {}) {
  return get('/v1/family/list', params, options)
}

export function getCurrentFamily(options = {}) {
  return get('/v1/family/current', {}, options)
}

export function getFamilyDetail(id, options = {}) {
  return get(`/v1/family/${id}`, {}, options)
}

/** 创建家庭 family_name */
export function createFamily(data, options = {}) {
  return post('/v1/family', data, options)
}

/** 邀请码加入 invite_code */
export function joinFamily(data, options = {}) {
  return post('/v1/family/join', data, options)
}

export function updateFamily(id, data, options = {}) {
  return put(`/v1/family/${id}`, data, options)
}

export function deleteFamily(id, options = {}) {
  return del(`/v1/family/${id}`, {}, options)
}

export function getFamilyMembers(params = {}, options = {}) {
  return get('/v1/family/members', params, options)
}

export function removeFamilyMember(id, options = {}) {
  return del(`/v1/family/member/${id}`, {}, options)
}

export function leaveFamily(data = {}, options = {}) {
  return post('/v1/family/leave', data, options)
}
