/**
 * 全局业务常量
 */

/** 本地缓存 Key，避免魔法字符串散落各处 */
export const STORAGE_KEYS = {
  TOKEN: 'diancan_token',
  USER_STORE: 'diancan_user',
  FAMILY_STORE: 'diancan_family',
}

/** 无需登录即可访问的页面（其余页面一律校验 token） */
export const WHITE_LIST = [
  '/pages/login/index',
]

/** 订单状态文案映射，页面展示统一从此读取 */
export const ORDER_STATUS_MAP = {
  pending: { text: '待确认', type: 'warning' },
  cooking: { text: '制作中', type: 'primary' },
  done: { text: '已完成', type: 'success' },
  cancelled: { text: '已取消', type: 'info' },
}

/** 家庭成员角色 */
export const FAMILY_ROLE_MAP = {
  owner: '家长',
  admin: '管理员',
  member: '成员',
}

/** 默认分页 */
export const DEFAULT_PAGE_SIZE = 10
