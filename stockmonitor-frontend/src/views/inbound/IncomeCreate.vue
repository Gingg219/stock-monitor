<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useInboundStore } from '@/stores/inbound'
import {
  CCard, CCardHeader, CCardBody, CButton, CRow, CCol,
  CForm, CFormInput, CFormSelect, CTable, CTableHead, CTableBody, CTableRow, CTableHeaderCell, CTableDataCell
} from '@coreui/vue'
import CIcon from '@coreui/icons-vue'
import { cilPlus, cilTrash, cilSave } from '@coreui/icons'

/* ====== Mock data ====== */
const vendors = ['VND1', 'VND2', 'VND3']
const parts = ['QK2-0001-000', 'QK2-0002-000', 'QK2-0003-000']

/* ====== Income header ====== */
const form = reactive({
  income_no: 'INC-241025-001',
  vendor: 'VND1',
  invoice_no: '',
  date: new Date().toISOString().slice(0, 10)
})

/* ====== Income lines ====== */
const lines = ref([
  { id: 1, part_no: 'QK2-0001-000', lot_no: 'LOT01', expiry: '2026-10-25', qty_total: 80000 },
  { id: 2, part_no: 'QK2-0002-000', lot_no: 'LOT02', expiry: '2026-12-31', qty_total: 4000 }
])
let nextId = 3

/* ====== UI state ====== */
const saved = ref(false)
const createdId = ref(null) // id inbound sau khi lưu

/* ====== Helpers ====== */
function addLine() {
  lines.value.push({ id: nextId++, part_no: '', lot_no: '', expiry: '', qty_total: null })
}
function removeLine(id) {
  lines.value = lines.value.filter(x => x.id !== id)
}

/* ====== Save & Navigate ====== */
const router = useRouter()
const store = useInboundStore()

async function saveIncome() {
  // Validate đơn giản
  if (!form.vendor || !lines.value.length) return alert('Vui lòng nhập đủ thông tin')
  const total = lines.value.reduce((s, l) => s + (Number(l.qty_total) || 0), 0)
  if (total <= 0) return alert('Tổng qty phải lớn hơn 0')

  // Chuẩn payload gửi lên API / lưu store
  const payload = {
    income_no: form.income_no,
    vendor: form.vendor,
    invoice_no: form.invoice_no,
    date: form.date,
    lines: lines.value.map((l, idx) => ({
      lineNo: idx + 1,
      part_no: l.part_no,
      lot_no: l.lot_no,
      expiry: l.expiry,
      qty_total: Number(l.qty_total) || 0,
    })),
  }

  // Tạo inbound (sau này thay bằng API create)
  const id = await store.canGenerateQR(payload)
  createdId.value = id
  saved.value = true
  alert(`Đã lưu chứng từ: ${form.income_no}\nTổng số lượng: ${total}`)
  // goToQR()
}

function goToQR() {
  console.log(1111);
  console.log(createdId.value);
  
  if (!createdId.value) return
  router.push({ name: 'Inbound QR', params: { id: String(createdId.value) } })
}
</script>
<template>
  <CCard>
    <CCardHeader class="fw-semibold">Tạo chứng từ nhập (Income)</CCardHeader>
    <CCardBody>
      <CForm>
        <CRow class="mb-3">
          <CCol md="3">
            <CFormInput v-model="form.income_no" label="Income No" readonly />
          </CCol>
          <CCol md="3">
            <CFormSelect v-model="form.vendor" :options="vendors" label="Vendor" />
          </CCol>
          <CCol md="3">
            <CFormInput v-model="form.invoice_no" label="Invoice No" placeholder="INV-001" />
          </CCol>
          <CCol md="3">
            <CFormInput v-model="form.date" label="Date" type="date" />
          </CCol>
        </CRow>

        <CTable small striped hover>
          <CTableHead>
            <CTableRow>
              <CTableHeaderCell>Part No</CTableHeaderCell>
              <CTableHeaderCell>Lot No</CTableHeaderCell>
              <CTableHeaderCell>Expiry</CTableHeaderCell>
              <CTableHeaderCell>Qty Total</CTableHeaderCell>
              <CTableHeaderCell class="text-end">Actions</CTableHeaderCell>
            </CTableRow>
          </CTableHead>
          <CTableBody>
            <CTableRow v-for="l in lines" :key="l.id">
              <CTableDataCell>
                <CFormSelect v-model="l.part_no" :options="['', ...parts]" />
              </CTableDataCell>
              <CTableDataCell>
                <CFormInput v-model="l.lot_no" />
              </CTableDataCell>
              <CTableDataCell>
                <CFormInput v-model="l.expiry" type="date" />
              </CTableDataCell>
              <CTableDataCell>
                <CFormInput v-model.number="l.qty_total" type="number" min="0" />
              </CTableDataCell>
              <CTableDataCell class="text-end">
                <CButton color="danger" variant="ghost" size="sm" @click="removeLine(l.id)">
                  <CIcon :icon="cilTrash" />
                </CButton>
              </CTableDataCell>
            </CTableRow>
            <CTableRow v-if="!lines.length">
              <CTableDataCell colspan="5" class="text-center text-body-secondary">Chưa có dòng hàng</CTableDataCell>
            </CTableRow>
          </CTableBody>
        </CTable>

        <div class="d-flex justify-content-between align-items-center mt-3">
          <CButton color="secondary" variant="outline" @click="addLine">
            <CIcon :icon="cilPlus" class="me-1" /> Add Line
          </CButton>
          <div>
            <CButton color="primary" class="me-2" @click="saveIncome">
              <CIcon :icon="cilSave" class="me-1" /> Save
            </CButton>
            <CButton v-if="saved" color="success" variant="outline" @click="goToQR">
              Tạo Pallet/Box
            </CButton>
          </div>
        </div>
      </CForm>
    </CCardBody>
  </CCard>
</template>

<style scoped>
.table td, .table th { vertical-align: middle; }
</style>