/**
 * 家庭状态：当前家庭 ID、家庭信息、家庭列表
 */
import { defineStore } from 'pinia'
import { STORAGE_KEYS } from '@/config/constants'
import {
  getCurrentFamily,
  getFamilyList,
  createFamily,
  joinFamily,
  getFamilyMembers,
} from '@/api/family'

export const useFamilyStore = defineStore('family', {
  state: () => ({
    currentFamilyId: '',
    familyInfo: {
      id: '',
      name: '',
      avatar: '',
      inviteCode: '',
      memberCount: 0,
      ownerId: '',
    },
    familyList: [],
    memberList: [],
  }),

  getters: {
    hasFamily: (state) => Boolean(state.currentFamilyId),
    familyName: (state) => state.familyInfo?.name || '未加入家庭',
  },

  actions: {
    setCurrentFamily(family = {}) {
      this.familyInfo = { ...this.familyInfo, ...family }
      this.currentFamilyId = family.id || family.familyId || ''
    },

    async fetchCurrentFamily() {
      const data = await getCurrentFamily({ loading: false, showError: false })
      if (data) this.setCurrentFamily(data)
      return data
    },

    async fetchFamilyList() {
      const list = await getFamilyList({ loading: false })
      this.familyList = Array.isArray(list) ? list : list?.records || []
      return this.familyList
    },

    async create(payload) {
      const data = await createFamily(payload)
      this.setCurrentFamily(data)
      return data
    },

    async join(inviteCode) {
      const data = await joinFamily({ inviteCode })
      this.setCurrentFamily(data)
      return data
    },

    async fetchMembers() {
      if (!this.currentFamilyId) {
        this.memberList = []
        return []
      }
      const list = await getFamilyMembers(this.currentFamilyId)
      this.memberList = Array.isArray(list) ? list : list?.records || []
      return this.memberList
    },

    switchFamily(family) {
      this.setCurrentFamily(family)
    },

    reset() {
      this.currentFamilyId = ''
      this.familyInfo = {
        id: '',
        name: '',
        avatar: '',
        inviteCode: '',
        memberCount: 0,
        ownerId: '',
      }
      this.familyList = []
      this.memberList = []
    },
  },

  persist: {
    key: STORAGE_KEYS.FAMILY_STORE,
    paths: ['currentFamilyId', 'familyInfo', 'familyList'],
  },
})
