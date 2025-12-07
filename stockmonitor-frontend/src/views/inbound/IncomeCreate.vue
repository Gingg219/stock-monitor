<script setup>
import { ref, reactive, nextTick, computed, onMounted } from 'vue'
import {
  CCard, CCardHeader, CCardBody,
  CButton, CRow, CCol,
  CForm, CFormInput, CFormSelect,
  CTable, CTableHead, CTableBody, CTableRow, CTableHeaderCell, CTableDataCell,
  CPagination, CPaginationItem,
} from '@coreui/vue'
import CIcon from '@coreui/icons-vue'
import { cilPencil, cilTrash, cilPlus, cilSave, cilQrCode } from '@coreui/icons'

import IncomeQrModal from '@/views/inbound/components/IncomeQRModal.vue'
import { useToast } from '@/composables/useToast'
import { storeIncome ,fetchIncomes, fetchIncome } from '@/api/incomes'
// import dayjs from 'dayjs'

const toast = useToast()
/* ====== Mock options (sau này load từ API) ====== */
const parts = ['QK2-0001-000', 'QK2-0002-000', 'QK2-0003-000']

/* ====== Income header (form nhập trên) ====== */
const form = reactive({
  warehouse_id: 1,
})

/* ====== Income lines (form nhập trên) ====== */
const lines = ref([])

function makeKey() { return `k_${Date.now()}_${Math.floor(Math.random()*10000)}` }

function addLine(){
  lines.value.push({
    _key: makeKey(),
    id: null,
    part_no: '',
    lot_no: '',
    vendor_code: '',
    qty_total: null,
    expiry: null
  })
}
function removeLine(index) {
  console.log('removeLine called with index=', index)
  // normalize
  index = Number(index)
  if (!Number.isFinite(index) || index < 0 || index >= lines.value.length) {
    console.warn('Invalid index to removeLine:', index)
    return
  }
  lines.value.splice(index, 1)
  // debug view
  console.log('lines after splice:', JSON.parse(JSON.stringify(lines.value)))
}


/* ====== Lịch sử nhập (bảng phía dưới) ====== */
// mỗi record = 1 chứng từ nhập, gồm header + lines
const incomes = ref([])              // list lịch sử
const historyRef = ref(null)         // dùng scroll xuống bảng

/* ====== Save & “nhảy xuống dưới” ====== */

const saveLabel = computed(() => (editingId.value ? 'Update' : 'Save'))
const saving = ref(false)

if (!lines.value.length) addLine()

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: null,
  to: null,
  next_page_url: null,
  prev_page_url: null,
})

const pageNumbers = computed(() => {
  const total = pagination.value.last_page || 1
  const current = pagination.value.current_page || 1
  const delta = 2 // hiển thị current ± delta
  const start = Math.max(1, current - delta)
  const end = Math.min(total, current + delta)
  const arr = []
  for (let i = start; i <= end; i++) arr.push(i)
  // optional: nếu bạn muốn show 1 và last luôn, add logic here
  return arr
})

//Fetch incomes list from API
async function loadIncomes(page = 1, perPage = pagination.value.per_page) {
  try {
    const res = await fetchIncomes({ page, per_page: perPage })
    // Support both: res.data = paginateObject  OR res.data = { success: true, data: paginateObject }
    const payload = res.data?.data ?? res.data
    // payload now should be paginate object: { current_page, data: [...], last_page, per_page, total, ... }

    // items array
    const items = payload?.data ?? []
    incomes.value = items
    incomes.value = items.map(i => ({
      ...i,
      total_qty: (i.total_qty ?? (Array.isArray(i.income_lines) ? i.income_lines.reduce((s,x)=> s + (Number(x.qty_total)||0),0) : 0)),
    }))

    // fill pagination
    pagination.value.current_page = payload?.current_page ?? 1
    pagination.value.last_page = payload?.last_page ?? 1
    pagination.value.per_page = payload?.per_page ?? perPage
    pagination.value.total = payload?.total ?? items.length
    pagination.value.from = payload?.from ?? null
    pagination.value.to = payload?.to ?? null
    pagination.value.next_page_url = payload?.next_page_url ?? null
    pagination.value.prev_page_url = payload?.prev_page_url ?? null

  } catch (err) {
    console.error('Lỗi tải danh sách chứng từ nhập:', err)
    toast.error('Lỗi tải danh sách chứng từ nhập', 'Lỗi')
  }
}
onMounted(() => loadIncomes())


//Page change functions
function goToPage(p) {
  if (!p || p < 1 || p > pagination.value.last_page) return
  loadIncomes(p, pagination.value.per_page)
}

