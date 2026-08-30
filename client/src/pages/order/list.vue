<template>
  <view class="page-wrap">
    <view class="tabs">
      <u-tabs :list="tabList" :current="current" line-color="#FF6B35" @change="onTabChange" />
    </view>

    <view class="page-pad">
      <PageLoading v-if="loading" />
      <PageEmpty v-else-if="!orderList.length" text="暂无订单" />
      <view v-else>
        <view
          v-for="item in orderList"
          :key="item.id"
          class="order-card card"
          @click="goDetail(item.id)"
        >
          <view class="flex-between">
            <text class="order-card__no">{{ item.orderNo || item.id }}</text>
            <u-tag :text="statusText(item.status)" size="mini" :type="statusType(item.status)" plain />
          </view>
          <text class="order-card__time text-tips">{{ formatDateTime(item.createdAt) }}</text>
          <view class="flex-between mt-24">
            <text class="text-tips">共 {{ item.totalCount || 0 }} 件</text>
            <text class="text-primary">{{ formatPrice(item.totalAmount) }}</text>
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onPullDownRefresh, onShow } from '@dcloudio/uni-app'
import { getOrderList } from '@/api/order'
import { ORDER_STATUS_MAP } from '@/config/constants'
import { useFamilyStore } from '@/store/modules/family'
import { formatDateTime } from '@/utils/date'
import { formatPrice } from '@/utils/data'
import { useAuthGuard } from '@/utils/use-auth-guard'

useAuthGuard()

const familyStore = useFamilyStore()
const loading = ref(false)
const current = ref(0)
const orderList = ref([])
const tabList = [
  { name: '全部', status: '' },
  { name: '待确认', status: 'pending' },
  { name: '制作中', status: 'cooking' },
  { name: '已完成', status: 'done' },
]

async function loadOrders() {
  loading.value = true
  try {
    const status = tabList[current.value].status
    const data = await getOrderList(
      { familyId: familyStore.currentFamilyId, status },
      { loading: false }
    )
    orderList.value = Array.isArray(data) ? data : data?.list || []
  } catch (error) {
    orderList.value = []
  } finally {
    loading.value = false
    uni.stopPullDownRefresh()
  }
}

onShow(() => {
  loadOrders()
})

onPullDownRefresh(() => {
  loadOrders()
})

function onTabChange({ index }) {
  current.value = index
  loadOrders()
}

function statusText(status) {
  return ORDER_STATUS_MAP[status]?.text || '未知'
}

function statusType(status) {
  return ORDER_STATUS_MAP[status]?.type || 'info'
}

function goDetail(id) {
  uni.navigateTo({ url: `/pages/order/detail?id=${id}` })
}
</script>

<style lang="scss" scoped>
.tabs {
  background: $bg-white;
}

.order-card {
  padding: 28rpx;
  margin-bottom: 20rpx;

  &__no {
    font-weight: 600;
  }

  &__time {
    display: block;
    margin-top: 8rpx;
    font-size: 24rpx;
  }
}
</style>
