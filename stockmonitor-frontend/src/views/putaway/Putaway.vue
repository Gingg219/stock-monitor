<script setup>
import { ref, reactive, onMounted } from 'vue'
import { getAll, assignLoc, scanPutAway, changeLoc } from '@/api/storageUnits'
import { cilPencil } from '@coreui/icons'


onMounted(loadData)

async function loadData() {
  // (1) Available + Allocated
  const resAvailable = await getAll({
    status_in: ['available', 'allocated']
  })

  tableAvailable.value = resAvailable.data?.data?.data ?? []

  // (2) Shipped
  const resShipped = await getAll({
    status: 'shipped'
  })

  tableShipped.value = resShipped.data?.data?.data ?? []
}


// Hàm submit form
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

  await loadData()
}

// Hàm xác nhận đổi vị trí
async function confirmChange() {
  await changeLoc({
    unit_code: changeForm.unit_code,
    slot_id: changeForm.new_slot
  })

  showChangeModal.value = false
  await loadData()
}


const mode = ref('assign') // 'assign' | 'scan'

const form = reactive({
  unit_code: '',
  part_code: '',
  warehouse: null,
  rack: null,
  tier: null,
  slot: null,
  location_qr: ''
})

const tableAvailable = ref([]) // status: available | allocated
const filter1 = ref('')

// Hàm chọn dòng từ bảng (nếu có) để điền thông tin
function selectRow(row) {
  mode.value = 'assign'
  form.unit_code = row.unit_code
  form.part_code = row.part.part_no
  form.qty = Math.floor(row.qty)
}

const tableShipped = ref([])
const showChangeModal = ref(false)
const changeForm = reactive({
  unit_code: '',
  new_slot: null
})

// Mở modal đổi vị trí
function openChange(row) {
  changeForm.unit_code = row.unit_code
  showChangeModal.value = true
}
</script>

<template>
  <CCard class="mb-3">
    <CCardHeader>
      <CButtonGroup>
        <CButton :color="mode === 'assign' ? 'primary' : 'secondary'" @click="mode = 'assign'">
          Nhập vị trí
        </CButton>
        <CButton :color="mode === 'scan' ? 'primary' : 'secondary'" @click="mode = 'scan'">
          Nhập kho
        </CButton>
      </CButtonGroup>
    </CCardHeader>

    <CCardBody>
      <!-- MODE 1: ASSIGN LOCATION -->
      <div v-if="mode === 'assign'">
        <CRow>
          <CCol md="3">
            <CFormInput v-model="form.unit_code" label="QR parts" />
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
            <CFormSelect label="Kho" v-model="form.warehouse" />
          </CCol>
          <CCol md="2">
            <CFormSelect label="Giá" v-model="form.rack" />
          </CCol>
          <CCol md="2">
            <CFormSelect label="Tầng" v-model="form.tier" />
          </CCol>
          <CCol md="2">
            <CFormSelect label="Vị trí" v-model="form.slot" />
          </CCol>
        </CRow>
      </div>
      <!-- MODE 2: SCAN -->
      <CRow v-else>
        <CCol md="3">
          <CFormInput v-model="form.unit_code" label="QR parts" />
        </CCol>
        <CCol md="3">
          <CFormInput v-model="form.location_qr" label="QR vị trí" />
        </CCol>
      </CRow>

      <CButton color="success" class="mt-3" @click="submit">
        Xác nhận
      </CButton>
    </CCardBody>
  </CCard>
  <CCard class="mb-3">
    <CCardHeader class="fw-semibold bg-secondary text-white">
      Storage Units (Available / Allocated)
    </CCardHeader>

    <CCardBody>
      <CFormInput v-model="filter1" placeholder="Lọc theo QR / Part" class="mb-2" />
      <div class="table-scroll">
        <CTable hover small striped class="align-middle">
          <CTableHead>
            <CTableRow>
              <CTableHeaderCell>QR</CTableHeaderCell>
              <CTableHeaderCell>Part</CTableHeaderCell>
              <CTableHeaderCell>Số lượng</CTableHeaderCell>
              <CTableHeaderCell>Income ID</CTableHeaderCell>
              <CTableHeaderCell>Trạng thái</CTableHeaderCell>
            </CTableRow>
          </CTableHead>

          <CTableBody>
            <CTableRow v-for="row in tableAvailable" :key="row.id" style="cursor:pointer" @click="selectRow(row)">
              <CTableDataCell>{{ row.unit_code }}</CTableDataCell>
              <CTableDataCell>{{ row.part.part_no }}</CTableDataCell>
              <CTableDataCell>{{ Math.floor(row.qty) }}</CTableDataCell>
              <CTableDataCell>{{ row.income_lines.income_id }}</CTableDataCell>
              <CTableDataCell>
                <CBadge :color="row.status === 'available' ? 'warning' : 'info'">
                  {{ row.status }}
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
              <CTableHeaderCell>QR</CTableHeaderCell>
              <CTableHeaderCell>Part</CTableHeaderCell>
              <CTableHeaderCell>Số lượng</CTableHeaderCell>
              <CTableHeaderCell>Vị trí</CTableHeaderCell>
              <CTableHeaderCell>Trạng thái</CTableHeaderCell>
              <CTableHeaderCell></CTableHeaderCell>
            </CTableRow>
          </CTableHead>

          <CTableBody>
            <CTableRow v-for="row in tableShipped" :key="row.id">
              <CTableDataCell>{{ row.unit_code }}</CTableDataCell>
              <CTableDataCell>{{ row.part.part_no }}</CTableDataCell>
              <CTableDataCell>{{ Math.floor(row.qty) }}</CTableDataCell>
              <CTableDataCell>{{ row.location_code }}</CTableDataCell>
              <CTableDataCell>
                <CBadge color="success">
                  {{ row.status }}
                </CBadge>
              </CTableDataCell>
              <CTableDataCell>
                <CButton
                  size="sm"
                  color="secondary"
                  variant="ghost"
                  class="me-1"
                  @click="openChange(row)"
                >
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
  <CModal :visible="showChangeModal" 
    alignment="center"
    backdrop="static">
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
  max-height: 380px;   /* chỉnh cao thấp tuỳ bạn */
  overflow-y: auto;
  border: 1px solid #dee2e6;
  border-radius: 6px;
}
.table-scroll thead th {
  position: sticky;
  top: 0;
  background: #f8f9fa;   /* màu nền header */
  z-index: 2;
  box-shadow: inset 0 -1px 0 #dee2e6;
}

.table-scroll table {
  table-layout: fixed;
}
</style>