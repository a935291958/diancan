/**
 * 用户状态：登录 token、用户信息
 */
import { defineStore } from 'pinia'
import { STORAGE_KEYS } from '@/config/constants'
import { wxLogin, getUserInfo, updateUserProfile } from '@/api/user'
import { setToken } from '@/utils/auth'

export const useUserStore = defineStore('user', {
  state: () => ({
    token: '',
    userInfo: {
      id: '',
      nickname: '',
      avatar: '',
      phone: '',
      gender: 0,
    },
  }),

  getters: {
    isLoggedIn: (state) => Boolean(state.token),
    nickname: (state) => state.userInfo?.nickname || '微信用户',
    avatar: (state) => state.userInfo?.avatar || '',
  },

  actions: {
    /**
     * 微信 code 登录
     * @param {string} code uni.login 返回的 code
     */
    async loginByWx(code) {
      const data = await wxLogin({ code })
      this.setLogin(data?.token || '', data?.userInfo || {})
      return data
    },

    setLogin(token, userInfo = {}) {
      this.token = token
      this.userInfo = { ...this.userInfo, ...userInfo }
      setToken(token)
    },

    async fetchUserInfo() {
      if (!this.token) return null
      const info = await getUserInfo({ loading: false, showError: false })
      if (info) {
        this.userInfo = { ...this.userInfo, ...info }
      }
      return info
    },

    async updateProfile(payload) {
      const info = await updateUserProfile(payload)
      this.userInfo = { ...this.userInfo, ...info }
      return info
    },

    logout() {
      this.token = ''
      this.userInfo = {
        id: '',
        nickname: '',
        avatar: '',
        phone: '',
        gender: 0,
      }
      setToken('')
    },

    reset() {
      this.logout()
    },
  },

  persist: {
    key: STORAGE_KEYS.USER_STORE,
    paths: ['token', 'userInfo'],
  },
})
