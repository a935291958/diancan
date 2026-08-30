/**
 * 全局 HTTP 请求封装
 * 基于 uni.request：请求/响应拦截、Token 自动携带、超时、错误提示、全局 Loading
 */
import { BASE_URL, REQUEST_TIMEOUT, SUCCESS_CODES, UNAUTHORIZED_CODE } from '@/config/env'
import { clearAuthAndToLogin, getToken } from '@/utils/auth'

/** 并发请求 loading 计数，全部结束后再关闭 */
let loadingCount = 0

function showGlobalLoading(title = '加载中') {
  if (loadingCount === 0) {
    uni.showLoading({ title, mask: true })
  }
  loadingCount += 1
}

function hideGlobalLoading() {
  if (loadingCount <= 0) return
  loadingCount -= 1
  if (loadingCount === 0) {
    uni.hideLoading()
  }
}

function showToast(title) {
  if (!title) return
  uni.showToast({
    title: String(title),
    icon: 'none',
    duration: 2000,
  })
}

function joinUrl(url = '') {
  if (/^https?:\/\//i.test(url)) return url
  const path = url.startsWith('/') ? url : `/${url}`
  return `${BASE_URL}${path}`
}

function isTimeoutError(error) {
  const message = error?.errMsg || error?.message || ''
  return /timeout/i.test(message)
}

/**
 * 请求拦截：拼接域名、写入 Token、统一 Content-Type
 */
function beforeRequest(config) {
  const header = {
    'Content-Type': 'application/json',
    ...(config.header || {}),
  }
  const token = getToken()
  if (token) {
    header.Authorization = `Bearer ${token}`
  }
  return {
    ...config,
    url: joinUrl(config.url),
    header,
  }
}

function handleUnauthorized() {
  showToast('登录已过期，请重新登录')
  clearAuthAndToLogin()
}

/**
 * 响应拦截：HTTP 状态 + 业务 code
 * 约定后端格式：{ code, message, data }
 */
function afterResponse(res, config) {
  const { statusCode, data } = res

  if (statusCode === 401) {
    handleUnauthorized()
    return Promise.reject(res)
  }

  if (statusCode < 200 || statusCode >= 300) {
    const message = data?.message || data?.msg || `请求失败(${statusCode})`
    if (config.showError !== false) showToast(message)
    return Promise.reject(res)
  }

  // 非对象（文件流/纯文本）直接返回
  if (data == null || typeof data !== 'object' || Array.isArray(data)) {
    return data
  }

  // 未包一层 code 的接口，视为成功
  if (!Object.prototype.hasOwnProperty.call(data, 'code')) {
    return data
  }

  const code = Number(data.code)
  if (code === UNAUTHORIZED_CODE) {
    handleUnauthorized()
    return Promise.reject(data)
  }

  if (SUCCESS_CODES.includes(code)) {
    return data.data !== undefined ? data.data : data
  }

  const message = data.message || data.msg || '请求失败'
  if (config.showError !== false) showToast(message)
  return Promise.reject(data)
}

/**
 * 发起请求
 * @param {object} options
 * @param {string} options.url 接口路径
 * @param {string} [options.method]
 * @param {object} [options.data]
 * @param {object} [options.header]
 * @param {boolean} [options.loading=true] 是否展示全局 loading
 * @param {string} [options.loadingText]
 * @param {boolean} [options.showError=true] 失败是否 toast
 * @param {number} [options.timeout]
 */
export function http(options = {}) {
  const {
    loading = true,
    loadingText = '加载中',
    showError = true,
    timeout = REQUEST_TIMEOUT,
    ...rest
  } = options

  const config = beforeRequest({ ...rest, showError, timeout })

  if (loading) showGlobalLoading(loadingText)

  return new Promise((resolve, reject) => {
    uni.request({
      ...config,
      timeout,
      success: (res) => {
        afterResponse(res, config).then(resolve).catch(reject)
      },
      fail: (error) => {
        if (showError) {
          showToast(isTimeoutError(error) ? '请求超时，请稍后重试' : '网络异常，请检查网络连接')
        }
        reject(error)
      },
      complete: () => {
        if (loading) hideGlobalLoading()
      },
    })
  })
}

export const get = (url, data = {}, options = {}) =>
  http({ url, data, method: 'GET', ...options })

export const post = (url, data = {}, options = {}) =>
  http({ url, data, method: 'POST', ...options })

export const put = (url, data = {}, options = {}) =>
  http({ url, data, method: 'PUT', ...options })

export const del = (url, data = {}, options = {}) =>
  http({ url, data, method: 'DELETE', ...options })

/**
 * 文件上传（菜品图片等）
 */
export function uploadFile(options = {}) {
  const {
    filePath,
    name = 'file',
    formData = {},
    url = '/upload',
    loading = true,
    loadingText = '上传中',
    showError = true,
  } = options

  const header = {}
  const token = getToken()
  if (token) header.Authorization = `Bearer ${token}`

  if (loading) showGlobalLoading(loadingText)

  return new Promise((resolve, reject) => {
    uni.uploadFile({
      url: joinUrl(url),
      filePath,
      name,
      formData,
      header,
      timeout: REQUEST_TIMEOUT,
      success: (res) => {
        let payload = res.data
        try {
          payload = typeof payload === 'string' ? JSON.parse(payload) : payload
        } catch (error) {
          // 保持原始字符串
        }
        afterResponse({ statusCode: res.statusCode, data: payload }, { showError })
          .then(resolve)
          .catch(reject)
      },
      fail: (error) => {
        if (showError) showToast('上传失败，请稍后重试')
        reject(error)
      },
      complete: () => {
        if (loading) hideGlobalLoading()
      },
    })
  })
}

export default http
