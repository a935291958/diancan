<template>
  <view class="page-wrap">
    <view class="toolbar">
      <view class="date-nav">
        <text @click="shiftDate(-1)">前一天</text>
        <text class="date-nav__cur">{{ orderDate }}</text>
        <text @click="shiftDate(1)">后一天</text>
      </view>
      <view class="meal-tabs">
        <view
          v-for="item in mealTabs"
          :key="item.value"
          class="meal-tabs__item"
          :class="{ 'meal-tabs__item--on': mealType === item.value }"
          @click="mealType = item.value; loadList()"
        >
          {{ item.name }}
        </view>
      </view>
    </view>

    <view class="page-pad">
      <PageLoading v-if="loading" />
      <PageEmpty v-else-if="!orderList.length" text="当日暂无点餐" show-action action-text="去点餐" @action="goMenu" />
      <OrderList
        v-else
        :list="orderList"
        group-by-meal
        show-actions
        @click="goDetail"
        @assign="openCookSheet"
        @status="changeStatus"
      />
    </view>

    <u-action-sheet
      :show="cookSheetShow"
      :actions="cookActions"
      title="选择烹饪人员"
      cancel-text="取消"
      @select="onPickCook"
      @close="cookSheetShow = false"
    />
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onPullDownRefresh, onShow } from '@dcloudio/uni-app'
import { assignOrderCook, getOrderList, getTodayOrders, updateOrderStatus } from '@/api/order'
import { MEAL_TYPES } from '@/config/constants'
import { useFamilyStore } from '@/store/modules/family'
import { memberNameOf, memberUidOf, parseCookUids, unwrapList } from '@/utils/biz'
import { addDays, formatDay } from '@/utils/date'
import { ensureFamily } from '@/utils/family-guard'
import { useAuthGuard } from '@/utils/use-auth-guard'

useAuthGuard()

const familyStore = useFamilyStore()
const loading = ref(false)
const orderDate = ref(formatDay(new Date()))
const mealType = ref('')
const orderList = ref([])
const cookSheetShow = ref(false)
const currentOrder = ref(null)
const cookActions = ref([])

const mealTabs = [{ name: '全部', value: '' }, ...MEAL_TYPES]

function goDetail(item) {
  uni.navigateTo({ url: `/pages/order/detail?id=${item.id}` })
}

async function loadList() {
  if (!ensureFamily()) {
    loading.value = false
    uni.stopPullDownRefresh()
    return
  }
  loading.value = true
  try {
    const params = {
      family_id: familyStore.currentFamilyId,
      order_date: orderDate.value,
      meal_type: mealType.value,
    }
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

function shiftDate(days) {
  orderDate.value = addDays(orderDate.value, days)
  loadList()
}

function goMenu() {
  uni.switchTab({ url: '/pages/menu/index' })
}

async function changeStatus(item, status) {
  try {
    await updateOrderStatus(item.id, status)
    uni.showToast({ title: '已更新', icon: 'success' })
    loadList()
  } catch (error) {
    console.warn('[duty] 更新状态失败', error)
  }
}

async function openCookSheet(item) {
  currentOrder.value = item
  if (!familyStore.memberList.length) {
    await familyStore.fetchMembers()
  }
  const preferred = parseCookUids(item.cook_uids || item.food?.cook_uids)
  const members = [...familyStore.memberList]
  members.sort((a, b) => {
    const aHit = preferred.includes(memberUidOf(a)) ? 0 : 1
    const bHit = preferred.includes(memberUidOf(b)) ? 0 : 1
    return aHit - bHit
  })
  cookActions.value = members.map((row) => ({
    name: `${memberNameOf(row)}${preferred.includes(memberUidOf(row)) ? '（擅长）' : ''}`,
    value: memberUidOf(row),
  }))
  if (!cookActions.value.length) {
    uni.showToast({ title: '暂无家庭成员可指派', icon: 'none' })
    return
  }
  cookSheetShow.value = true
}

async function onPickCook(e) {
  cookSheetShow.value = false
  const cookUid = e?.value ?? cookActions.value[e?.index]?.value ?? e
  if (!currentOrder.value || !cookUid) return
  try {
    await assignOrderCook(currentOrder.value.id, cookUid)
    uni.showToast({ title: '已指派', icon: 'success' })
    loadList()
  } catch (error) {
    console.warn('[duty] 指派失败', error)
  }
}
</script>

<style lang="scss" scoped>
.toolbar {
  background: $bg-white;
  padding: 16rpx 24rpx 8rpx;
}

.date-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 26rpx;
  color: $color-primary;
  margin-bottom: 16rpx;

  &__cur {
    font-size: 30rpx;
    font-weight: 700;
    color: $text-main;
  }
}

.meal-tabs {
  display: flex;
  gap: 12rpx;
  padding-bottom: 12rpx;

  &__item {
    flex: 1;
    text-align: center;
    padding: 10rpx 0;
    border-radius: 999rpx;
    background: $bg-page;
    font-size: 24rpx;

    &--on {
      background: $color-primary-light;
      color: $color-primary;
      font-weight: 600;
    }
  }
}
</style>
