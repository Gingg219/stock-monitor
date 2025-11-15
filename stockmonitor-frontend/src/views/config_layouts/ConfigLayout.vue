<script setup>
import { getLayouts } from '@/api/layouts'
import { ref, reactive, computed, watch, onMounted } from 'vue'
import {
  CCard, CCardBody, CCardHeader, CButton, CContainer, CForm, CFormInput,
  CButtonGroup, CFormCheck, CTable, CTableHead, CTableBody, CTableRow, CTableHeaderCell, CTableDataCell,
  CPagination, CPaginationItem, CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter,
  CRow, CFormLabel
} from '@coreui/vue'
import CIcon from '@coreui/icons-vue'
import { cilPencil, cilDelete, cilPlaylistAdd } from '@coreui/icons'

/* ------------------ State ------------------ */

// danh sách record hiển thị trong bảng (đã flatten)
const rows = ref([])

// nếu muốn giữ lại raw layouts để dùng chỗ khác
const layouts = ref([])

let nextId = 1
const loading = ref(false)
const error = ref('')

/**
 * Flatten dữ liệu từ API:
 * warehouses[] -> racks[] -> tiers[] -> slots[]
 * thành dạng:
 * { id, wh, rack, tier, slot, pack, part }
 */
function flattenLayouts(data) {
  const result = []
  let idCounter = 1

  data?.forEach(wh => {
    wh.racks?.forEach(rack => {
      rack.tiers?.forEach(tier => {
        tier.slots?.forEach(slot => {
          result.push({
            id: idCounter++,
            wh: wh.code,                 // K1 / K2
            rack: rack.code,             // A / B / ...
            tier: tier.level_no,         // số tầng
            slot: slot.code,             // A-1 / A-2 ...
            pack: slot.allowed_unit || '', // 'box' / 'pallet' ...
            // nếu backend có field khác thì đổi lại ở đây
            part: slot.part_code || '',  // tạm thời để trống nếu chưa có
          })
        })
      })
    })
  })

  return result
}

// load dữ liệu khi component mount
onMounted(async () => {
  try {
    loading.value = true
    const res = await getLayouts()       // axios response
    const data = res?.data ?? []         // layouts thực tế

    console.log('Get layouts thành công:', data)
    layouts.value = data
    rows.value = flattenLayouts(data)
    nextId = rows.value.length + 1
  } catch (err) {
    console.error(err)
    error.value = 'Get layouts thất bại'
  } finally {
    loading.value = false
  }
})

/* ------------------ State cho UI ------------------ */
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