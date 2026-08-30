<template>
  <view class="page-wrap page-pad">
    <view class="card form-card">
      <u-form label-width="80">
        <u-form-item label="邀请码" required>
          <u-input
            v-model="inviteCode"
            maxlength="6"
            placeholder="请输入6位邀请码"
            border="none"
            @blur="inviteCode = inviteCode.trim().toUpperCase()"
          />
        </u-form-item>
      </u-form>
    </view>
    <view class="mt-24">
      <u-button type="primary" text="加入家庭" :loading="submitting" @click="handleSubmit" />
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { useFamilyStore } from '@/store/modules/family'
import { assertInviteCode } from '@/utils/validate'

const familyStore = useFamilyStore()
const inviteCode = ref('')
const submitting = ref(false)

async function handleSubmit() {
  const code = inviteCode.value.trim()
  if (!assertInviteCode(code)) return
  submitting.value = true
  try {
    await familyStore.join(code)
    uni.showToast({ title: '加入成功', icon: 'success' })
    setTimeout(() => uni.navigateBack(), 400)
  } catch (error) {
    console.warn('[family] 加入失败', error)
  } finally {
    submitting.value = false
  }
}
</script>

<style lang="scss" scoped>
.form-card {
  padding: 12rpx 24rpx;
}
</style>
