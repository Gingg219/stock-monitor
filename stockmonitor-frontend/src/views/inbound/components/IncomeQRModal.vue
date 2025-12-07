<script setup>
import { ref, computed, watch } from 'vue'
import { useInboundStore } from '@/stores/inbound'
import {
  CModal, CModalHeader, CModalBody, CModalFooter, CModalTitle,
  CCard, CCardHeader, CCardBody,
  CRow, CCol,
  CTable, CTableHead, CTableRow, CTableHeaderCell, CTableBody, CTableDataCell,
  CFormInput, CFormLabel,
  CBadge, CButton, CAlert,
} from '@coreui/vue'
import CIcon from '@coreui/icons-vue'
import { cilQrCode, cilPrint, cilCloudDownload } from '@coreui/icons'
import QRCode from 'qrcode'

const props = defineProps({
  visible: { type: Boolean, default: false },
  income: { type: Object, default: null },   // dữ liệu truyền từ màn chính
})

const emit = defineEmits(['update:visible'])

const store = useInboundStore()

// nếu có props.income thì dùng, không thì fallback về store.current
const income = computed(() => props.income || store.current || null)

// --- plan khởi tạo sau khi lines có ---
const plan = ref([]) // [{ lineNo, pallet_size, box_size }]

watch(
  () => income.value?.lines,
  (lines) => {
    if (!lines) { plan.value = []; return }
    const current = new Map(plan.value.map(p => [p.lineNo, p]))
    plan.value = lines.map(l => current.get(l.lineNo) ?? ({
      lineNo: l.lineNo,
      pallet_size: 0,
      box_size: 0,
    }))
  },
  { immediate: true },
)

function findPlan(lineNo) {
  return plan.value.find(p => p.lineNo === lineNo) ?? { pallet_size: 0, box_size: 0 }
}

function calcForLine(line) {
  const p = findPlan(line.lineNo)
  const total = Number(line.qty_total) || 0
  const ps = Math.max(0, Number(p.pallet_size) || 0)
  const bs = Math.max(0, Number(p.box_size) || 0)

  if (total <= 0 || (ps <= 0 && bs <= 0)) return { pallets: 0, boxes: 0, remainder: total }

  let pallets = 0, boxes = 0, remainder = total
  if (ps > 0) { pallets = Math.floor(total / ps); remainder = total - pallets * ps }
  if (bs > 0 && remainder > 0) { boxes = Math.ceil(remainder / bs) }
  return { pallets, boxes, remainder }
}

const computedLines = computed(() => {
  const lines = income.value?.lines ?? []
  return lines.map((l) => ({
    ...l,
    ...calcForLine(l),
    pallet_size: findPlan(l.lineNo).pallet_size,
    box_size: findPlan(l.lineNo).box_size,
  }))
})

// ====== Generate QR ======
const generated = ref([])
const generating = ref(false)
const errorMsg = ref('')

function codeSeq(n) { return String(n).padStart(3, '0') }
function buildCode(prefix, lineNo, seq) {
  return `${prefix}-${income.value?.income_no ?? ''}-${String(lineNo).padStart(2,'0')}-${codeSeq(seq)}`
}
async function generateQRFor(code) { return await QRCode.toDataURL(code, { margin: 1, scale: 5 }) }

async function generateAll() {
  if (!income.value) return
  try {
    errorMsg.value = ''
    generating.value = true
    const out = []

    for (const l of computedLines.value) {
      if (l.qty_total <= 0) continue
      if ((l.pallet_size ?? 0) <= 0 && (l.box_size ?? 0) <= 0) {
        errorMsg.value = 'Pallet size và/hoặc Box size phải > 0.'
        continue
      }
      // Pallet
      for (let i = 1; i <= l.pallets; i++) {
        const code = buildCode('PAL', l.lineNo, i)
        const qr = await generateQRFor(code)
        out.push({ code, type:'PALLET', qty:l.pallet_size, part_no:l.part_no, lot_no:l.lot_no, expiry:l.expiry, lineNo:l.lineNo, qrDataUrl:qr })
      }
      // Boxes
      if (l.boxes > 0) {
        let remain = l.qty_total - l.pallets * l.pallet_size
        for (let i = 1; i <= l.boxes; i++) {
          const take = Math.min(l.box_size, remain)
          const code = buildCode('BOX', l.lineNo, i)
          const qr = await generateQRFor(code)
          out.push({ code, type:'BOX', qty:take, part_no:l.part_no, lot_no:l.lot_no, expiry:l.expiry, lineNo:l.lineNo, qrDataUrl:qr })
          remain -= take
        }
      }
    }

    generated.value = out
    if (income.value) {
      store.markGenerated?.()
    }
  } catch (e) {
    console.error(e)
    errorMsg.value = 'Có lỗi khi sinh QR.'
  } finally {
    generating.value = false
  }
}

