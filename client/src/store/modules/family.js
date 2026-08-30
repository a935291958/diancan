/**
 * 家庭状态：当前家庭 ID、家庭信息、成员列表
 * 字段对齐 jt_jiating_family / family_member
 */
import { defineStore } from 'pinia'
import {
  createFamily,
  getCurrentFamily,
  getFamilyList,
  getFamilyMembers,
  joinFamily,
  leaveFamily,
  updateFamily,
} from '@/api/family'
import { STORAGE_KEYS } from '@/config/constants'
import { normalizeFamily, unwrapList } from '@/utils/biz'
import { useUserStore } from '@/store/modules/user'

export const useFamilyStore = defineStore('family', {
  state: () => ({
    currentFamilyId: '',
    familyInfo: {
      id: '',
      family_name: '',
      invite_code: '',
      admin_uid: 0,
      member_count: 0,
    },
    familyList: [],
    memberList: [],
  }),

  getters: {
    hasFamily: (state) => Boolean(state.currentFamilyId),
    familyName: (state) => state.familyInfo?.family_name || state.familyInfo?.name || '未加入家庭',
    inviteCode: (state) => state.familyInfo?.invite_code || state.familyInfo?.inviteCode || '',
    isAdmin(state) {
      const userStore = useUserStore()
      return Number(state.familyInfo.admin_uid) === Number(userStore.userInfo.id)
    },
  },

  actions: {
    setCurrentFamily(family = {}) {
      this.familyInfo = { ...this.familyInfo, ...normalizeFamily(family) }
      this.currentFamilyId = this.familyInfo.id || ''
    },

    async fetchCurrentFamily() {
      const data = await getCurrentFamily({ loading: false, showError: false })
      if (data && (data.id || data.family_name)) {
        this.setCurrentFamily(data)
      }
      return data
    },

    async fetchFamilyList() {
      const list = unwrapList(await getFamilyList({}, { loading: false }))
      this.familyList = list.map(normalizeFamily)
      return this.familyList
    },

    async create(payload) {
      const data = await createFamily({ family_name: payload.family_name || payload.name })
      this.setCurrentFamily(data)
      return data
    },

    async join(inviteCode) {
      const data = await joinFamily({ invite_code: String(inviteCode).trim() })
      this.setCurrentFamily(data)
      return data
    },

    async updateName(familyName) {
      const data = await updateFamily(this.currentFamilyId, { family_name: familyName })
      this.setCurrentFamily(data || { ...this.familyInfo, family_name: familyName })
      return data
    },

    async fetchMembers() {
      if (!this.currentFamilyId) {
        this.memberList = []
        return []
      }
      const list = unwrapList(
        await getFamilyMembers({ family_id: this.currentFamilyId }, { loading: false })
      )
      this.memberList = list
      return list
    },

    async leave() {
      await leaveFamily({ family_id: this.currentFamilyId })
      this.reset()
    },

    switchFamily(family) {
      this.setCurrentFamily(family)
    },

    reset() {
      this.currentFamilyId = ''
      this.familyInfo = {
        id: '',
        family_name: '',
        invite_code: '',
        admin_uid: 0,
        member_count: 0,
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
