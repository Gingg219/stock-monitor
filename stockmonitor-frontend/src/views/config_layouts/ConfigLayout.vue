<!-- src/views/LayoutsView.vue -->
<script setup>
import { ref, reactive, computed, watch } from 'vue'
import {
  CCard, CCardBody, CCardHeader, CButton, CContainer, CForm, CFormInput,
  CButtonGroup, CFormCheck, CTable, CTableHead, CTableBody, CTableRow, CTableHeaderCell, CTableDataCell,
  CPagination, CPaginationItem, CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter,
  CRow, CFormLabel, CBadge
} from '@coreui/vue'
import CIcon from '@coreui/icons-vue'
import { cilPencil, cilDelete, cilPlaylistAdd } from '@coreui/icons'

/* ------------------ Mock data ------------------ */
const rows = ref([
  { id: 1,  wh: 'K1', rack: 'A', tier: 1, slot: '1.1', pack: 'Pallet', part: 'VE4-4150-470' },
  { id: 2,  wh: 'K1', rack: 'A', tier: 2, slot: '2.1', pack: 'Box',    part: 'VE4-4150-472' },
  { id: 3,  wh: 'K1', rack: 'B', tier: 1, slot: '1.1', pack: 'Pallet', part: 'VE4-4150-473' },
  { id: 4,  wh: 'K1', rack: 'C', tier: 1, slot: '1.1', pack: 'Box',    part: 'VE4-4150-474' },
  { id: 5,  wh: 'K1', rack: 'D', tier: 1, slot: '1.1', pack: 'Pallet', part: 'VE4-4150-475' },
  { id: 6,  wh: 'K1', rack: 'E', tier: 1, slot: '1.1', pack: 'Box',    part: 'VE4-4150-470' },
  { id: 7,  wh: 'K1', rack: 'E', tier: 1, slot: '1.1', pack: 'Box',    part: 'VE4-4150-470' },
  { id: 8,  wh: 'K1', rack: 'E', tier: 1, slot: '1.1', pack: 'Box',    part: 'VE4-4150-470' },
  { id: 9,  wh: 'K1', rack: 'E', tier: 1, slot: '1.1', pack: 'Box',    part: 'VE4-4150-470' },
  { id: 10, wh: 'K1', rack: 'E', tier: 1, slot: '1.1', pack: 'Box',    part: 'VE4-4150-470' },
  { id: 11, wh: 'K1', rack: 'E', tier: 1, slot: '1.1', pack: 'Box',    part: 'VE4-4150-470' },
  { id: 12, wh: 'K1', rack: 'E', tier: 1, slot: '1.1', pack: 'Box',    part: 'VE4-4150-470' },
  { id: 13, wh: 'K1', rack: 'E', tier: 1, slot: '1.1', pack: 'Box',    part: 'VE4-4150-470' },
  { id: 14, wh: 'K1', rack: 'E', tier: 1, slot: '1.1', pack: 'Box',    part: 'VE4-4150-470' },
  { id: 15, wh: 'K1', rack: 'E', tier: 1, slot: '1.1', pack: 'Box',    part: 'VE4-4150-470' },
  { id: 16, wh: 'K1', rack: 'E', tier: 1, slot: '1.1', pack: 'Box',    part: 'VE4-4150-470' },
])
let nextId = 17

/* ------------------ State ------------------ */
const activeWH = ref('K1')
const q = ref('')
const qEff = ref('')
const currentPage = ref(1)
const perPage = ref(10)

const showForm = ref(false)
const showConfirm = ref(false)
const editingId = ref(null)
const toDeleteId = ref(null)
const form = reactive({ wh: 'K1', rack: '', tier: 1, slot: '', pack: '', part: '' })

/* ------------------ Reactions ------------------ */
// đổi kho -> quay về trang 1
watch(activeWH, () => { currentPage.value = 1 })

// debounce search
let t = null
watch(q, v => {
  clearTimeout(t)
  t = setTimeout(() => { qEff.value = (v || '').trim().toLowerCase() }, 250)
})

/* ------------------ Derived data ------------------ */
const filtered = computed(() => {
  const query = qEff.value
  return rows.value.filter(r =>
    r.wh === activeWH.value &&
    (!query || String(r.part).toLowerCase().includes(query))
  )
})

