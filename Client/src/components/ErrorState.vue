<template>
  <div class="text-center py-12 sm:py-16 px-4 rounded-3xl bg-amber-50/40 dark:bg-[#20140E]/80 border border-amber-200/60 dark:border-[#4A2E1E] max-w-lg mx-auto space-y-4 shadow-xs">
    <!-- Warning/Error Icon Badge -->
    <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-3xl bg-white dark:bg-[#2A1810] border border-amber-200 dark:border-[#553522] shadow-md flex items-center justify-center text-3xl sm:text-4xl text-amber-600">
      <span>⚠️</span>
    </div>

    <!-- Title & Description -->
    <div class="space-y-1.5 px-2">
      <h3 class="font-serif font-bold text-lg sm:text-xl text-[#2A1810] dark:text-[#FAF3EE] leading-tight">
        {{ title || (isAmharic ? 'መረጃውን መጫን አልተቻለም' : 'Unable to Load Content') }}
      </h3>
      <p class="text-xs sm:text-sm text-[#786B60] dark:text-[#C5B7AF] leading-relaxed max-w-sm mx-auto">
        {{ message || (isAmharic ? 'የኔትወርክ ግንኙነትዎን ያረጋግጡ እና እባክዎ እንደገና ይሞክሩ።' : 'Please check your internet connection or try again.') }}
      </p>
    </div>

    <!-- Retry Button -->
    <div class="pt-2">
      <button
        @click="handleRetry"
        :disabled="isRetrying"
        class="inline-flex items-center space-x-2 px-6 py-2.5 min-h-[42px] text-xs sm:text-sm font-bold bg-[#DE1B54] hover:bg-[#C51345] text-white rounded-full shadow-md hover:shadow-lg transition-all transform hover:scale-105 active:scale-95 disabled:opacity-60 cursor-pointer"
      >
        <span :class="{ 'animate-spin': isRetrying }">🔄</span>
        <span>{{ isRetrying ? (isAmharic ? 'በመሞከር ላይ...' : 'Retrying...') : (retryLabel || (isAmharic ? 'እንደገና ይሞክሩ' : 'Retry Again')) }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

defineProps({
  title: { type: String, default: '' },
  message: { type: String, default: '' },
  retryLabel: { type: String, default: '' },
});

const emit = defineEmits(['retry']);
const isRetrying = ref(false);

const isAmharic = computed(() => {
  return typeof document !== 'undefined' && document.documentElement.lang === 'am';
});

function handleRetry() {
  if (isRetrying.value) return;
  isRetrying.value = true;
  emit('retry');
  setTimeout(() => {
    isRetrying.value = false;
  }, 1000);
}
</script>

