<template>
  <view class="page-wrap page-pad">
    <view class="card form-card">
      <u-form label-width="90">
        <u-form-item label="家庭名称" required>
          <u-input v-model="familyName" maxlength="50" placeholder="例如：张家厨房" border="none" />
        </u-form-item>
      </u-form>
    </view>
    <text class="tips">创建后将自动生成 6 位邀请码，可分享给家人加入。</text>
    <view class="mt-24">
      <u-button type="primary" text="立即创建" :loading="submitting" @click="handleSubmit" />
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { useFamilyStore } from '@/store/modules/family'
import { assertRequired } from '@/utils/validate'

const familyStore = useFamilyStore()
const familyName = ref('')
const submitting = ref(false)

async function handleSubmit() {
  if (!assertRequired(familyName.value.trim(), '请填写家庭名称')) return
  submitting.value = true
  try {
    await familyStore.create({ family_name: familyName.value.trim() })
    uni.showToast({ title: '创建成功', icon: 'success' })
    setTimeout(() => uni.navigateBack(), 400)
  } catch (error) {
    console.warn('[family] 创建失败', error)
  } finally {
    submitting.value = false
  }
}
</script>

<style lang="scss" scoped>
.form-card {
  padding: 12rpx 24rpx;
}

.tips {
  display: block;
  margin-top: 16rpx;
  font-size: 24rpx;
  color: $text-tips;
}
</style>