/* Phân trang an toàn */
const pageCount = computed(() => {
  const total = Math.ceil(filtered.value.length / perPage.value) || 1
  return total
})
function setPage(p) {
  const n = pageCount.value
  if (Number.isNaN(p)) p = 1
  currentPage.value = Math.min(Math.max(1, p), n)
}
watch([filtered, perPage], () => setPage(currentPage.value))

const paged = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return filtered.value.slice(start, start + perPage.value)
})

/* ------------------ Actions ------------------ */
function openCreate() {
  editingId.value = null
  Object.assign(form, { wh: activeWH.value, rack: '', tier: 1, slot: '', pack: '', part: '' })
  showForm.value = true
}
function openEdit(row) {
  editingId.value = row.id
  Object.assign(form, { ...row })
  showForm.value = true
}
function saveForm() {
  if (!form.rack || !form.slot || !form.part) return
  if (editingId.value) {
    const idx = rows.value.findIndex(r => r.id === editingId.value)
    if (idx >= 0) rows.value[idx] = { id: editingId.value, ...form }
  } else {
    rows.value.unshift({ id: nextId++, ...form })
  }
  showForm.value = false
}
function askDelete(row) {
  toDeleteId.value = row.id
  showConfirm.value = true
}
function doDelete() {
  if (toDeleteId.value != null) rows.value = rows.value.filter(r => r.id !== toDeleteId.value)
  showConfirm.value = false
}
</script>

