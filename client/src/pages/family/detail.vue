<template>
  <view class="page-wrap page-pad">
    <view class="card block">
      <text class="block__name">{{ familyStore.familyName }}</text>
      <text class="text-tips mt-12">邀请码 {{ familyStore.familyInfo.inviteCode || '-' }}</text>
      <text class="text-tips mt-12">成员 {{ familyStore.familyInfo.memberCount || 0 }} 人</text>
    </view>
    <view class="mt-24">
      <u-button type="primary" text="查看成员" @click="goMembers" />
    </view>
  </view>
</template>

<script setup>
import { onShow } from '@dcloudio/uni-app'
import { useFamilyStore } from '@/store/modules/family'

const familyStore = useFamilyStore()

onShow(() => {
  familyStore.fetchCurrentFamily()
})

function goMembers() {
  uni.navigateTo({ url: '/pages/family/members' })
}
</script>

<style lang="scss" scoped>
.block {
  padding: 32rpx;

  &__name {
    font-size: 40rpx;
    font-weight: 700;
  }
}
</style>
