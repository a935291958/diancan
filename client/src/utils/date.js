/**
 * 时间格式化工具
 * 不依赖 dayjs，避免小程序端额外包体；输入支持 Date / 时间戳 / 日期字符串
 */

function toDate(value) {
  if (!value && value !== 0) return null
  if (value instanceof Date) {
    return Number.isNaN(value.getTime()) ? null : value
  }
  if (typeof value === 'number') {
    const ms = value < 1e12 ? value * 1000 : value
    const date = new Date(ms)
    return Number.isNaN(date.getTime()) ? null : date
  }
  const normalized = String(value).replace(/-/g, '/')
  const date = new Date(normalized)
  return Number.isNaN(date.getTime()) ? null : date
}

function pad(num, length = 2) {
  return String(num).padStart(length, '0')
}

/**
 * 按 token 格式化
 * YYYY MM DD HH mm ss
 */
export function formatDate(value, pattern = 'YYYY-MM-DD HH:mm:ss') {
  const date = toDate(value)
  if (!date) return ''

  const map = {
    YYYY: date.getFullYear(),
    MM: pad(date.getMonth() + 1),
    DD: pad(date.getDate()),
    HH: pad(date.getHours()),
    mm: pad(date.getMinutes()),
    ss: pad(date.getSeconds()),
  }

  return pattern.replace(/YYYY|MM|DD|HH|mm|ss/g, (token) => map[token])
}

export function formatDateTime(value) {
  return formatDate(value, 'YYYY-MM-DD HH:mm')
}

export function formatDay(value) {
  return formatDate(value, 'YYYY-MM-DD')
}

const WEEK_DAYS = ['日', '一', '二', '三', '四', '五', '六']

export function getWeekday(value) {
  const date = toDate(value) || new Date()
  return `星期${WEEK_DAYS[date.getDay()]}`
}

export function isToday(value) {
  const date = toDate(value)
  if (!date) return false
  const now = new Date()
  return (
    date.getFullYear() === now.getFullYear() &&
    date.getMonth() === now.getMonth() &&
    date.getDate() === now.getDate()
  )
}

/**
 * 相对时间：刚刚 / x分钟前 / 昨天 / 日期
 */
export function fromNow(value) {
  const date = toDate(value)
  if (!date) return ''

  const diff = Date.now() - date.getTime()
  if (diff < 0) return formatDateTime(date)
  if (diff < 60 * 1000) return '刚刚'
  if (diff < 60 * 60 * 1000) return `${Math.floor(diff / 60000)}分钟前`
  if (diff < 24 * 60 * 60 * 1000) return `${Math.floor(diff / 3600000)}小时前`
  if (diff < 48 * 60 * 60 * 1000) return '昨天'
  return formatDay(date)
}

export function getTodayText() {
  const now = new Date()
  return `${formatDay(now)} ${getWeekday(now)}`
}
