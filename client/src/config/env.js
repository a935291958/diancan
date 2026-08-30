/**
 * 环境与请求相关常量
 * Vite 通过 import.meta.env 注入 .env.* 变量
 */
export const APP_ENV = import.meta.env.VITE_APP_ENV || 'development'

/** 接口根地址，末尾不含斜杠 */
export const BASE_URL = String(import.meta.env.VITE_API_BASE_URL || '').replace(/\/$/, '')

/** 请求超时时间（毫秒） */
export const REQUEST_TIMEOUT = Number(import.meta.env.VITE_REQUEST_TIMEOUT) || 15000

/** 业务成功码（兼容 0 / 200） */
export const SUCCESS_CODES = [0, 200]

/** 登录失效业务码 */
export const UNAUTHORIZED_CODE = 401

/** 登录页路径 */
export const LOGIN_PATH = '/pages/login/index'

/** 首页路径（tabBar） */
export const HOME_PATH = '/pages/index/index'
