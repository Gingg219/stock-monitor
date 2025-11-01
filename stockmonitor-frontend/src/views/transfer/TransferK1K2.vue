<script setup>
import { ref, computed } from 'vue'
import {
  CCard, CCardHeader, CCardBody, CRow, CCol, CForm, CFormInput, CButton,
  CTable, CTableHead, CTableBody, CTableRow, CTableHeaderCell, CTableDataCell,
  CBadge, CAlert
} from '@coreui/vue'
import CIcon from '@coreui/icons-vue'
import { cilArrowRight, cilSearch, cilCheckCircle } from '@coreui/icons'

/* ===== Mock Pallet tồn ở 2 kho ===== */
const palletsK1 = ref([
  { code: 'PAL-INC-241025-001-01-001', part_no:'QK2-0001-000', qty:8000, location:'A-T1-5' },
  { code: 'PAL-INC-241025-001-01-002', part_no:'QK2-0001-000', qty:8000, location:'A-T1-6' },
  { code: 'BOX-INC-241025-001-01-001', part_no:'QK2-0001-000', qty:1000, location:'A-T1-7' },
])
const palletsK2 = ref([])

/* ===== Tìm & chọn chuyển ===== */
const q = ref('')
const filtered = computed(() => {
  const s = (q.value||'').toLowerCase()
  return palletsK1.value.filter(p => !s || p.code.toLowerCase().includes(s) || p.part_no.toLowerCase().includes(s))
})
const selectedCodes = ref(new Set())
function toggle(code){
  if (selectedCodes.value.has(code)) selectedCodes.value.delete(code)
  else selectedCodes.value.add(code)
}

/* ===== Vị trí đích (K2) ===== */
const target = ref({ rack:'A', tier:1, slotStart: 10 }) // slotStart: sẽ +1 dần cho các kiện tiếp theo
const msg = ref('')

function targetLabel(i){ // mỗi kiện lùi/tiến 1 ô
  return `${target.value.rack}-T${target.value.tier}-${target.value.slotStart + i}`
}

/* ===== Thực hiện chuyển ===== */
function doTransfer(){
  msg.value = ''
  const items = filtered.value.filter(p => selectedCodes.value.has(p.code))
  if (!items.length) { msg.value='❌ Chưa chọn pallet/box'; return }

  // chuyển
  items.forEach((p,idx) => {
    // bỏ khỏi K1
    palletsK1.value = palletsK1.value.filter(x => x.code !== p.code)
    // thêm vào K2
    palletsK2.value.push({
      ...p,
      location: targetLabel(idx),
    })
  })
  selectedCodes.value = new Set()
  msg.value = `✅ Đã chuyển ${items.length} kiện sang K2 (bắt đầu tại ${targetLabel(0)})`
}
</script>

<template>
  <CCard>
    <CCardHeader class="fw-semibold d-flex align-items-center gap-2">
      Transfer K1 → K2
      <CBadge color="secondary">No DID</CBadge>
      <div class="ms-auto d-flex">
        <CForm class="d-flex">
          <CFormInput v-model="q" placeholder="Tìm theo code/part..." class="me-2" />
          <CButton color="secondary" variant="outline"><CIcon :icon="cilSearch"/></CButton>
        </CForm>
      </div>
    </CCardHeader>

    <CCardBody>
      <CAlert v-if="msg" :color="msg.startsWith('✅') ? 'success':'danger'" class="py-2">{{ msg }}</CAlert>

      <CRow>
        <!-- K1 -->
        <CCol md="6">
          <div class="mb-2"><strong>Kho K1 (nguồn)</strong></div>
          <CTable small hover class="align-middle">
            <CTableHead>
              <CTableRow>
                <CTableHeaderCell style="width:40px"></CTableHeaderCell>
                <CTableHeaderCell>Code</CTableHeaderCell>
                <CTableHeaderCell>Part</CTableHeaderCell>
                <CTableHeaderCell>Qty</CTableHeaderCell>
                <CTableHeaderCell>Vị trí</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              <CTableRow v-for="p in filtered" :key="p.code">
                <CTableDataCell>
                  <input type="checkbox" class="form-check-input" :checked="selectedCodes.has(p.code)" @change="toggle(p.code)"/>
                </CTableDataCell>
                <CTableDataCell>{{ p.code }}</CTableDataCell>
                <CTableDataCell>{{ p.part_no }}</CTableDataCell>
                <CTableDataCell>{{ p.qty }}</CTableDataCell>
                <CTableDataCell>{{ p.location }}</CTableDataCell>
              </CTableRow>
              <CTableRow v-if="!filtered.length">
                <CTableDataCell colspan="5" class="text-center text-body-secondary">Trống</CTableDataCell>
              </CTableRow>
            </CTableBody>
          </CTable>
        </CCol>

        <!-- Điều khiển chuyển -->
        <CCol md="1" class="d-flex align-items-center justify-content-center">
          <CIcon :icon="cilArrowRight" size="xl" />
        </CCol>

        <!-- K2 -->
        <CCol md="5">
          <div class="mb-2"><strong>Kho K2 (đích)</strong></div>

          <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
            <span>Rack</span>
            <select v-model="target.rack" class="form-select form-select-sm" style="width:auto">
              <option>A</option><option>B</option><option>C</option>
            </select>
            <span>Tầng</span>
            <select v-model.number="target.tier" class="form-select form-select-sm" style="width:auto">
              <option :value="1">1</option><option :value="2">2</option><option :value="3">3</option>
            </select>
            <span>Slot bắt đầu</span>
            <input v-model.number="target.slotStart" type="number" min="1" class="form-control form-control-sm" style="width:100px">
            <CButton color="primary" class="ms-2" @click="doTransfer"><CIcon :icon="cilCheckCircle" class="me-1"/>Chuyển</CButton>
          </div>

          <CTable small hover class="align-middle">
            <CTableHead>
              <CTableRow>
                <CTableHeaderCell>Code</CTableHeaderCell>
                <CTableHeaderCell>Part</CTableHeaderCell>
                <CTableHeaderCell>Qty</CTableHeaderCell>
                <CTableHeaderCell>Vị trí K2</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              <CTableRow v-for="p in palletsK2" :key="p.code">
                <CTableDataCell>{{ p.code }}</CTableDataCell>
                <CTableDataCell>{{ p.part_no }}</CTableDataCell>
                <CTableDataCell>{{ p.qty }}</CTableDataCell>
                <CTableDataCell><CBadge color="primary">{{ p.location }}</CBadge></CTableDataCell>
              </CTableRow>
              <CTableRow v-if="!palletsK2.length">
                <CTableDataCell colspan="4" class="text-center text-body-secondary">Chưa có kiện nào ở K2</CTableDataCell>
              </CTableRow>
            </CTableBody>
          </CTable>
        </CCol>
      </CRow>
    </CCardBody>
  </CCard>
</template>