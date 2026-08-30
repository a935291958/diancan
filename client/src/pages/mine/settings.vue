<template>
  <view class="page-wrap page-pad">
    <view class="card">
      <u-cell-group :border="false">
        <u-cell title="当前环境" :value="appEnv" />
        <u-cell title="退出登录" is-link @click="handleLogout" />
      </u-cell-group>
    </view>
  </view>
</template>

<script setup>
import { APP_ENV, LOGIN_PATH } from '@/config/env'
import { useUserStore } from '@/store/modules/user'
import { useFamilyStore } from '@/store/modules/family'

const userStore = useUserStore()
const familyStore = useFamilyStore()
const appEnv = APP_ENV

function handleLogout() {
  uni.showModal({
    title: '提示',
    content: '确认退出登录？',
    success: (res) => {
      if (!res.confirm) return
      userStore.logout()
      familyStore.reset()
      uni.reLaunch({ url: LOGIN_PATH })
    },
  })
}
</script>
