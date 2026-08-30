<template>
  <view class="page-wrap page-pad">
    <text class="page-title">菜品分类</text>
    <text class="text-tips">分类保存在菜品表 category 字段，可在新增/编辑菜品时选择。</text>
    <view class="card mt-24">
      <view v-for="item in FOOD_CATEGORIES" :key="item" class="cate-row">{{ item }}</view>
    </view>
  </view>
</template>

<script setup>
import { FOOD_CATEGORIES } from '@/config/constants'
</script>

<style lang="scss" scoped>
.page-title {
  display: block;
  margin-bottom: 12rpx;
  font-size: 32rpx;
  font-weight: 700;
}

.cate-row {
  padding: 28rpx 24rpx;
  border-bottom: 1rpx solid $border-color;

  &:last-child {
    border-bottom: none;
  }
}
</style>
