/**
 * 表单校验
 */

import { isEmpty } from '@/utils/data'

const PHONE_REG = /^1[3-9]\d{9}$/

export function isPhone(value) {
  return PHONE_REG.test(String(value || '').trim())
}

export function isWxCode(value) {
  return !isEmpty(value) && String(value).length >= 4
}

export function assertRequired(value, message = '请填写完整信息') {
  if (isEmpty(value)) {
    uni.showToast({ title: message, icon: 'none' })
    return false
  }
  return true
}
