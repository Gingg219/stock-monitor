<script setup>
import { useInboundStore } from '@/stores/inbound'
import { CTable, CTableHead, CTableRow, CTableHeaderCell, CTableBody, CTableDataCell } from '@coreui/vue'
const store = useInboundStore()
</script>

<template>
  <CTable striped hover>
    <CTableHead>
      <CTableRow>
        <CTableHeaderCell>#</CTableHeaderCell>
        <CTableHeaderCell>Part</CTableHeaderCell>
        <CTableHeaderCell>Lot</CTableHeaderCell>
        <CTableHeaderCell>Expiry</CTableHeaderCell>
        <CTableHeaderCell class="text-end">Qty Total</CTableHeaderCell>
      </CTableRow>
    </CTableHead>
    <CTableBody>
      <CTableRow v-for="l in store.current?.lines || []" :key="l.lineNo">
        <CTableDataCell>{{ l.lineNo }}</CTableDataCell>
        <CTableDataCell>{{ l.part_no }}</CTableDataCell>
        <CTableDataCell>{{ l.lot_no }}</CTableDataCell>
        <CTableDataCell>{{ l.expiry }}</CTableDataCell>
        <CTableDataCell class="text-end">{{ l.qty_total.toLocaleString() }}</CTableDataCell>
      </CTableRow>
    </CTableBody>
  </CTable>
</template>
