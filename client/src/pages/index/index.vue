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

    <view class="quick">
      <view class="quick__item" @click="goMenu">
        <text class="quick__num">点</text>
        <text>去点餐</text>
      </view>
      <view class="quick__item" @click="goDuty">
        <text class="quick__num">{{ todayCount }}</text>
        <text>今日点餐</text>
      </view>
      <view class="quick__item" @click="goDish">
        <text class="quick__num">菜</text>
        <text>菜品管理</text>
      </view>
    </view>

    <view class="section">
      <view class="flex-between">
        <text class="section__title">今日清单</text>
        <text class="text-primary" @click="goDuty">查看分工</text>
      </view>
      <PageLoading v-if="loading" />
      <PageEmpty
        v-else-if="!orderList.length"
        text="今天还没有点餐"
        show-action
        action-text="去点餐"
        @action="goMenu"
      />
      <OrderList v-else :list="orderList" @click="goDetail" />
    </view>
  </view>
</template>

<script setup>
import { computed, ref } from 'vue'
import { onPullDownRefresh, onShow } from '@dcloudio/uni-app'
import { getOrderList, getTodayOrders } from '@/api/order'
import { useUserStore } from '@/store/modules/user'
import { useFamilyStore } from '@/store/modules/family'
import { unwrapList } from '@/utils/biz'
import { formatDay, getTodayText } from '@/utils/date'
import { useAuthGuard } from '@/utils/use-auth-guard'

useAuthGuard()

const userStore = useUserStore()
const familyStore = useFamilyStore()
const loading = ref(false)
const orderList = ref([])
const todayText = getTodayText()

const todayCount = computed(() => orderList.value.length)

async function loadToday() {
  loading.value = true
  try {
    const params = {
      family_id: familyStore.currentFamilyId,
      order_date: formatDay(new Date()),
    }
    let data
    try {
      data = await getTodayOrders(params, { loading: false, showError: false })
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
  loadToday()
})

onPullDownRefresh(() => {
  loadToday()
})

function goMenu() {
  uni.switchTab({ url: '/pages/menu/index' })
}

function goDuty() {
  uni.switchTab({ url: '/pages/duty/index' })
}

function goDish() {
  uni.navigateTo({ url: '/pages/dish/list' })
}

function goDetail(item) {
  uni.navigateTo({ url: `/pages/order/detail?id=${item.id}` })
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

.quick {
  display: flex;
  gap: 16rpx;
  margin-top: 24rpx;

  &__item {
    flex: 1;
    background: $bg-white;
    border-radius: $radius-md;
    padding: 24rpx 12rpx;
    text-align: center;
    font-size: 24rpx;
    color: $text-content;
    box-shadow: $shadow-card;
  }

  &__num {
    display: block;
    margin-bottom: 8rpx;
    font-size: 36rpx;
    font-weight: 700;
    color: $color-primary;
  }
}

.section {
  margin-top: 32rpx;

  &__title {
    font-size: 32rpx;
    font-weight: 600;
  }
}
</style>
