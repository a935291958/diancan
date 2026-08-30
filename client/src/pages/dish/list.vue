<template>
  <view class="page-wrap page-pad">
    <view class="flex-between">
      <u-search
        v-model="keyword"
        placeholder="搜索菜品名称"
        :show-action="false"
        @search="loadList"
        @clear="onClear"
      />
      <u-button type="primary" size="mini" text="新增" @click="goEdit()" />
    </view>

    <scroll-view class="cate-scroll" scroll-x>
      <view
        v-for="item in categoryTabs"
        :key="item"
        class="cate-chip"
        :class="{ 'cate-chip--on': item === category }"
        @click="selectCategory(item)"
      >
        {{ item }}
      </view>
    </scroll-view>

    <PageLoading v-if="loading" />
    <PageEmpty
      v-else-if="!foodList.length"
      text="还没有菜品"
      show-action
      action-text="去新增"
      @action="goEdit()"
    />
    <FoodCard
      v-for="item in foodList"
      :key="item.id"
      class="mt-24"
      :food="item"
      :members="familyStore.memberList"
      show-manage
      @click="goDetail(item.id)"
      @edit="goEdit(item.id)"
      @delete="handleDelete"
    />
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onPullDownRefresh, onShow } from '@dcloudio/uni-app'
import { deleteFood, getFoodList } from '@/api/food'
import { FOOD_CATEGORIES } from '@/config/constants'
import { useFamilyStore } from '@/store/modules/family'
import { confirmDialog, unwrapList } from '@/utils/biz'
import { ensureFamily } from '@/utils/family-guard'
import { useAuthGuard } from '@/utils/use-auth-guard'

useAuthGuard()

const familyStore = useFamilyStore()
const keyword = ref('')
const category = ref('全部')
const loading = ref(false)
const foodList = ref([])
const categoryTabs = ['全部', ...FOOD_CATEGORIES]

async function loadList() {
  if (!ensureFamily()) {
    loading.value = false
    uni.stopPullDownRefresh()
    return
  }
  loading.value = true
  try {
    const data = await getFoodList(
      {
        family_id: familyStore.currentFamilyId,
        category: category.value === '全部' ? '' : category.value,
        keyword: keyword.value,
      },
      { loading: false }
    )
    foodList.value = unwrapList(data)
  } catch (error) {
    foodList.value = []
  } finally {
    loading.value = false
    uni.stopPullDownRefresh()
  }
}

onShow(() => {
  familyStore.fetchMembers()
  loadList()
})

onPullDownRefresh(() => {
  loadList()
})

function onClear() {
  keyword.value = ''
  loadList()
}

function selectCategory(name) {
  category.value = name
  loadList()
}

function goEdit(id) {
  if (!ensureFamily()) return
  uni.navigateTo({ url: id ? `/pages/dish/edit?id=${id}` : '/pages/dish/edit' })
}

function goDetail(id) {
  uni.navigateTo({ url: `/pages/dish/detail?id=${id}` })
}

async function handleDelete(item) {
  const ok = await confirmDialog(`确认删除「${item.food_name}」？`)
  if (!ok) return
  try {
    await deleteFood(item.id)
    uni.showToast({ title: '已删除', icon: 'success' })
    loadList()
  } catch (error) {
    console.warn('[food] 删除失败', error)
  }
}
</script>

<style lang="scss" scoped>
.flex-between {
  gap: 16rpx;
}

.cate-scroll {
  white-space: nowrap;
  margin: 20rpx 0;
}

.cate-chip {
  display: inline-block;
  margin-right: 12rpx;
  padding: 8rpx 24rpx;
  border-radius: 999rpx;
  background: $bg-white;
  font-size: 24rpx;
  color: $text-content;

  &--on {
    background: $color-primary-light;
    color: $color-primary;
    font-weight: 600;
  }
}
</style>
