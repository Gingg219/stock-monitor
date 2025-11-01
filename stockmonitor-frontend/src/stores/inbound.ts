import { defineStore } from 'pinia'

export const useInboundStore = defineStore('inbound', {
  state: () => ({
    current: null as null | {
      id: string|number
      income_no: string
      vendor: string
      date: string
      status: 'draft'|'generated'|'posted'
      lines: Array<{ lineNo:number; part_no:string; lot_no:string; expiry:string; qty_total:number }>
    },
  }),
  actions: {
    async fetchOne(id: string) {
      // TODO: call API. Mock:
      this.current = {
        id, income_no: 'INC-241025-001', vendor: 'VND1', date: '2025-10-25',
        status: 'draft',
        lines: [
          { lineNo:1, part_no:'QK2-0001-000', lot_no:'LOT01', expiry:'2026-10-25', qty_total:80000 },
          { lineNo:2, part_no:'QK2-0002-000', lot_no:'LOT02', expiry:'2026-12-31', qty_total:4000 },
        ],
      }
    },
    canGenerateQR(id: string) {
      return !!(this.current && String(this.current.id) === id && this.current.lines?.length)
    },
    markGenerated() { if (this.current) this.current.status = 'generated' },
  },
})
