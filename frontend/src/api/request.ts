import axios from 'axios'
import router from '@/router'

const request = axios.create({
  baseURL: import.meta.env.VITE_API_BASE || '/',
  timeout: 30000,
  // 同域部署，cookie 认证
  withCredentials: true,
})

// 未登录统一跳转登录页
request.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      router.push('/pass')
    }
    return Promise.reject(error)
  }
)

export default request
