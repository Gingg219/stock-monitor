import api from '@/utils/request'


export function fetchIncomes(params={}) {
  return api.get('/api/admin/incomes', { params })
}

export function fetchIncome(id: number | string) {
  return api.get(`/api/admin/incomes/${id}`)
} 

export function storeIncome(id: number | string, payload: any) {
  return api.post(`/api/admin/incomes`, payload)
}
