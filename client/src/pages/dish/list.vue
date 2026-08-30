<template>
  <view class="page-wrap page-pad">
    <view class="flex-between">
      <text class="page-title">菜品管理</text>
      <u-button type="primary" size="mini" text="新增" @click="goEdit()" />
    </view>

    <PageLoading v-if="loading" />
    <PageEmpty v-else-if="!dishList.length" text="还没有菜品" show-action action-text="去新增" @action="goEdit()" />
    <DishCard
      v-for="item in dishList"
      :key="item.id"
      class="mt-24"
      :name="item.name"
      :desc="item.description"
      :price="item.price"
      :cover="item.cover"
      :show-add="false"
      @click="goEdit(item.id)"
    />
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onPullDownRefresh, onShow } from '@dcloudio/uni-app'
import { getDishList } from '@/api/dish'
import { useFamilyStore } from '@/store/modules/family'

const familyStore = useFamilyStore()
const loading = ref(false)
const dishList = ref([])

async function loadList() {
  loading.value = true
  try {
    const data = await getDishList({ familyId: familyStore.currentFamilyId }, { loading: false })
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

function goEdit(id) {
  const query = id ? `?id=${id}` : ''
  uni.navigateTo({ url: `/pages/dish/edit${query}` })
}
</script>

<style lang="scss" scoped>
.page-title {
  font-size: 32rpx;
  font-weight: 700;
}
</style>
