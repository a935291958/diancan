<template>
  <view class="page-wrap page-pad">
    <text class="page-title">今日菜单</text>
    <PageLoading v-if="loading" />
    <PageEmpty
      v-else-if="!dishList.length"
      text="还没有安排今日菜单"
      show-action
      action-text="去点餐"
      @action="goMenu"
    />
    <DishCard
      v-for="item in dishList"
      :key="item.id"
      class="mt-24"
      :name="item.name"
      :desc="item.description"
      :price="item.price"
      :cover="item.cover"
      @click="goDetail(item.id)"
    />
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onPullDownRefresh, onShow } from '@dcloudio/uni-app'
import { getTodayMenu } from '@/api/menu'
import { useFamilyStore } from '@/store/modules/family'

const familyStore = useFamilyStore()
const loading = ref(false)
const dishList = ref([])

async function loadList() {
  loading.value = true
  try {
    const data = await getTodayMenu({ familyId: familyStore.currentFamilyId }, { loading: false })
    dishList.value = Array.isArray(data) ? data : data?.list || []
  } catch (error) {
    dishList.value = []
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

function goDetail(id) {
  uni.navigateTo({ url: `/pages/dish/detail?id=${id}` })
}
</script>

<style lang="scss" scoped>
.page-title {
  display: block;
  font-size: 32rpx;
  font-weight: 700;
}
</style>
