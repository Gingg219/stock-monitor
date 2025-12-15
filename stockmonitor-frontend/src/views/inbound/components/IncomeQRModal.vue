<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { useInboundStore } from '@/stores/inbound'
import QRCode from 'qrcode'
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
import { storeStorageUnit, fetchLatestCode } from '@/api/storageUnits'
import { useToast } from '@/composables/useToast'


const props = defineProps({
  visible: { type: Boolean, default: false },
  income: { type: Object, default: null },
  saved: Boolean,
  printed: Boolean,
})
const emit = defineEmits([
  'update:visible',
  'update:saved',
  'update:printed',
  'save'
])

//use toast
const toast = useToast()

const store = useInboundStore()
const income = computed(() => props.income ?? null)

const latestStats = ref([]) 

const latestMap = computed(() => {
  const map = {}

  if (!Array.isArray(latestStats.value)) return map

  latestStats.value.forEach(r => {
    const key = `${r.income_line_id}-${r.unit_type}`
    map[key] = {
      usedQty: Number(r.total_qty) || 0,
      seq: Number(r.sequence_latest) || 0,
    } 
  })

  return map
})


// plan aligned with normalizedLines; always same length
const plan = ref([]) // [{ lineNo, pallet_size, box_size }]
const normalizedLines = ref([])

async function loadLatestStats () {
  if (!income.value?.id) return
  try {
    const res = await fetchLatestCode(income.value.id)
    console.log(res);
    latestStats.value = Array.isArray(res.data?.data)
      ? res.data.data
      : []
  } catch (e) {
    console.error('Load latest code failed', e)
    latestStats.value = []
  }
  
}

watch(
  () => props.visible,
  (v) => {
    if (v) {
      savedSuccessfully.value = false
      canPrints.value = false
      qrPrinted.value = false
      loadLatestStats()
    }
    
    if (!v) {
      resetModal()
    }
  }
)

function resetModal() {
   plan.value = plan.value.map(p => ({
    ...p,
    pallet_size: 0,
    box_size: 0,
  }))
  generated.value = []
  generating.value = false
  errorMsg.value = ''
  savedSuccessfully.value = false
}

function onClose() {
  if (props.saved && !props.printed) {
    toast.warn('Bạn phải in mã QR trước khi đóng', 'Chưa in QR')
    return
  }

  emit('update:visible', false)
}


const summary = computed(() => {
  let pal = 0, box = 0, usedQty = 0, totalQty = 0

  computedLines.value.forEach(l => {
    totalQty += l.qty_total
    usedQty += l.usedQty
  })

  latestStats.value.forEach(r => {
    if (r.unit_type === 'pallet') pal += Number(r.sequence_latest || 0)
    if (r.unit_type === 'box') box += Number(r.sequence_latest || 0)
  })

  return {
    pal,
    box,
    usedQty,
    remainQty: Math.max(0, totalQty - usedQty)
  }
})

// normalize income lines when income changes
watch(
  () => income.value,
  (inc) => {
    const src = (inc?.income_lines ?? inc?.lines ?? []) || []
    normalizedLines.value = src.map((l, idx) => {
      const part_no = l.part?.part_no ?? l.parts?.part_no ?? l.part_no ?? ''
      const lot_no = l.lot_no ?? ''
      const qty_total = Number(l.qty_total) || 0
      const expiry = l.expiry_date ?? l.expiry ?? null
      // lineNo prefer server-provided lineNo else fallback to index+1 or id
      const lineNo = l.lineNo ?? l.line_no ?? (l.id ? l.id : idx + 1)
      return {
        ...l,
        part_no,
        lot_no,
        qty_total,
        expiry,
        lineNo,
      }
    })

    // sync plan length and preserve existing values by lineNo
    const existing = new Map(plan.value.map(p => [p.lineNo, p]))
    plan.value = normalizedLines.value.map(nl => {
      const ex = existing.get(nl.lineNo)
      return ex ? { ...ex } : { lineNo: nl.lineNo, pallet_size: 0, box_size: 0 }
    })
  },
  { immediate: true }
)

// calc based on plan[idx]
function calcForLine(line, idx) {
  const p = plan.value[idx] ?? { pallet_size: 0, box_size: 0 }

  const remain = Math.max(0, Number(line.remainQty ?? line.qty_total) || 0)
  const ps = Math.max(0, Number(p.pallet_size) || 0)
  const bs = Math.max(0, Number(p.box_size) || 0)

  if (remain <= 0 || (ps <= 0 && bs <= 0)) {
    return { pallets: 0, boxes: 0, remainder: remain }
  }

  let pallets = 0
  let boxes = 0
  let remainder = remain

  if (ps > 0) {
    pallets = Math.floor(remainder / ps)
    remainder -= pallets * ps
  }

  if (bs > 0 && remainder > 0) {
    boxes = Math.ceil(remainder / bs)
  }

  return { pallets, boxes, remainder }
}


