<template>
  <view class="page-wrap">
    <image class="cover" :src="detail.food_img || '/static/tabbar/menu-active.png'" mode="aspectFill" />
    <view class="page-pad">
      <PageLoading v-if="loading" />
      <view v-else class="card info">
        <text class="info__name">{{ detail.food_name || '菜品详情' }}</text>
        <text class="info__cate">{{ detail.category || '未分类' }}</text>
        <view v-if="specText" class="info__spec">{{ specText }}</view>
        <u-button class="mt-24" type="primary" text="去点这道菜" @click="goOrder" />
        <u-button class="mt-24" type="primary" plain text="编辑菜品" @click="goEdit" />
      </view>
    </view>
  </view>
</template>

<script setup>
import { computed, ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { getFoodDetail } from '@/api/food'
import { groupSpecs, stringifySelectSpec } from '@/utils/biz'
import { useOrderDraftStore } from '@/store/modules/order-draft'

const loading = ref(false)
const detail = ref({})
const foodId = ref('')
const draftStore = useOrderDraftStore()

const specText = computed(() => {
  const groups = groupSpecs(detail.value.specs || [])
  return groups.map((item) => `${item.spec_name}：${item.values.join(' / ')}`).join('；')
})

onLoad(async (query) => {
  foodId.value = query?.id || ''
  if (!foodId.value) return
  loading.value = true
  try {
    detail.value = (await getFoodDetail(foodId.value, { loading: false })) || {}
  } catch (error) {
    detail.value = {}
  } finally {
    loading.value = false
  }
})

function goEdit() {
  uni.navigateTo({ url: `/pages/dish/edit?id=${foodId.value}` })
}

function goOrder() {
  const groups = groupSpecs(detail.value.specs || [])
  const selected = {}
  groups.forEach((group) => {
    selected[group.spec_name] = group.values[0] || ''
  })
  draftStore.upsertItem({
    food_id: detail.value.id,
    food_name: detail.value.food_name,
    food_img: detail.value.food_img,
    specs: detail.value.specs || [],
    select_spec: stringifySelectSpec(selected),
    cook_uid: 0,
  })
  uni.switchTab({ url: '/pages/menu/index' })
}
</script>

<style lang="scss" scoped>
.cover {
  width: 100%;
  height: 420rpx;
  background: $color-primary-light;
}

.info {
  padding: 32rpx;
  margin-top: -40rpx;
  position: relative;

  &__name {
    display: block;
    font-size: 40rpx;
    font-weight: 700;
  }

  &__cate {
    display: block;
    margin-top: 8rpx;
    color: $text-tips;
  }

  &__spec {
    margin-top: 16rpx;
    font-size: 26rpx;
    color: $text-content;
  }
}
</style>
