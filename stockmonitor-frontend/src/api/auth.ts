import api from '@/utils/request'

// Trước khi login, luôn gọi csrf-cookie
export async function login(email: string, password: string) {
  await api.get('/sanctum/csrf-cookie')   // set XSRF-TOKEN
  return api.post('/login', { email, password })
}

export async function me() {
  return api.get('/api/user'); // 3) test lấy user sau login
}

export async function logout() {
  return api.post('/logout');
}