/* ====== Tổng hợp để hiển thị nhanh ====== */
const totals = computed(() => {
  let pal = 0, box = 0, qty = 0
  for (const g of generated.value) {
    if (g.type === 'PALLET') pal++
    if (g.type === 'BOX') box++
    qty += Number(g.qty) || 0
  }
  return { pal, box, qty }
})

/* ====== In / Tải nhãn (QR) ====== */
function printLabels() {
  if (!income.value) return
  const win = window.open('', '_blank')
  if (!win) return

  let body = ''
  generated.value.forEach(g => {
    body += ''
      + '<div class="card">'
      +   '<div class="code">' + g.code + '</div>'
      +   '<div class="qr"><img src="' + g.qrDataUrl + '" alt="' + g.code + '" /></div>'
      +   '<div class="meta">'
      +     '<div>Type: ' + g.type + ' • Qty: ' + g.qty + '</div>'
      +     '<div>Part: ' + g.part_no + '</div>'
      +     '<div>Lot: ' + g.lot_no + '</div>'
      +     '<div>Exp: ' + g.expiry + '</div>'
      +     '<div>Line: ' + g.lineNo + '</div>'
      +   '</div>'
      + '</div>'
  })

  const html =
    '<!doctype html>'
    + '<html><head><meta charset="utf-8">'
    + '<title>Labels - ' + income.value.income_no + '</title>'
    + '<style>'
    + 'body{font-family:Arial,sans-serif;margin:16px;}'
    + '.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;}'
    + '.card{border:1px solid #ddd;border-radius:8px;padding:8px;}'
    + '.code{font-weight:700;margin:4px 0 6px 0;}'
    + '.meta{font-size:12px;color:#555;line-height:1.35;}'
    + '.qr{display:flex;justify-content:center;margin:6px 0;}'
    + 'hr{border:none;border-top:1px dashed #ccc;margin:6px 0;}'
    + '</style></head><body>'
    + '<h3>Labels for ' + income.value.income_no + '</h3>'
    + '<div class="grid">' + body + '</div>'
    + '<script>window.onload=function(){window.print()}</' + 'script>'
    + '</body></html>'

  win.document.open()
  win.document.write(html)
  win.document.close()
}

