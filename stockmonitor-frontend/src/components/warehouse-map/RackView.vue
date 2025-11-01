<script setup>
import { CCard, CCardBody, CCardHeader } from '@coreui/vue'
import TierRow from './TierRow.vue'

const props = defineProps({
  rack: { type: Object, required: true },
  cols: { type: Number, default: 25 },
  selectedSlotId: { type: [String, null], default: null },
  matchedIds: { type: Object, default: () => new Set() }

})
</script>

<template>
  <CCard class="rack-card">
    <CCardHeader class="fw-semibold">{{ rack.name }}</CCardHeader>
    <CCardBody>
      <div class="rack-frame">
        <div class="rack-inner">
          <TierRow
            v-for="t in rack.tiers"
            :key="t.id"
            :tier="t"
            :cols="cols"
            :selected-slot-id="selectedSlotId"
            :matched-ids="matchedIds"
            @select-slot="$emit('select-slot', $event)"
          />
        </div>
      </div>
    </CCardBody>
  </CCard>
</template>

<style scoped>
.rack-card { border: 1px solid #e5e7eb; }
.rack-frame { border: 2px solid #94a3b8; border-radius: 8px; padding: 8px; background: #f8fafc; }
.rack-inner { border: 2px solid #cbd5e1; border-radius: 6px; overflow: hidden; }
</style>