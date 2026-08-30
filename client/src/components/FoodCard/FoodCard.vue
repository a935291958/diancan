<template>
  <view class="food-card" @click="onClick">
    <view v-if="showCheck" class="food-card__check" @click.stop="onSelect">
      <u-icon
        :name="checked ? 'checkmark-circle-fill' : 'minus-circle'"
        :color="checked ? '#FF6B35' : '#c0c4cc'"
        size="22"
      />
    </view>

    <image class="food-card__cover" :src="coverSrc" mode="aspectFill" />

    <view class="food-card__body">
      <view class="flex-between">
        <text class="food-card__name text-ellipsis">{{ displayName }}</text>
        <u-tag v-if="displayCategory" :text="displayCategory" size="mini" plain type="warning" />
      </view>

      <view v-if="cookLabels.length" class="food-card__cooks">
        <text class="food-card__cook-label">可做</text>
        <text class="food-card__cook-text text-ellipsis">{{ cookLabels.join('、') }}</text>
      </view>
      <text v-else-if="hint" class="food-card__hint text-ellipsis">{{ hint }}</text>

      <view v-if="showManage" class="food-card__ops">
        <text class="text-primary" @click.stop="onEdit">编辑</text>
        <text class="food-card__del" @click.stop="onDelete">删除</text>
      </view>
    </view>
  </view>
</template>

<script setup>
/**
 * 菜品卡片
 * 展示 food_name / food_img / category / 可烹饪成员
 *
 * @example
 * <FoodCard :food="item" :members="memberList" show-check :checked="true" @select="onSelect" @edit="onEdit" />
 */
import { computed } from 'vue'
import { foodImgOf, foodNameOf, memberNameOf, resolveCookMembers } from '@/utils/biz'

const props = defineProps({
  /** 菜品对象（优先），字段对齐 jt_jiating_food */
  food: {
    type: Object,
    default: null,
  },
  /** 兼容散传 */
  name: { type: String, default: '' },
  cover: { type: String, default: '' },
  category: { type: String, default: '' },
  hint: { type: String, default: '' },
  cookUids: { type: [String, Array], default: '' },
  /** 家庭成员，用于把 cook_uids 解析成昵称 */
  members: { type: Array, default: () => [] },
  checked: { type: Boolean, default: false },
  showCheck: { type: Boolean, default: false },
  showManage: { type: Boolean, default: false },
})

const emit = defineEmits(['click', 'select', 'toggle', 'edit', 'delete'])

const source = computed(() => props.food || {})
const displayName = computed(() => foodNameOf(source.value) || props.name || '未命名菜品')
const coverSrc = computed(
  () => foodImgOf(source.value) || props.cover || '/static/tabbar/menu-active.png'
)
const displayCategory = computed(() => source.value.category || props.category || '')
const cookLabels = computed(() => {
  const cooks = resolveCookMembers(source.value.cook_uids || props.cookUids, props.members)
  return cooks.map((item) => memberNameOf(item)).slice(0, 4)
})

function onClick() {
  emit('click', source.value.id ? source.value : undefined)
}

function onSelect() {
  emit('select', source.value)
  emit('toggle', source.value)
}

function onEdit() {
  emit('edit', source.value)
}

function onDelete() {
  emit('delete', source.value)
}
</script>

<style lang="scss" scoped>
.food-card {
  display: flex;
  background: $bg-white;
  border-radius: $radius-md;
  overflow: hidden;
  box-shadow: $shadow-card;

  &__check {
    display: flex;
    align-items: center;
    padding-left: 16rpx;
  }

  &__cover {
    width: 168rpx;
    height: 168rpx;
    flex-shrink: 0;
    background: $color-primary-light;
  }

  &__body {
    flex: 1;
    min-width: 0;
    padding: 16rpx 20rpx;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  &__name {
    flex: 1;
    min-width: 0;
    margin-right: 12rpx;
    font-size: 30rpx;
    font-weight: 600;
    color: $text-main;
  }

  &__cooks {
    display: flex;
    align-items: center;
    gap: 8rpx;
    margin-top: 8rpx;
  }

  &__cook-label {
    flex-shrink: 0;
    font-size: 20rpx;
    color: $color-primary;
    background: $color-primary-light;
    padding: 2rpx 10rpx;
    border-radius: 8rpx;
  }

  &__cook-text,
  &__hint {
    font-size: 24rpx;
    color: $text-tips;
  }

  &__hint {
    margin-top: 8rpx;
  }

  &__ops {
    display: flex;
    gap: 32rpx;
    margin-top: 8rpx;
    font-size: 24rpx;
  }

  &__del {
    color: $color-danger;
  }
}
</style>
