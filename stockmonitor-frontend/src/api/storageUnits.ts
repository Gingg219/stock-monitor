import api from '@/utils/request'


export function storeStorageUnit(payload: any) {
  return api.post('/api/admin/storage-unit/store', payload)
}

export function fetchLatestCode(income_id: number | string) {
  return api.get(`/api/admin/storage-unit/${income_id}`)
}

export function assignLoc(payload: any) {
  return api.get(`/api/admin/storage-unit/assign/`, payload)
}

export function changeLoc(payload: any) {
  return api.get(`/api/admin/storage-unit/change-location/`, payload)
}

export function scanPutAway(payload: any) {
  return api.get(`/api/admin/storage-unit/scan/`, payload)
}

export function getAll(payload: any) {
  return api.get(`/api/admin/storage-unit/`, payload)
}