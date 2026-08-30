<template>
  <view class="page-wrap page-pad">
    <view class="hero card">
      <view class="flex-between">
        <view>
          <text class="hero__hello">你好，{{ userStore.nickname }}</text>
          <text class="hero__date">{{ todayText }}</text>
        </view>
        <u-tag :text="familyStore.familyName" size="mini" type="warning" plain />
      </view>
    </view>

    <view class="section">
      <view class="flex-between">
        <text class="section__title">今日菜单</text>
        <text class="text-primary" @click="goToday">查看全部</text>
      </view>

      <PageLoading v-if="loading" />
      <PageEmpty
        v-else-if="!dishList.length"
        text="今天还没有安排菜单"
        show-action
        action-text="去点餐"
        @action="goMenu"
      />
      <view v-else class="dish-list">
        <DishCard
          v-for="item in dishList"
          :key="item.id"
          class="mt-24"
          :name="item.name"
          :desc="item.description"
          :price="item.price"
          :cover="item.cover"
          @click="goDishDetail(item.id)"
          @add="handleAddCart(item)"
        />
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onPullDownRefresh, onShow } from '@dcloudio/uni-app'
import { addCartItem, getTodayMenu } from '@/api/menu'
import { useUserStore } from '@/store/modules/user'
import { useFamilyStore } from '@/store/modules/family'
import { getTodayText } from '@/utils/date'
import { useAuthGuard } from '@/utils/use-auth-guard'

useAuthGuard()

const userStore = useUserStore()
const familyStore = useFamilyStore()
const loading = ref(false)
const dishList = ref([])
const todayText = getTodayText()

async function loadTodayMenu() {
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
  loadTodayMenu()
})

onPullDownRefresh(() => {
  loadTodayMenu()
})

function goToday() {
  uni.navigateTo({ url: '/pages/today/index' })
}

function goMenu() {
  uni.switchTab({ url: '/pages/menu/index' })
}

function goDishDetail(id) {
  uni.navigateTo({ url: `/pages/dish/detail?id=${id}` })
}

async function handleAddCart(item) {
  try {
    await addCartItem({ dishId: item.id, count: 1 })
    uni.showToast({ title: '已加入购物车', icon: 'success' })
  } catch (error) {
    // 错误已由请求层提示
  }
}
</script>

<style lang="scss" scoped>
.hero {
  padding: 32rpx;
  background: linear-gradient(135deg, #ff6b35 0%, #ff8f66 100%);

  &__hello {
    display: block;
    font-size: 36rpx;
    font-weight: 700;
    color: #fff;
  }

  &__date {
    display: block;
    margin-top: 8rpx;
    font-size: 24rpx;
    color: rgba(255, 255, 255, 0.86);
  }
}

.section {
  margin-top: 32rpx;

  &__title {
    font-size: 32rpx;
    font-weight: 600;
  }
}

.dish-list {
  margin-top: 8rpx;
}
</style>
