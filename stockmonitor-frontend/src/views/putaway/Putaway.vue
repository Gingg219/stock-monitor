<script setup>
import { ref, reactive } from 'vue'

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
  form.part_code = row.part_code
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

      <CButton color="success" class="mt-3" >
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

      <CTable hover small striped class="align-middle">
        <CTableHead>
          <CTableRow>
            <CTableHeaderCell>QR</CTableHeaderCell>
            <CTableHeaderCell>Part</CTableHeaderCell>
            <CTableHeaderCell>Số lượng</CTableHeaderCell>
            <CTableHeaderCell>Income</CTableHeaderCell>
            <CTableHeaderCell>Trạng thái</CTableHeaderCell>
          </CTableRow>
        </CTableHead>

        <CTableBody>
          <CTableRow v-for="row in tableAvailable" :key="row.id" style="cursor:pointer" @click="selectRow(row)">
            <CTableDataCell>{{ row.unit_code }}</CTableDataCell>
            <CTableDataCell>{{ row.part_name }}</CTableDataCell>
            <CTableDataCell>{{ row.qty }}</CTableDataCell>
            <CTableDataCell>{{ row.income_id }}</CTableDataCell>
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
    </CCardBody>
  </CCard>

  <CCard>
    <CCardHeader class="fw-semibold bg-secondary text-white">
      Storage Units (Shipped)
    </CCardHeader>
    <CCardBody>
      <CTable small striped hover class="align-middle">
        <CTableHead>
          <CTableRow>
            <CTableHeaderCell>QR</CTableHeaderCell>
            <CTableHeaderCell>Part</CTableHeaderCell>
            <CTableHeaderCell>Số lượng</CTableHeaderCell>
            <CTableHeaderCell>Vị trí</CTableHeaderCell>
            <CTableHeaderCell></CTableHeaderCell>
          </CTableRow>
        </CTableHead>

        <CTableBody>
          <CTableRow v-for="row in tableShipped" :key="row.id">
            <CTableDataCell>{{ row.unit_code }}</CTableDataCell>
            <CTableDataCell>{{ row.part_name }}</CTableDataCell>
            <CTableDataCell>{{ row.qty }}</CTableDataCell>
            <CTableDataCell>{{ row.slot_code }}</CTableDataCell>
            <CTableDataCell>
              <CButton size="sm" @click="openChange(row)">
                Đổi vị trí
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
    </CCardBody>
  </CCard>
  <CModal :visible="showChangeModal">
    <CModalHeader>Đổi vị trí</CModalHeader>

    <CModalBody>
      <CFormInput v-model="changeForm.unit_code" disabled />
      <CFormSelect v-model="changeForm.new_slot" label="Vị trí mới" />
    </CModalBody>

    <CModalFooter>
      <CButton color="secondary" @click="showChangeModal = false">Huỷ</CButton>
      <CButton color="primary">Xác nhận</CButton>
    </CModalFooter>
  </CModal>
</template>


<style scoped></style>