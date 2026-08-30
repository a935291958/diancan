<template>
  <view class="page-empty">
    <u-empty :mode="mode" :text="text" :icon-size="iconSize">
      <template v-if="showAction" #bottom>
        <view class="page-empty__action">
          <u-button
            type="primary"
            size="small"
            shape="circle"
            :text="actionText"
            @click="onAction"
          />
        </view>
      </template>
    </u-empty>
  </view>
</template>

<script setup>
/**
 * 全局空状态，统一 uView empty 视觉
 */
defineProps({
  mode: {
    type: String,
    default: 'data',
  },
  text: {
    type: String,
    default: '暂无数据',
  },
  iconSize: {
    type: [String, Number],
    default: 120,
  },
  showAction: {
    type: Boolean,
    default: false,
  },
  actionText: {
    type: String,
    default: '去看看',
  },
})

const emit = defineEmits(['action'])

function onAction() {
  emit('action')
}
</script>

<style lang="scss" scoped>
.page-empty {
  padding: 80rpx 32rpx;

  &__action {
    margin-top: 24rpx;
    min-width: 200rpx;
  }
}
</style>
