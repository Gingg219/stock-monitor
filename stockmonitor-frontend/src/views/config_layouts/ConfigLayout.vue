<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import {
  CCard, CCardBody, CCardHeader, CButton, CContainer, CForm, CFormInput,
  CButtonGroup, CFormCheck, CTable, CTableHead, CTableBody, CTableRow, CTableHeaderCell, CTableDataCell,
  CPagination, CPaginationItem, CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter,
  CRow, CFormLabel
} from '@coreui/vue'
import CIcon from '@coreui/icons-vue'
import { cilPencil, cilDelete, cilPlaylistAdd } from '@coreui/icons'

// API gọi về backend
import { getLayouts, createLayout, updateLayout, deleteLayout } from '@/api/layouts'

/* ------------------ State chính ------------------ */

// danh sách record hiển thị trong bảng (đã flatten)
const rows = ref([])
// raw data từ API (nếu sau này cần dùng)
const layouts = ref([])

let nextId = 1
const loading = ref(false)
const error = ref('')

/**
 * Flatten dữ liệu từ API:
 * warehouses[] -> racks[] -> tiers[] -> slots[] -> fixed_locations[]
 * thành dạng:
 * { id, wh, rack, tier, slot, pack, part, backend_id }
 */

const warehouseCodes = computed(() => {
  const set = new Set()
  layouts.value.forEach(wh => {
    if (wh.code) set.add(wh.code)
  })
  return Array.from(set)
})


// sau khi load dữ liệu, nếu activeWH chưa set thì cho nó = kho đầu tiên
onMounted(async () => {
  try {
    loading.value = true
    const res = await getLayouts()
    const data = res?.data ?? []


    layouts.value = data
    rows.value = flattenLayouts(data)
    nextId = rows.value.length + 1


    if (!activeWH.value && data.length) {
      activeWH.value = data[0].code
    }
  } catch (err) {
    
  } finally {
    loading.value = false
  }
})

function flattenLayouts(data) {
  const result = []
  let idCounter = 1

  data?.forEach((wh) => {
    const whCode = wh.code
    const mode = wh.mode // 'fixed' hoặc kiểu khác

    wh.racks?.forEach((rack) => {
      rack.tiers?.forEach((tier) => {
        tier.slots?.forEach((slot) => {
          const pack = slot.allowed_unit || ''

          if (mode === 'fixed') {
            // ===== KHO 2: dùng fixed_locations =====
            const fxList = slot.fixed_locations || []

            if (fxList.length) {
              fxList.forEach((fx) => {
                const part = fx.parts || null

                result.push({
                  id: idCounter++,
                  backend_id: fx.id ?? null,      // id fixed_location trong DB
                  wh: whCode,
                  rack: rack.code,
                  tier: tier.level_no,
                  slot: slot.code,
                  pack,
                  part: part?.part_no || '',
                  mode,
                  current_qty: 0,                 // optional, K2 không dùng
                })
              })
            } else {
              // slot không có fixed_location nào -> vẫn hiển thị dòng trống
              result.push({
                id: idCounter++,
                backend_id: null,
                wh: whCode,
                rack: rack.code,
                tier: tier.level_no,
                slot: slot.code,
                pack,
                part: '',
                mode,
                current_qty: 0,
              })
            }
          } else {
            // ===== KHO 1: dùng current_part hiện tại trên slot =====
            const currentPart = slot.current_part || null

            result.push({
              id: idCounter++,
              backend_id: null,                 // không phải fixed_location
              wh: whCode,
              rack: rack.code,
              tier: tier.level_no,
              slot: slot.code,
              pack,
              part: currentPart?.part_no || '',
              mode,
              current_qty: slot.current_qty ?? 0,
            })
          }
        })
      })
    })
  })

  return result
}

/* ------------------ State cho UI ------------------ */

const activeWH = ref('K1')       // kho đang chọn
const q = ref('')               // ô search thô
const qEff = ref('')            // search đã debounce/lowercase

const currentPage = ref(1)
const perPage = ref(10)

const showForm = ref(false)
const showConfirm = ref(false)

const editingId = ref(null)         // id dòng hiển thị
const editingBackendId = ref(null)  // id record trong DB

const toDeleteId = ref(null)
const toDeleteBackendId = ref(null)

const form = reactive({
  wh: 'K1',
  rack: '',
  tier: 1,
  slot: '',
  pack: '',
  part: '',
})

/* ------------------ Reactions ------------------ */

// đổi kho -> quay về trang 1
watch(activeWH, () => { currentPage.value = 1 })

// debounce search
let debounceTimer = null
watch(q, v => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    qEff.value = (v || '').trim().toLowerCase()
  }, 250)
})

/* ------------------ Derived data (filter, paging) ------------------ */

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

// khi filtered/perPage đổi, giữ currentPage trong khoảng hợp lệ
watch([filtered, perPage], () => setPage(currentPage.value))

const paged = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return filtered.value.slice(start, start + perPage.value)
})

/* ------------------ Actions: Create / Edit / Delete ------------------ */

function openCreate() {
  editingId.value = null
  editingBackendId.value = null

  Object.assign(form, {
    wh: activeWH.value,
    rack: '',
    tier: 1,
    slot: '',
    pack: '',
    part: '',
  })

  showForm.value = true
}

function openEdit(row) {
  editingId.value = row.id
  console.log(row.id);
  console.log('0000');
  console.log(row.backend_id);
  
  editingBackendId.value = row.backend_id ?? null

  Object.assign(form, {
    wh: row.wh,
    rack: row.rack,
    tier: row.tier,
    slot: row.slot,
    pack: row.pack,
    part: row.part,
  })

  showForm.value = true
}

async function saveForm() {
  if (!form.rack || !form.slot) {
    alert('Vui lòng nhập đầy đủ Dãy & Vị trí')
    return
  }

  const payload = {
    wh: form.wh,
    rack: form.rack,
    tier: form.tier,
    slot: form.slot,
    pack: form.pack,
    part: form.part,
  }

  try {
    // UPDATE: đã có record fixed_location trong DB
    if (editingBackendId.value) {
      
      await updateLayout(editingBackendId.value, payload)

      const idx = rows.value.findIndex(r => r.id === editingId.value)
      if (idx >= 0) {
        rows.value[idx] = {
          id: editingId.value,
          backend_id: editingBackendId.value,
          ...payload,
        }
      }
    }
    // CREATE: tạo fixed_location mới
    else {
      const res = await createLayout(payload)
      const newBackendId = res.data?.id ?? null

      rows.value.unshift({
        id: nextId++,
        backend_id: newBackendId,
        ...payload,
      })
    }

    showForm.value = false
  } catch (err) {
    console.error(err)
    alert('Lưu thất bại!')
  }
}

function askDelete(row) {
  toDeleteId.value = row.id
  toDeleteBackendId.value = row.backend_id ?? null
  showConfirm.value = true
}

async function doDelete() {
  try {
    if (toDeleteBackendId.value) {
      await deleteLayout(toDeleteBackendId.value)
    }
    rows.value = rows.value.filter(r => r.id !== toDeleteId.value)
  } catch (err) {
    console.error(err)
    alert('Xoá thất bại!')
  } finally {
    showConfirm.value = false
  }
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
          <CFormCheck
            v-for="code in warehouseCodes"
            :key="code"
            v-model="activeWH"
            type="radio"
            :button="{ color: 'primary', variant: 'outline' }"
            name="wh"
            :value="code"
            :label="code"
          />
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