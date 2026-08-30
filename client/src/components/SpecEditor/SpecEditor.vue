<template>
  <view class="spec-editor">
    <view class="spec-editor__presets">
      <u-tag
        v-for="item in SPEC_PRESETS"
        :key="item.spec_name"
        :text="`+ ${item.spec_name}`"
        size="mini"
        plain
        type="warning"
        @click="addPreset(item)"
      />
      <u-tag text="+ 自定义规格" size="mini" plain type="primary" @click="addCustom" />
    </view>

    <view v-for="(group, index) in innerList" :key="group.spec_name + index" class="spec-group card">
      <view class="flex-between">
        <u-input :model-value="group.spec_name" placeholder="规格名称，如口味" border="surround" @change="(e) => onNameChange(index, e)" />
        <u-icon name="trash" color="#fa3534" size="20" @click="removeGroup(index)" />
      </view>
      <view class="spec-group__tags">
        <u-tag
          v-for="(val, vIndex) in group.values"
          :key="val"
          :text="val"
          closable
          size="mini"
          type="warning"
          plain
          @close="removeValue(index, vIndex)"
        />
      </view>
      <view class="spec-group__add">
        <u-input v-model="drafts[index]" placeholder="输入选项后添加" border="surround" />
        <u-button type="primary" size="mini" text="添加" @click="addValue(index)" />
      </view>
    </view>

    <text v-if="!innerList.length" class="text-tips spec-editor__empty">尚未添加规格，点餐时将使用默认选项</text>
  </view>
</template>

<script setup>
/**
 * 多规格动态编辑：辣度 / 分量 / 自定义
 * v-model: [{ spec_name, values: string[] }]
 */
import { computed, ref, watch } from 'vue'
import { SPEC_PRESETS } from '@/config/constants'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['update:modelValue'])

const innerList = computed(() => props.modelValue || [])
const drafts = ref([])

watch(
  innerList,
  (list) => {
    drafts.value = list.map((_, index) => drafts.value[index] || '')
  },
  { immediate: true }
)

function commit(next) {
  emit('update:modelValue', next)
}

function addPreset(preset) {
  if (innerList.value.some((item) => item.spec_name === preset.spec_name)) {
    uni.showToast({ title: `已存在「${preset.spec_name}」`, icon: 'none' })
    return
  }
  commit([...innerList.value, { spec_name: preset.spec_name, values: [...preset.values] }])
}

function addCustom() {
  commit([...innerList.value, { spec_name: '', values: [] }])
}

function removeGroup(index) {
  const next = [...innerList.value]
  next.splice(index, 1)
  commit(next)
}

function onNameChange(index, event) {
  const name = typeof event === 'object' ? event?.detail || event : event
  const next = innerList.value.map((item, i) =>
    i === index ? { ...item, spec_name: String(name || '') } : item
  )
  commit(next)
}

function addValue(index) {
  const text = String(drafts.value[index] || '').trim()
  if (!text) {
    uni.showToast({ title: '请输入选项', icon: 'none' })
    return
  }
  const group = innerList.value[index]
  if (group.values.includes(text)) {
    uni.showToast({ title: '选项已存在', icon: 'none' })
    return
  }
  const next = innerList.value.map((item, i) =>
    i === index ? { ...item, values: [...item.values, text] } : item
  )
  drafts.value[index] = ''
  commit(next)
}

function removeValue(index, vIndex) {
  const next = innerList.value.map((item, i) => {
    if (i !== index) return item
    const values = [...item.values]
    values.splice(vIndex, 1)
    return { ...item, values }
  })
  commit(next)
}
</script>

<style lang="scss" scoped>
.spec-editor {
  &__presets {
    display: flex;
    flex-wrap: wrap;
    gap: 12rpx;
    margin-bottom: 16rpx;
  }

  &__empty {
    display: block;
    margin-top: 12rpx;
    font-size: 24rpx;
  }
}

.spec-group {
  padding: 20rpx;
  margin-bottom: 16rpx;

  &__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 12rpx;
    margin: 16rpx 0;
  }

  &__add {
    display: flex;
    align-items: center;
    gap: 12rpx;
  }
}
</style>
