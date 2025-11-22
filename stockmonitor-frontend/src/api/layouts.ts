import api from '@/utils/request'

export function getLayouts() {
  return api.get('/api/admin/locations')
}

export function updateLayout(id: number | string, payload: any) {
  return api.put(`/api/admin/locations/${id}`, payload)
}

// Dữ liệu form của bạn (rack, slot, tier, part, pack...)
export function createLayout(payload: any) {
  return api.post('/api/admin/locations', payload)
}

export function deleteLayout(id: number) {
  return api.delete(`/api/admin/locations/${id}`)
}
