<script setup>
import { ref, reactive } from 'vue'
import {
  CCard, CCardHeader, CCardBody, CRow, CCol,
  CForm, CFormInput, CButton, CBadge, CAlert,
  CTable, CTableHead, CTableRow, CTableHeaderCell, CTableBody, CTableDataCell,
  CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter,
} from '@coreui/vue'
import CIcon from '@coreui/icons-vue'
import { cilQrCode, cilCheckCircle, cilX, cilShieldAlt } from '@coreui/icons'

/* ================= Mock data ================= */
const pallets = ref([
  { code: 'PAL-INC-241025-001-01-001', part_no: 'QK2-0001-000', lot_no: 'LOT01', qty: 8000, wh: 'K1', location: null, status: 'new' },
  { code: 'PAL-INC-241025-001-01-002', part_no: 'QK2-0001-000', lot_no: 'LOT01', qty: 8000, wh: 'K1', location: null, status: 'new' },
  { code: 'BOX-INC-241025-001-01-001', part_no: 'QK2-0001-000', lot_no: 'LOT01', qty: 1000, wh: 'K1', location: null, status: 'new' },
])

// Layout đơn giản cho K1
const racks = ['A', 'B', 'C']
const tiers = [3, 2, 1]           // hiển thị từ trên xuống
const slotsPerTier = 24
const slots = Array.from({ length: slotsPerTier }, (_, i) => i + 1) // 1..24

/* ================= State ================= */
const scanCode = ref('')           // ô scan Pallet
const scanLoc = ref('')            // ô scan Location (QR vị trí)
const selected = ref(null)         // pallet đang thao tác
const pick = reactive({ rack: 'A', tier: 1, slot: 1 }) // vị trí dự kiến
const message = ref('')

const putawayLogs = ref([]) // {code, expected, confirmed, by, time, override}

/* ===== Admin override (khi sai vị trí) ===== */
const showOverride = ref(false)
const adminPass = ref('')
const overrideReason = ref('')
const expectedCache = ref('')
const confirmedCache = ref('')

/* ================= Helpers ================= */
function slotLabel(rack, tier, slot) {
  return rack + '-T' + tier + '-' + slot
}
function findPallet(code) {
  const c = (code || '').trim().toLowerCase()
  return pallets.value.find(p => p.code.toLowerCase() === c)
}

/* ================= Actions ================= */
function onScanPallet() {
  message.value = ''
  const p = findPallet(scanCode.value)
  if (!p) {
    message.value = '❌ Không tìm thấy Pallet/Box'
    selected.value = null
    return
  }
  // Nếu pallet đã có vị trí → yêu cầu đổi vị trí (có thể cần quyền)
  selected.value = p
  scanCode.value = ''
}

function onClickCell(t, s) {
  pick.tier = t
  pick.slot = s
}

function setRack(r) {
  pick.rack = r
}

function confirmLocation() {
  message.value = ''
  if (!selected.value) {
    message.value = '❌ Chưa chọn Pallet/Box'
    return
  }
  const expected = slotLabel(pick.rack, pick.tier, pick.slot)
  const confirmed = (scanLoc.value || '').trim().toUpperCase()
  if (!confirmed) {
    message.value = '❌ Vui lòng scan mã vị trí thực tế (QR Location)'
    return
  }

  // So sánh expected vs confirmed
  if (confirmed !== expected) {
    // yêu cầu admin override
    expectedCache.value = expected
    confirmedCache.value = confirmed
    showOverride.value = true
    return
  }

  // Đúng vị trí → lưu
  finishPutaway(expected, confirmed, false)
}

function finishPutaway(expected, confirmed, override) {
  selected.value.location = confirmed
  selected.value.wh = 'K1'
  selected.value.status = 'stored'

  putawayLogs.value.unshift({
    code: selected.value.code,
    expected,
    confirmed,
    by: 'E00123', // mock
    time: new Date().toLocaleString(),
    override,
  })

  message.value = '✅ Đã xác nhận vị trí: ' + confirmed + (override ? ' (admin override)' : '')
  scanLoc.value = ''
  selected.value = null
}

