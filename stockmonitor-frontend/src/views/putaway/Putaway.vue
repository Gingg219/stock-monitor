<script setup>
import { ref, reactive, onMounted, watch, nextTick, defineExpose } from 'vue'
import { getAll, assignLoc, scanPutAway, changeLoc } from '@/api/storageUnits'
import { fetchWarehouses, fetchRacks, fetchTiers, fetchSlots } from '@/api/locations'
import { cilPencil } from '@coreui/icons'
import { useToast } from '@/composables/useToast'

//use toast
const toast = useToast()

/* ======================
  DATA LOAD
====================== */
onMounted(() => {
  loadData()
  loadWarehouses()
})

async function loadData() {
  const resAvailable = await getAll({ status_in: ['available', 'allocated'] })
  tableAvailable.value = resAvailable.data?.data?.data ?? []

  const resShipped = await getAll({ status: 'shipped' })
  tableShipped.value = resShipped.data?.data?.data ?? []
}

/* ======================
  FORM & STATE
====================== */
const mode = ref('assign')

const form = reactive({
  unit_code: '',
  part_code: '',
  qty: null,
  warehouse: null,
  rack: null,
  tier: null,
  slot: null,
  location_qr: ''
})

const tableAvailable = ref([])
const tableShipped = ref([])
const filter1 = ref('')

/* ======================
  LOCATION DATA
====================== */
const warehouses = ref([])
const racks = ref([])
const tiers = ref([])
const slots = ref([])

async function loadWarehouses() {
  const res = await fetchWarehouses()
  warehouses.value = res.data ?? []
}

async function loadRacks() {
  if (!form.warehouse) return
  const res = await fetchRacks({ warehouse_id: form.warehouse })
  racks.value = res.data ?? []
}

async function loadTiers() {
  if (!form.rack) return
  const res = await fetchTiers({ rack_id: form.rack })
  tiers.value = res.data ?? []
}

async function loadSlots() {
  if (!form.tier) return
  const res = await fetchSlots({ tier_id: form.tier })
  slots.value = res.data ?? []
}

/* ======================
  WATCH CASCADE
====================== */
watch(() => form.warehouse, () => {
  form.rack = form.tier = form.slot = null
  racks.value = tiers.value = slots.value = []
  loadRacks()
})

watch(() => form.rack, () => {
  form.tier = form.slot = null
  tiers.value = slots.value = []
  loadTiers()
})

watch(() => form.tier, () => {
  form.slot = null
  slots.value = []
  loadSlots()
})

/* ======================
  ACTIONS
====================== */
async function submit() {
  if (mode.value === 'assign') {
    await assignLoc({
      unit_code: form.unit_code,
      slot_id: form.slot
    })
  } else {
    await scanPutAway({
      unit_code: form.unit_code,
      location_qr: form.location_qr
    })
  }
  toast.success(`Thêm vị trí thành công`, '')
  await loadData()
}

const selectedRowId = ref(null)

async function selectRow(row) {
   clearForm()

  mode.value = 'assign'
  form.unit_code = row.unit_code
  form.part_code = row.part.part_no
  form.qty = Math.floor(row.qty)

  console.log('CLICK ROW ID:', row.id)

  selectedRowId.value = row.id

  console.log('SELECTED:', selectedRowId.value)
  
  scrollToForm()

  // Nếu đã có vị trí → fill cascade
  if (row.slot) {
    const slot = row.slot
    const tier = slot.tier
    const rack = tier.rack
    const warehouse = rack.warehouse

    form.warehouse = warehouse.id
    await loadRacks()

    form.rack = rack.id
    await loadTiers()

    form.tier = tier.id
    await loadSlots()

    form.slot = slot.id
  }

  // scroll + highlight (bên dưới)
  highlightRow(row.id)
}

const formRef = ref(null)


function clearForm() {
  form.unit_code = '',
  form.part_code = '',
  form.warehouse = null
  form.rack = null
  form.tier = null
  form.slot = null
  form.qty = null
}

function scrollToForm() {
   window.scrollTo({
    top: 0,
    behavior: 'smooth'
  })
}

function highlightRow(id) {
  selectedRowId.value = id
}

