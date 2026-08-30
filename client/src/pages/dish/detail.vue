<template>
  <view class="page-wrap">
    <image class="cover" :src="detail.cover" mode="aspectFill" />
    <view class="page-pad">
      <PageLoading v-if="loading" />
      <view v-else class="card info">
        <text class="info__name">{{ detail.name || '菜品详情' }}</text>
        <text class="info__price">{{ formatPrice(detail.price) }}</text>
        <text class="info__desc">{{ detail.description || '暂无简介' }}</text>
        <u-button class="mt-24" type="primary" text="加入购物车" @click="handleAdd" />
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { getDishDetail } from '@/api/dish'
import { addCartItem } from '@/api/menu'
import { formatPrice } from '@/utils/data'

const loading = ref(false)
const detail = ref({})
const dishId = ref('')

onLoad(async (query) => {
  dishId.value = query?.id || ''
  if (!dishId.value) return
  loading.value = true
  try {
    detail.value = (await getDishDetail(dishId.value, { loading: false })) || {}
  } catch (error) {
    detail.value = {}
  } finally {
    loading.value = false
  }
})

async function handleAdd() {
  await addCartItem({ dishId: dishId.value, count: 1 })
  uni.showToast({ title: '已加入购物车', icon: 'success' })
}
</script>

<style lang="scss" scoped>
.cover {
  width: 100%;
  height: 420rpx;
  background: $color-primary-light;
}

.info {
  padding: 32rpx;
  margin-top: -40rpx;
  position: relative;

  &__name {
    display: block;
    font-size: 40rpx;
    font-weight: 700;
  }

  &__price {
    display: block;
    margin-top: 12rpx;
    color: $color-primary;
    font-size: 36rpx;
    font-weight: 700;
  }

  &__desc {
    display: block;
    margin-top: 16rpx;
    color: $text-content;
    font-size: 26rpx;
  }
}
</style>
