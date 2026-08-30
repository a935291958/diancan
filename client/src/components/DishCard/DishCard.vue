<template>
  <view class="dish-card" @click="onClick">
    <image class="dish-card__cover" :src="cover" mode="aspectFill" />
    <view class="dish-card__body">
      <text class="dish-card__name text-ellipsis">{{ name }}</text>
      <text class="dish-card__desc text-ellipsis">{{ desc || '暂无简介' }}</text>
      <view class="flex-between">
        <text class="dish-card__price">{{ formatPrice(price) }}</text>
        <u-icon v-if="showAdd" name="plus-circle-fill" :color="$colorPrimary" size="22" @click.stop="onAdd" />
      </view>
    </view>
  </view>
</template>

<script setup>
import { formatPrice } from '@/utils/data'

defineProps({
  name: {
    type: String,
    default: '',
  },
  desc: {
    type: String,
    default: '',
  },
  price: {
    type: [Number, String],
    default: 0,
  },
  cover: {
    type: String,
    default: '/static/tabbar/menu-active.png',
  },
  showAdd: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['click', 'add'])
const $colorPrimary = '#FF6B35'

function onClick() {
  emit('click')
}

function onAdd() {
  emit('add')
}
</script>

<style lang="scss" scoped>
.dish-card {
  display: flex;
  background: $bg-white;
  border-radius: $radius-md;
  overflow: hidden;
  box-shadow: $shadow-card;

  &__cover {
    width: 180rpx;
    height: 180rpx;
    flex-shrink: 0;
    background: $color-primary-light;
  }

  &__body {
    flex: 1;
    min-width: 0;
    padding: 16rpx 20rpx;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  &__name {
    font-size: 30rpx;
    font-weight: 600;
    color: $text-main;
  }

  &__desc {
    font-size: 24rpx;
    color: $text-tips;
    margin-top: 8rpx;
  }

  &__price {
    font-size: 32rpx;
    font-weight: 600;
    color: $color-primary;
  }
}
</style>
