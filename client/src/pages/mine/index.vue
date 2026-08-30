<template>
  <view class="page-wrap">
    <view class="header">
      <u-avatar :src="userStore.avatar" size="64" />
      <view class="header__info">
        <text class="header__name">{{ userStore.nickname }}</text>
        <text class="header__family">{{ familyStore.familyName }}</text>
      </view>
    </view>

    <view class="page-pad">
      <view class="card">
        <u-cell-group :border="false">
          <u-cell title="个人资料" is-link url="/pages/mine/profile" />
          <u-cell title="菜品管理" is-link url="/pages/dish/list" />
          <u-cell title="点餐记录" is-link url="/pages/order/list" />
          <u-cell title="设置" is-link url="/pages/mine/settings" />
        </u-cell-group>
      </view>
    </view>
  </view>
</template>

<script setup>
import { useUserStore } from '@/store/modules/user'
import { useFamilyStore } from '@/store/modules/family'
import { useAuthGuard } from '@/utils/use-auth-guard'

useAuthGuard()

const userStore = useUserStore()
const familyStore = useFamilyStore()
</script>

<style lang="scss" scoped>
.header {
  display: flex;
  align-items: center;
  gap: 24rpx;
  padding: 48rpx 32rpx 56rpx;
  background: linear-gradient(135deg, #ff6b35 0%, #ff8f66 100%);

  &__name {
    display: block;
    font-size: 36rpx;
    font-weight: 700;
    color: #fff;
  }

  &__family {
    display: block;
    margin-top: 8rpx;
    font-size: 24rpx;
    color: rgba(255, 255, 255, 0.86);
  }
}
</style>
