<template>
  <view class="page-wrap page-pad">
    <view class="card block">
      <text class="block__title">确认点餐</text>
      <text class="text-tips mt-12">
        {{ familyStore.familyName }} · {{ mealLabel }} · {{ draftStore.order_date }}
      </text>
    </view>

    <PageEmpty v-if="!draftStore.hasItems" text="还没有选择菜品" show-action action-text="去点餐" @action="goMenu" />
    <view v-else>
      <view v-for="item in draftStore.items" :key="item.food_id" class="card food-row">
        <image class="food-row__img" :src="item.food_img || '/static/tabbar/menu-active.png'" mode="aspectFill" />
        <view class="flex-1">
          <text class="food-row__name">{{ item.food_name }}</text>
          <text class="text-tips">{{ formatSelectSpec(item.select_spec) }}</text>
        </view>
        <text class="food-row__del" @click="draftStore.removeItem(item.food_id)">移除</text>
      </view>
    </view>

    <view class="mt-24">
      <u-button type="primary" text="提交点餐" :loading="submitting" :disabled="!draftStore.hasItems" @click="handleSubmit" />
    </view>
  </view>
</template>

<script setup>
import { computed, ref } from 'vue'
import { createOrders } from '@/api/order'
import { MEAL_TYPES } from '@/config/constants'
import { useFamilyStore } from '@/store/modules/family'
import { useUserStore } from '@/store/modules/user'
import { useOrderDraftStore } from '@/store/modules/order-draft'
import { formatSelectSpec } from '@/utils/biz'
import { ensureFamily } from '@/utils/family-guard'

const familyStore = useFamilyStore()
const userStore = useUserStore()
const draftStore = useOrderDraftStore()
const submitting = ref(false)

const mealLabel = computed(
  () => MEAL_TYPES.find((item) => item.value === draftStore.meal_type)?.name || draftStore.meal_type
)

function goMenu() {
  uni.switchTab({ url: '/pages/menu/index' })
}

async function handleSubmit() {
  if (!ensureFamily()) return
  if (!draftStore.hasItems) {
    uni.showToast({ title: '请先选择菜品', icon: 'none' })
    return
  }
  if (!draftStore.meal_type) {
    uni.showToast({ title: '请选择用餐时段', icon: 'none' })
    return
  }
  submitting.value = true
  try {
    await createOrders({
      family_id: familyStore.currentFamilyId,
      order_uid: userStore.userInfo.id,
      meal_type: draftStore.meal_type,
      order_date: draftStore.order_date,
      items: draftStore.items.map((item) => ({
        food_id: item.food_id,
        select_spec: item.select_spec,
        cook_uid: item.cook_uid || 0,
        status: 1,
      })),
    })
    draftStore.clear()
    uni.showToast({ title: '点餐成功', icon: 'success' })
    setTimeout(() => {
      uni.switchTab({ url: '/pages/duty/index' })
    }, 400)
  } catch (error) {
    console.warn('[order] 提交失败', error)
  } finally {
    submitting.value = false
  }
}
</script>

<style lang="scss" scoped>
.block {
  padding: 28rpx;
  margin-bottom: 20rpx;

  &__title {
    font-size: 32rpx;
    font-weight: 600;
  }
}

.food-row {
  display: flex;
  align-items: center;
  gap: 16rpx;
  padding: 20rpx;
  margin-bottom: 16rpx;

  &__img {
    width: 96rpx;
    height: 96rpx;
    border-radius: $radius-sm;
    background: $color-primary-light;
  }

  &__name {
    display: block;
    font-weight: 600;
  }

  &__del {
    font-size: 24rpx;
    color: $color-danger;
  }
}
</style>