function downloadJSON() {
  if (!income.value) return
  const blob = new Blob([JSON.stringify(generated.value, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `storage-units-${income.value.income_no}.json`
  document.body.appendChild(a); a.click(); a.remove()
  URL.revokeObjectURL(url)
}

// Admin confirm để cho phép sinh lại
const showAdminConfirm = ref(false)
const adminPassword = ref('')

function confirmAdmin() {
  if (adminPassword.value === '123') { // TODO: call API verify
    alert('✅ Xác nhận admin thành công! Bạn có thể sinh lại Pallet/Box.')
    if (income.value) income.value.status = 'draft'
    showAdminConfirm.value = false
  } else {
    alert('❌ Sai mật khẩu quản trị viên!')
  }
}
</script>

<template>
  <CModal
    :visible="visible"
    size="xl"
    alignment="center"
    backdrop="static"
    @close="emit('update:visible', false)"
  >
    <CModalHeader>
      <CModalTitle>
        <span class="fw-semibold">{{ income.income_no }}</span>
        <CBadge v-if="income" color="secondary" class="ms-2">
     
        </CBadge>
      </CModalTitle>
    </CModalHeader>

    <CModalBody v-if="income">
      <CCard>
        <CCardHeader class="d-flex align-items-center gap-2">
          <span class="fw-semibold">Thông tin chứng từ</span>
          <div class="ms-auto d-flex gap-2">
            <CButton color="primary" :disabled="!generated.length" @click="printLabels">
              <CIcon :icon="cilPrint" class="me-1" /> In nhãn
            </CButton>
            <CButton color="success" variant="outline" :disabled="!generated.length" @click="downloadJSON">
              <CIcon :icon="cilCloudDownload" class="me-1" /> Tải JSON
            </CButton>
          </div>
        </CCardHeader>

        <CCardBody>
          <CRow class="mb-2">
            <CCol md="3"><strong>Vendor:</strong> {{ income.vendor }}</CCol>
            <CCol md="3"><strong>Date:</strong> {{ income.date }}</CCol>
            <CCol md="3">
              <strong>Đã sinh:</strong>
              <CBadge color="primary" class="ms-1">PAL: {{ totals.pal }}</CBadge>
              <CBadge color="info" class="ms-1">BOX: {{ totals.box }}</CBadge>
              <CBadge color="success" class="ms-1">QTY: {{ totals.qty }}</CBadge>
            </CCol>
          </CRow>

          <CAlert v-if="errorMsg" color="danger" class="py-2">{{ errorMsg }}</CAlert>

          <!-- Bảng khai báo quy cách sinh theo từng dòng -->
          <CTable small striped hover class="align-middle">
            <CTableHead>
              <CTableRow>
                <CTableHeaderCell>#</CTableHeaderCell>
                <CTableHeaderCell>Part</CTableHeaderCell>
                <CTableHeaderCell>Lot</CTableHeaderCell>
                <CTableHeaderCell>Expiry</CTableHeaderCell>
                <CTableHeaderCell class="text-end">Qty Total</CTableHeaderCell>
                <CTableHeaderCell class="text-end">Pallet Size</CTableHeaderCell>
                <CTableHeaderCell class="text-end">Box Size</CTableHeaderCell>
                <CTableHeaderCell class="text-end">→ Pallets</CTableHeaderCell>
                <CTableHeaderCell class="text-end">→ Boxes</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              <CTableRow v-for="l in computedLines" :key="l.lineNo">
                <CTableDataCell>{{ l.lineNo }}</CTableDataCell>
                <CTableDataCell>{{ l.part_no }}</CTableDataCell>
                <CTableDataCell>{{ l.lot_no }}</CTableDataCell>
                <CTableDataCell>{{ l.expiry }}</CTableDataCell>
                <CTableDataCell class="text-end">{{ l.qty_total.toLocaleString() }}</CTableDataCell>
                <CTableDataCell class="text-end">
                  <CFormInput v-model.number="plan.find(x => x.lineNo===l.lineNo).pallet_size" type="number" min="0" />
                </CTableDataCell>
                <CTableDataCell class="text-end">
                  <CFormInput v-model.number="plan.find(x => x.lineNo===l.lineNo).box_size" type="number" min="0" />
                </CTableDataCell>
                <CTableDataCell class="text-end"><CBadge color="primary">{{ l.pallets }}</CBadge></CTableDataCell>
                <CTableDataCell class="text-end"><CBadge color="info">{{ l.boxes }}</CBadge></CTableDataCell>
              </CTableRow>
            </CTableBody>
          </CTable>

          <div class="d-flex justify-content-end mt-3">
            <CButton color="primary" :disabled="generating" @click="generateAll">
              <CIcon :icon="cilQrCode" class="me-1" /> {{ generating ? 'Đang sinh...' : 'Sinh QR' }}
            </CButton>
          </div>

          <!-- Preview QR sinh ra -->
          <div v-if="generated.length" class="mt-4">
            <h6 class="mb-2">Preview nhãn ({{ generated.length }})</h6>
            <div class="qr-grid">
              <div class="qr-card" v-for="g in generated" :key="g.code">
                <div class="qr-code">{{ g.code }}</div>
                <img class="qr-img" :src="g.qrDataUrl" :alt="g.code" />
                <div class="qr-meta">
                  <div>{{ g.type }} • Qty: {{ g.qty }}</div>
                  <div>Part: {{ g.part_no }}</div>
                  <div>Lot: {{ g.lot_no }}</div>
                  <div>Exp: {{ g.expiry }}</div>
                </div>
              </div>
            </div>
          </div>
        </CCardBody>
      </CCard>

      <CAlert v-if="income.status === 'generated'" color="warning" class="d-flex justify-content-between align-items-center mt-3">
        <div>
          <strong>Chứng từ này đã sinh Pallet/Box trước đó.</strong>
          <br>
          Nếu muốn sinh lại, vui lòng xác nhận quyền admin.
        </div>
        <CButton color="danger" variant="outline" @click="showAdminConfirm = true">
          Xác nhận sinh lại
        </CButton>
      </CAlert>

      <CModal :visible="showAdminConfirm" @close="showAdminConfirm=false">
        <CModalHeader><CModalTitle>Xác nhận quyền Admin</CModalTitle></CModalHeader>
        <CModalBody>
          <CFormLabel>Mật khẩu Admin</CFormLabel>
          <CFormInput v-model="adminPassword" type="password" placeholder="Nhập mật khẩu quản trị viên" />
        </CModalBody>
        <CModalFooter>
          <CButton color="secondary" @click="showAdminConfirm=false">Huỷ</CButton>
          <CButton color="danger" @click="confirmAdmin">Xác nhận</CButton>
        </CModalFooter>
      </CModal>
    </CModalBody>

    <CModalFooter>
      <CButton color="secondary" @click="emit('update:visible', false)">Đóng</CButton>
    </CModalFooter>
  </CModal>
</template>

<style scoped>
.qr-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 12px;
}
.qr-card {
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 10px;
  background: #fff;
}
.qr-code { font-weight: 700; margin-bottom: 6px; }
.qr-img { display: block; width: 160px; height: 160px; margin: 0 auto 6px auto; object-fit: contain; }
.qr-meta { font-size: 12px; color: #555; line-height: 1.35; }
</style>
