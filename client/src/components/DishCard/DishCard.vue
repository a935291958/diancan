<template>
  <FoodCard
    :name="name"
    :cover="cover"
    :hint="desc"
    :show-check="showAdd"
    @click="onClick"
    @select="onAdd"
  />
</template>

<script setup>
/**
 * 兼容旧名称，内部复用 FoodCard
 */
defineProps({
  name: { type: String, default: '' },
  desc: { type: String, default: '' },
  cover: { type: String, default: '' },
  showAdd: { type: Boolean, default: false },
  price: { type: [Number, String], default: 0 },
})

const emit = defineEmits(['click', 'add'])

function onClick() {
  emit('click')
}

function onAdd() {
  emit('add')
}
</script>