const computedLines = computed(() => {
  return normalizedLines.value.map((l, idx) => {

    const usedPal = latestMap.value[`${l.lineNo}-pallet`]?.usedQty || 0
    const usedBox = latestMap.value[`${l.lineNo}-box`]?.usedQty || 0
    const usedTotal = usedPal + usedBox

    const remainQty = Math.max(0, l.qty_total - usedTotal)

    const calc = calcForLine(
      { ...l, remainQty },   // ✅ TRUYỀN remainQty VÀO
      idx
    )

    return {
      ...l,
      ...calc,
      usedQty: usedTotal,
      remainQty,
      pallet_size: plan.value[idx]?.pallet_size ?? 0,
      box_size: plan.value[idx]?.box_size ?? 0,
    }
  })
})

// QR logic
const generated = ref([])
const generating = ref(false)
const errorMsg = ref('')
const canPrints = ref(false)
const qrPrinted = ref(false)
const savedSuccessfully = ref(false)

function codeSeq(n) { return String(n).padStart(3, '0') }
function buildCode(prefix, lineNo, seq) {
  return `${prefix}-${income.value?.income_no ?? ''}-${String(lineNo).padStart(2,'0')}-${codeSeq(seq)}`
}
async function generateQRFor(code) { return await QRCode.toDataURL(code, { margin: 1, scale: 5 }) }

async function generateAll () {
  if (!income.value) return

  try {
    errorMsg.value = ''
    generating.value = true
    generated.value = []

    await nextTick()

    for (const l of computedLines.value) {

      const keyPAL = `${l.lineNo}-pallet`
      const keyBOX = `${l.lineNo}-box`

      const usedPAL = latestMap.value[keyPAL]?.usedQty ?? 0
      const usedBOX = latestMap.value[keyBOX]?.usedQty ?? 0

      const usedQty = usedPAL + usedBOX

      // qty generate
      const willQty =
        l.pallets * l.pallet_size +
        l.boxes * l.box_size

      if (usedQty + willQty > l.qty_total) {
        errorMsg.value = `Line ${l.lineNo}: Tổng qty vượt quá số lượng nhập`
        return
      }

      // ===== PALLET =====
      const key = `${l.lineNo}-pallet`
      const latest = latestMap.value[key]?.seq ?? 0
      for (let i = 1; i <= l.pallets; i++) {
        const seq = latest + i
        const code = buildCode('PAL', l.lineNo, seq)
        const qr = await generateQRFor(code)

        generated.value.push({
          code,
          type: 'PALLET',
          qty: l.pallet_size,
          part_no: l.part_no,
          lot_no: l.lot_no,
          expiry: l.expiry,
          lineNo: l.lineNo,
          qrDataUrl: qr
        })
      }

      // ===== BOX =====
      const keyB = `${l.lineNo}-box`
      const latestB = latestMap.value[keyB]?.seq ?? 0

      const used = latestMap.value[key]?.usedQty ?? 0
      let remain = l.qty_total - (l.pallets * l.pallet_size) - used

      for (let i = 1; i <= l.boxes; i++) {
        const take = Math.min(l.box_size, remain)
        if (take <= 0) break

        const seqB = latestB + i
        const code = buildCode('BOX', l.lineNo, seqB)
        const qr = await generateQRFor(code)

        generated.value.push({
          code,
          type: 'BOX',
          qty: take,
          part_no: l.part_no,
          lot_no: l.lot_no,
          expiry: l.expiry,
          lineNo: l.lineNo,
          qrDataUrl: qr
        })
        remain -= take
      }
    }

  } finally {
    generating.value = false
  }
}

const totals = computed(() => {
  let pal = 0, box = 0, qty = 0
  for (const g of generated.value) {
    if (g.type === 'PALLET') pal++
    if (g.type === 'BOX') box++
    qty += Number(g.qty) || 0
  }
  return { pal, box, qty }
})

function saveGenerated() {
  if (!generated.value.length) {
    errorMsg.value = 'Chưa có nhãn để lưu. Hãy bấm "Sinh QR" trước.'
    return
  }
  const slim = generated.value.map(g => ( {lineNo:g.lineNo, code:g.code, type:g.type, qty:g.qty, part_no:g.part_no, lot_no:g.lot_no, expiry:g.expiry }))
  emit('save', { incomeId: income.value?.id ?? null, income_no: income.value?.income_no ?? null, received_at: income.value?.received_at ?? null, labels: slim ,
    onSuccess: () => {
      emit('update:saved', true)
      savedSuccessfully.value = true
      canPrints.value = true
    }
  })
}

function printLabels() {
  if (!income.value) return
  const win = window.open('', '_blank')
  if (!win) return
  let body = ''
  generated.value.forEach(g => {
    body += '<div class="card"><div class="code">' + g.code + '</div><div class="qr"><img src="' + g.qrDataUrl + '" alt="' + g.code + '" /></div><div class="meta"><div>Type: ' + g.type + ' • Qty: ' + g.qty + '</div><div>Part: ' + g.part_no + '</div><div>Lot: ' + g.lot_no + '</div><div>Line: ' + g.lineNo + '</div></div></div>'
  })
  const html = '<!doctype html><html><head><meta charset="utf-8"><title>Labels - ' + income.value.income_no + '</title><style>body{font-family:Arial,sans-serif;margin:16px;}.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;}.card{border:1px solid #ddd;border-radius:8px;padding:8px;}.code{font-weight:700;margin:4px 0 6px 0;}.meta{font-size:12px;color:#555;line-height:1.35;}.qr{display:flex;justify-content:center;margin:6px 0;}hr{border:none;border-top:1px dashed #ccc;margin:6px 0;}</style></head><body><h3>Labels for ' + income.value.income_no + '</h3><div class="grid">' + body + '</div><script>window.onload=function(){window.print()}</' + 'script></body></html>'
  win.document.open(); win.document.write(html); win.document.close()

  emit('update:printed', true)
}

