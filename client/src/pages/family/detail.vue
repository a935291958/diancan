<template>
  <view class="page-wrap page-pad">
    <view class="card block">
      <text class="block__name">{{ familyStore.familyName }}</text>
      <text class="text-tips mt-12">邀请码 {{ familyStore.inviteCode || '-' }}</text>
      <text class="text-tips mt-12">成员 {{ familyStore.familyInfo.member_count || familyStore.memberList.length || 0 }} 人</text>
    </view>

    <view v-if="familyStore.isAdmin" class="card form-card mt-24">
      <u-form label-width="90">
        <u-form-item label="家庭名称">
          <u-input v-model="familyName" maxlength="50" placeholder="修改家庭名称" border="none" />
        </u-form-item>
      </u-form>
      <u-button type="primary" size="small" text="保存名称" :loading="saving" @click="saveName" />
    </view>

    <view class="mt-24">
      <u-button type="primary" text="查看成员" @click="goMembers" />
      <u-button class="mt-24" type="error" plain text="退出家庭" @click="handleLeave" />
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { useFamilyStore } from '@/store/modules/family'
import { confirmDialog } from '@/utils/biz'
import { assertRequired } from '@/utils/validate'

const familyStore = useFamilyStore()
const familyName = ref('')
const saving = ref(false)

onShow(async () => {
  await familyStore.fetchCurrentFamily()
  await familyStore.fetchMembers()
  familyName.value = familyStore.familyName
})

function goMembers() {
  uni.navigateTo({ url: '/pages/family/members' })
}

async function saveName() {
  if (!assertRequired(familyName.value.trim(), '请填写家庭名称')) return
  saving.value = true
  try {
    await familyStore.updateName(familyName.value.trim())
    uni.showToast({ title: '已保存', icon: 'success' })
  } finally {
    saving.value = false
  }
}

async function handleLeave() {
  const ok = await confirmDialog('退出后需重新输入邀请码才能加入，确认退出？')
  if (!ok) return
  try {
    await familyStore.leave()
    uni.showToast({ title: '已退出', icon: 'none' })
    setTimeout(() => uni.navigateBack(), 400)
  } catch (error) {
    console.warn('[family] 退出失败', error)
  }
}
</script>

<style lang="scss" scoped>
.block {
  padding: 32rpx;

  &__name {
    font-size: 40rpx;
    font-weight: 700;
  }
}

.form-card {
  padding: 12rpx 24rpx 24rpx;
}
</style>
