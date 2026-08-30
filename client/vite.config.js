import { defineConfig, loadEnv } from 'vite'
import uni from '@dcloudio/vite-plugin-uni'
import { fileURLToPath, URL } from 'node:url'

/**
 * Vite 构建配置
 * 微信小程序开发：npm run dev:mp-weixin
 * H5 本地开发：把 /api 代理到后端，避免浏览器 CORS
 */
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  let apiOrigin = 'http://127.0.0.1:9501'
  try {
    apiOrigin = new URL(env.VITE_API_BASE_URL || apiOrigin).origin
  } catch {
    // 相对路径或非法 URL 时沿用默认 origin
  }

  return {
    plugins: [uni()],
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url)),
      },
    },
    css: {
      preprocessorOptions: {
        scss: {
          silenceDeprecations: ['legacy-js-api', 'color-functions', 'import'],
        },
      },
    },
    server: {
      port: 5173,
      host: true,
      proxy: {
        '/api': {
          target: apiOrigin,
          changeOrigin: true,
          // 后端路由是 /v1/...，开发环境 BASE_URL 带 /api，代理时剥掉前缀
          rewrite: (path) => path.replace(/^\/api/, '') || '/',
        },
      },
    },
  }
})
