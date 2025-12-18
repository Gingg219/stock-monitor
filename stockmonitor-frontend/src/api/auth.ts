import api from '@/utils/request'

export async function login(email: string, password: string) {
  await api.get('/sanctum/csrf-cookie')   // set XSRF-TOKEN
  return api.post('/login', { email, password })
}

export async function me() {
  return api.get('/api/user');
}

export async function logout() {
  await api.post('/logout')
  window.location.replace('/#/pages/login')
}
