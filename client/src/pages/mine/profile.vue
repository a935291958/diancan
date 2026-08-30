<template>
  <view class="page-wrap page-pad">
    <view class="card form-card">
      <u-form label-width="80">
        <u-form-item label="昵称">
          <u-input v-model="form.nickname" placeholder="请输入昵称" border="none" />
        </u-form-item>
        <u-form-item label="手机号">
          <u-input v-model="form.phone" type="number" maxlength="11" placeholder="选填" border="none" />
        </u-form-item>
      </u-form>
    </view>
    <view class="mt-24">
      <u-button type="primary" text="保存" :loading="submitting" @click="handleSave" />
    </view>
  </view>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { useUserStore } from '@/store/modules/user'
import { assertRequired } from '@/utils/validate'

const userStore = useUserStore()
const submitting = ref(false)
const form = reactive({
  nickname: '',
  phone: '',
})

onLoad(() => {
  form.nickname = userStore.userInfo.nickname || ''
  form.phone = userStore.userInfo.phone || ''
})

async function handleSave() {
  if (!assertRequired(form.nickname, '请输入昵称')) return
  submitting.value = true
  try {
    await userStore.updateProfile({ ...form })
    uni.showToast({ title: '已保存', icon: 'success' })
    setTimeout(() => uni.navigateBack(), 400)
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
