/**
 * 应用入口
 * Vue3 组合式 + Pinia + uview-plus
 */
import { createSSRApp } from 'vue'
import uviewPlus from 'uview-plus'
import App from './App.vue'
import pinia from './store'
import { setupInterceptors } from './utils/interceptor'

export function createApp() {
  const app = createSSRApp(App)
  app.use(pinia)
  app.use(uviewPlus)

  // 全局路由拦截（登录态校验）
  setupInterceptors()

  return { app }
}
