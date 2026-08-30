<template>
  <view class="page-wrap">
    <view class="tabs">
      <u-tabs :list="tabList" :current="current" line-color="#FF6B35" @change="onTabChange" />
    </view>
    <view class="page-pad">
      <PageLoading v-if="loading" />
      <OrderList v-else :list="orderList" empty-text="暂无点餐记录" @click="goDetail" />
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onPullDownRefresh, onShow } from '@dcloudio/uni-app'
import { getOrderList } from '@/api/order'
import { useFamilyStore } from '@/store/modules/family'
import { unwrapList } from '@/utils/biz'
import { useAuthGuard } from '@/utils/use-auth-guard'

useAuthGuard()

const familyStore = useFamilyStore()
const loading = ref(false)
const current = ref(0)
const orderList = ref([])
const tabList = [
  { name: '全部', status: '' },
  { name: '待制作', status: 1 },
  { name: '制作中', status: 2 },
  { name: '已完成', status: 3 },
]

async function loadOrders() {
  loading.value = true
  try {
    const status = tabList[current.value].status
    const data = await getOrderList(
      {
        family_id: familyStore.currentFamilyId,
        status,
      },
      { loading: false }
    )
    orderList.value = unwrapList(data)
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

function goDetail(item) {
  uni.navigateTo({ url: `/pages/order/detail?id=${item.id}` })
}
</script>

<style lang="scss" scoped>
.tabs {
  background: $bg-white;
}
</style>
