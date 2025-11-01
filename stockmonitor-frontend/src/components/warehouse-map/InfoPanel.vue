<template>
  <CCard class="shadow-sm">
    <CCardHeader class="fw-semibold">Chi tiết vị trí</CCardHeader>
    <CCardBody v-if="slot && slot.parts?.length">
    <div class="mb-2"><strong>Slot:</strong> {{ slot.id }}</div>

    <div class="list-group">
        <div v-for="(p,i) in slot.parts" :key="i"
            class="list-group-item d-flex flex-column gap-1 py-2">
        <div><span class="text-body-secondary">Part:</span> <strong>{{ p.part_no }}</strong></div>
        <div class="d-flex flex-wrap gap-3">
            <div class="mb-2"><span class="text-body-secondary">Qty:</span> <strong>{{ p.qty }}</strong></div>
            <div class="mb-2"><span class="text-body-secondary">Expiry:</span> <strong>{{ p.expiry || '-' }}</strong></div>
            <div class="mb-2"><span class="text-body-secondary">Type:</span> <strong>{{ p.type || '-' }}</strong></div>
            <div>
            <CBadge color="secondary" class="me-1">Status</CBadge>
            <CBadge :color="badgeColor(p.status)">{{ p.status }}</CBadge>
            </div>
        </div>
        </div>
    </div>
    </CCardBody>
    <CCardBody v-else class="text-body-secondary">
      Click 1 ô bên trái để xem chi tiết.
    </CCardBody>
  </CCard>
</template>

<script setup>
import { CCard, CCardBody, CCardHeader, CBadge } from '@coreui/vue'
const props = defineProps({ slot: Object })
const badgeColor = (s) => ({
  ok: 'success', low: 'warning', near_expire: 'info', wrong: 'danger', empty: 'secondary'
}[s] || 'secondary')
</script>


<!-- <script setup>
import { CCard, CCardBody, CCardHeader, CBadge } from '@coreui/vue'
const props = defineProps({
  slot: { type: Object, default: null }
})
</script>

<template>
  <CCard class="shadow-sm">
    <CCardHeader class="fw-semibold">Chi tiết vị trí</CCardHeader>
    <CCardBody v-if="slot">
      <div class="mb-2"><span class="text-body-secondary">Slot:</span> <strong>{{ slot.id }}</strong></div>
      <div class="mb-2"><span class="text-body-secondary">Part:</span> <strong>{{ slot.part_no || '-' }}</strong></div>
      <div class="mb-2"><span class="text-body-secondary">Qty:</span> <strong>{{ slot.qty ?? 0 }}</strong></div>
      <div class="mb-2"><span class="text-body-secondary">Expiry:</span> <strong>{{ slot.expiry || '-' }}</strong></div>
      <div class="mt-3">
        <CBadge color="secondary" class="me-2">Trạng thái</CBadge>
        <CBadge :color="badgeColor(slot.status)">{{ slot.status }}</CBadge>
      </div>
    </CCardBody>

    <CCardBody v-else class="text-body-secondary">
      Click 1 ô bên trái để xem chi tiết.
    </CCardBody>
  </CCard>
</template>

<script>
export default {
  methods: {
    badgeColor(s) {
      switch (s) {
        case 'ok': return 'success'
        case 'low': return 'warning'
        case 'near_expire': return 'info'
        case 'wrong': return 'danger'
        default: return 'secondary'
      }
    }
  }
}
</script> -->