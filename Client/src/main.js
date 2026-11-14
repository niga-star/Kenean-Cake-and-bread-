import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import './style.css';
import App from './App.vue';
import { useThemeStore } from './stores/theme';
import { useLocaleStore } from './stores/locale';
import { vReveal } from './directives/vReveal';

const app = createApp(App);
const pinia = createPinia();
app.use(pinia);
app.use(router);

// Register Global Directives
app.directive('reveal', vReveal);

// Initialize theme and language from persisted localStorage
const themeStore = useThemeStore(pinia);
themeStore.initTheme();

const localeStore = useLocaleStore(pinia);
localeStore.initLocale();

// Register PWA Service Worker for Offline Resilience
if ('serviceWorker' in navigator && typeof window !== 'undefined') {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch((err) => {
      console.warn('PWA SW registration notice:', err);
    });
  });
}

app.mount('#app');
