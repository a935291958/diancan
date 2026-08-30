<template>
  <view class="menu-page">
    <view class="menu-page__top">
      <view class="meal-tabs">
        <view
          v-for="item in MEAL_TYPES"
          :key="item.value"
          class="meal-tabs__item"
          :class="{ 'meal-tabs__item--on': draftStore.meal_type === item.value }"
          @click="draftStore.setMealType(item.value)"
        >
          {{ item.name }}
        </view>
      </view>
      <u-search
        v-model="keyword"
        placeholder="搜索家常菜"
        :show-action="false"
        @search="loadFoodList"
        @clear="loadFoodList"
      />
    </view>

    <view class="menu-page__body">
      <scroll-view class="menu-page__cate" scroll-y>
        <view
          v-for="item in categoryList"
          :key="item"
          class="cate-item"
          :class="{ 'cate-item--active': item === activeCategory }"
          @click="selectCategory(item)"
        >
          {{ item }}
        </view>
      </scroll-view>

      <scroll-view class="menu-page__list" scroll-y>
        <PageLoading v-if="loading" />
        <PageEmpty v-else-if="!foodList.length" text="暂无菜品，先去添加" show-action action-text="去添加" @action="goDishManage" />
        <view v-else>
          <FoodCard
            v-for="item in foodList"
            :key="item.id"
            class="mt-24"
            :food="item"
            :members="familyStore.memberList"
            :hint="specHint(item)"
            show-check
            :checked="draftStore.isSelected(item.id)"
            @click="openSpec(item)"
            @select="openSpec(item)"
          />
        </view>
        <view class="safe-spacer" />
      </scroll-view>
    </view>

    <view class="menu-page__bar safe-bottom">
      <text>已选 {{ draftStore.selectedCount }} 道 · {{ mealLabel }}</text>
      <u-button type="primary" size="small" shape="circle" text="去提交" :disabled="!draftStore.hasItems" @click="goConfirm" />
    </view>

    <SpecPicker
      :show="pickerShow"
      :food-name="currentFood.food_name"
      :specs="currentFood.specs || []"
      :value="currentSelected"
      @update:show="pickerShow = $event"
      @confirm="onSpecConfirm"
    />
  </view>
</template>

<script setup>
import { computed, ref } from 'vue'
import { onPullDownRefresh, onShow } from '@dcloudio/uni-app'
import { getFoodDetail, getFoodList } from '@/api/food'
import { FOOD_CATEGORIES, MEAL_TYPES } from '@/config/constants'
import { useFamilyStore } from '@/store/modules/family'
import { useOrderDraftStore } from '@/store/modules/order-draft'
import { formatSelectSpec, groupSpecs, parseSelectSpec, stringifySelectSpec, unwrapList } from '@/utils/biz'
import { ensureFamily } from '@/utils/family-guard'
import { formatDay, getDefaultMealType } from '@/utils/date'
import { useAuthGuard } from '@/utils/use-auth-guard'

useAuthGuard()

const familyStore = useFamilyStore()
const draftStore = useOrderDraftStore()
const keyword = ref('')
const loading = ref(false)
const foodList = ref([])
const activeCategory = ref('全部')
const pickerShow = ref(false)
const currentFood = ref({})
const currentSelected = ref({})

const categoryList = computed(() => {
  const fromData = [...new Set(foodList.value.map((item) => item.category).filter(Boolean))]
  const merged = ['全部', ...FOOD_CATEGORIES, ...fromData]
  return [...new Set(merged)]
})

const mealLabel = computed(() => MEAL_TYPES.find((item) => item.value === draftStore.meal_type)?.name || '用餐')

function specHint(item) {
  const selected = draftStore.items.find((row) => Number(row.food_id) === Number(item.id))
  if (selected) return formatSelectSpec(selected.select_spec)
  const groups = groupSpecs(item.specs || [])
  if (!groups.length) return item.category || '默认规格'
  return groups.map((row) => row.spec_name).join(' / ')
}

async function loadFoodList() {
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
        category: activeCategory.value === '全部' ? '' : activeCategory.value,
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
  if (!draftStore.order_date) draftStore.setOrderDate(formatDay(new Date()))
  if (!draftStore.meal_type) draftStore.setMealType(getDefaultMealType())
  familyStore.fetchMembers()
  loadFoodList()
})

onPullDownRefresh(() => {
  loadFoodList()
})

function selectCategory(name) {
  activeCategory.value = name
  loadFoodList()
}

function goDishManage() {
  uni.navigateTo({ url: '/pages/dish/list' })
}

async function openSpec(food) {
  if (!ensureFamily()) return
  if (draftStore.isSelected(food.id)) {
    draftStore.removeItem(food.id)
    return
  }
  let detail = food
  if (!food.specs) {
    try {
      detail = (await getFoodDetail(food.id, { loading: true })) || food
    } catch (error) {
      detail = food
    }
  }
  currentFood.value = detail
  const exist = draftStore.items.find((row) => Number(row.food_id) === Number(food.id))
  currentSelected.value = parseSelectSpec(exist?.select_spec)
  const groups = groupSpecs(detail.specs || [])
  if (!groups.length) {
    onSpecConfirm({})
    return
  }
  pickerShow.value = true
}

function onSpecConfirm(selected) {
  const food = currentFood.value
  draftStore.upsertItem({
    food_id: food.id,
    food_name: food.food_name,
    food_img: food.food_img,
    specs: food.specs || [],
    select_spec: stringifySelectSpec(selected),
    cook_uid: 0,
  })
}

function goConfirm() {
  if (!draftStore.hasItems) {
    uni.showToast({ title: '请先勾选菜品', icon: 'none' })
    return
  }
  uni.navigateTo({ url: '/pages/order/confirm' })
}
</script>

<style lang="scss" scoped>
.menu-page {
  height: 100vh;
  display: flex;
  flex-direction: column;
  background: $bg-page;

  &__top {
    padding: 16rpx 24rpx;
    background: $bg-white;
  }

  &__body {
    flex: 1;
    min-height: 0;
    display: flex;
  }

  &__cate {
    width: 160rpx;
    background: #f3f4f6;
  }

  &__list {
    flex: 1;
    padding: 0 20rpx 160rpx;
  }

  &__bar {
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
  }
}

.meal-tabs {
  display: flex;
  gap: 12rpx;
  margin-bottom: 16rpx;

  &__item {
    flex: 1;
    text-align: center;
    padding: 12rpx 0;
    border-radius: 999rpx;
    background: $bg-page;
    font-size: 26rpx;
    color: $text-content;

    &--on {
      background: $color-primary-light;
      color: $color-primary;
      font-weight: 600;
    }
  }
}

.cate-item {
  padding: 28rpx 12rpx;
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
