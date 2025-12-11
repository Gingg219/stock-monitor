import api from '@/utils/request'


export function storeStorageUnit(payload: any) {
  return api.post('/api/admin/storage-unit', payload)
}

