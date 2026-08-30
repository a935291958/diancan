<template>
  <view class="order-list">
    <view v-if="!list.length && !grouped.length">
      <u-empty mode="data" :text="emptyText" />
    </view>

    <view v-for="group in displayGroups" :key="group.meal || 'all'">
      <text v-if="groupByMeal && group.label" class="order-list__group">
        {{ group.label }} · {{ group.list.length }} 道
      </text>
      <view
        v-for="item in group.list"
        :key="item.id"
        class="order-card"
        @click="onClick(item)"
      >
        <image class="order-card__cover" :src="coverOf(item)" mode="aspectFill" />
        <view class="order-card__body">
          <view class="flex-between">
            <text class="order-card__name text-ellipsis">{{ foodNameOf(item) }}</text>
            <u-tag :text="statusText(item.status)" size="mini" :type="statusType(item.status)" plain />
          </view>
          <text class="order-card__spec text-ellipsis">{{ formatSelectSpec(item.select_spec) }}</text>
          <view class="order-card__meta">
            <text>点餐 {{ orderUserNameOf(item) }}</text>
            <text>烹饪 {{ orderCookNameOf(item) }}</text>
          </view>
          <text v-if="showMeal" class="order-card__time">
            {{ item.meal_type || '-' }} · {{ item.order_date || '-' }}
          </text>
          <view v-if="showActions && canOperate(item)" class="order-card__ops" @click.stop>
            <u-button size="mini" type="primary" plain text="指派" @click="onAssign(item)" />
            <u-button
              v-if="Number(item.status) === 1"
              size="mini"
              type="primary"
              text="开始"
              @click="onStatus(item, 2)"
            />
            <u-button
              v-if="Number(item.status) === 2"
              size="mini"
              type="primary"
              text="完成"
              @click="onStatus(item, 3)"
            />
            <u-button
              v-if="Number(item.status) === 1"
              size="mini"
              type="error"
              plain
              text="取消"
              @click="onStatus(item, 4)"
            />
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup>
/**
 * 点餐清单
 * 展示当日菜品、选中规格、点餐人、烹饪人、制作状态
 *
 * @example
 * <OrderList :list="orders" group-by-meal show-actions @assign="onAssign" @status="onStatus" />
 */
import { computed } from 'vue'
import { MEAL_TYPES, ORDER_STATUS_MAP } from '@/config/constants'
import { foodImgOf, foodNameOf, formatSelectSpec, orderCookNameOf, orderUserNameOf } from '@/utils/biz'

const props = defineProps({
  list: { type: Array, default: () => [] },
  emptyText: { type: String, default: '暂无点餐' },
  /** 按早/中/晚分组 */
  groupByMeal: { type: Boolean, default: false },
  showActions: { type: Boolean, default: false },
  showMeal: { type: Boolean, default: true },
})

const emit = defineEmits(['click', 'assign', 'status'])

const grouped = computed(() => {
  if (!props.groupByMeal) return []
  return MEAL_TYPES.map((item) => ({
    meal: item.value,
    label: item.name,
    list: props.list.filter((row) => row.meal_type === item.value),
  })).filter((item) => item.list.length)
})

const displayGroups = computed(() => {
  if (props.groupByMeal) return grouped.value
  return [{ meal: '', label: '', list: props.list }]
})

function coverOf(item) {
  return foodImgOf(item) || '/static/tabbar/menu-active.png'
}

function statusText(status) {
  return ORDER_STATUS_MAP[status]?.text || '未知'
}

function statusType(status) {
  return ORDER_STATUS_MAP[status]?.type || 'info'
}

function canOperate(item) {
  const status = Number(item.status)
  return status === 1 || status === 2
}

function onClick(item) {
  emit('click', item)
}

function onAssign(item) {
  emit('assign', item)
}

function onStatus(item, status) {
  emit('status', item, status)
}
</script>

<style lang="scss" scoped>
.order-list {
  &__group {
    display: block;
    margin: 8rpx 0 16rpx;
    font-size: 28rpx;
    font-weight: 600;
  }
}

.order-card {
  display: flex;
  gap: 16rpx;
  padding: 20rpx;
  margin-bottom: 16rpx;
  background: $bg-white;
  border-radius: $radius-md;
  box-shadow: $shadow-card;

  &__cover {
    width: 128rpx;
    height: 128rpx;
    border-radius: $radius-sm;
    background: $color-primary-light;
    flex-shrink: 0;
  }

  &__body {
    flex: 1;
    min-width: 0;
  }

  &__name {
    flex: 1;
    min-width: 0;
    margin-right: 12rpx;
    font-size: 28rpx;
    font-weight: 600;
  }

  &__spec,
  &__time {
    display: block;
    margin-top: 6rpx;
    font-size: 22rpx;
    color: $text-tips;
  }

  &__meta {
    display: flex;
    justify-content: space-between;
    margin-top: 8rpx;
    font-size: 22rpx;
    color: $text-content;
  }

  &__ops {
    display: flex;
    flex-wrap: wrap;
    gap: 12rpx;
    margin-top: 12rpx;
  }
}
</style>
