// import api from '@/utils/request'

// export const csrf = () => api.get('/sanctum/csrf-cookie')

// export const login = (email: string, password: string) =>
//   csrf().then(() => api.post('/login', { email, password }))

// export const me = () => api.get('/api/user')

// export const logout = () => api.post('/logout')

import axios from 'axios'

export const api = axios.create({
  baseURL: 'http://127.0.0.1:8000',   // trỏ về backend
  withCredentials: true                   // BẮT BUỘC để cookie đi kèm
})

// Trước khi login, luôn gọi csrf-cookie
export async function login(email: string, password: string) {
  await api.get('/sanctum/csrf-cookie')   // set XSRF-TOKEN
  return api.post('/login', { email, password })
}
export const logout = () => api.post('/logout')

export const me = () => api.get('/api/user')