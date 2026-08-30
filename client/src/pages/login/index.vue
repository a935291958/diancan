<template>
  <view class="login">
    <view class="login__hero" :style="{ paddingTop: statusBarHeight + 40 + 'px' }">
      <view class="login__logo">家</view>
      <text class="login__title">家庭点餐</text>
      <text class="login__sub">一家人的每日菜单，一起点、一起吃</text>
    </view>

    <view class="login__panel card">
      <u-button
        type="primary"
        shape="circle"
        text="微信一键登录"
        :loading="submitting"
        @click="handleWxLogin"
      />
      <u-button
        v-if="isDev"
        class="mt-24"
        type="info"
        plain
        shape="circle"
        text="开发预览登录"
        @click="handlePreviewLogin"
      />
      <text class="login__tips">登录即代表同意《用户协议》与《隐私政策》</text>
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { HOME_PATH } from '@/config/env'
import { useUserStore } from '@/store/modules/user'
import { useFamilyStore } from '@/store/modules/family'
import { getToken } from '@/utils/auth'

const userStore = useUserStore()
const familyStore = useFamilyStore()
const submitting = ref(false)
const isDev = import.meta.env.DEV
const statusBarHeight = uni.getSystemInfoSync().statusBarHeight || 20

onLoad(() => {
  if (!getToken()) {
    userStore.reset()
    familyStore.reset()
  }
})

async function afterLogin(payload) {
  if (payload?.family) {
    familyStore.setCurrentFamily(payload.family)
  }
  uni.switchTab({ url: HOME_PATH })
}

async function handleWxLogin() {
  if (submitting.value) return
  submitting.value = true
  try {
    const loginRes = await uni.login({ provider: 'weixin' })
    const code = loginRes.code || loginRes[1]?.code
    if (!code) {
      uni.showToast({ title: '获取微信登录凭证失败', icon: 'none' })
      return
    }
    const data = await userStore.loginByWx(code)
    await afterLogin(data)
  } catch (error) {
    console.warn('[login] 微信登录失败', error)
  } finally {
    submitting.value = false
  }
}

function handlePreviewLogin() {
  userStore.setLogin('dev-preview-token', {
    id: 'dev-user',
    nickname: '体验用户',
    avatar: '',
    phone: '',
    gender: 0,
  })
  uni.switchTab({ url: HOME_PATH })
}
</script>

<style lang="scss" scoped>
.login {
  min-height: 100vh;
  background: linear-gradient(180deg, $color-primary 0%, #ff8f66 42%, $bg-page 42%);

  &__hero {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-bottom: 80rpx;
  }

  &__logo {
    width: 140rpx;
    height: 140rpx;
    border-radius: 36rpx;
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
    font-size: 64rpx;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  &__title {
    margin-top: 24rpx;
    font-size: 44rpx;
    font-weight: 700;
    color: #fff;
  }

  &__sub {
    margin-top: 12rpx;
    font-size: 26rpx;
    color: rgba(255, 255, 255, 0.88);
  }

  &__panel {
    margin: 0 40rpx;
    padding: 48rpx 40rpx 32rpx;
  }

  &__tips {
    display: block;
    margin-top: 32rpx;
    text-align: center;
    font-size: 22rpx;
    color: $text-tips;
  }
}
</style>
