<template>
  <view class="page-wrap page-pad">
    <PageLoading v-if="loading" />
    <PageEmpty v-else-if="!detail.id" text="点餐记录不存在" />
    <view v-else>
      <view class="card block">
        <view class="flex-between">
          <text class="block__title">{{ foodNameOf(detail) }}</text>
          <u-tag :text="statusText" size="mini" :type="statusType" plain />
        </view>
        <text class="text-tips mt-12">{{ detail.meal_type }} · {{ detail.order_date }}</text>
        <text class="text-tips mt-12">规格 {{ formatSelectSpec(detail.select_spec) }}</text>
        <text class="text-tips mt-12">点餐人 {{ orderUserName }}</text>
        <text class="text-tips mt-12">烹饪 {{ cookName }}</text>
      </view>

      <view v-if="Number(detail.status) === 1" class="mt-24">
        <u-button type="primary" text="开始制作" @click="changeStatus(2)" />
        <u-button class="mt-24" type="error" plain text="取消点餐" @click="changeStatus(4)" />
      </view>
      <view v-else-if="Number(detail.status) === 2" class="mt-24">
        <u-button type="primary" text="标记完成" @click="changeStatus(3)" />
      </view>
    </view>
  </view>
</template>

<script setup>
import { computed, ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { getOrderDetail, updateOrderStatus } from '@/api/order'
import { ORDER_STATUS_MAP } from '@/config/constants'
import { foodNameOf, formatSelectSpec } from '@/utils/biz'

const loading = ref(false)
const detail = ref({})
const orderId = ref('')

const statusText = computed(() => ORDER_STATUS_MAP[detail.value.status]?.text || '未知')
const statusType = computed(() => ORDER_STATUS_MAP[detail.value.status]?.type || 'info')
const orderUserName = computed(
  () => detail.value.order_nickname || detail.value.order_user?.nickname || `#${detail.value.order_uid || '-'}`
)
const cookName = computed(
  () => detail.value.cook_nickname || detail.value.cook?.nickname || (detail.value.cook_uid ? `#${detail.value.cook_uid}` : '未指派')
)

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

async function changeStatus(status) {
  await updateOrderStatus(orderId.value, status)
  uni.showToast({ title: '已更新', icon: 'success' })
  loadDetail()
}
</script>

<style lang="scss" scoped>
.block {
  padding: 28rpx;

  &__title {
    font-size: 32rpx;
    font-weight: 600;
  }
}
</style>
