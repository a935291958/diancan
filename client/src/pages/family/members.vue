<template>
  <view class="page-wrap page-pad">
    <PageLoading v-if="loading" />
    <MemberList
      v-else
      :list="memberList"
      :admin-uid="familyStore.familyInfo.admin_uid"
      :show-remove="familyStore.isAdmin"
      @remove="handleRemove"
    />
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { removeFamilyMember } from '@/api/family'
import { useFamilyStore } from '@/store/modules/family'
import { confirmDialog, memberNameOf } from '@/utils/biz'

const familyStore = useFamilyStore()
const loading = ref(false)
const memberList = ref([])

async function loadMembers() {
  loading.value = true
  try {
    memberList.value = await familyStore.fetchMembers()
  } catch (error) {
    memberList.value = []
  } finally {
    loading.value = false
  }
}

onShow(() => {
  loadMembers()
})

async function handleRemove(item) {
  const ok = await confirmDialog(`确认移除「${memberNameOf(item)}」？`)
  if (!ok) return
  try {
    await removeFamilyMember(item.id)
    uni.showToast({ title: '已移除', icon: 'success' })
    loadMembers()
  } catch (error) {
    console.warn('[family] 移除成员失败', error)
  }
}
</script>
