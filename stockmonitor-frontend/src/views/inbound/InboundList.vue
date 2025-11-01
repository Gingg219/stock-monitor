<script setup>
import { useRouter } from 'vue-router'
import { CCard, CCardHeader, CCardBody, CButton, CTable, CTableHead, CTableRow, CTableHeaderCell, CTableBody, CTableDataCell } from '@coreui/vue'

const router = useRouter()
// TODO: thay bằng API list
const rows = [
  { id: '101', income_no:'INC-241025-001', vendor:'VND1', date:'2025-10-25', status:'draft' },
]
function open(row){ router.push({ name:'Inbound Detail', params:{ id: row.id } }) }
</script>

<template>
  <CCard>
    <CCardHeader class="fw-semibold">Inbound</CCardHeader>
    <CCardBody>
      <CTable hover>
        <CTableHead>
          <CTableRow>
            <CTableHeaderCell>#</CTableHeaderCell>
            <CTableHeaderCell>Income No</CTableHeaderCell>
            <CTableHeaderCell>Vendor</CTableHeaderCell>
            <CTableHeaderCell>Date</CTableHeaderCell>
            <CTableHeaderCell>Status</CTableHeaderCell>
            <CTableHeaderCell></CTableHeaderCell>
          </CTableRow>
        </CTableHead>
        <CTableBody>
          <CTableRow v-for="(r,i) in rows" :key="r.id">
            <CTableDataCell>{{ i+1 }}</CTableDataCell>
            <CTableDataCell>{{ r.income_no }}</CTableDataCell>
            <CTableDataCell>{{ r.vendor }}</CTableDataCell>
            <CTableDataCell>{{ r.date }}</CTableDataCell>
            <CTableDataCell>{{ r.status }}</CTableDataCell>
            <CTableDataCell class="text-end">
              <CButton size="sm" color="primary" @click="open(r)">Mở</CButton>
            </CTableDataCell>
          </CTableRow>
        </CTableBody>
      </CTable>
    </CCardBody>
  </CCard>
</template>