function changePerPage(n) {
  pagination.value.per_page = Number(n)
  loadIncomes(1, pagination.value.per_page) // quay về trang 1 khi đổi page size
}

//Fech single income by ID from API
async function loadIncome(id) {
  try {
    const res = await fetchIncome(id)
    return res.data || res // bảo toàn nếu axios wrapper trả .data hoặc trực tiếp
  } catch (err) {
    console.error('Lỗi tải chứng từ nhập:', err)
    toast.error('Lỗi tải chứng từ nhập', 'Lỗi')
    return null
  }
}

//Create or Update income function
async function saveIncome () {
  // validate header + lines
  if (!form.vendor && !form.invoice_no && !form.income_no) {
  }

  if (!lines.value.length) {
    toast.error('Vui lòng thêm ít nhất 1 dòng hàng', 'Lỗi')
    return
  }

  const invalidLine = lines.value.some(l => !l.part_no || !l.lot_no || !l.vendor_code || (Number(l.qty_total) <= 0))
  if (invalidLine) {
    toast.error('Mỗi dòng phải có Part, Lot, Vendor và Qty > 0', 'Lỗi')
    return
  }

  const total = lines.value.reduce((s, l) => s + (Number(l.qty_total) || 0), 0)
  if (total <= 0) {
    toast.error('Tổng qty phải lớn hơn 0', 'Lỗi')
    return
  }

  // build payload theo shape backend mong muốn
  const payload = {
     id: editingId.value ?? null,
    warehouse_id: form.warehouse_id,
    income_no: form.income_no,
    invoice_no: form.invoice_no,
    date: form.date,
    lines: lines.value.map(l => ({
      id: l.id ?? null,                // server id or null
      part_id: l.part_id ?? null,      // prefer part_id; otherwise part_no
      part_no: l.part_no ?? null,
      vendor_id: l.vendor_id ?? null,
      vendor_code: l.vendor_code ?? null,
      lot_no: l.lot_no,
      expiry_date: l.expiry ?? null,
      qty_total: Number(l.qty_total) || 0
    })),
  }

  // gọi API
  try {
    // show pending toast (optional)
    toast.info('Đang lưu...', 'Vui lòng chờ', 2000)

    // gọi API: dùng hàm bạn import
    const res = await storeIncome(editingId.value, payload)
    const data = res.data || res // bảo toàn nếu axios wrapper trả .data hoặc trực tiếp

    // cập nhật incomes list theo response
    if (!editingId.value) {
      // tạo mới: thêm vào đầu
      incomes.value.unshift({
        id: data.id,
        income_no: data.income_no,
        invoice_no: data.invoice_no,
        date: data.date,
        total_qty: data.total_qty ?? total,
        lines: data.lines ?? payload.lines,
      })
      highlightedId.value = data.id
      toast.success(`Đã lưu chứng từ mới: ${form.income_no}`, 'Lưu thành công')
    } else {
      // update: tìm và cập nhật
      const idx = incomes.value.findIndex(r => r.id === editingId.value)
      if (idx !== -1) {
        incomes.value[idx] = {
          ...incomes.value[idx],
          id: data.id ?? editingId.value,
          income_no: data.income_no ?? payload.income_no,
          invoice_no: data.invoice_no ?? payload.invoice_no,
          date: data.date ?? payload.date,
          total_qty: data.total_qty ?? total,
          lines: data.lines ?? payload.lines,
        }
      } else {
        // nếu không thấy trong list (vd list chưa có), push vào đầu
        incomes.value.unshift({
          id: data.id,
          income_no: data.income_no,
          invoice_no: data.invoice_no,
          date: data.date,
          total_qty: data.total_qty ?? total,
          lines: data.lines ?? payload.lines,
        })
      }
      highlightedId.value = editingId.value
      toast.success(`Đã cập nhật chứng từ: ${payload.income_no}`, 'Lưu thành công')
    }
    const page = (pagination.value && pagination.value.current_page) ? pagination.value.current_page : 1
    await loadIncomes(page)

    // sau khi lưu thì clear form & thoát chế độ sửa
    editingId.value = null
    clearForm()

    // cuộn xuống lịch sử (nếu muốn)
    await nextTick()
    if (historyRef.value) {
      historyRef.value.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
  } catch (err) {
    // xử lý lỗi từ API
    console.error(err)

    // nếu axios trả validation 422
    if (err.response) {
      const errors = err.response.data.errors || {}
      // lấy first error message
      const firstKey = Object.keys(errors)[0]
      const firstMsg = firstKey ? errors[firstKey][0] : 'Dữ liệu không hợp lệ'
      toast.error(firstMsg, 'Lỗi xác thực')
      return
    }

    // lỗi auth
    if (err.response && err.response.status === 401) {
      toast.error('Bạn chưa đăng nhập hoặc phiên đã hết hạn', 'Lỗi')
      return
    }
  }
  finally {
    saving.value = false
  }
}


/* ====== Mở modal QR (dùng component riêng) ====== */
const showQrModal = ref(false)
const selectedIncome = ref(null)

function openQrModal (income) {
  selectedIncome.value = income
  showQrModal.value = true
}

//Edit income function can be added here
const editingId = ref(null) 
const highlightedId = ref(null)

function clearForm () {
  form.income_no = ''
  form.invoice_no = ''
  form.date = new Date().toISOString().slice(0, 10)

  // reset lines về 1 dòng trống
  lines.value = []
  addLine()
}

async function startEdit (income) {
  editingId.value = income.id

  // fill header
  form.income_no = income.income_no
  form.invoice_no = income.invoice_no
  // form.date = income.received_at ? dayjs(income.received_at).format('YYYY-MM-DD') : dayjs().format('YYYY-MM-DD')
  form.date = income.received_at.slice(0, 10)


  // clone lines để sửa, không mutate trực tiếp lịch sử
  lines.value = income.income_lines.map(l => ({
    _key: l.id ? `s_${l.id}` : makeKey(),
    id: l.id ?? null,               // server id
    part_id: l.parts.id ?? null,
    part_no: l.parts.part_no ?? null,
    vendor_id: l.vendors.id ?? null,
    vendor_code: l.vendors.code ?? null,
    lot_no: l.lot_no,
    expiry: l.expiry_date ?? null,
    qty_total: l.qty_total
  }))

  if (!income_lines.value.length) addLine()
  await nextTick()
  window.scrollTo({ top:0, behavior:'smooth' })
}

</script>

<template>
  <CCard>
    <CCardHeader class="fw-semibold">Tạo chứng từ nhập (Income)</CCardHeader>
    <CCardBody>
      <!-- Chọn kho -->
      <div class="w-50 mb-3">
        <select
          v-model="form.warehouse_id"
          class="form-select w-25 text-bg-secondary"
        >
          <option :value="1">Kho PCB</option>
          <option :value="2">Kho MH</option>
        </select>
      </div>

      <!-- Form header + lines -->
      <CForm class="mb-4">
        <CRow class="mb-3">
          <CCol md="3">
            <CFormInput v-model="form.income_no" label="Invoice No" />
          </CCol>
          <CCol md="3">
            <CFormInput v-model="form.date" type="date" label="Ngày" />
          </CCol>
        </CRow>

        <!-- Lines nhập -->
        <CTable small striped hover>
          <CTableHead>
            <CTableRow>
              <CTableHeaderCell>Part No</CTableHeaderCell>
              <CTableHeaderCell>Lot No</CTableHeaderCell>
              <CTableHeaderCell>Vendor Code</CTableHeaderCell>
              <CTableHeaderCell>Số lượng</CTableHeaderCell>
            </CTableRow>
          </CTableHead>
          <CTableBody>
            <CTableRow v-for="(l, idx) in lines" :key="l._key">
              <CTableDataCell>
                <CFormSelect v-model="l.part_no" :options="['', ...parts]" />
              </CTableDataCell>
              <CTableDataCell>
                <CFormInput v-model="l.lot_no" />
              </CTableDataCell>
              <CTableDataCell>
                <CFormInput v-model="l.vendor_code" type="text" />
              </CTableDataCell>
              <CTableDataCell>
                <CFormInput v-model.number="l.qty_total" type="number" min="0" />
              </CTableDataCell>
              <CTableDataCell class="text-end">
                <CButton
                  color="danger"
                  variant="ghost"
                  size="sm"
                  @click="removeLine(idx)"
                >
                  <CIcon :icon="cilTrash" />
                </CButton>
              </CTableDataCell>
            </CTableRow>

            <CTableRow v-if="!lines.length">
              <CTableDataCell colspan="5" class="text-center text-body-secondary">
                Chưa có dòng hàng
              </CTableDataCell>
            </CTableRow>
          </CTableBody>
        </CTable>

        <div class="d-flex justify-content-between align-items-center mt-3">
          <CButton color="secondary" variant="outline" @click="addLine">
            <CIcon :icon="cilPlus" class="me-1" /> Thêm part
          </CButton>
          <CButton :disabled="saving" color="primary" class="me-2" @click="saveIncome">
            <CIcon :icon="cilSave" class="me-1" /> {{ saveLabel }}
          </CButton>
        </div>
      </CForm>

      <!-- Lịch sử nhập -->
      <CCard ref="historyRef">
        <CCardHeader class="fw-semibold bg-secondary text-white">
          Lịch sử nhận
        </CCardHeader>
        <CCardBody>
          <div class="table-responsive">
            <CTable small striped hover class="align-middle">
              <CTableHead class="sticky">
                <CTableRow>
                  <CTableHeaderCell class="w-1">#</CTableHeaderCell>
                  <CTableHeaderCell>Invoice No</CTableHeaderCell>
                  <CTableHeaderCell>Số part</CTableHeaderCell>
                  <CTableHeaderCell>Tổng số lượng</CTableHeaderCell>
                  <CTableHeaderCell>Ngày</CTableHeaderCell>
                  <CTableHeaderCell class="text-end">Thao tác</CTableHeaderCell>
                </CTableRow>
              </CTableHead>
              <CTableBody>
                <CTableRow
                  v-for="row in incomes"
                  :key="row.id"
                >
                  <CTableDataCell>{{ row.id }}</CTableDataCell>
                  <CTableDataCell>{{ row.income_no }}</CTableDataCell>
                  <CTableDataCell>{{ (row.income_lines.length) }}</CTableDataCell>
                  <CTableDataCell>{{ (row.total_qty ?? 0).toLocaleString() }}</CTableDataCell>
                  <CTableDataCell>{{ row.received_at }}</CTableDataCell>
                  <CTableDataCell class="text-end">
                    <CButton
                      size="sm"
                      color="secondary"
                      variant="ghost"
                      class="me-1"
                      @click="startEdit(row)"
                    >
                      <CIcon :icon="cilPencil" />
                    </CButton>

                    <CButton
                      size="sm"
                      class="btn btn-outline-success"
                      variant="ghost"
                      @click="openQrModal(row)"
                    >
                      <CIcon :icon="cilQrCode" />
                    </CButton>
                  </CTableDataCell>
                </CTableRow>

                <CTableRow v-if="!incomes.length">
                  <CTableDataCell colspan="6" class="text-center text-body-secondary py-4">
                    Không có dữ liệu
                  </CTableDataCell>
                </CTableRow>
              </CTableBody>
            </CTable>
          </div>

          <!-- footer pagination -->
          <div class="d-flex justify-content-between align-items-center mt-3">
            <small class="text-body-secondary">
              Tổng: {{ pagination.total }} bản ghi • Trang {{ pagination.current_page }} / {{ pagination.last_page }}
            </small>

            <div class="d-flex align-items-center gap-3">
              <div class="d-flex align-items-center gap-2">
                <span class="text-body-secondary">Hiển thị:</span>
                <select class="form-select form-select-sm" style="width:auto" @change="(e) => changePerPage(e.target.value)" :value="pagination.per_page">
                  <option :value="10">10</option>
                  <option :value="15">15</option>
                  <option :value="25">25</option>
                  <option :value="50">50</option>
                </select>
              </div>

              <CPagination align="end" class="mb-0">
                <CPaginationItem :disabled="pagination.current_page <= 1" @click="goToPage(1)">&laquo;</CPaginationItem>
                <CPaginationItem :disabled="pagination.current_page <= 1" @click="goToPage(pagination.current_page - 1)">Prev</CPaginationItem>

                <!-- render page numbers (simple window) -->
                <CPaginationItem
                  v-for="p in pageNumbers"
                  :key="p"
                  :active="p === pagination.current_page"
                  @click="goToPage(p)"
                >{{ p }}</CPaginationItem>

                <CPaginationItem :disabled="pagination.current_page >= pagination.last_page" @click="goToPage(pagination.current_page + 1)">Next</CPaginationItem>
                <CPaginationItem :disabled="pagination.current_page >= pagination.last_page" @click="goToPage(pagination.last_page)">&raquo;</CPaginationItem>
              </CPagination>
            </div>
          </div>
        </CCardBody>
      </CCard>
    </CCardBody>
  </CCard>

  <!-- Modal QR dùng component riêng -->
  <IncomeQrModal
    v-model:visible="showQrModal"
    :income="selectedIncome"
  />
</template>

<style scoped>
.table td,
.table th {
  vertical-align: middle;
}
.table-responsive {
  overflow-x: auto;
}
thead.sticky {
  position: sticky;
  top: 0;
  z-index: 1;
  background: var(--cui-body-bg);
}
.row-hit {
  outline: 2px solid #22c55e;
  background: rgba(34, 197, 94, 0.08);
}
</style>
