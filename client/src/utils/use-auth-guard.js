/**
 * 页面级登录守卫
 * 原生 tabBar 点击不会走 navigate 拦截器，需在页面 onShow 中补检
 */
import { onShow } from '@dcloudio/uni-app'
import { WHITE_LIST } from '@/config/constants'
import { LOGIN_PATH } from '@/config/env'
import { hasLogin } from '@/utils/auth'

export function useAuthGuard() {
  onShow(() => {
    const pages = getCurrentPages()
    const current = pages[pages.length - 1]
    const route = current ? `/${current.route}` : ''
    if (WHITE_LIST.includes(route) || hasLogin()) return
    uni.reLaunch({ url: LOGIN_PATH })
  })
}