/* ======================
  CHANGE LOCATION MODAL
====================== */
const showChangeModal = ref(false)
const changeForm = reactive({
  unit_code: '',
  new_slot: null
})

function openChange(row) {
  changeForm.unit_code = row.unit_code
  showChangeModal.value = true
}

async function confirmChange() {
  await changeLoc({
    unit_code: changeForm.unit_code,
    slot_id: changeForm.new_slot
  })
  showChangeModal.value = false
  await loadData()
}

/* ======================
  SCAN HANDLER
====================== */
const warehouseRef = ref()
const locationQrRef = ref()

const warehouseSelectRef = ref(null)
const unitInputRef = ref(null)

// async function focusUnitInput() {
//     // Luôn chờ nextTick để đảm bảo component đã render và ref đã được gán giá trị
//     await nextTick();

//     if (!unitInputRef.value) {
//         // Debug: Nếu vẫn null, có nghĩa là có lỗi trong cú pháp gán ref hoặc component chưa tồn tại
//         console.error("Focus failed: unitInputRef.value is null.");
//         return;
//     }

//     const componentInstance = unitInputRef.value; 

//     // 1. THỬ CÁCH CHUẨN: Gọi phương thức focus() tích hợp (nếu có)
//     if (typeof componentInstance.focus === 'function') {
//         componentInstance.focus();
//         return;
//     }

//     // 2. THỬ TRUY CẬP DOM NATIVE (dùng $el, nếu thư viện vẫn hỗ trợ)
//     // $el là DOM Element gốc của component wrapper.
//     // Nếu nó tồn tại, ta dùng querySelector để tìm thẻ <input> bên trong.
//     if (componentInstance.$el && typeof componentInstance.$el.querySelector === 'function') {
//         const inputElement = componentInstance.$el.querySelector('input');
        
//         if (inputElement) {
//             inputElement.focus();
//             return;
//         }
//     }
    
//     // Nếu không có cách nào hoạt động, in ra đối tượng để tìm kiếm thủ công
//     console.error("Focus failed: Cannot find native input element in instance.", componentInstance);
// }

async function focusUnitInput() {
  await nextTick()

  const el = unitInputRef.value?.$el?.querySelector('input')

  if (!el) {
    console.warn('Không tìm thấy input DOM')
    return
  }

  el.focus()
}

async function onScanUnit() {
  if (!form.unit_code) return

  if (mode.value === 'assign') {
    await handleAssignScan()
  } else {
    await handleScanMode()
  }
}

async function handleAssignScan() {
  const row = tableAvailable.value.find(
    r => r.unit_code === form.unit_code
  )

  if (!row) {
    toast.error('QR không tồn tại hoặc đã nhập kho')
    resetUnitInput()
    return
  }

  // fill form
  form.part_code = row.part.part_no
  form.qty = Math.floor(row.qty)

  // highlight row
  selectedRowId.value = row.id

  // scroll top
  window.scrollTo({ top: 0, behavior: 'smooth' })

  // focus kho
  await nextTick()
  // warehouseSelectRef?.focus()
}

async function handleScanMode() {
  try {
    await getAll({
      search: form.unit_code,
      status: 'allocated'
    })

    toast.success('QR hợp lệ, quét vị trí')

    await nextTick()
    // focusInput(locationQrRef)

  } catch (e) {
    toast.error('QR không hợp lệ hoặc chưa gán vị trí')
    resetUnitInput()
  }
}

function resetUnitInput() {
  form.unit_code = ''
  nextTick(() => unitInputRef.value?.focus())
}

watch(mode, async (val) => {
  clearForm()
  selectedRowId.value = null

  if (val === 'assign') {
    await nextTick()
    focusUnitInput()
  }
})




</script>

