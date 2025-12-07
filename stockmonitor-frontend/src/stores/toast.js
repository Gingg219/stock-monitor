// src/stores/toast.js
import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
  const toasts = ref([]) // { id, type, title, message, duration, timeoutId }

  function genId() {
    return Date.now().toString(36) + Math.random().toString(36).slice(2,7)
  }

  function push({ type = 'info', title = '', message = '', duration = 5000 } = {}) {
    const id = genId()
    const t = { id, type, title, message, duration, timeoutId: null }
    toasts.value.push(t)
    if (duration > 0) {
      t.timeoutId = setTimeout(() => remove(id), duration)
    }
    return id
  }

  function remove(id) {
    const idx = toasts.value.findIndex(x => x.id === id)
    if (idx === -1) return
    const t = toasts.value[idx]
    if (t.timeoutId) clearTimeout(t.timeoutId)
    toasts.value.splice(idx, 1)
  }

  function clearAll() {
    toasts.value.forEach(t => t.timeoutId && clearTimeout(t.timeoutId))
    toasts.value.splice(0)
  }

  return { toasts, push, remove, clearAll }
})
