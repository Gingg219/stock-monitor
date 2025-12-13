import api from '@/utils/request'


export function storeStorageUnit(payload: any) {
  return api.post('/api/admin/storage-unit', payload)
}

export function fetchLatestCode(income_id: number | string) {
  return api.get(`/api/admin/storage-unit/${income_id}`)
}

