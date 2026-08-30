/**
 * 全局业务常量 —— 与数据表 jt_jiating_* 字段、枚举保持一致
 */

export const STORAGE_KEYS = {
  TOKEN: 'diancan_token',
  USER_STORE: 'diancan_user',
  FAMILY_STORE: 'diancan_family',
}

export const WHITE_LIST = ['/pages/login/index']

/** 点餐状态：order.status tinyint */
export const ORDER_STATUS = {
  PENDING: 1,
  COOKING: 2,
  DONE: 3,
  CANCELLED: 4,
}

export const ORDER_STATUS_MAP = {
  1: { text: '待制作', type: 'warning' },
  2: { text: '制作中', type: 'primary' },
  3: { text: '已完成', type: 'success' },
  4: { text: '已取消', type: 'info' },
}

/** 用餐时段：order.meal_type */
export const MEAL_TYPES = [
  { name: '早餐', value: '早' },
  { name: '午餐', value: '中' },
  { name: '晚餐', value: '晚' },
]

/** 菜品分类（food.category 为字符串，非独立表） */
export const FOOD_CATEGORIES = ['家常菜', '汤羹', '主食', '凉菜', '热菜', '甜品', '其他']

/** 规格预设：food_spec.spec_name / spec_value */
export const SPEC_PRESETS = [
  { spec_name: '辣度', values: ['不辣', '微辣', '中辣', '特辣'] },
  { spec_name: '分量', values: ['小份', '中份', '大份'] },
]

export const DEFAULT_PAGE_SIZE = 20
