<template>
  <view class="page-wrap page-pad">
    <view class="card block">
      <text class="block__title">确认订单</text>
      <text class="text-tips mt-12">将提交到当前家庭：{{ familyStore.familyName }}</text>
    </view>

    <view class="card block mt-24">
      <u-textarea v-model="remark" placeholder="给做饭的人留个言（选填）" maxlength="80" count />
    </view>

    <view class="mt-24">
      <u-button type="primary" text="提交订单" :loading="submitting" @click="handleSubmit" />
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { createOrder } from '@/api/order'
import { useFamilyStore } from '@/store/modules/family'
import { assertRequired } from '@/utils/validate'

const familyStore = useFamilyStore()
const remark = ref('')
const submitting = ref(false)

async function handleSubmit() {
  if (!assertRequired(familyStore.currentFamilyId, '请先加入家庭')) return
  submitting.value = true
  try {
    const data = await createOrder({
      familyId: familyStore.currentFamilyId,
      remark: remark.value,
    })
    uni.showToast({ title: '下单成功', icon: 'success' })
    setTimeout(() => {
      uni.redirectTo({ url: `/pages/order/detail?id=${data?.id || ''}` })
    }, 400)
  } finally {
    submitting.value = false
  }
}
</script>

<style lang="scss" scoped>
.block {
  padding: 28rpx;

  &__title {
    font-size: 32rpx;
    font-weight: 600;
  }
}
</style>
