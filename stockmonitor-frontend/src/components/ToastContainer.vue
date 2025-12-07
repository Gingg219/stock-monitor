<template>
  <!-- Bootstrap toast container (top-right) -->
  <div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3" style="z-index: 2000; pointer-events: none;">
    <div class="toast-stack" style="min-width: 280px;">
      <transition-group name="toast-fade" tag="div">
        <div
          v-for="t in toasts"
          :key="t.id"
          class="toast show align-items-start"
          :class="toastBgClass(t.type)"
          role="alert"
          aria-live="assertive"
          aria-atomic="true"
          style="pointer-events: auto; margin-bottom: 0.75rem;"
        >
          <div class="d-flex">
            <div class="toast-body p-2" style="flex:1;">
              <div v-if="t.title" class="fw-semibold small mb-1">{{ t.title }}</div>
              <div class="small" v-html="t.message"></div>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" aria-label="Close" @click="remove(t.id)"></button>
          </div>
        </div>
      </transition-group>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useToastStore } from '@/stores/toast'

const store = useToastStore()
const toasts = computed(() => store.toasts)

function remove(id) { store.remove(id) }

// map type -> bootstrap background / text classes
function toastBgClass(type) {
  // prefer subtle bg + white close button: use bg-* + text-white for success/error, or bg-light for info
  if (type === 'success') return 'bg-success text-white'
  if (type === 'error') return 'bg-danger text-white'
  if (type === 'warn') return 'bg-warning text-dark'
  return 'bg-light text-dark'
}
</script>

<style scoped>
/* simple fade/slide for enter/leave */
.toast-fade-enter-active, .toast-fade-leave-active {
  transition: all .25s ease;
}
.toast-fade-enter-from {
  transform: translateX(12px);
  opacity: 0;
}
.toast-fade-enter-to {
  transform: translateX(0);
  opacity: 1;
}
.toast-fade-leave-from {
  transform: translateX(0);
  opacity: 1;
}
.toast-fade-leave-to {
  transform: translateX(12px);
  opacity: 0;
}

/* ensure toast body wraps nicely */
.toast .toast-body { padding: 0.5rem 0.75rem; }
</style>
