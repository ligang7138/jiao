import axios from 'axios'
import { ElMessage, ElMessageBox } from 'element-plus'
import { useUserStore } from '@/stores/modules/user'
import { getToken } from '@/utils/auth'
import router from '@/router'
import { normalizeResponse } from '@/utils/legacyResponse'

// 创建 axios 实例
const service = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api/v1',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json;charset=utf-8',
  },
})

// 请求拦截器
service.interceptors.request.use(
  (config) => {
    const token = getToken()
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`
    }

    // GET 请求参数处理
    if (config.method === 'get' && config.params) {
      config.params = JSON.parse(JSON.stringify(config.params))
    }

    return config
  },
  (error) => {
    console.error('Request error:', error)
    return Promise.reject(error)
  }
)

// 响应拦截器
service.interceptors.response.use(
  (response) => {
    if (response.config.responseType === 'blob') {
      return response
    }

    const res = normalizeResponse(response.data)

    // code 不为 200 时判断为错误
    if (res.code !== 200) {
      ElMessage({
        message: res.msg || '请求失败',
        type: 'error',
        duration: 3000,
      })

      // Token 过期
      if (res.code === 40101 || res.code === 40102) {
        ElMessageBox.confirm('登录状态已过期，请重新登录', '提示', {
          confirmButtonText: '重新登录',
          cancelButtonText: '取消',
          type: 'warning',
        }).then(() => {
          const userStore = useUserStore()
          userStore.resetState()
          router.push('/login')
        })
      }

      return Promise.reject(new Error(res.msg || '请求失败'))
    }

    return res
  },
  (error) => {
    console.error('Response error:', error)

    let message = '请求失败'

    if (error.response) {
      const backendMessage = error.response.data?.msg || error.response.data?.message
      switch (error.response.status) {
        case 400:
          message = backendMessage || '请求参数错误'
          break
        case 401:
          message = backendMessage || '未授权，请登录'
          const userStore = useUserStore()
          userStore.resetState()
          router.push('/login')
          break
        case 403:
          message = backendMessage || '拒绝访问'
          break
        case 404:
          message = backendMessage || '请求的资源不存在'
          break
        case 500:
          message = backendMessage || '服务器内部错误'
          break
        default:
          message = backendMessage || '请求失败'
      }
    } else if (error.message.includes('timeout')) {
      message = '请求超时'
    } else if (error.code === 'ERR_NETWORK' || error.message.includes('Network Error')) {
      const apiTarget = import.meta.env.VITE_API_PROXY_TARGET || 'http://127.0.0.1:8001'
      message = `无法连接后端 API（${apiTarget}）。请先执行：npm run api:up 或 npm run dev:all`
    }

    ElMessage({
      message,
      type: 'error',
      duration: 5000,
    })

    return Promise.reject(error)
  }
)

export default service
