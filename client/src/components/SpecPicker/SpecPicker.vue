<template>
  <u-popup :show="show" mode="bottom" round="16" @close="onClose">
    <view class="spec-picker">
      <view class="flex-between spec-picker__hd">
        <view>
          <text class="spec-picker__title">{{ foodName || '选择规格' }}</text>
          <text class="spec-picker__sub">{{ multipleHint }}</text>
        </view>
        <u-icon name="close" size="18" @click="onClose" />
      </view>

      <scroll-view class="spec-picker__body" scroll-y>
        <view v-for="group in groups" :key="group.spec_name" class="spec-block">
          <view class="flex-between">
            <text class="spec-block__name">{{ group.spec_name }}</text>
            <text class="spec-block__mode">{{ isMulti(group.spec_name) ? '可多选' : '单选' }}</text>
          </view>
          <view class="spec-block__tags">
            <view
              v-for="val in group.values"
              :key="val"
              class="spec-tag"
              :class="{ 'spec-tag--on': isOn(group.spec_name, val) }"
              @click="pick(group.spec_name, val)"
            >
              {{ val }}
            </view>
          </view>
        </view>
        <view v-if="!groups.length" class="text-tips">该菜品暂无规格，可直接点餐</view>
      </scroll-view>

      <u-button type="primary" text="确定" @click="onConfirm" />
    </view>
  </u-popup>
</template>

<script setup>
/**
 * 菜品规格弹窗
 * - 动态渲染多规格（辣度 / 分量 / 自定义）
 * - 辣度、分量默认单选；自定义规格默认可多选
 * - 支持默认选中第一项、select_spec 回显
 *
 * @example
 * <SpecPicker :show="show" :specs="food.specs" :value="selectSpec" @confirm="onConfirm" />
 */
import { computed, reactive, watch } from 'vue'
import { groupSpecs, parseSelectSpec, splitSpecValues, stringifySelectSpec } from '@/utils/biz'

const SINGLE_SPEC_NAMES = ['辣度', '分量']

const props = defineProps({
  show: { type: Boolean, default: false },
  foodName: { type: String, default: '' },
  /** food_spec 行：[{ spec_name, spec_value }] */
  specs: { type: Array, default: () => [] },
  /** 回显：对象或 JSON 字符串 */
  value: { type: [Object, String], default: () => ({}) },
  /**
   * true=全部多选，false=全部单选
   * 不传 / null 则：辣度、分量单选，自定义规格多选
   */
  multiple: {
    default: null,
  },
})

const emit = defineEmits(['close', 'confirm', 'update:show'])

const selected = reactive({})
const groups = computed(() => groupSpecs(props.specs))
const echoed = computed(() => parseSelectSpec(props.value))

const multipleHint = computed(() => {
  if (props.multiple === true) return '每项规格均可多选'
  if (props.multiple === false) return '每项规格单选'
  return '辣度/分量单选，其余可多选'
})

function isMulti(specName) {
  if (props.multiple === true) return true
  if (props.multiple === false) return false
  return !SINGLE_SPEC_NAMES.includes(specName)
}

function toArrayValue(raw) {
  if (Array.isArray(raw)) return raw.filter(Boolean)
  return splitSpecValues(raw)
}

function fillDefault() {
  Object.keys(selected).forEach((key) => delete selected[key])
  groups.value.forEach((group) => {
    const echoedVal = echoed.value[group.spec_name]
    const first = group.values[0] || ''
    if (isMulti(group.spec_name)) {
      const list = echoedVal != null && echoedVal !== '' ? toArrayValue(echoedVal) : first ? [first] : []
      selected[group.spec_name] = list.filter((item) => group.values.includes(item))
      if (!selected[group.spec_name].length && first) selected[group.spec_name] = [first]
      return
    }
    const single = Array.isArray(echoedVal) ? echoedVal[0] : echoedVal
    selected[group.spec_name] = group.values.includes(single) ? single : first
  })
}

watch(
  () => [props.show, props.specs, props.value, props.multiple],
  () => {
    if (props.show) fillDefault()
  },
  { immediate: true, deep: true }
)

function isOn(name, val) {
  const cur = selected[name]
  return Array.isArray(cur) ? cur.includes(val) : cur === val
}

function pick(name, val) {
  if (isMulti(name)) {
    const list = Array.isArray(selected[name]) ? [...selected[name]] : []
    const index = list.indexOf(val)
    if (index >= 0) {
      if (list.length === 1) return
      list.splice(index, 1)
    } else {
      list.push(val)
    }
    selected[name] = list
    return
  }
  selected[name] = val
}

function onClose() {
  emit('update:show', false)
  emit('close')
}

function onConfirm() {
  const missing = groups.value.find((group) => {
    if (!group.values.length) return false
    const cur = selected[group.spec_name]
    return Array.isArray(cur) ? cur.length === 0 : !cur
  })
  if (missing) {
    uni.showToast({ title: `请选择${missing.spec_name}`, icon: 'none' })
    return
  }
  const map = { ...selected }
  emit('confirm', map, stringifySelectSpec(map))
  onClose()
}
</script>

<style lang="scss" scoped>
.spec-picker {
  padding: 32rpx 32rpx calc(24rpx + env(safe-area-inset-bottom));

  &__hd {
    margin-bottom: 12rpx;
  }

  &__title {
    display: block;
    font-size: 32rpx;
    font-weight: 700;
  }

  &__sub {
    display: block;
    margin-top: 6rpx;
    font-size: 22rpx;
    color: $text-tips;
  }

  &__body {
    max-height: 58vh;
    margin-bottom: 24rpx;
  }
}

.spec-block {
  margin-bottom: 24rpx;

  &__name {
    font-size: 26rpx;
    color: $text-content;
    font-weight: 600;
  }

  &__mode {
    font-size: 22rpx;
    color: $text-tips;
  }

  &__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 12rpx;
    margin-top: 12rpx;
  }
}

.spec-tag {
  padding: 10rpx 24rpx;
  border-radius: 999rpx;
  background: $bg-page;
  color: $text-content;
  font-size: 24rpx;

  &--on {
    background: $color-primary-light;
    color: $color-primary;
    font-weight: 600;
  }
}
</style>
