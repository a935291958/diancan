/**
 * 全局路由拦截
 * 未登录访问非白名单页面时，统一跳转登录页
 */
import { WHITE_LIST } from '@/config/constants'
import { LOGIN_PATH } from '@/config/env'
import { hasLogin } from '@/utils/auth'

let installed = false

function normalizeUrl(url = '') {
  const path = url.split('?')[0]
  return path.startsWith('/') ? path : `/${path}`
}

function isWhiteList(url) {
  const path = normalizeUrl(url)
  return WHITE_LIST.some((item) => path === item || path.startsWith(`${item}?`))
}

function guard(args) {
  if (isWhiteList(args.url) || hasLogin()) {
    return args
  }
  uni.showToast({ title: '请先登录', icon: 'none' })
  uni.reLaunch({ url: LOGIN_PATH })
  return false
}

const METHODS = ['navigateTo', 'redirectTo', 'reLaunch', 'switchTab']

export function setupInterceptors() {
  if (installed) return
  installed = true

  METHODS.forEach((method) => {
    uni.addInterceptor(method, {
      invoke(args) {
        return guard(args)
      },
      fail(error) {
        console.warn(`[router] ${method} 失败`, error)
      },
    })
  })
}
