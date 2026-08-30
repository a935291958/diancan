<template>
  <view class="page-wrap page-pad">
    <text class="page-title">今日菜单</text>
    <PageLoading v-if="loading" />
    <PageEmpty
      v-else-if="!orderList.length"
      text="还没有今日点餐"
      show-action
      action-text="去点餐"
      @action="goMenu"
    />
    <OrderList v-else class="mt-24" :list="orderList" group-by-meal @click="goDetail" />
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onPullDownRefresh, onShow } from '@dcloudio/uni-app'
import { getOrderList, getTodayOrders } from '@/api/order'
import { useFamilyStore } from '@/store/modules/family'
import { unwrapList } from '@/utils/biz'
import { formatDay } from '@/utils/date'

const familyStore = useFamilyStore()
const loading = ref(false)
const orderList = ref([])

async function loadList() {
  loading.value = true
  try {
    const params = { family_id: familyStore.currentFamilyId, order_date: formatDay(new Date()) }
    let data
    try {
      data = await getTodayOrders(params, { loading: false })
    } catch (error) {
      data = await getOrderList(params, { loading: false, showError: false })
    }
    orderList.value = unwrapList(data)
  } catch (error) {
    orderList.value = []
  } finally {
    loading.value = false
    uni.stopPullDownRefresh()
  }
}

onShow(() => {
  loadList()
})

onPullDownRefresh(() => {
  loadList()
})

function goMenu() {
  uni.switchTab({ url: '/pages/menu/index' })
}

function goDetail(item) {
  uni.navigateTo({ url: `/pages/order/detail?id=${item.id}` })
}
</script>

<style lang="scss" scoped>
.page-title {
  display: block;
  font-size: 32rpx;
  font-weight: 700;
  margin-bottom: 16rpx;
}
</style>
