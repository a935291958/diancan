/**
 * 登录态读写
 * 仅依赖 storage，避免与 Pinia / request 产生循环引用
 */
import { STORAGE_KEYS } from '@/config/constants'
import { LOGIN_PATH } from '@/config/env'
import { getStorage, removeStorage, setStorage } from '@/utils/storage'

export function getToken() {
  const token = getStorage(STORAGE_KEYS.TOKEN, '')
  if (token) return token

  // 兼容仅存在 Pinia 持久化数据的场景（冷启动竞态）
  try {
    const raw = getStorage(STORAGE_KEYS.USER_STORE, '')
    if (!raw) return ''
    const parsed = typeof raw === 'string' ? JSON.parse(raw) : raw
    return parsed?.token || ''
  } catch (error) {
    return ''
  }
}

export function setToken(token) {
  if (!token) {
    removeStorage(STORAGE_KEYS.TOKEN)
    return
  }
  setStorage(STORAGE_KEYS.TOKEN, token)
}

export function hasLogin() {
  return Boolean(getToken())
}

/**
 * 清除登录缓存并跳转登录页
 * Pinia 内存态由登录页 onLoad 再执行 $reset，避免循环依赖
 */
export function clearAuthAndToLogin() {
  removeStorage(STORAGE_KEYS.TOKEN)
  removeStorage(STORAGE_KEYS.USER_STORE)
  removeStorage(STORAGE_KEYS.FAMILY_STORE)

  const pages = getCurrentPages()
  const current = pages[pages.length - 1]
  const route = current ? `/${current.route}` : ''
  if (route === LOGIN_PATH) return

  uni.reLaunch({ url: LOGIN_PATH })
}
