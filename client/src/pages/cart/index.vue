<template>
  <view class="page-wrap page-pad">
    <PageLoading v-if="loading" />
    <PageEmpty
      v-else-if="!cartList.length"
      text="购物车还是空的"
      show-action
      action-text="去点餐"
      @action="goMenu"
    />
    <view v-else>
      <view v-for="item in cartList" :key="item.id" class="cart-item card">
        <image class="cart-item__cover" :src="item.cover" mode="aspectFill" />
        <view class="flex-1">
          <text class="cart-item__name">{{ item.name }}</text>
          <view class="flex-between mt-12">
            <text class="text-primary">{{ formatPrice(item.price) }}</text>
            <u-number-box v-model="item.count" :min="0" @change="onCountChange(item)" />
          </view>
        </view>
      </view>
    </view>

    <view v-if="cartList.length" class="cart-bar safe-bottom">
      <text class="cart-bar__total">合计 {{ formatPrice(totalPrice) }}</text>
      <u-button type="primary" size="small" shape="circle" text="去下单" @click="goConfirm" />
    </view>
  </view>
</template>

<script setup>
import { computed, ref } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { getCartList, updateCartItem } from '@/api/menu'
import { formatPrice, toNumber } from '@/utils/data'
import { useFamilyStore } from '@/store/modules/family'

const familyStore = useFamilyStore()
const loading = ref(false)
const cartList = ref([])

const totalPrice = computed(() =>
  cartList.value.reduce((sum, item) => sum + toNumber(item.price) * toNumber(item.count), 0)
)

async function loadCart() {
  loading.value = true
  try {
    const data = await getCartList({ familyId: familyStore.currentFamilyId }, { loading: false })
    cartList.value = Array.isArray(data) ? data : data?.list || []
  } catch (error) {
    cartList.value = []
  } finally {
    loading.value = false
  }
}

onShow(() => {
  loadCart()
})

function goMenu() {
  uni.switchTab({ url: '/pages/menu/index' })
}

function goConfirm() {
  uni.navigateTo({ url: '/pages/order/confirm' })
}

async function onCountChange(item) {
  try {
    await updateCartItem({ id: item.id, count: item.count }, { loading: false })
    if (item.count <= 0) {
      cartList.value = cartList.value.filter((row) => row.id !== item.id)
    }
  } catch (error) {
    loadCart()
  }
}
</script>

<style lang="scss" scoped>
.cart-item {
  display: flex;
  gap: 20rpx;
  padding: 20rpx;
  margin-bottom: 20rpx;

  &__cover {
    width: 140rpx;
    height: 140rpx;
    border-radius: $radius-sm;
    background: $color-primary-light;
  }

  &__name {
    font-size: 30rpx;
    font-weight: 600;
  }
}

.cart-bar {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16rpx 24rpx;
  background: $bg-white;
  box-shadow: 0 -4rpx 16rpx rgba(0, 0, 0, 0.04);

  &__total {
    font-size: 32rpx;
    font-weight: 700;
    color: $color-primary;
  }
}
</style>
