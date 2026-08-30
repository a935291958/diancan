<script setup>
/**
 * 应用根组件
 * 负责冷启动拉用户/家庭、以及原生 tabBar 点击时的登录校验
 */
import { onLaunch, onShow, onHide } from '@dcloudio/uni-app'
import { WHITE_LIST } from '@/config/constants'
import { LOGIN_PATH } from '@/config/env'
import { useUserStore } from '@/store/modules/user'
import { useFamilyStore } from '@/store/modules/family'
import { getToken, hasLogin, setToken } from '@/utils/auth'

function getCurrentRoute() {
  const pages = getCurrentPages()
  const current = pages[pages.length - 1]
  return current ? `/${current.route}` : ''
}

onLaunch(async () => {
  const userStore = useUserStore()
  const familyStore = useFamilyStore()

  // 持久化恢复后，把 token 同步到独立缓存，供 request 拦截器读取
  if (userStore.token) {
    setToken(userStore.token)
  } else if (getToken()) {
    userStore.token = getToken()
  }

  if (!userStore.isLoggedIn) return

  try {
    await userStore.fetchUserInfo()
    await familyStore.fetchCurrentFamily()
  } catch (error) {
    console.warn('[App] 初始化用户数据失败', error)
  }
})

onShow(() => {
  const route = getCurrentRoute() || '/pages/index/index'
  if (WHITE_LIST.includes(route) || hasLogin()) return
  uni.reLaunch({ url: LOGIN_PATH })
})

onHide(() => {})
</script>

<style lang="scss">
/* 必须写在第一行，且 style 带 lang="scss" */
@import 'uview-plus/index.scss';
@import '@/styles/common.scss';
</style>
