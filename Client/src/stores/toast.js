import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useToastStore = defineStore('toast', () => {
  const toasts = ref([]);

  const show = (message, type = 'success', duration = 4000, action = null) => {
    if (!message) return null;
    const msgString = typeof message === 'string' ? message.trim() : String(message);
    if (!msgString) return null;

    // Deduplication guard: prevent identical active toast spam within active window
    const existing = toasts.value.find(t => t.message === msgString && t.type === type);
    if (existing) {
      if (existing.timer) clearTimeout(existing.timer);
      existing.timer = setTimeout(() => remove(existing.id), duration);
      return existing.id;
    }

    const id = Date.now() + Math.random().toString(36).substring(2, 6);
    const timer = setTimeout(() => {
      remove(id);
    }, duration);

    toasts.value.push({ id, message: msgString, type, action, timer });
    return id;
  };

  const success = (message, action = null) => show(message, 'success', 3800, action);
  const error = (message, action = null) => show(message, 'error', 5200, action);
  const warning = (message, action = null) => show(message, 'warning', 4500, action);
  const info = (message, action = null) => show(message, 'info', 4000, action);

  const remove = (id) => {
    const item = toasts.value.find(t => t.id === id);
    if (item && item.timer) clearTimeout(item.timer);
    toasts.value = toasts.value.filter(t => t.id !== id);
  };

  const clear = () => {
    toasts.value.forEach(t => {
      if (t.timer) clearTimeout(t.timer);
    });
    toasts.value = [];
  };

  return {
    toasts,
    show,
    success,
    error,
    warning,
    info,
    remove,
    clear,
  };
});
