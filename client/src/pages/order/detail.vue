<template>
  <view class="page-wrap page-pad">
    <PageLoading v-if="loading" />
    <PageEmpty v-else-if="!detail.id" text="订单不存在" />
    <view v-else>
      <view class="card block">
        <view class="flex-between">
          <text class="block__title">{{ detail.orderNo || detail.id }}</text>
          <u-tag :text="statusText" size="mini" type="primary" plain />
        </view>
        <text class="text-tips mt-12">下单时间 {{ formatDateTime(detail.createdAt) }}</text>
      </view>

      <view class="card block mt-24">
        <text class="block__title">菜品明细</text>
        <view v-for="item in detail.items || []" :key="item.id" class="flex-between mt-24">
          <text>{{ item.name }} × {{ item.count }}</text>
          <text class="text-primary">{{ formatPrice(item.price) }}</text>
        </view>
      </view>

      <view v-if="detail.status === 'pending'" class="mt-24">
        <u-button type="primary" text="确认接单" @click="handleConfirm" />
        <u-button class="mt-24" type="error" plain text="取消订单" @click="handleCancel" />
      </view>
    </view>
  </view>
</template>

<script setup>
import { computed, ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { cancelOrder, confirmOrder, getOrderDetail } from '@/api/order'
import { ORDER_STATUS_MAP } from '@/config/constants'
import { formatDateTime } from '@/utils/date'
import { formatPrice } from '@/utils/data'

const loading = ref(false)
const detail = ref({})
const orderId = ref('')

const statusText = computed(() => ORDER_STATUS_MAP[detail.value.status]?.text || '未知')

async function loadDetail() {
  if (!orderId.value) return
  loading.value = true
  try {
    detail.value = (await getOrderDetail(orderId.value, { loading: false })) || {}
  } catch (error) {
    detail.value = {}
  } finally {
    loading.value = false
  }
}

onLoad((query) => {
  orderId.value = query?.id || ''
  loadDetail()
})

async function handleConfirm() {
  await confirmOrder(orderId.value)
  uni.showToast({ title: '已确认', icon: 'success' })
  loadDetail()
}

async function handleCancel() {
  await cancelOrder(orderId.value)
  uni.showToast({ title: '已取消', icon: 'none' })
  loadDetail()
}
</script>

<style lang="scss" scoped>
.block {
  padding: 28rpx;

  &__title {
    font-size: 30rpx;
    font-weight: 600;
  }
}
</style>
