<script setup>
import SlotCell from './SlotCell.vue'
const props = defineProps({
  tier: { type: Object, required: true },
  cols: { type: Number, default: 25 },
  selectedSlotId: { type: [String, null], default: null },
  matchedIds: { type: Object, default: () => new Set() }
})
</script>

<template>
  <div class="tier-row">
    <div class="slots">
      <!-- chia nhóm 25 ô -->
      <div
        v-for="(chunk, idx) in chunkedSlots"
        :key="idx"
        class="slot-row"
        :style="{ gridTemplateColumns: `repeat(${cols}, 1fr)` }"
      >
        <SlotCell
          v-for="s in chunk"
          :key="s.id"
          :slot-data="s"
          :selected="selectedSlotId === s.id"
          :matched="matchedIds.has(s.id)"
          @click="$emit('select-slot', s)"
        />
      </div>
    </div>
    <div class="tier-label">{{ tier.name }}</div>
  </div>
</template>

<script>
export default {
  computed: {
    chunkedSlots() {
      const size = this.cols
      const slots = this.tier.slots
      const groups = []
      for (let i = 0; i < slots.length; i += size)
        groups.push(slots.slice(i, i + size))
      return groups
    },
  },
}
</script>

<style scoped>
.tier-row {
  display: grid;
  grid-template-columns: 1fr 48px;
  border-bottom: 2px solid #cbd5e1;
}
.slot-row {
  display: grid;
  gap: 4px;                              /* ✅ tạo khe hở bằng gap (không padding) */
  border-right: 2px solid #cbd5e1;
  padding: 6px 6px;                      /* khoảng cách viền khung */
}
.tier-label {
  display:flex; align-items:center; justify-content:center;
  font-weight:600; color:#64748b; background:#f1f5f9;
}
</style>