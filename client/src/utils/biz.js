/**
 * 业务数据处理：列表解包、规格 JSON、烹饪人 ID
 * 字段名与数据表保持 snake_case
 */
import { toArray } from '@/utils/data'

/** 兼容数组 / { list } / { records } / { items } */
export function unwrapList(data) {
  if (Array.isArray(data)) return data
  if (!data || typeof data !== 'object') return []
  if (Array.isArray(data.list)) return data.list
  if (Array.isArray(data.records)) return data.records
  if (Array.isArray(data.items)) return data.items
  if (Array.isArray(data.data)) return data.data
  return []
}

export function normalizeFamily(raw = {}) {
  return {
    id: raw.id || '',
    family_name: raw.family_name || raw.name || '',
    invite_code: raw.invite_code || raw.inviteCode || '',
    admin_uid: Number(raw.admin_uid || raw.adminUid || 0),
    member_count: Number(raw.member_count || raw.memberCount || 0),
    create_time: raw.create_time || raw.createTime || 0,
  }
}

export function parseCookUids(value) {
  if (Array.isArray(value)) return value.map(Number).filter(Boolean)
  return String(value || '')
    .split(',')
    .map((item) => Number(String(item).trim()))
    .filter(Boolean)
}

export function joinCookUids(list) {
  return toArray(list)
    .map(Number)
    .filter(Boolean)
    .join(',')
}

/** spec_value 支持逗号 / 顿号分隔 */
export function splitSpecValues(value) {
  return String(value || '')
    .split(/[,，、]/)
    .map((item) => item.trim())
    .filter(Boolean)
}

/**
 * 将 food_spec 行转为编辑器结构
 * [{ spec_name, spec_value }] => [{ spec_name, values: [] }]
 */
export function groupSpecs(specRows = []) {
  const map = new Map()
  toArray(specRows).forEach((row) => {
    const name = row.spec_name || row.specName || ''
    if (!name) return
    if (!map.has(name)) {
      map.set(name, {
        id: row.id,
        spec_name: name,
        values: [],
      })
    }
    splitSpecValues(row.spec_value || row.specValue).forEach((item) => {
      const group = map.get(name)
      if (!group.values.includes(item)) group.values.push(item)
    })
  })
  return Array.from(map.values())
}

/** 编辑器结构还原为接口 specs */
export function flattenSpecs(groups = []) {
  return toArray(groups)
    .filter((item) => item.spec_name && toArray(item.values).length)
    .map((item) => ({
      spec_name: String(item.spec_name).trim(),
      spec_value: toArray(item.values).join(','),
    }))
}

export function parseSelectSpec(value) {
  if (!value) return {}
  if (typeof value === 'object') return value
  try {
    const parsed = JSON.parse(value)
    return parsed && typeof parsed === 'object' ? parsed : {}
  } catch (error) {
    return {}
  }
}

export function stringifySelectSpec(map = {}) {
  return JSON.stringify(map || {})
}

export function formatSelectSpec(value) {
  const map = parseSelectSpec(value)
  const text = Object.keys(map)
    .map((key) => {
      const val = map[key]
      const label = Array.isArray(val) ? val.join('、') : val
      return `${key}:${label}`
    })
    .join(' / ')
  return text || '默认'
}

export function memberUidOf(item = {}) {
  return Number(item.uid || item.user_id || item.user?.id || 0)
}

export function memberNameOf(item = {}) {
  return item.nickname || item.user?.nickname || '家庭成员'
}

export function memberAvatarOf(item = {}) {
  return item.avatar || item.user?.avatar || ''
}

export function resolveCookMembers(cookUids, members = []) {
  const ids = parseCookUids(cookUids)
  if (!ids.length) return []
  return toArray(members).filter((item) => ids.includes(memberUidOf(item)))
}

export function orderUserNameOf(item = {}) {
  return item.order_nickname || item.order_user?.nickname || (item.order_uid ? `#${item.order_uid}` : '-')
}

export function orderCookNameOf(item = {}) {
  return item.cook_nickname || item.cook?.nickname || (item.cook_uid ? `#${item.cook_uid}` : '未指派')
}

export function foodNameOf(item = {}) {
  return item.food_name || item.food?.food_name || item.name || '菜品'
}

export function foodImgOf(item = {}) {
  return item.food_img || item.food?.food_img || item.cover || ''
}

export function confirmDialog(content, title = '提示') {
  return new Promise((resolve) => {
    uni.showModal({
      title,
      content,
      success: (res) => resolve(Boolean(res.confirm)),
    })
  })
}
