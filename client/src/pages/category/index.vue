<template>
  <view class="page-wrap page-pad">
    <text class="page-title">分类管理</text>
    <PageLoading v-if="loading" />
    <PageEmpty v-else-if="!list.length" text="暂无分类" />
    <view v-else class="card">
      <view v-for="item in list" :key="item.id" class="cate-row">
        <text>{{ item.name }}</text>
      </view>
    </view>
    <view class="mt-24">
      <u-button type="primary" text="新增分类" @click="handleCreate" />
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { getCategoryList, saveCategory } from '@/api/dish'
import { useFamilyStore } from '@/store/modules/family'

const familyStore = useFamilyStore()
const loading = ref(false)
const list = ref([])

async function loadList() {
  loading.value = true
  try {
    const data = await getCategoryList({ familyId: familyStore.currentFamilyId }, { loading: false })
    list.value = Array.isArray(data) ? data : data?.list || []
  } catch (error) {
    list.value = []
  } finally {
    loading.value = false
  }
}

onShow(() => {
  loadList()
})

function handleCreate() {
  uni.showModal({
    title: '新增分类',
    editable: true,
    placeholderText: '例如：家常菜',
    success: async (res) => {
      if (!res.confirm || !res.content) return
      await saveCategory({ name: res.content, familyId: familyStore.currentFamilyId })
      uni.showToast({ title: '已新增', icon: 'success' })
      loadList()
    },
  })
}
</script>

<style lang="scss" scoped>
.page-title {
  display: block;
  margin-bottom: 24rpx;
  font-size: 32rpx;
  font-weight: 700;
}

.cate-row {
  padding: 28rpx 24rpx;
  border-bottom: 1rpx solid $border-color;

  &:last-child {
    border-bottom: none;
  }
}
</style>
