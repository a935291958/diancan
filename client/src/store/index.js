/**
 * Pinia 入口
 * 使用 uni.storage 适配微信小程序持久化
 */
import { createPinia } from 'pinia'
import { createPersistedState } from 'pinia-plugin-persistedstate'

const pinia = createPinia()

pinia.use(
  createPersistedState({
    storage: {
      getItem: (key) => {
        const value = uni.getStorageSync(key)
        return value === '' || value === undefined ? null : value
      },
      setItem: (key, value) => {
        uni.setStorageSync(key, value)
      },
    },
  })
)

export default pinia
export { useUserStore } from './modules/user'
export { useFamilyStore } from './modules/family'
export { useOrderDraftStore } from './modules/order-draft'
