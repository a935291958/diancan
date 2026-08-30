<template>
  <view class="page-wrap page-pad">
    <PageLoading v-if="loading" />
    <PageEmpty v-else-if="!memberList.length" text="暂无成员" />
    <view v-else class="card">
      <view v-for="item in memberList" :key="item.id" class="member">
        <u-avatar :src="item.avatar" size="40" />
        <view class="flex-1">
          <text class="member__name">{{ item.nickname || '家庭成员' }}</text>
          <text class="text-tips">{{ roleText(item.role) }}</text>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { FAMILY_ROLE_MAP } from '@/config/constants'
import { useFamilyStore } from '@/store/modules/family'

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

function roleText(role) {
  return FAMILY_ROLE_MAP[role] || '成员'
}
</script>

<style lang="scss" scoped>
.member {
  display: flex;
  align-items: center;
  gap: 20rpx;
  padding: 24rpx;

  &__name {
    display: block;
    font-weight: 600;
  }
}
</style>
