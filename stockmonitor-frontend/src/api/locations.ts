import api from '@/utils/request'

export function fetchWarehouses(params?: { search?: string }) {
  return api.get('/api/admin/locations/warehouses', { params })
}

export function fetchRacks(params: { warehouse_id: number, search?: string }) {
  return api.get('/api/admin/locations/racks', { params })
}

export function fetchTiers(params: { rack_id: number, search?: string }) {
  return api.get('/api/admin/locations/tiers', { params })
}

export function fetchSlots(params: { tier_id: number, search?: string }) {
  return api.get('/api/admin/locations/slots', { params })
}