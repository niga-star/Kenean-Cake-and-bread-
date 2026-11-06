import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useToastStore = defineStore('toast', () => {
  const toasts = ref([]);

  const show = (message, type = 'success', duration = 3500) => {
    const id = Date.now() + Math.random().toString(36).substring(2, 6);
    toasts.value.push({ id, message, type });

    setTimeout(() => {
      remove(id);
    }, duration);
  };

  const success = (message) => show(message, 'success');
  const error = (message) => show(message, 'error');
  const info = (message) => show(message, 'info');

  const remove = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
  };

  return {
    toasts,
    show,
    success,
    error,
    info,
    remove,
  };
});
