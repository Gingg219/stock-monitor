// src/services/axios.js
import axios from 'axios'
import router from '@/router'
import { useAuthStore } from '@/stores/auth'

const api = axios.create({
  baseURL: 'http://localhost:8000',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
  },
})

api.interceptors.response.use(
  res => res,
  async error => {
    if (error.response?.status === 401) {
      const auth = useAuthStore()

      auth.user = null

      if (router.currentRoute.value.name !== 'Login') {
        router.replace({ name: 'Login' })
      }
    }

    return Promise.reject(error)
  }
)

export default api
