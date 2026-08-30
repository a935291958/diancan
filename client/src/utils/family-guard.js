/**
 * 家庭校验：无当前家庭时引导去创建/加入
 */
import { useFamilyStore } from '@/store/modules/family'

export function ensureFamily() {
  const familyStore = useFamilyStore()
  if (familyStore.currentFamilyId) return true

  uni.showModal({
    title: '请先加入家庭',
    content: '创建或加入家庭后才能使用该功能',
    confirmText: '去家庭',
    success: (res) => {
      if (res.confirm) {
        uni.switchTab({ url: '/pages/family/index' })
      }
    },
  })
  return false
}
