<template>
  <view class="page-wrap page-pad">
    <view class="card form-card">
      <u-form label-width="80">
        <u-form-item label="家庭名称" required>
          <u-input v-model="form.name" placeholder="例如：张家厨房" border="none" />
        </u-form-item>
        <u-form-item label="简介">
          <u-textarea v-model="form.intro" placeholder="写一句家庭点餐宣言" maxlength="60" count />
        </u-form-item>
      </u-form>
    </view>
    <view class="mt-24">
      <u-button type="primary" text="立即创建" :loading="submitting" @click="handleSubmit" />
    </view>
  </view>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useFamilyStore } from '@/store/modules/family'
import { assertRequired } from '@/utils/validate'

const familyStore = useFamilyStore()
const submitting = ref(false)
const form = reactive({
  name: '',
  intro: '',
})

async function handleSubmit() {
  if (!assertRequired(form.name, '请填写家庭名称')) return
  submitting.value = true
  try {
    await familyStore.create({ ...form })
    uni.showToast({ title: '创建成功', icon: 'success' })
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
