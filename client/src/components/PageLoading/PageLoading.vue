<template>
  <view v-if="show" class="page-loading">
    <u-loading-icon :text="text" text-size="24rpx" />
  </view>
</template>

<script setup>
/**
 * 页面级加载态（与请求全局 loading 互补，用于首屏骨架）
 */
defineProps({
  show: {
    type: Boolean,
    default: true,
  },
  text: {
    type: String,
    default: '加载中',
  },
})
</script>

<style lang="scss" scoped>
.page-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 400rpx;
}
</style>
