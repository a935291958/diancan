<template>
  <view class="page-wrap page-pad">
    <view v-if="familyStore.hasFamily" class="card family-card" @click="goDetail">
      <view class="flex-between">
        <view>
          <text class="family-card__name">{{ familyStore.familyName }}</text>
          <text class="family-card__code text-tips">邀请码 {{ familyStore.familyInfo.inviteCode || '-' }}</text>
        </view>
        <u-icon name="arrow-right" color="#c0c4cc" />
      </view>
    </view>
    <PageEmpty v-else text="还没有加入家庭" />

    <view class="actions mt-24">
      <u-button type="primary" text="创建家庭" @click="goCreate" />
      <u-button class="mt-24" type="primary" plain text="加入家庭" @click="goJoin" />
      <u-button v-if="familyStore.hasFamily" class="mt-24" text="家庭成员" @click="goMembers" />
    </view>
  </view>
</template>

<script setup>
import { onPullDownRefresh, onShow } from '@dcloudio/uni-app'
import { useFamilyStore } from '@/store/modules/family'
import { useAuthGuard } from '@/utils/use-auth-guard'

useAuthGuard()

const familyStore = useFamilyStore()

async function refresh() {
  try {
    await familyStore.fetchCurrentFamily()
  } finally {
    uni.stopPullDownRefresh()
  }
}

onShow(() => {
  refresh()
})

onPullDownRefresh(() => {
  refresh()
})

function goCreate() {
  uni.navigateTo({ url: '/pages/family/create' })
}

function goJoin() {
  uni.navigateTo({ url: '/pages/family/join' })
}

function goDetail() {
  uni.navigateTo({ url: '/pages/family/detail' })
}

function goMembers() {
  uni.navigateTo({ url: '/pages/family/members' })
}
</script>

<style lang="scss" scoped>
.family-card {
  padding: 32rpx;

  &__name {
    display: block;
    font-size: 36rpx;
    font-weight: 700;
  }

  &__code {
    display: block;
    margin-top: 8rpx;
    font-size: 24rpx;
  }
}
</style>
