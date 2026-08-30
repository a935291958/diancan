<template>
  <view class="menu-page">
    <view class="menu-page__search">
      <u-search
        v-model="keyword"
        placeholder="搜索家常菜"
        :show-action="false"
        @search="loadDishList"
        @clear="loadDishList"
      />
    </view>

    <view class="menu-page__body">
      <scroll-view class="menu-page__cate" scroll-y>
        <view
          v-for="item in categoryList"
          :key="item.id"
          class="cate-item"
          :class="{ 'cate-item--active': item.id === activeCategoryId }"
          @click="selectCategory(item.id)"
        >
          {{ item.name }}
        </view>
      </scroll-view>

      <scroll-view class="menu-page__list" scroll-y>
        <PageLoading v-if="loading" />
        <PageEmpty v-else-if="!dishList.length" text="暂无菜品" />
        <view v-else>
          <DishCard
            v-for="item in dishList"
            :key="item.id"
            class="mt-24"
            :name="item.name"
            :desc="item.description"
            :price="item.price"
            :cover="item.cover"
            @click="goDetail(item.id)"
            @add="handleAddCart(item)"
          />
        </view>
        <view class="safe-spacer" />
      </scroll-view>
    </view>

    <view class="menu-page__cart safe-bottom" @click="goCart">
      <u-icon name="shopping-cart" color="#ffffff" size="22" />
      <text>购物车</text>
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onPullDownRefresh, onShow } from '@dcloudio/uni-app'
import { getCategoryList, getDishList } from '@/api/dish'
import { addCartItem } from '@/api/menu'
import { useFamilyStore } from '@/store/modules/family'
import { useAuthGuard } from '@/utils/use-auth-guard'

useAuthGuard()

const familyStore = useFamilyStore()
const keyword = ref('')
const loading = ref(false)
const categoryList = ref([{ id: '', name: '全部' }])
const activeCategoryId = ref('')
const dishList = ref([])

async function loadCategories() {
  try {
    const data = await getCategoryList({ familyId: familyStore.currentFamilyId }, { loading: false, showError: false })
    const list = Array.isArray(data) ? data : data?.list || []
    categoryList.value = [{ id: '', name: '全部' }, ...list]
  } catch (error) {
    categoryList.value = [{ id: '', name: '全部' }]
  }
}

async function loadDishList() {
  loading.value = true
  try {
    const data = await getDishList(
      {
        familyId: familyStore.currentFamilyId,
        categoryId: activeCategoryId.value,
        keyword: keyword.value,
      },
      { loading: false }
    )
    dishList.value = Array.isArray(data) ? data : data?.list || []
  } catch (error) {
    dishList.value = []
  } finally {
    loading.value = false
    uni.stopPullDownRefresh()
  }
}

onShow(async () => {
  await loadCategories()
  await loadDishList()
})

onPullDownRefresh(() => {
  loadDishList()
})

function selectCategory(id) {
  activeCategoryId.value = id
  loadDishList()
}

function goDetail(id) {
  uni.navigateTo({ url: `/pages/dish/detail?id=${id}` })
}

function goCart() {
  uni.navigateTo({ url: '/pages/cart/index' })
}

async function handleAddCart(item) {
  try {
    await addCartItem({ dishId: item.id, count: 1 })
    uni.showToast({ title: '已加入购物车', icon: 'success' })
  } catch (error) {
    // 请求层已提示
  }
}
</script>

<style lang="scss" scoped>
.menu-page {
  height: 100vh;
  display: flex;
  flex-direction: column;
  background: $bg-page;

  &__search {
    padding: 16rpx 24rpx;
    background: $bg-white;
  }

  &__body {
    flex: 1;
    min-height: 0;
    display: flex;
  }

  &__cate {
    width: 180rpx;
    background: #f3f4f6;
  }

  &__list {
    flex: 1;
    padding: 0 20rpx 140rpx;
  }

  &__cart {
    position: fixed;
    right: 32rpx;
    bottom: calc(40rpx + #{$safe-bottom});
    display: flex;
    align-items: center;
    gap: 8rpx;
    padding: 18rpx 28rpx;
    border-radius: 999rpx;
    background: $color-primary;
    color: #fff;
    font-size: 26rpx;
    box-shadow: 0 8rpx 24rpx rgba(255, 107, 53, 0.35);
  }
}

.cate-item {
  padding: 28rpx 16rpx;
  text-align: center;
  font-size: 26rpx;
  color: $text-content;

  &--active {
    background: $bg-white;
    color: $color-primary;
    font-weight: 600;
  }
}

.safe-spacer {
  height: 40rpx;
}
</style>
