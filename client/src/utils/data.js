/**
 * 数据处理 & 空值判断
 */

const EMPTY_VALUES = [null, undefined, '']

/** 是否为空：null / undefined / '' / [] / {} */
export function isEmpty(value) {
  if (EMPTY_VALUES.includes(value)) return true
  if (Array.isArray(value)) return value.length === 0
  if (typeof value === 'object') return Object.keys(value).length === 0
  return false
}

export function isNotEmpty(value) {
  return !isEmpty(value)
}

/** 空值回退 */
export function emptyTo(value, fallback = '') {
  return isEmpty(value) ? fallback : value
}

export function toNumber(value, fallback = 0) {
  const num = Number(value)
  return Number.isFinite(num) ? num : fallback
}

export function toArray(value) {
  if (Array.isArray(value)) return value
  if (isEmpty(value)) return []
  return [value]
}

export function deepClone(source) {
  if (source == null || typeof source !== 'object') return source
  try {
    return JSON.parse(JSON.stringify(source))
  } catch (error) {
    return source
  }
}

export function pick(object, keys = []) {
  if (!object) return {}
  return keys.reduce((result, key) => {
    if (Object.prototype.hasOwnProperty.call(object, key)) {
      result[key] = object[key]
    }
    return result
  }, {})
}

export function omit(object, keys = []) {
  if (!object) return {}
  const exclude = new Set(keys)
  return Object.keys(object).reduce((result, key) => {
    if (!exclude.has(key)) result[key] = object[key]
    return result
  }, {})
}

export function uniqueBy(list, key) {
  const seen = new Set()
  return toArray(list).filter((item) => {
    const flag = key ? item?.[key] : item
    if (seen.has(flag)) return false
    seen.add(flag)
    return true
  })
}

export function groupBy(list, key) {
  return toArray(list).reduce((result, item) => {
    const groupKey = item?.[key] ?? 'other'
    if (!result[groupKey]) result[groupKey] = []
    result[groupKey].push(item)
    return result
  }, {})
}

export function debounce(fn, wait = 300) {
  let timer = null
  return function debounced(...args) {
    clearTimeout(timer)
    timer = setTimeout(() => fn.apply(this, args), wait)
  }
}

export function throttle(fn, wait = 300) {
  let last = 0
  return function throttled(...args) {
    const now = Date.now()
    if (now - last < wait) return
    last = now
    fn.apply(this, args)
  }
}

/** 金额分转元，保留两位 */
export function fenToYuan(fen) {
  return (toNumber(fen) / 100).toFixed(2)
}

export function yuanToFen(yuan) {
  return Math.round(toNumber(yuan) * 100)
}

export function formatPrice(value) {
  return `¥${toNumber(value).toFixed(2)}`
}
