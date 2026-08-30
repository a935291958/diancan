<template>
  <view class="page-wrap page-pad">
    <view class="card form-card">
      <u-form label-width="80">
        <u-form-item label="名称" required>
          <u-input v-model="form.name" placeholder="菜品名称" border="none" />
        </u-form-item>
        <u-form-item label="价格" required>
          <u-input v-model="form.price" type="digit" placeholder="0.00" border="none" />
        </u-form-item>
        <u-form-item label="简介">
          <u-textarea v-model="form.description" placeholder="口味、做法小提示" maxlength="120" count />
        </u-form-item>
      </u-form>
    </view>
    <view class="mt-24">
      <u-button type="primary" :text="form.id ? '保存修改' : '新增菜品'" :loading="submitting" @click="handleSubmit" />
    </view>
  </view>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { getDishDetail, saveDish } from '@/api/dish'
import { useFamilyStore } from '@/store/modules/family'
import { assertRequired } from '@/utils/validate'

const familyStore = useFamilyStore()
const submitting = ref(false)
const form = reactive({
  id: '',
  name: '',
  price: '',
  description: '',
})

onLoad(async (query) => {
  form.id = query?.id || ''
  if (!form.id) return
  const data = await getDishDetail(form.id)
  Object.assign(form, data || {})
})

async function handleSubmit() {
  if (!assertRequired(form.name, '请填写菜品名称')) return
  if (!assertRequired(form.price, '请填写价格')) return
  submitting.value = true
  try {
    await saveDish({
      ...form,
      familyId: familyStore.currentFamilyId,
    })
    uni.showToast({ title: '保存成功', icon: 'success' })
    setTimeout(() => uni.navigateBack(), 400)
  } finally {
    submitting.value = false
  }
}
</script>

<style lang="scss" scoped>
.form-card {
  padding: 12rpx 24rpx 24rpx;
}
</style>