<template>
  <CCard class="mb-3" ref="formRef">
    <CCardHeader>
      <CButtonGroup>
        <CButton :color="mode === 'assign' ? 'primary' : 'secondary'" @click="mode = 'assign'; clearForm()">
          Nhập vị trí
        </CButton>
        <CButton :color="mode === 'scan' ? 'primary' : 'secondary'" @click="mode = 'scan'; clearForm()">
          Nhập kho
        </CButton>
      </CButtonGroup>
    </CCardHeader>

    <CCardBody>
      <!-- MODE 1: ASSIGN LOCATION -->
      <div v-if="mode === 'assign'">
        <CRow>
          <CCol md="3">
            <CFormInput
              v-model="form.unit_code"
              label="QR parts"
              ref="unitInputRef"
              @keydown.enter.prevent="onScanUnit"
            />
          </CCol>
          <CCol md="3">
            <CFormInput v-model="form.part_code" label="Part code" />
          </CCol>
          <CCol md="2">
            <CFormInput v-model="form.qty" label="Số lượng" />
          </CCol>
        </CRow>
        <CRow>
          <CCol md="2">
            <CFormSelect label="Kho" v-model="form.warehouse" @focus="loadWarehouses()">
              <option value="">-- Chọn kho --</option>
              <option v-for="w in warehouses" :key="w.id" :value="w.id">
                {{ w.code }}
              </option>
            </CFormSelect>
          </CCol>

          <CCol md="2">
            <CFormSelect label="Giá" v-model="form.rack" :disabled="!form.warehouse">
              <option value="">-- Chọn giá --</option>
              <option v-for="r in racks" :key="r.id" :value="r.id">
                {{ r.code }}
              </option>
            </CFormSelect>
          </CCol>

          <CCol md="2">
            <CFormSelect label="Tầng" v-model="form.tier" :disabled="!form.rack">
              <option value="">-- Chọn tầng --</option>
              <option v-for="t in tiers" :key="t.id" :value="t.id">
                {{ t.level_no }}
              </option>
            </CFormSelect>
          </CCol>

          <CCol md="2">
            <CFormSelect label="Vị trí" v-model="form.slot" :disabled="!form.tier">
              <option value="">-- Chọn vị trí --</option>
              <option v-for="s in slots" :key="s.id" :value="s.id">
                {{ s.code }}
              </option>
            </CFormSelect>
          </CCol>
        </CRow>

      </div>
      <!-- MODE 2: SCAN -->
      <CRow v-else>
        <CCol md="3">
          <CFormInput
              v-model="form.unit_code"
              label="QR parts"
              ref="unitInputRef"
              @keydown.enter.prevent="onScanUnit"
          />
        </CCol>
        <CCol md="3">
          <CFormInput
            v-if="mode === 'scan'"
            :select-ref="el => warehouseSelectRef = el"
            v-model="form.location_qr"
            label="QR vị trí"
            @keydown.enter.prevent="submit"
          />
        </CCol>
      </CRow>

      <CButton color="success" class="mt-3" @click="submit">
        Xác nhận
      </CButton>
    </CCardBody>
  </CCard>
  <CCard class="mb-3" v-if="mode === 'assign'">
    <CCardHeader class="fw-semibold bg-secondary text-white">
      Storage Units (Available / Allocated)
    </CCardHeader>

    <CCardBody>
      <CFormInput v-model="filter1" placeholder="Lọc theo QR / Part" class="mb-2" />
      <div class="table-scroll">
        <CTable hover small striped class="align-middle">
          <CTableHead>
            <CTableRow>
              <CTableHeaderCell>Income</CTableHeaderCell>
              <CTableHeaderCell>QR</CTableHeaderCell>
              <CTableHeaderCell>Part</CTableHeaderCell>
              <CTableHeaderCell>Số lượng</CTableHeaderCell>
              <CTableHeaderCell>Vị trí</CTableHeaderCell>
              <CTableHeaderCell>Ngày nhận</CTableHeaderCell>
              <CTableHeaderCell>Trạng thái</CTableHeaderCell>
            </CTableRow>
          </CTableHead>

          <CTableBody>
            <CTableRow v-for="row in tableAvailable" 
              :key="row.id" 
              :class="{ 'row-selected': row.id === selectedRowId }"
              style="cursor:pointer"
              @click="selectRow(row)"
            >
              <CTableDataCell>{{ row.income_lines.income.income_no }}</CTableDataCell>
              <CTableDataCell>{{ row.unit_code }}</CTableDataCell>
              <CTableDataCell>{{ row.part.part_no }}</CTableDataCell>
              <CTableDataCell>{{ Math.floor(row.qty) }}</CTableDataCell>
              <CTableDataCell>{{ row.location_code === null ? '#NA' : row.location_code }}</CTableDataCell>
              <CTableDataCell>{{ row.income_lines.income.received_at.slice(0, 10) }}</CTableDataCell>
              <CTableDataCell>
                <CBadge :color="row.status === 'available' ? 'warning' : 'info'">
                  {{ row.status === 'available' ? 'Chờ thêm vị trí' : 'Chờ nhập kho' }}
                </CBadge>
              </CTableDataCell>
            </CTableRow>
            <CTableRow v-if="!tableAvailable.length">
              <CTableDataCell colspan="6" class="text-center text-body-secondary py-4">
                Không có dữ liệu
              </CTableDataCell>
            </CTableRow>
          </CTableBody>
        </CTable>
      </div>
    </CCardBody>
  </CCard>

  <CCard>
    <CCardHeader class="fw-semibold bg-secondary text-white">
      Storage Units (Shipped)
    </CCardHeader>
    <CCardBody>
      <div class="table-scroll">
        <CTable small striped hover class="align-middle">
          <CTableHead>
            <CTableRow>
              <CTableHeaderCell>Income</CTableHeaderCell>
              <CTableHeaderCell>QR</CTableHeaderCell>
              <CTableHeaderCell>Part</CTableHeaderCell>
              <CTableHeaderCell>Số lượng</CTableHeaderCell>
              <CTableHeaderCell>Vị trí</CTableHeaderCell>
              <CTableHeaderCell>Ngày nhận</CTableHeaderCell>
              <CTableHeaderCell>Trạng thái</CTableHeaderCell>
              <CTableHeaderCell></CTableHeaderCell>
            </CTableRow>
          </CTableHead>

          <CTableBody>
            <CTableRow v-for="row in tableShipped" :key="row.id">
              <CTableDataCell>{{ row.income_lines.income.income_no }}</CTableDataCell>
              <CTableDataCell>{{ row.unit_code }}</CTableDataCell>
              <CTableDataCell>{{ row.part.part_no }}</CTableDataCell>
              <CTableDataCell>{{ Math.floor(row.qty) }}</CTableDataCell>
              <CTableDataCell>{{ row.location_code }}</CTableDataCell>
              <CTableDataCell>{{ row.income_lines.income.received_at.slice(0, 10) }}</CTableDataCell>
              <CTableDataCell>
                <CBadge color="success">
                  {{ row.status === 'shipped' ? 'Đã nhập kho' : '' }}
                </CBadge>
              </CTableDataCell>
              <CTableDataCell>
                <CButton size="sm" color="secondary" variant="ghost" class="me-1" @click="openChange(row)">
                  <CIcon :icon="cilPencil" />
                </CButton>
              </CTableDataCell>
            </CTableRow>
            <CTableRow v-if="!tableAvailable.length">
              <CTableDataCell colspan="6" class="text-center text-body-secondary py-4">
                Không có dữ liệu
              </CTableDataCell>
            </CTableRow>
          </CTableBody>
        </CTable>
      </div>
    </CCardBody>
  </CCard>
  <CModal :visible="showChangeModal" alignment="center" backdrop="static">
    <CModalHeader :close-button="false">Đổi vị trí</CModalHeader>

    <CModalBody>
      <CFormInput v-model="changeForm.unit_code" disabled />
      <CFormSelect v-model="changeForm.new_slot" label="Vị trí mới" />
    </CModalBody>

    <CModalFooter>
      <CButton color="secondary" @click="showChangeModal = false">Huỷ</CButton>
      <CButton color="primary" @click="confirmChange">Xác nhận</CButton>
    </CModalFooter>
  </CModal>
</template>


<style scoped>
.table-scroll {
  max-height: 380px;
  /* chỉnh cao thấp tuỳ bạn */
  overflow-y: auto;
  border: 1px solid #dee2e6;
  border-radius: 6px;
}

.table-scroll thead th {
  position: sticky;
  top: 0;
  background: #f8f9fa;
  /* màu nền header */
  z-index: 2;
  box-shadow: inset 0 -1px 0 #dee2e6;
}

.table-scroll table {
  table-layout: fixed;
}

/* highlight row */
:deep(.row-selected) {
  background-color: #e6f4ff !important;
}

/* giữ màu khi hover */
:deep(.row-selected:hover) {
  background-color: #d0ebff !important;
}

</style>