<template>
  <view class="page-wrap page-pad">
    <PageEmpty text="请在点餐页勾选菜品后提交" show-action action-text="去点餐" @action="goMenu" />
  </view>
</template>

<script setup>
function goMenu() {
  uni.switchTab({ url: '/pages/menu/index' })
}
</script>
