<script setup>
import { computed } from 'vue'


const props = defineProps({
  slotData: { type: Object, required: true }, // { id, parts: [] }
  selected: { type: Boolean, default: false },
  matched:  { type: Boolean, default: false }

})


const colorMap = {
  ok: '#22c55e', low: '#eab308', near_expire: '#fb923c',
  wrong: '#ef4444', empty: '#cbd5e1',
}


// Lấy màu theo mức “xấu nhất”
const priority = (s) => ['wrong','near_expire','low','ok','empty'].indexOf(s)
const slotColor = computed(() => {
  const parts = props.slotData?.parts || []
  if (!parts.length) return colorMap.empty
  let pick = 'ok'
  for (const p of parts) if (priority(p.status) < priority(pick)) pick = p.status
  return colorMap[pick]
})


// Số vị trí hiển thị trong ô (lấy đoạn cuối của ID, ví dụ A-T3-24 -> 24)
const labelShort = computed(() => {
  const id = props.slotData?.id ?? ''
  const seg = id.split('-')
  return seg[seg.length - 1] || id
})
</script>


<template>
  <div class="cell-wrapper">
    <div
      class="slot-cell"
      :class="{ selected, matched }" 
      :style="{ backgroundColor: slotColor }"
      :title="slotData.id"              
      role="button"
      @click="$emit('click')"
    >
      <span class="slot-label">{{ labelShort }}</span>
    </div>
  </div>
</template>


<style scoped>
.cell-wrapper {
  border-right: 1px solid #e5e7eb;
  padding: 3px;
}

/* Ô slot */
.slot-cell {
  aspect-ratio: 1 / 1;                /* ✅ làm ô vuông */
  width: 100%;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  background-clip: padding-box;
  transition: all 0.1s ease-in-out;
}

/* Hiệu ứng hover & chọn */
.slot-cell:hover {
  transform: scale(1.05);
}
.slot-cell.selected {
  outline: 3px solid #0ea5e9;
  box-shadow: 0 0 0 2px rgba(14,165,233,0.25);
  border-color: #0284c7;
}

/* 🔆 Highlight khi khớp tìm kiếm */
@keyframes pulseMatch { 0%{ box-shadow:0 0 0 0 rgba(34,197,94,.65) } 100%{ box-shadow:0 0 0 10px rgba(34,197,94,0) } }
.slot-cell.matched {
  outline: 2px dashed #22c55e;
  animation: pulseMatch 1.2s ease-out infinite;
}

/* Số vị trí trong ô */
.slot-label {
  font-size: 11px;
  font-weight: 700;
  color: #111827;
  text-shadow: 0 1px 0 rgba(255,255,255,0.6);
  user-select: none;
}
</style>
