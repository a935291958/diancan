/**
 * 本地存储封装
 * 统一走 uni.storage，兼容微信小程序同步读写
 */
export function getStorage(key, defaultValue = null) {
  try {
    const value = uni.getStorageSync(key)
    return value === '' || value === undefined ? defaultValue : value
  } catch (error) {
    console.warn('[storage] get 失败', key, error)
    return defaultValue
  }
}

export function setStorage(key, value) {
  try {
    uni.setStorageSync(key, value)
    return true
  } catch (error) {
    console.warn('[storage] set 失败', key, error)
    return false
  }
}

export function removeStorage(key) {
  try {
    uni.removeStorageSync(key)
    return true
  } catch (error) {
    console.warn('[storage] remove 失败', key, error)
    return false
  }
}

export function clearStorage() {
  try {
    uni.clearStorageSync()
    return true
  } catch (error) {
    console.warn('[storage] clear 失败', error)
    return false
  }
}
