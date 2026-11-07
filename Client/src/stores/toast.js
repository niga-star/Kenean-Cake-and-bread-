import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useToastStore = defineStore('toast', () => {
  const toasts = ref([]);

  const show = (message, type = 'success', duration = 3800, action = null) => {
    const id = Date.now() + Math.random().toString(36).substring(2, 6);
    toasts.value.push({ id, message, type, action });

    setTimeout(() => {
      remove(id);
    }, duration);
  };

  const success = (message, action = null) => show(message, 'success', 3800, action);
  const error = (message, action = null) => show(message, 'error', 4800, action);
  const warning = (message, action = null) => show(message, 'warning', 4200, action);
  const info = (message, action = null) => show(message, 'info', 3800, action);

  const remove = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
  };

  return {
    toasts,
    show,
    success,
    error,
    warning,
    info,
    remove,
  };
});
