<script setup>
import { ref, computed } from 'vue'
import { CCard, CCardHeader, CCardBody, CRow, CCol, CFormSelect, CBadge, CFormInput } from '@coreui/vue'
import RackView from './RackView.vue'
import InfoPanel from './InfoPanel.vue'

const selectedSlot = ref(null)
const selectedSlotId = ref(null)          // ✅ thêm
const onSelectSlot = (slot) => {
  selectedSlot.value = slot
  selectedSlotId.value = slot?.id ?? null // ✅ lưu id để highlight
}

const searchText = ref('')
/* Tập id các slot match theo part_no trong các rack đang hiển thị */
const matchedIds = computed(() => {
  const q = (searchText.value || '').trim().toLowerCase()
  if (!q) return new Set()
  const ids = new Set()
  for (const r of racksToShow.value) {
    for (const t of r.tiers) {
      for (const s of t.slots) {
        if ((s.parts || []).some(p => String(p.part_no || '').toLowerCase().includes(q))) {
          ids.add(s.id)
        }
      }
    }
  }
  return ids
})


/* ===== Demo data (giữ status để thấy màu) ===== */
const warehouses = [
  {
    id: 'K1', name: 'Kho K1',
    racks: [
      { id: 'A', name: 'RACK A', tiers: makeTiers('A') },
      { id: 'B', name: 'RACK B', tiers: makeTiers('B') },
    ]
  },
  { id: 'K2', name: 'Kho K2', racks: [] },
]

function makeTiers(prefix) {
  const statuses = ['ok', 'low', 'near_expire', 'wrong', 'empty']
  const mkSlots = (tier) => Array.from({ length: 40 }, (_, i) => {
    const status = statuses[i % statuses.length]
    if (status === 'empty') {
      return { id: `${prefix}-${tier}-${i + 1}`, parts: [] }
    } else {
      // random 1-3 part trong slot
      const partCount = Math.floor(Math.random() * 3) + 1
      const parts = Array.from({ length: partCount }, (_, j) => ({
        part_no: `QK2-${1000 + i + j}-000`,
        qty: 100 * (j + 1),
        expiry: '2026-10-25',
        status,
        type: j % 2 === 0 ? 'BOX' : 'PALLET',
      }))
      return { id: `${prefix}-${tier}-${i + 1}`, parts }
    }
  })
  return [
    { id: 'T3', name: 'T3', slots: mkSlots('T3') },
    { id: 'T2', name: 'T2', slots: mkSlots('T2') },
    { id: 'T1', name: 'T1', slots: mkSlots('T1') },
  ]
}

const selectedWarehouseId = ref('K1')
const selectedRackId = ref('ALL') // ✅ mặc định ALL

const selectedWarehouse = computed(() =>
  warehouses.find(w => w.id === selectedWarehouseId.value)
)

const racksToShow = computed(() => {
  const rs = selectedWarehouse.value?.racks || []
  return selectedRackId.value === 'ALL' ? rs : rs.filter(r => r.id === selectedRackId.value)
})

const legend = [
  { key: 'ok',          label: 'Đúng vị trí, có hàng',  hex: '#22c55e' },
  { key: 'low',         label: 'Dưới min',              hex: '#eab308' },
  { key: 'near_expire', label: 'Gần hết hạn',           hex: '#fb923c' },
  { key: 'wrong',       label: 'Sai part',              hex: '#ef4444' },
  { key: 'empty',       label: 'Trống',                 hex: '#cbd5e1' },
]
</script>

<template>
  <CCard class="shadow-sm">
    <CCardHeader class="d-flex flex-wrap gap-2 align-items-center">
      <div class="fw-semibold">Warehouse</div>

      <CFormSelect
        v-model="selectedWarehouseId"
        :options="warehouses.map(w => ({ value: w.id, label: w.name }))"
        class="w-auto ms-2"
        @change="selectedRackId = 'ALL'"
      />

      <!-- ✅ có thêm ALL -->
      <CFormSelect
        v-if="selectedWarehouse?.racks?.length"
        v-model="selectedRackId"
        :options="[{value:'ALL',label:'ALL RACKS'}, ...selectedWarehouse.racks.map(r => ({value: r.id, label: r.name}))]"
        class="w-auto"
      />

      <!-- 🔎 Ô tìm kiếm -->
      <CFormInput
        v-model="searchText"
        placeholder="Tìm theo mã linh kiện"
        class="ms-2"
        style="max-width: 320px"
      />
      <small v-if="searchText" class="text-muted ms-1">Kết quả: {{ matchedIds.size }}</small>

      
      <div class="ms-auto d-flex align-items-center gap-2">
        <CBadge color="secondary">Legend</CBadge>
        <div class="d-flex flex-wrap gap-2">
          <span v-for="l in legend" :key="l.key" class="legend-item">
            <i class="legend-dot" :style="{ backgroundColor: l.hex }"></i>{{ l.label }}
          </span>
        </div>
      </div>
    </CCardHeader>

    <CCardBody>
        <CRow>
        <CCol md="7" lg="8">
            <template v-if="racksToShow.length">
            <RackView
                v-for="r in racksToShow"
                :key="r.id"
                :rack="r"
                :cols="25"
                :selected-slot-id="selectedSlotId"
                :matched-ids="matchedIds"    
                class="mb-3"
                @select-slot="onSelectSlot"
            />
            </template>
            <div v-else class="text-body-secondary">(Kho chưa có rack)</div>
        </CCol>

        <CCol md="5" lg="4">
            <InfoPanel :slot="selectedSlot" />
        </CCol>
        </CRow>
    </CCardBody>

  </CCard>
</template>

<style scoped>
.legend-item { display:inline-flex; align-items:center; gap:.4rem; font-size:.85rem; }
.legend-dot { width:14px; height:14px; border-radius:3px; display:inline-block; border:1px solid #e5e7eb; }
</style>