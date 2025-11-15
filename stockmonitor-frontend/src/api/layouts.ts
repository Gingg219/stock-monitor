import api from '@/utils/request'


export async function getLayouts() {
  await api.get('/sanctum/csrf-cookie')  
  return api.get('api/admin/locations')
}
