/**
 * 家庭相关接口
 */
import { get, post, put, del } from '@/utils/request'

export function getCurrentFamily(options = {}) {
  return get('/family/current', {}, options)
}

export function getFamilyList(options = {}) {
  return get('/family/list', {}, options)
}

export function getFamilyDetail(id, options = {}) {
  return get(`/family/${id}`, {}, options)
}

export function createFamily(data, options = {}) {
  return post('/family/create', data, options)
}

export function joinFamily(data, options = {}) {
  return post('/family/join', data, options)
}

export function updateFamily(data, options = {}) {
  return put('/family/update', data, options)
}

export function getFamilyMembers(familyId, options = {}) {
  return get('/family/members', { familyId }, options)
}

export function removeFamilyMember(data, options = {}) {
  return del('/family/member', data, options)
}