/* ===== Admin xác nhận khi sai vị trí ===== */
function doOverride() {
  // TODO: call API verify admin
  if (adminPass.value !== 'admin123') {
    message.value = '❌ Sai mật khẩu quản trị viên'
    showOverride.value = false
    adminPass.value = ''
    overrideReason.value = ''
    return
  }
  // Cho phép confirm khác vị trí dự kiến
  finishPutaway(expectedCache.value, confirmedCache.value, true)
  showOverride.value = false
  adminPass.value = ''
  overrideReason.value = ''
}

/* ===== Enter keys ===== */
function onEnterPallet(e) {
  e.preventDefault()
  onScanPallet()
}
function onEnterLoc(e) {
  e.preventDefault()
  confirmLocation()
}
</script>

<template>
  <CCard>
    <CCardHeader class="fw-semibold d-flex align-items-center gap-2">
      Putaway (Scan + Xác nhận vị trí thực tế)
      <CBadge color="secondary">Kho K1</CBadge>
    </CCardHeader>

    <CCardBody>
      <CAlert v-if="message" :color="message.startsWith('✅') ? 'success' : 'danger'" class="py-2">
        {{ message }}
      </CAlert>

      <CRow class="mb-3">
        <CCol md="7">
          <!-- Chọn vị trí dự kiến -->
          <div class="d-flex align-items-center gap-2 mb-2">
            <strong>Chọn Rack:</strong>
            <div class="d-flex gap-2">
              <CButton
                v-for="r in racks" :key="r"
                :color="pick.rack===r ? 'primary' : 'secondary'"
                variant="outline"
                size="sm"
                @click="setRack(r)"
              >{{ r }}</CButton>
            </div>
            <span class="ms-2">Vị trí dự kiến:</span>
            <CBadge color="primary">{{ slotLabel(pick.rack, pick.tier, pick.slot) }}</CBadge>
          </div>

          <div class="mini-map">
            <div v-for="t in tiers" :key="t" class="tier">
              <div class="tier-label">T{{ t }}</div>
              <div class="grid" :style="{ gridTemplateColumns: 'repeat(' + slotsPerTier + ', 1fr)' }">
                <button
                  v-for="s in slots" :key="'T'+t+'-'+s"
                  class="cell"
                  :class="{ active: pick.tier===t && pick.slot===s }"
                  @click="onClickCell(t, s)"
                  :title="'T'+t+'-'+s"
                />
              </div>
            </div>
          </div>
        </CCol>

        <CCol md="5">
          <!-- Scan Pallet + Scan Location -->
          <CForm class="d-flex align-items-end gap-2 mb-3" @submit.prevent="onScanPallet">
            <div class="flex-grow-1">
              <label class="form-label mb-1">Scan Pallet/Box</label>
              <CFormInput v-model="scanCode" placeholder="Bắn QR pallet/box..." @keyup.enter="onEnterPallet" />
            </div>
            <CButton color="primary" @click="onScanPallet">
              <CIcon :icon="cilQrCode" class="me-1" /> Scan
            </CButton>
          </CForm>

          <div class="p-3 border rounded bg-white mb-3" v-if="selected">
            <div class="fw-semibold mb-1">{{ selected.code }}</div>
            <div>Part: <strong>{{ selected.part_no }}</strong></div>
            <div>Lot: {{ selected.lot_no }}</div>
            <div>Qty: {{ selected.qty }}</div>
            <div>Vị trí hiện tại: <em>{{ selected.location || '(chưa có)' }}</em></div>

            <CForm class="d-flex align-items-end gap-2 mt-3" @submit.prevent="confirmLocation">
              <div class="flex-grow-1">
                <label class="form-label mb-1">Scan vị trí thực tế (QR Location)</label>
                <CFormInput v-model="scanLoc" placeholder="Bắn QR vị trí trên kệ..."
                            @keyup.enter="onEnterLoc" />
              </div>
              <CButton color="success" @click="confirmLocation">
                <CIcon :icon="cilCheckCircle" class="me-1" /> Xác nhận
              </CButton>
              <CButton color="secondary" variant="outline" @click="selected=null">
                <CIcon :icon="cilX" class="me-1" /> Bỏ
              </CButton>
            </CForm>
          </div>
          <div v-else class="text-body-secondary">(Chưa chọn Pallet/Box)</div>

          <!-- Logs -->
          <div class="mt-4"><strong>Putaway logs</strong></div>
          <CTable small hover class="align-middle">
            <CTableHead>
              <CTableRow>
                <CTableHeaderCell>#</CTableHeaderCell>
                <CTableHeaderCell>Code</CTableHeaderCell>
                <CTableHeaderCell>Expected</CTableHeaderCell>
                <CTableHeaderCell>Confirmed</CTableHeaderCell>
                <CTableHeaderCell>By</CTableHeaderCell>
                <CTableHeaderCell>Time</CTableHeaderCell>
                <CTableHeaderCell>OVR</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              <CTableRow v-for="(l,i) in putawayLogs" :key="i">
                <CTableDataCell>{{ i+1 }}</CTableDataCell>
                <CTableDataCell>{{ l.code }}</CTableDataCell>
                <CTableDataCell>{{ l.expected }}</CTableDataCell>
                <CTableDataCell>{{ l.confirmed }}</CTableDataCell>
                <CTableDataCell>{{ l.by }}</CTableDataCell>
                <CTableDataCell>{{ l.time }}</CTableDataCell>
                <CTableDataCell>
                  <CBadge :color="l.override ? 'danger' : 'success'">
                    {{ l.override ? 'YES' : 'NO' }}
                  </CBadge>
                </CTableDataCell>
              </CTableRow>
              <CTableRow v-if="!putawayLogs.length">
                <CTableDataCell colspan="7" class="text-center text-body-secondary">Chưa có log</CTableDataCell>
              </CTableRow>
            </CTableBody>
          </CTable>
        </CCol>
      </CRow>
    </CCardBody>
  </CCard>

  <!-- Modal: Admin override khi sai vị trí -->
  <CModal alignment="center" :visible="showOverride" @close="() => showOverride=false">
    <CModalHeader>
      <CModalTitle>
        <CIcon :icon="cilShieldAlt" class="me-2" /> Xác nhận quản trị viên
      </CModalTitle>
    </CModalHeader>
    <CModalBody>
      <div class="mb-2">Vị trí dự kiến: <strong>{{ expectedCache }}</strong></div>
      <div class="mb-3">Vị trí quét thực tế: <strong class="text-danger">{{ confirmedCache }}</strong></div>
      <label class="form-label">Mật khẩu Admin</label>
      <CFormInput v-model="adminPass" type="password" class="mb-3" placeholder="Nhập mật khẩu quản trị viên" />
      <label class="form-label">Lý do (optional)</label>
      <CFormInput v-model="overrideReason" placeholder="Ghi lý do override..." />
    </CModalBody>
    <CModalFooter>
      <CButton color="secondary" @click="() => { showOverride=false }">Huỷ</CButton>
      <CButton color="danger" @click="doOverride">Xác nhận override</CButton>
    </CModalFooter>
  </CModal>
</template>

<style scoped>
.mini-map { border:2px solid #cbd5e1; border-radius:8px; padding:8px; background:#f8fafc; }
.tier { display:grid; grid-template-columns: 48px 1fr; align-items:center; gap:8px; border-bottom:2px solid #e5e7eb; padding:6px 0; }
.tier:last-child{ border-bottom:none; }
.tier-label { font-weight:600; color:#64748b; }
.grid { display:grid; gap:4px; }
.cell { height:18px; border:1px solid #e5e7eb; border-radius:3px; background:#fff; cursor:pointer; }
.cell:hover{ outline:2px solid #94a3b8; }
.cell.active{ background:#22c55e; border-color:#16a34a; }
</style>