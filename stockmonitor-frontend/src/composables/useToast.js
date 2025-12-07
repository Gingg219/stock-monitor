// src/composables/useToast.js
import { useToastStore } from '@/stores/toast'

export function useToast() {
  const store = useToastStore()

  function show(type, message, title = '', duration = 5000) {
    return store.push({ type, message, title, duration })
  }
  return {
    info: (msg, title = '', duration = 5000) => show('info', msg, title, duration),
    success: (msg, title = '', duration = 4000) => show('success', msg, title, duration),
    error: (msg, title = '', duration = 7000) => show('error', msg, title, duration),
    warn: (msg, title = '', duration = 6000) => show('warn', msg, title, duration),
    remove: store.remove,
    clear: store.clearAll,
  }
}
