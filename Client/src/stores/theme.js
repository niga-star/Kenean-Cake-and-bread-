import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useThemeStore = defineStore('theme', () => {
  const currentTheme = ref(localStorage.getItem('sweetbakes_theme') || 'light');

  const isDark = computed(() => currentTheme.value === 'dark');

  const applyTheme = (theme) => {
    currentTheme.value = theme;
    localStorage.setItem('sweetbakes_theme', theme);

    if (theme === 'dark') {
      document.documentElement.classList.add('dark');
      document.documentElement.classList.remove('light');
    } else {
      document.documentElement.classList.remove('dark');
      document.documentElement.classList.add('light');
    }
  };

  const toggleTheme = () => {
    applyTheme(isDark.value ? 'light' : 'dark');
  };

  const initTheme = () => {
    const saved = localStorage.getItem('sweetbakes_theme');
    if (saved === 'dark' || saved === 'light') {
      applyTheme(saved);
    } else {
      // Default is light as requested
      applyTheme('light');
    }
  };

  return {
    currentTheme,
    isDark,
    toggleTheme,
    applyTheme,
    initTheme,
  };
});
