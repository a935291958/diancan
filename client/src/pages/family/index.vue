<template>
  <view class="page-wrap page-pad">
    <view v-if="familyStore.hasFamily" class="card family-card">
      <view class="flex-between" @click="goDetail">
        <view>
          <text class="family-card__name">{{ familyStore.familyName }}</text>
          <text class="family-card__code">邀请码 {{ familyStore.inviteCode || '-' }}</text>
        </view>
        <u-icon name="arrow-right" color="#c0c4cc" />
      </view>
      <view class="family-card__ops">
        <u-button type="primary" size="mini" plain text="复制邀请码" @click="copyCode" />
        <u-button type="primary" size="mini" text="成员列表" @click="goMembers" />
      </view>
    </view>
    <PageEmpty v-else text="还没有加入家庭" />

    <view class="mt-24">
      <u-button type="primary" text="创建家庭" @click="goCreate" />
      <u-button class="mt-24" type="primary" plain text="输入邀请码加入" @click="goJoin" />
    </view>

    <view v-if="familyStore.familyList.length > 1" class="mt-24">
      <text class="section-title">我的家庭</text>
      <view
        v-for="item in familyStore.familyList"
        :key="item.id"
        class="card list-item"
        @click="switchFamily(item)"
      >
        <text>{{ item.family_name }}</text>
        <u-tag v-if="item.id === familyStore.currentFamilyId" text="当前" size="mini" type="warning" />
      </view>
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
    await familyStore.fetchFamilyList()
  } catch (error) {
    console.warn('[family] 刷新失败', error)
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

function copyCode() {
  if (!familyStore.inviteCode) {
    uni.showToast({ title: '暂无邀请码', icon: 'none' })
    return
  }
  uni.setClipboardData({
    data: familyStore.inviteCode,
    success: () => uni.showToast({ title: '邀请码已复制', icon: 'success' }),
  })
}

function switchFamily(item) {
  familyStore.switchFamily(item)
  uni.showToast({ title: `已切换到${item.family_name}`, icon: 'success' })
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
    color: $text-tips;
  }

  &__ops {
    display: flex;
    gap: 16rpx;
    margin-top: 24rpx;
  }
}

.section-title {
  display: block;
  margin-bottom: 16rpx;
  font-size: 28rpx;
  font-weight: 600;
}

.list-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 24rpx;
  margin-bottom: 16rpx;
}
</style>