const showAdminConfirm = ref(false)
const adminPassword = ref('')
function confirmAdmin() {
  if (adminPassword.value === '123') {
    if (income.value) income.value.status = 'draft'
    showAdminConfirm.value = false
  } else {
    alert('Sai mật khẩu quản trị viên!')
  }
}

</script>

<template>
  <CModal
    :visible="visible"
    size="xl"
    alignment="center"
    backdrop="static"
  >
    <CModalHeader :close-button="false">
      <CModalTitle>
        <span class="fw-semibold">{{ income.income_no }}</span>
        <CBadge v-if="income" color="secondary" class="ms-2">
     
        </CBadge>
      </CModalTitle>
    </CModalHeader>

    <CModalBody v-if="income">
      <CCard>
        <CCardHeader class="d-flex align-items-center gap-2">
          <span class="fw-semibold">Thông tin Income</span>
          <div class="ms-auto d-flex gap-2">
            <CButton color="primary" :disabled="!canPrints" @click="printLabels">
              <CIcon :icon="cilPrint" class="me-1" /> In nhãn
            </CButton>
            <!-- <CButton color="success" variant="outline" :disabled="!generated.length" @click="downloadJSON">
              <CIcon :icon="cilCloudDownload" class="me-1" /> Tải JSON
            </CButton> -->
          </div>
        </CCardHeader>

        <CCardBody>
          <CRow class="mb-2">
            <CCol md="3"><strong>Date:</strong> {{ income.received_at }}</CCol>
            <CCol md="5">
              <strong>Đã sinh:</strong>
              <CBadge color="primary">PAL: {{ summary.pal }}</CBadge>
              <CBadge color="info">BOX: {{ summary.box }}</CBadge>
              <CBadge color="warning">Đã sinh: {{ summary.usedQty }}</CBadge>
              <CBadge color="success">Còn lại: {{ summary.remainQty }}</CBadge>
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
                <CTableHeaderCell class="text-end">Đã sinh</CTableHeaderCell>
                <CTableHeaderCell class="text-end">Còn lại</CTableHeaderCell>
                <CTableHeaderCell class="text-end">Pallet Size</CTableHeaderCell>
                <CTableHeaderCell class="text-end">Box Size</CTableHeaderCell>
                <CTableHeaderCell class="text-end">→ Pallets</CTableHeaderCell>
                <CTableHeaderCell class="text-end">→ Boxes</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              <CTableRow v-for="(l, idx) in computedLines" :key="l.lineNo">
                <CTableDataCell>{{ l.lineNo }}</CTableDataCell>
                <CTableDataCell>{{ l.part_no }}</CTableDataCell>
                <CTableDataCell>{{ l.lot_no }}</CTableDataCell>
                <CTableDataCell class="text-end">
                  <CBadge color="secondary">{{ l.usedQty }}/{{ l.qty_total.toLocaleString() }}</CBadge>
                </CTableDataCell>
                <CTableDataCell class="text-end">
                  <CBadge color="success">{{ l.remainQty }}</CBadge>
                </CTableDataCell>

                <CTableDataCell class="text-end">
                  <!-- bind trực tiếp vào plan[idx].pallet_size (plan đã được sync trong watch) -->
                  <CFormInput v-model.number="plan[idx].pallet_size" type="number" min="0" />
                </CTableDataCell>

                <CTableDataCell class="text-end">
                  <CFormInput v-model.number="plan[idx].box_size" type="number" min="0" />
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
            <h6 class="mb-2">Xem trước nhãn ({{ generated.length }})</h6>
            <div class="qr-grid">
              <div class="qr-card" v-for="g in generated" :key="g.code">
                <div class="qr-code">{{ g.code }}</div>
                <img class="qr-img" :src="g.qrDataUrl" :alt="g.code" />
                <div class="qr-meta">
                  <div>{{ g.type }} • Qty: {{ g.qty }}</div>
                  <div>Part: {{ g.part_no }}</div>
                  <div>Lot: {{ g.lot_no }}</div>
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
      <!-- Nút Lưu: emit 'save' để parent gọi API -->
      <CButton
        v-if="!savedSuccessfully"
        color="success"
        class="ms-2"
        :disabled="!generated.length"
        @click="saveGenerated"
      >
        <CIcon :icon="cilCloudDownload" class="me-1" /> Lưu
      </CButton>
      <CButton v-else color="success">
        ✔ Đã lưu QR
      </CButton>
      <CButton color="secondary"  @click="onClose">Đóng</CButton>
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