<template>
  <!-- Modal: Add/Edit -->
  <CModal alignment="center" :visible="showForm" @close="() => showForm = false" aria-labelledby="formModal">
    <CModalHeader><CModalTitle id="formModal">{{ editingId ? 'Sửa vị trí' : 'Thêm vị trí' }}</CModalTitle></CModalHeader>
    <CModalBody>
      <CRow class="mb-3">
        <CFormLabel class="col-sm-3 col-form-label">Kho</CFormLabel>
        <div class="col-sm-9">
          <CFormCheck v-model="form.wh" type="radio" name="wh" value="K1" label="K1" class="me-2" />
          <CFormCheck v-model="form.wh" type="radio" name="wh" value="K2" label="K2" />
        </div>
      </CRow>
      <CRow class="mb-3">
        <CFormLabel class="col-sm-3 col-form-label">Dãy</CFormLabel>
        <div class="col-sm-9"><CFormInput v-model="form.rack" placeholder="A/B/C..." /></div>
      </CRow>
      <CRow class="mb-3">
        <CFormLabel class="col-sm-3 col-form-label">Tầng</CFormLabel>
        <div class="col-sm-9"><CFormInput v-model.number="form.tier" type="number" min="1" /></div>
      </CRow>
      <CRow class="mb-3">
        <CFormLabel class="col-sm-3 col-form-label">Vị trí</CFormLabel>
        <div class="col-sm-9"><CFormInput v-model="form.slot" placeholder="vd: 1.1" /></div>
      </CRow>
      <CRow class="mb-3">
        <CFormLabel class="col-sm-3 col-form-label">Pack</CFormLabel>
        <div class="col-sm-9"><CFormInput v-model="form.pack" placeholder="Pallet/Box..." /></div>
      </CRow>
      <CRow>
        <CFormLabel class="col-sm-3 col-form-label">Part</CFormLabel>
        <div class="col-sm-9"><CFormInput v-model="form.part" placeholder="Mã linh kiện" /></div>
      </CRow>
    </CModalBody>
    <CModalFooter>
      <CButton color="secondary" @click="() => (showForm = false)">Huỷ</CButton>
      <CButton color="primary" @click="saveForm">Lưu</CButton>
    </CModalFooter>
  </CModal>

  <!-- Modal: Confirm delete -->
  <CModal alignment="center" :visible="showConfirm" @close="() => showConfirm = false" aria-labelledby="confirmDel">
    <CModalHeader><CModalTitle id="confirmDel">Xoá vị trí</CModalTitle></CModalHeader>
    <CModalBody>Bạn có chắc chắn muốn xoá bản ghi #{{ toDeleteId }}?</CModalBody>
    <CModalFooter>
      <CButton color="secondary" @click="() => (showConfirm = false)">Đóng</CButton>
      <CButton color="danger" @click="doDelete">Xoá</CButton>
    </CModalFooter>
  </CModal>

  <CCard>
    <CCardHeader class="d-flex align-items-center gap-2">
      <span class="fw-semibold">Layouts</span>
      <!-- <CBadge color="secondary" class="ms-2">TS PDC 2</CBadge> -->
      <div class="ms-auto d-flex gap-2">
        <CForm class="d-flex">
          <CFormInput v-model="q" type="search" placeholder="Tìm theo Part..." class="me-2" />
          <CButton type="button" color="success" variant="outline">Search</CButton>
        </CForm>
        <CButton color="success" variant="outline" @click="openCreate">
          Thêm vị trí <CIcon :icon="cilPlaylistAdd" class="ms-1" />
        </CButton>
      </div>
    </CCardHeader>

    <CCardBody>
      <CContainer fluid class="my-2">
        <CButtonGroup role="group" aria-label="warehouse">
          <CFormCheck v-model="activeWH" type="radio" :button="{ color: 'primary', variant: 'outline' }"
                      name="wh" value="K1" label="Kho 1" />
          <CFormCheck v-model="activeWH" type="radio" :button="{ color: 'primary', variant: 'outline' }"
                      name="wh" value="K2" label="Kho 2" />
        </CButtonGroup>
      </CContainer>

      <div class="table-responsive">
        <CTable small striped hover class="align-middle">
          <CTableHead class="sticky">
            <CTableRow>
              <CTableHeaderCell class="w-1">#</CTableHeaderCell>
              <CTableHeaderCell>Dãy</CTableHeaderCell>
              <CTableHeaderCell>Tầng</CTableHeaderCell>
              <CTableHeaderCell>Vị trí</CTableHeaderCell>
              <CTableHeaderCell>Pack</CTableHeaderCell>
              <CTableHeaderCell>Part</CTableHeaderCell>
              <CTableHeaderCell class="text-end">Thao tác</CTableHeaderCell>
            </CTableRow>
          </CTableHead>
          <CTableBody>
            <CTableRow v-for="r in paged" :key="r.id" :class="{ 'row-hit': qEff && r.part.toLowerCase().includes(qEff) }">
              <CTableDataCell>{{ r.id }}</CTableDataCell>
              <CTableDataCell>{{ r.rack }}</CTableDataCell>
              <CTableDataCell>{{ r.tier }}</CTableDataCell>
              <CTableDataCell>{{ r.slot }}</CTableDataCell>
              <CTableDataCell>{{ r.pack }}</CTableDataCell>
              <CTableDataCell>{{ r.part }}</CTableDataCell>
              <CTableDataCell class="text-end">
                <CButton size="sm" color="secondary" variant="ghost" class="me-1" @click="openEdit(r)">
                  <CIcon :icon="cilPencil" />
                </CButton>
                <CButton size="sm" color="danger" variant="ghost" @click="askDelete(r)">
                  <CIcon :icon="cilDelete" />
                </CButton>
              </CTableDataCell>
            </CTableRow>
            <CTableRow v-if="!paged.length">
              <CTableDataCell colspan="7" class="text-center text-body-secondary py-4">Không có dữ liệu</CTableDataCell>
            </CTableRow>
          </CTableBody>
        </CTable>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
        <small class="text-body-secondary">
          Tổng: {{ filtered.length }} bản ghi • Trang {{ currentPage }}/{{ pageCount }}
        </small>
        <div class="d-flex align-items-center gap-3">
          <div class="d-flex align-items-center gap-2">
            <span class="text-body-secondary">Hiển thị:</span>
            <select class="form-select form-select-sm" style="width:auto"
                    v-model.number="perPage" @change="setPage(1)">
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </div>
          <CPagination align="end" class="mb-0">
            <CPaginationItem href="#" :disabled="currentPage===1" @click.prevent="setPage(1)">&laquo;</CPaginationItem>
            <CPaginationItem href="#" :disabled="currentPage===1" @click.prevent="setPage(currentPage-1)">Prev</CPaginationItem>
            <CPaginationItem href="#" active>{{ currentPage }}</CPaginationItem>
            <CPaginationItem href="#" :disabled="currentPage===pageCount" @click.prevent="setPage(currentPage+1)">Next</CPaginationItem>
            <CPaginationItem href="#" :disabled="currentPage===pageCount" @click.prevent="setPage(pageCount)">&raquo;</CPaginationItem>
          </CPagination>
        </div>
      </div>
    </CCardBody>
  </CCard>
</template>

<style scoped>
.table-responsive { overflow-x: auto; }
thead.sticky { position: sticky; top: 0; z-index: 1; background: var(--cui-body-bg); }
.row-hit { outline: 2px solid #22c55e; background: rgba(34,197,94,.08); }
</style>